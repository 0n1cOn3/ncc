<?php

    namespace ncc\Classes\GithubExtension;

    use ncc\Abstracts\HttpRequestType;
    use ncc\Abstracts\Versions;
    use ncc\Classes\HttpClient;
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
    use ncc\Objects\RepositoryQueryResults;
    use ncc\Objects\Vault\Entry;
    use ncc\ThirdParty\jelix\Version\VersionComparator;
    use ncc\Utilities\Functions;

    class GithubService implements  RepositorySourceInterface
    {
        /**
         * Returns the git repository url of the repository, versions cannot be specified.
         *
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return RepositoryQueryResults
         * @throws AuthenticationException
         * @throws GithubServiceException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         */
        public static function getGitRepository(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): RepositoryQueryResults
        {
            $httpRequest = new HttpRequest();
            $protocol = ($definedRemoteSource->SSL ? "https" : "http");
            $owner_f = str_ireplace("/", "%2F", $packageInput->Vendor);
            $owner_f = str_ireplace(".", "%2F", $owner_f);
            $repository = urlencode($packageInput->Package);
            $httpRequest->Url = $protocol . '://' . $definedRemoteSource->Host . "/repos/$owner_f/$repository";
            $response_decoded = self::getJsonResponse($httpRequest, $entry);

            $query = new RepositoryQueryResults();
            $query->Files->GitSshUrl = ($response_decoded['ssh_url'] ?? null);
            $query->Files->GitHttpUrl = ($response_decoded['clone_url'] ?? null);
            $query->Version = Functions::convertToSemVer($response_decoded['default_branch'] ?? null);
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
         * @throws GithubServiceException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         * @throws VersionNotFoundException
         */
        public static function getRelease(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): RepositoryQueryResults
        {
            return self::processReleases($packageInput, $definedRemoteSource, $entry);
        }

        /**
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return RepositoryQueryResults
         * @throws AuthenticationException
         * @throws GithubServiceException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         * @throws VersionNotFoundException
         */
        public static function getNccPackage(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry = null): RepositoryQueryResults
        {
            return self::processReleases($packageInput, $definedRemoteSource, $entry);
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
            $response_decoded = self::getJsonResponse($httpRequest, $entry);

            if(count($response_decoded) == 0)
                return [];

            $return = [];
            foreach($response_decoded as $release)
            {
                $query_results = new RepositoryQueryResults();
                $query_results->Version = Functions::convertToSemVer($release['tag_name']);
                $query_results->ReleaseName = $release['name'];
                $query_results->ReleaseDescription = $release['body'];
                $query_results->Files->ZipballUrl = ($release['zipball_url'] ?? null);
                $query_results->Files->TarballUrl = ($release['tarball_url'] ?? null);

                if(isset($release['assets']))
                {
                    foreach($release['assets'] as $asset)
                    {
                        $parsed_asset = self::parseAsset($asset);
                        if($parsed_asset !== null)
                            $query_results->Files->PackageUrl = $parsed_asset;
                    }
                }

                $return[$query_results->Version] = $query_results;
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

        /**
         * @param HttpRequest $httpRequest
         * @param Entry|null $entry
         * @return array
         * @throws AuthenticationException
         * @throws GithubServiceException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         */
        private static function getJsonResponse(HttpRequest $httpRequest, ?Entry $entry): array
        {
            $httpRequest->Type = HttpRequestType::GET;
            $httpRequest = Functions::prepareGitServiceRequest($httpRequest, $entry, false);
            $httpRequest->Headers[] = 'X-GitHub-Api-Version: 2022-11-28';
            $httpRequest->Headers[] = 'Accept: application/vnd.github+json';

            $response = HttpClient::request($httpRequest, true);

            if ($response->StatusCode != 200)
                throw new GithubServiceException(sprintf('Failed to fetch releases for the given repository. Status code: %s', $response->StatusCode));

            $response_decoded = Functions::loadJson($response->Body, Functions::FORCE_ARRAY);
            return $response_decoded;
        }

        /**
         * @param RemotePackageInput $packageInput
         * @param DefinedRemoteSource $definedRemoteSource
         * @param Entry|null $entry
         * @return mixed
         * @throws AuthenticationException
         * @throws GithubServiceException
         * @throws GitlabServiceException
         * @throws HttpException
         * @throws MalformedJsonException
         * @throws VersionNotFoundException
         */
        private static function processReleases(RemotePackageInput $packageInput, DefinedRemoteSource $definedRemoteSource, ?Entry $entry): mixed
        {
            $releases = self::getReleases($packageInput, $definedRemoteSource, $entry);

            if (count($releases) === 0)
                throw new VersionNotFoundException('No releases found for the given repository.');

            if ($packageInput->Version == Versions::Latest)
            {
                $latest_version = null;
                foreach ($releases as $release)
                {
                    if ($latest_version == null)
                    {
                        $latest_version = $release->Version;
                        continue;
                    }

                    if (VersionComparator::compareVersion($release->Version, $latest_version) == 1)
                        $latest_version = $release->Version;
                }

                return $releases[$latest_version];
            }

            // Query a specific version
            if (!isset($releases[$packageInput->Version]))
            {
                // Find the closest thing to the requested version
                $selected_version = null;
                foreach ($releases as $version => $url)
                {
                    if ($selected_version == null)
                    {
                        $selected_version = $version;
                        continue;
                    }

                    if (VersionComparator::compareVersion($version, $packageInput->Version) == 1)
                        $selected_version = $version;
                }

                if ($selected_version == null)
                    throw new VersionNotFoundException('No releases found for the given repository.');
            }
            else
            {
                $selected_version = $packageInput->Version;
            }

            if (!isset($releases[$selected_version]))
                throw new VersionNotFoundException(sprintf('No releases found for the given repository. (Selected version: %s)', $selected_version));

            return $releases[$selected_version];
        }
    }