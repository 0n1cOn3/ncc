<?php

    namespace ncc\Classes\GitlabExtension;

    use Exception;
    use ncc\Abstracts\Versions;
    use ncc\Classes\GitClient;
    use ncc\Classes\HttpClient;
    use ncc\Classes\NccExtension\PackageCompiler;
    use ncc\Exceptions\AuthenticationException;
    use ncc\Exceptions\GitlabServiceException;
    use ncc\Exceptions\HttpException;
    use ncc\Exceptions\MalformedJsonException;
    use ncc\Exceptions\NotSupportedException;
    use ncc\Exceptions\VersionNotFoundException;
    use ncc\Interfaces\RepositorySourceInterface;
    use ncc\Objects\DefinedRemoteSource;
    use ncc\Objects\HttpRequest;
    use ncc\Objects\RemotePackageInput;
    use ncc\Objects\Vault\Entry;
    use ncc\ThirdParty\jelix\Version\VersionComparator;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;

    class GitlabService implements RepositorySourceInterface
    {

        /**
         * Attempts to fetch the requested package from the Gitlab repository, and returns the pre-compiled package
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         * @throws GitlabServiceException
         */
        public static function fetch(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry): string
        {
            // Check if the specified version is a release
            try
            {
                Console::outVerbose(sprintf('Attempting to fetch source code from %s', $definedRemoteSource->Host));
                $release_url = self::getRelease($packageInput, $definedRemoteSource, $entry);
            }
            catch(Exception $e)
            {
                $release_url = null;
                unset($e);
            }

            // If the specified version is a release, download the source code
            if($release_url !== null)
            {
                try
                {
                    $release_file = Functions::downloadGitServiceFile($release_url, $entry);
                    $project_path = Functions::extractArchive($release_file);
                    return PackageCompiler::tryCompile($project_path);
                }
                catch(Exception $e)
                {
                    throw new GitlabServiceException(sprintf('Failed to download release from %s', $definedRemoteSource->Host), $e);
                }
            }

            try
            {
                Console::outVerbose(sprintf('Attempting to fetch git repository from %s', $definedRemoteSource->Host));
                $git_url = self::fetchGitUri($packageInput, $definedRemoteSource, $entry);
            }
            catch(Exception $e)
            {
                $git_url = null;
                unset($e);
            }

            if($git_url !== null)
            {
                try
                {
                    $project_path = GitClient::cloneRepository($git_url);

                    foreach(GitClient::getTags($project_path) as $tag)
                    {
                        $tag = str_replace('v', '', $tag);
                        if(VersionComparator::compareVersion($tag, $packageInput->Version) === 0)
                        {
                            GitClient::checkout($project_path, $tag);
                            return PackageCompiler::tryCompile($project_path);
                        }
                    }
                }
                catch(Exception $e)
                {
                    throw new GitlabServiceException(sprintf('Failed to clone git repository from %s', $definedRemoteSource->Host), $e);
                }
            }

            throw new GitlabServiceException('Unable to fetch package from remote source');
        }

        /**
         * Attempts to return the gitRepositoryUrl of a release, cannot specify a version.
         * This needs to be done using git
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         * @throws AuthenticationException
         */
        public static function fetchGitUri(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry=null): string
        {
            $httpRequest = new HttpRequest();
            $protocol = ($definedRemoteSource->SSL ? "https" : "http");
            $owner_f = str_ireplace("/", "%2F", $packageInput->Vendor);
            $owner_f = str_ireplace(".", "%2F", $owner_f);
            $repository = urlencode($packageInput->Package);
            $httpRequest->Url = $protocol . '://' . $definedRemoteSource->Host . "/api/v4/projects/$owner_f%2F$repository";
            $httpRequest = Functions::prepareGitServiceRequest($httpRequest, $entry);

            $response = HttpClient::request($httpRequest);

            if($response->StatusCode != 200)
                throw new GitlabServiceException(sprintf('Failed to fetch releases for the given repository. Status code: %s', $response->StatusCode));

            $response_decoded = Functions::loadJson($response->Body, Functions::FORCE_ARRAY);

            return
                $response_decoded['http_url_to_repo'] ??
                $response_decoded['ssh_url_to_repo'] ??
                throw new GitlabServiceException('Failed to fetch the repository URL.');
        }

        /**
         * Returns the download URL of the requested version of the package.
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         * @throws AuthenticationException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         * @throws VersionNotFoundException
         */
        public static function getRelease(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): string
        {
            $releases = self::getReleases($packageInput->Vendor, $packageInput->Package, $definedRemoteSource, $entry);

            if(count($releases) === 0)
                throw new VersionNotFoundException('No releases found for the given repository.');

            if($packageInput->Version == Versions::Latest)
            {
                $latest_version = null;
                foreach($releases as $version => $url)
                {
                    if($latest_version == null)
                    {
                        $latest_version = $version;
                        continue;
                    }

                    if(VersionComparator::compareVersion($version, $latest_version) == 1)
                        $latest_version = $version;
                }

                return $releases[$latest_version];
            }

            if(!isset($releases[$packageInput->Version]))
                throw new VersionNotFoundException(sprintf('The given version "%s" does not exist.', $packageInput->Version));

            return $releases[$packageInput->Version];
        }

        /**
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         * @throws NotSupportedException
         */
        public static function getNccPackage(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): string
        {
            throw new NotSupportedException(sprintf('The given repository source "%s" does not support ncc packages.', $definedRemoteSource->Host));
        }

        /**
         * Returns an array of all the tags for the given owner and repository name.
         *
         * @param string $owner
         * @param string $repository
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return array
         * @throws AuthenticationException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         */
        private static function getReleases(string $owner, string $repository, DefinedRemoteSource $definedRemoteSource, ?Entry $entry): array
        {
            $httpRequest = new HttpRequest();
            $protocol = ($definedRemoteSource->SSL ? "https" : "http");
            $owner_f = str_ireplace("/", "%2F", $owner);
            $owner_f = str_ireplace(".", "%2F", $owner_f);
            $httpRequest->Url = $protocol . '://' . $definedRemoteSource->Host . "/api/v4/projects/$owner_f%2F$repository/releases";
            $httpRequest = Functions::prepareGitServiceRequest($httpRequest, $entry);

            $response = HttpClient::request($httpRequest);

            if($response->StatusCode != 200)
               throw new GitlabServiceException(sprintf('Failed to fetch releases for the given repository. Status code: %s', $response->StatusCode));

            $response_decoded = Functions::loadJson($response->Body, Functions::FORCE_ARRAY);

            if(count($response_decoded) == 0)
                return [];

            $return = [];
            foreach($response_decoded as $release)
            {
                // Make the tag_name version friendly
                $release_version = str_replace('v', '', $release['tag_name']);

                if(isset($release['assets']) && isset($release['assets']['sources']))
                {
                    if(count($release['assets']['sources']) > 0)
                    {
                        // Use the first source as the download url, if a tar.gz file is available, use that instead.
                        $return[$release_version] = $release['assets']['sources'][0]['url'];
                        foreach($release['assets']['sources'] as $source)
                        {
                            if($source['format'] == 'tar.gz')
                            {
                                $return[$release_version] = $source['url'];
                                break;
                            }
                        }
                    }
                }
            }

            return $return;
        }
    }