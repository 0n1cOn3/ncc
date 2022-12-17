<?php

    namespace ncc\Classes\GitlabExtension;

    use ncc\Abstracts\Versions;
    use ncc\Classes\HttpClient;
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
    use ncc\Objects\RepositoryQueryResults;
    use ncc\Objects\Vault\Entry;
    use ncc\ThirdParty\jelix\Version\VersionComparator;
    use ncc\Utilities\Functions;

    class GitlabService implements RepositorySourceInterface
    {
        /**
         * Attempts to return the gitRepositoryUrl of a release, cannot specify a version.
         * This needs to be done using git
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return RepositoryQueryResults
         * @throws AuthenticationException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         */
        public static function getGitRepository(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry=null): RepositoryQueryResults
        {
            $httpRequest = new HttpRequest();
            $protocol = ($definedRemoteSource->SSL ? "https" : "http");
            $owner_f = str_ireplace("/", "%2F", $packageInput->Vendor);
            $owner_f = str_ireplace(".", "%2F", $owner_f);
            $project_f = str_ireplace("/", "%2F", $packageInput->Package);
            $project_f = str_ireplace(".", "%2F", $project_f);
            $httpRequest->Url = $protocol . '://' . $definedRemoteSource->Host . "/api/v4/projects/$owner_f%2F$project_f";
            $httpRequest = Functions::prepareGitServiceRequest($httpRequest, $entry);

            $response = HttpClient::request($httpRequest, true);

            if($response->StatusCode != 200)
                throw new GitlabServiceException(sprintf('Failed to fetch releases for the given repository. Status code: %s', $response->StatusCode));

            $response_decoded = Functions::loadJson($response->Body, Functions::FORCE_ARRAY);

            $query = new RepositoryQueryResults();
            $query->Files->GitSshUrl = ($response_decoded['ssh_url_to_repo'] ?? null);
            $query->Files->GitHttpUrl = ($response_decoded['http_url_to_repo'] ?? null);
            $query->Version = Functions::convertToSemVer($response_decoded['default_branch']) ?? null;
            $query->ReleaseDescription = ($response_decoded['description'] ?? null);
            $query->ReleaseName = ($response_decoded['name'] ?? null);

            return $query;
        }

        /**
         * Returns the download URL of the requested version of the package.
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return RepositoryQueryResults
         * @throws AuthenticationException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         * @throws VersionNotFoundException
         */
        public static function getRelease(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): RepositoryQueryResults
        {
            $releases = self::getReleases($packageInput->Vendor, $packageInput->Package, $definedRemoteSource, $entry);

            if(count($releases) === 0)
                throw new VersionNotFoundException('No releases found for the given repository.');

            // Query the latest package only
            if($packageInput->Version == Versions::Latest)
            {
                $latest_version = null;
                foreach($releases as $release)
                {
                    if($latest_version == null)
                    {
                        $latest_version = $release->Version;
                        continue;
                    }

                    if(VersionComparator::compareVersion($release->Version, $latest_version) == 1)
                        $latest_version = $release->Version;
                }

                return $releases[$latest_version];
            }

            // Query a specific version
            if(!isset($releases[$packageInput->Version]))
            {
                // Find the closest thing to the requested version
                $selected_version = null;
                foreach($releases as $version => $url)
                {
                    if($selected_version == null)
                    {
                        $selected_version = $version;
                        continue;
                    }

                    if(VersionComparator::compareVersion($version, $packageInput->Version) == 1)
                        $selected_version = $version;
                }

                if($selected_version == null)
                    throw new VersionNotFoundException('No releases found for the given repository.');
            }
            else
            {
                $selected_version = $packageInput->Version;
            }

            if(!isset($releases[$selected_version]))
                throw new VersionNotFoundException(sprintf('No releases found for the given repository. (Selected version: %s)', $selected_version));

            return $releases[$selected_version];
        }

        /**
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return RepositoryQueryResults
         * @throws NotSupportedException
         */
        public static function getNccPackage(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): RepositoryQueryResults
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
            $repository_f = str_ireplace("/", "%2F", $repository);
            $repository_f = str_ireplace(".", "%2F", $repository_f);

            $httpRequest->Url = $protocol . '://' . $definedRemoteSource->Host . "/api/v4/projects/$owner_f%2F$repository_f/releases";
            $httpRequest = Functions::prepareGitServiceRequest($httpRequest, $entry);

            $response = HttpClient::request($httpRequest, true);

            if($response->StatusCode != 200)
               throw new GitlabServiceException(sprintf('Failed to fetch releases for the given repository. Status code: %s', $response->StatusCode));

            $response_decoded = Functions::loadJson($response->Body, Functions::FORCE_ARRAY);

            if(count($response_decoded) == 0)
                return [];

            $return = [];
            foreach($response_decoded as $release)
            {
                $query_results = new RepositoryQueryResults();
                $query_results->ReleaseName = ($release['name'] ?? null);
                $query_results->ReleaseDescription = ($release['description'] ?? null);
                $query_results->Version = Functions::convertToSemVer($release['tag_name']);

                if(isset($release['assets']) && isset($release['assets']['sources']))
                {
                    if(count($release['assets']['sources']) > 0)
                    {
                        foreach($release['assets']['sources'] as $source)
                        {
                            if($source['format'] == 'zip')
                            {
                                $query_results->Files->ZipballUrl = $source['url'];
                                break;
                            }

                            if($source['format'] == 'tar.gz')
                            {
                                $query_results->Files->ZipballUrl = $source['url'];
                                break;
                            }

                            if($source['format'] == 'ncc')
                            {
                                $query_results->Files->PackageUrl = $source['url'];
                                break;
                            }
                        }
                    }
                }

                $return[$query_results->Version] = $query_results;
            }

            return $return;
        }
    }