<?php
    /*
     * Copyright (c) Nosial 2022-2023, all rights reserved.
     *
     *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
     *  associated documentation files (the "Software"), to deal in the Software without restriction, including without
     *  limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
     *  Software, and to permit persons to whom the Software is furnished to do so, subject to the following
     *  conditions:
     *
     *  The above copyright notice and this permission notice shall be included in all copies or substantial portions
     *  of the Software.
     *
     *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
     *  INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
     *  PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
     *  LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
     *  OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
     *  DEALINGS IN THE SOFTWARE.
     *
     */

    namespace ncc\Classes\GiteaExtension;

    use CurlHandle;
    use Exception;
    use JsonException;
    use ncc\Enums\Types\AuthenticationType;
    use ncc\Enums\Types\HttpRequestType;
    use ncc\Enums\Types\RepositoryResultType;
    use ncc\Enums\Versions;
    use ncc\Exceptions\AuthenticationException;
    use ncc\Exceptions\NetworkException;
    use ncc\Interfaces\AuthenticationInterface;
    use ncc\Interfaces\RepositoryInterface;
    use ncc\Objects\RepositoryConfiguration;
    use ncc\Objects\RepositoryResult;
    use ncc\Objects\Vault\Password\AccessToken;
    use ncc\Objects\Vault\Password\UsernamePassword;
    use RuntimeException;

    class GiteaRepository implements RepositoryInterface
    {
        /**
         * @inheritDoc
         */
        public static function fetchSourceArchive(RepositoryConfiguration $repository, string $vendor, string $project, string $version = Versions::LATEST, ?AuthenticationType $authentication = null): RepositoryResult
        {
            try
            {
                return self::getReleaseArchive($repository, $vendor, $project, $version, $authentication);
            }
            catch(Exception $e)
            {
                unset($e);
            }

            return self::getTagArchive($repository, $vendor, $project, $version, $authentication);
        }

        /**
         * @inheritDoc
         */
        public static function fetchPackage(RepositoryConfiguration $repository, string $vendor, string $project, string $version = Versions::LATEST, ?AuthenticationType $authentication = null): RepositoryResult
        {
            return self::getReleasePackage($repository, $vendor, $project, $version, $authentication);
        }

        /**
         * Returns an array of tags for the specified group and project, usually
         * sorted by the most recent tag first if the server supports it.
         *
         * @param RepositoryConfiguration $repository The remote repository to make the request to
         * @param string $group The group to get the tags for (eg; "Nosial")
         * @param string $project The project to get the tags for (eg; "ncc" or "libs/config")
         * @param AuthenticationInterface|null $authentication Optional. The authentication to use. If null, No authentication will be used.
         * @return string[] An array of tags for the specified group and project
         * @throws AuthenticationException
         * @throws NetworkException
         */
        private static function getTags(RepositoryConfiguration $repository, string $group, string $project, ?AuthenticationInterface $authentication=null): array
        {
            $curl = curl_init();
            $endpoint = sprintf('%s://%s/api/v1/repos/%s/%s/tags', ($repository->isSsl() ? 'https' : 'http'), $repository->getHost(), rawurlencode($group), rawurlencode($project));
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: ncc'
            ];

            if($authentication !== null)
            {
                $headers = self::injectAuthentication($authentication, $curl, $headers);
            }

            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, HttpRequestType::GET);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $results = [];
            foreach(self::processHttpResponse($curl, $group, $project) as $tag)
            {
                if(isset($tag['name']))
                {
                    $results[] = $tag['name'];
                }
            }

            return $results;
        }

        /**
         * Returns the latest tag for the specified group and project.
         *
         * @param RepositoryConfiguration $repository The remote repository to make the request to
         * @param string $group The group to get the tag for (eg; "Nosial")
         * @param string $project The project to get the tag for (eg; "ncc" or "libs/config")
         * @param AuthenticationInterface|null $authentication Optional. The authentication to use. If null, No authentication will be used.
         * @return string The latest tag for the specified group and project
         * @throws AuthenticationException
         * @throws NetworkException
         */
        private static function getLatestTag(RepositoryConfiguration $repository, string $group, string $project, ?AuthenticationInterface $authentication=null): string
        {
            $results = self::getTags($repository, $group, $project, $authentication);

            if(count($results) === 0)
            {
                throw new NetworkException(sprintf('No tags found for %s/%s', $group, $project));
            }

            return $results[0];
        }

        /**
         * Returns a downloadable archive of the specified tag for the specified group and project.
         * The function will try to find a .zip archive first, and if it can't find one, it will
         * try to find a .tar.gz archive. If it can't find either, it will throw an exception.
         *
         * @param RepositoryConfiguration $repository
         * @param string $group The group to get the tag for (eg; "Nosial")
         * @param string $project The project to get the tag for (eg; "ncc" or "libs/config")
         * @param string $tag The tag to get the tag for (eg; "v1.0.0")
         * @param AuthenticationInterface|null $authentication Optional. The authentication to use. If null, No authentication will be used.
         * @return RepositoryResult The URL to the archive
         * @throws AuthenticationException
         * @throws NetworkException
         */
        private static function getTagArchive(RepositoryConfiguration $repository, string $group, string $project, string $tag, ?AuthenticationInterface $authentication=null): RepositoryResult
        {
            if($tag === Versions::LATEST)
            {
                $tag = self::getLatestTag($repository, $group, $project, $authentication);
            }

            $curl = curl_init();
            $endpoint = sprintf('%s://%s/api/v1/repos/%s/%s/tags/%s', ($repository->isSsl() ? 'https' : 'http'), $repository->getHost(), rawurlencode($group), rawurlencode($project), rawurlencode($tag));
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: ncc'
            ];

            if($authentication !== null)
            {
                $headers = self::injectAuthentication($authentication, $curl, $headers);
            }

            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, HttpRequestType::GET);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $response = self::processHttpResponse($curl, $group, $project);

            if(isset($response['zipball_url']))
            {
                return $response['zipball_url'];
            }

            if(isset($response['tarball_url']))
            {
                return $response['tarball_url'];
            }

            throw new NetworkException(sprintf('Failed to get tag archive %s url for %s/%s', $tag, $group, $project));
        }

        /**
         * Returns an array of tags for the specified group and project,
         * usually sorted by the most recent tag first if the server supports it.
         *
         * @param RepositoryConfiguration $repository The remote repository to make the request to
         * @param string $group The group to get the tags for (eg; "Nosial")
         * @param string $project The project to get the tags for (eg; "ncc" or "libs/config")
         * @param AuthenticationInterface|null $authentication Optional. The authentication to use. If null, No authentication will be used.
         * @return array An array of tag names for releases
         * @throws AuthenticationException
         * @throws NetworkException
         */
        private static function getReleases(RepositoryConfiguration $repository, string $group, string $project, ?AuthenticationInterface $authentication=null): array
        {
            $curl = curl_init();
            $endpoint = sprintf('%s://%s/api/v1/repos/%s/%s/releases', ($repository->isSsl() ? 'https' : 'http'), $repository->getHost(), rawurlencode($group), rawurlencode($project));
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: ncc'
            ];

            if($authentication !== null)
            {
                $headers = self::injectAuthentication($authentication, $curl, $headers);
            }

            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, HttpRequestType::GET);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $results = [];
            foreach(self::processHttpResponse($curl, $group, $project) as $release)
            {
                if(isset($release['tag_name']))
                {
                    $results[] = $release['tag_name'];
                }
            }

            return $results;
        }

        /**
         * Returns the latest release for the specified group and project.
         *
         * @param RepositoryConfiguration $repository The remote repository to make the request to
         * @param string $group The group to get the release for (eg; "Nosial")
         * @param string $project The project to get the release for (eg; "ncc" or "libs/config")
         * @param AuthenticationInterface|null $authentication Optional. The authentication to use. If null, No authentication will be used.
         * @return string The latest release for the specified group and project
         * @throws AuthenticationException
         * @throws NetworkException
         */
        private static function getLatestRelease(RepositoryConfiguration $repository, string $group, string $project, ?AuthenticationInterface $authentication=null): string
        {
            $results = self::getReleases($repository, $group, $project, $authentication);

            if(count($results) === 0)
            {
                throw new NetworkException(sprintf('No releases found for %s/%s', $group, $project));
            }

            return $results[0];
        }

        /**
         * Returns a downloadable ncc package of the specified release for the specified group and project.
         * If the function can't find a .ncc package, it will throw an exception.
         *
         * @param RepositoryConfiguration $repository The remote repository to make the request to
         * @param string $group The group to get the release for (eg; "Nosial")
         * @param string $project The project to get the release for (eg; "ncc" or "libs/config")
         * @param string $release The release to get the release for (eg; "v1.0.0")
         * @param AuthenticationInterface|null $authentication Optional. The authentication to use. If null, No authentication will be used.
         * @return RepositoryResult The URL to the archive
         * @throws AuthenticationException
         * @throws NetworkException
         */
        private static function getReleasePackage(RepositoryConfiguration $repository, string $group, string $project, string $release, ?AuthenticationInterface $authentication=null): RepositoryResult
        {
            /** @noinspection DuplicatedCode */
            if($release === Versions::LATEST)
            {
                $release = self::getLatestRelease($repository, $group, $project, $authentication);
            }

            $curl = curl_init();
            $endpoint = sprintf('%s://%s/api/v1/repos/%s/%s/releases/tags/%s', ($repository->isSsl() ? 'https' : 'http'), $repository->getHost(), rawurlencode($group), rawurlencode($project), rawurlencode($release));
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: ncc'
            ];

            if($authentication !== null)
            {
                $headers = self::injectAuthentication($authentication, $curl, $headers);
            }

            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, HttpRequestType::GET);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $response = self::processHttpResponse($curl, $group, $project);

            if(!isset($response['assets']))
            {
                throw new NetworkException(sprintf('Failed to get release %s package url for %s/%s', $release, $group, $project));
            }

            foreach($response['assets'] as $asset)
            {
                if(isset($asset['name'], $asset['browser_download_url']) && preg_match('/\.ncc$/', $asset['name']))
                {
                    return new RepositoryResult($asset['browser_download_url'], RepositoryResultType::PACKAGE, $release);
                }
            }

            throw new NetworkException(sprintf('No ncc package found for %s/%s/%s', $group, $project, $release));
        }

        /**
         * Returns a downloadable archive of the specified release for the specified group and project.
         * The function will try to find a .zip archive first, and if it can't find one, it will
         * try to find a .tar.gz archive. If it can't find either, it will throw an exception.
         *
         * @param RepositoryConfiguration $repository The remote repository to make the request to
         * @param string $group The group to get the release for (eg; "Nosial")
         * @param string $project The project to get the release for (eg; "ncc" or "libs/config")
         * @param string $release The release to get the release for (eg; "v1.0.0")
         * @param AuthenticationInterface|null $authentication Optional. The authentication to use. If null, No authentication will be used.
         * @return RepositoryResult The URL to the archive
         * @throws AuthenticationException
         * @throws NetworkException
         */
        private static function getReleaseArchive(RepositoryConfiguration $repository, string $group, string $project, string $release, ?AuthenticationInterface $authentication=null): RepositoryResult
        {
            /** @noinspection DuplicatedCode */
            if ($release === Versions::LATEST)
            {
                $release = self::getLatestRelease($repository, $group, $project, $authentication);
            }

            $curl = curl_init();
            $endpoint = sprintf('%s://%s/api/v1/repos/%s/%s/releases/tags/%s', ($repository->isSsl() ? 'https' : 'http'), $repository->getHost(), rawurlencode($group), rawurlencode($project), rawurlencode($release));
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: ncc'
            ];

            if ($authentication !== null)
            {
                $headers = self::injectAuthentication($authentication, $curl, $headers);
            }

            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, HttpRequestType::GET);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $response = self::processHttpResponse($curl, $group, $project);

            if(isset($response['zipball_url']))
            {
                return new RepositoryResult($response['zipball_url'], RepositoryResultType::SOURCE, $release);
            }

            if(isset($response['tarball_url']))
            {
                return new RepositoryResult($response['tarball_url'], RepositoryResultType::SOURCE, $release);
            }

            throw new NetworkException(sprintf('Failed to get release %s archive url for %s/%s', $release, $group, $project));
        }

        /**
         * Injects the authentication into the curl request
         *
         * @param AuthenticationInterface $authentication
         * @param CurlHandle $curl
         * @param array $headers
         * @return array
         * @throws AuthenticationException
         */
        private static function injectAuthentication(AuthenticationInterface $authentication, CurlHandle $curl, array $headers): array
        {
            switch($authentication->getAuthenticationType())
            {
                case AuthenticationType::ACCESS_TOKEN:
                    if($authentication instanceof AccessToken)
                    {
                        $headers[] = 'Authorization: token ' . $authentication->getAccessToken();
                        break;
                    }

                    throw new AuthenticationException(sprintf('Invalid authentication type for Access Token, got %s instead', $authentication->getAuthenticationType()));

                case AuthenticationType::USERNAME_PASSWORD:
                    if($authentication instanceof UsernamePassword)
                    {
                        curl_setopt($curl, CURLOPT_USERPWD, $authentication->getUsername() . ':' . $authentication->getPassword());
                        break;
                    }

                    throw new AuthenticationException(sprintf('Invalid authentication type for Username/Password, got %s instead', $authentication->getAuthenticationType()));
            }

            return $headers;
        }

        /**
         * Executes the HTTP request and processes the response
         * Throws an exception if the request failed
         *
         * @param CurlHandle $curl
         * @param string $group
         * @param string $project
         * @return array
         * @throws AuthenticationException
         * @throws NetworkException
         */
        private static function processHttpResponse(CurlHandle $curl, string $group, string $project): array
        {
            $response = curl_exec($curl);

            if($response === false)
            {
                throw new NetworkException(sprintf('HTTP request failed for %s/%s: %s', $group, $project, curl_error($curl)));
            }

            switch(curl_getinfo($curl, CURLINFO_HTTP_CODE))
            {
                case 200:
                    break;

                case 401:
                    throw new AuthenticationException(sprintf('Authentication failed for %s/%s, 401 Unauthorized, invalid/expired access token', $group, $project));

                case 403:
                    throw new AuthenticationException(sprintf('Authentication failed for %s/%s, 403 Forbidden, insufficient scope', $group, $project));

                case 404:
                    throw new NetworkException(sprintf('HTTP request failed for %s/%s, 404 Not Found', $group, $project));

                default:
                    throw new NetworkException(sprintf('Server responded with HTTP code %s for %s/%s: %s', curl_getinfo($curl, CURLINFO_HTTP_CODE), $group, $project, $response));
            }

            try
            {
                return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            }
            catch(JsonException $e)
            {
                throw new RuntimeException(sprintf('Failed to parse response from %s/%s: %s', $group, $project, $e->getMessage()), $e);
            }
        }
    }