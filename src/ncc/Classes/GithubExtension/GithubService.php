<?php

    namespace ncc\Classes\GithubExtension;

    use Exception;
    use ncc\Abstracts\HttpRequestType;
    use ncc\Abstracts\Versions;
    use ncc\Classes\GitClient;
    use ncc\Classes\HttpClient;
    use ncc\Classes\NccExtension\PackageCompiler;
    use ncc\Exceptions\AuthenticationException;
    use ncc\Exceptions\GithubServiceException;
    use ncc\Exceptions\GitlabServiceException;
    use ncc\Exceptions\HttpException;
    use ncc\Exceptions\MalformedJsonException;
    use ncc\Exceptions\VersionNotFoundException;
    use ncc\Interfaces\RepositorySourceInterface;
    use ncc\Objects\DefinedRemoteSource;
    use ncc\Objects\HttpRequest;
    use ncc\Objects\RemotePackageInput;
    use ncc\Objects\Vault\Entry;
    use ncc\ThirdParty\jelix\Version\VersionComparator;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;

    class GithubService implements  RepositorySourceInterface
    {

        /**
         * Attempts to fetch the .ncc package from a remote source, optionally attempts to compile
         * the package if it cannot find a pre-compiled version.
         *
         * Priority of fetching:
         *  - Pre-compiled version
         *  - Source code of specified release
         *  - Git repository (checkout specified release)
         *  - Git repository master branch (If version is not specified or set to latest)
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         * @throws GithubServiceException
         */
        public static function fetch(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry): string
        {
            // Check if there is a pre-compiled version of the package available
            try
            {
                Console::outVerbose(sprintf('Attempting to fetch pre-compiled package from %s', $definedRemoteSource->Host));
                $ncc_package = self::getNccPackage($packageInput, $definedRemoteSource, $entry);
            }
            catch(Exception $e)
            {
                $ncc_package = null;
                unset($e);
            }

            if($ncc_package !== null)
            {
                try
                {
                    return Functions::downloadGitServiceFile($ncc_package, $entry);
                }
                catch(Exception $e)
                {
                    throw new GithubServiceException(sprintf('Failed to download pre-compiled package from %s', $definedRemoteSource->Host), $e);
                }
            }

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
                    throw new GithubServiceException(sprintf('Failed to download release from %s', $definedRemoteSource->Host), $e);
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
                    throw new GithubServiceException(sprintf('Failed to clone git repository from %s', $definedRemoteSource->Host), $e);
                }
            }

            throw new GithubServiceException('Unable to fetch package from remote source');
        }


        /**
         * Returns the git repository url of the repository, versions cannot be specified.
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         * @throws GithubServiceException
         * @throws GitlabServiceException
         * @throws AuthenticationException
         * @throws HttpException
         * @throws MalformedJsonException
         */
        public static function fetchGitUri(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): string
        {
            $httpRequest = new HttpRequest();
            $protocol = ($definedRemoteSource->SSL ? "https" : "http");
            $owner_f = str_ireplace("/", "%2F", $packageInput->Vendor);
            $owner_f = str_ireplace(".", "%2F", $owner_f);
            $repository = urlencode($packageInput->Package);
            $httpRequest->Url = $protocol . '://' . $definedRemoteSource->Host . "/repos/$owner_f/$repository";
            $httpRequest->Type = HttpRequestType::POST;
            $httpRequest = Functions::prepareGitServiceRequest($httpRequest, $entry);

            $response = HttpClient::request($httpRequest);

            if($response->StatusCode != 200)
                throw new GithubServiceException(sprintf('Failed to fetch releases for the given repository. Status code: %s', $response->StatusCode));

            $response_decoded = Functions::loadJson($response->Body, Functions::FORCE_ARRAY);

            return
                $response_decoded['git_url'] ??
                $response_decoded['clone_url'] ??
                $response_decoded['ssh_url'] ??
                throw new GithubServiceException('Failed to fetch the repository URL.');
        }

        /**
         * Returns the download URL of the requested version of the package.
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         * @throws AuthenticationException
         * @throws GithubServiceException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         * @throws VersionNotFoundException
         */
        public static function getRelease(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): string
        {
            $releases = self::getReleases($packageInput, $definedRemoteSource, $entry);

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

                return $releases[$latest_version]['url'];
            }

            if(!isset($releases[$packageInput->Version]))
                throw new VersionNotFoundException(sprintf('The given version "%s" does not exist.', $packageInput->Version));

            return $releases[$packageInput->Version]['url'];
        }

        /**
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return string
         * @throws AuthenticationException
         * @throws GithubServiceException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         * @throws VersionNotFoundException
         */
        public static function getNccPackage(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): string
        {
            $releases = self::getReleases($packageInput, $definedRemoteSource, $entry);

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

                return $releases[$latest_version]['package'];
            }

            if(!isset($releases[$packageInput->Version]))
                throw new VersionNotFoundException(sprintf('The given version "%s" does not exist.', $packageInput->Version));

            return $releases[$packageInput->Version]['package'];
        }

        /**
         * Returns a list of all releases of the given repository with their download URL.
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return array
         * @throws AuthenticationException
         * @throws GithubServiceException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         */
        private static function getReleases(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): array
        {
            $httpRequest = new HttpRequest();
            $protocol = ($definedRemoteSource->SSL ? "https" : "http");
            $owner_f = str_ireplace("/", "%2F", $packageInput->Vendor);
            $owner_f = str_ireplace(".", "%2F", $owner_f);
            $repository = urlencode($packageInput->Package);
            $httpRequest->Url = $protocol . '://' . $definedRemoteSource->Host . "/repos/$owner_f/$repository/releases";
            $httpRequest->Type = HttpRequestType::POST;
            $httpRequest = Functions::prepareGitServiceRequest($httpRequest, $entry);

            $response = HttpClient::request($httpRequest);

            if($response->StatusCode != 200)
                throw new GithubServiceException(sprintf('Failed to fetch releases for the given repository. Status code: %s', $response->StatusCode));

            $response_decoded = Functions::loadJson($response->Body, Functions::FORCE_ARRAY);

            if(count($response_decoded) == 0)
                return [];

            $return = [];
            foreach($response_decoded as $release)
            {
                // Make the tag_name version friendly
                $release_version = str_replace('v', '', $release['tag_name']);
                $return[$release_version] = [
                    'url' => ($release['tarball_url'] ?? $release['zipball_url'] ?? null)
                ];

                if(isset($release['assets']))
                {
                    foreach($release['assets'] as $asset)
                    {
                        if(self::parseAsset($asset) !== null)
                            $return[$release_version]['package'] = $asset['browser_download_url'];
                    }
                }
            }

            return $return;
        }

        /**
         * Returns the asset download URL if it points to a .ncc package.
         *
         * @param array $asset
         * @return string|null'
         */
        private static function parseAsset(array $asset): ?string
        {
            if(isset($asset['browser_download_url']))
            {
                $file_extension = pathinfo($asset['browser_download_url'], PATHINFO_EXTENSION);
                if($file_extension == 'ncc')
                    return $asset['browser_download_url'];
            }

            return null;
        }
    }