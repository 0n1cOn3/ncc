<?php

    namespace ncc\Utilities;

    use ncc\Abstracts\HttpRequestType;
    use ncc\Exceptions\HttpException;
    use ncc\Objects\HttpRequest;
    use ncc\Objects\HttpResponse;

    class HttpClient
    {
        /**
         * Creates a new HTTP request and returns the response.
         *
         * @param HttpRequest $httpRequest
         * @return HttpResponse
         * @throws HttpException
         */
        public static function request(HttpRequest $httpRequest): HttpResponse
        {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $httpRequest->Url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            switch($httpRequest->Type)
            {
                case HttpRequestType::GET:
                    curl_setopt($curl, CURLOPT_HTTPGET, true);
                    break;

                case HttpRequestType::POST:
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $httpRequest->Body);
                    break;

                case HttpRequestType::PUT:
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $httpRequest->Body);
                    break;

                case HttpRequestType::DELETE:
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;

                default:
                    throw new HttpException(sprintf('Invalid HTTP request type: %s', $httpRequest->Type));
            }

            if (is_array($httpRequest->Authentication))
            {
                curl_setopt($curl, CURLOPT_USERPWD, $httpRequest->Authentication[0] . ':' . $httpRequest->Authentication[1]);
            }
            else if (is_string($httpRequest->Authentication))
            {
                curl_setopt($curl, CURLOPT_USERPWD, $httpRequest->Authentication);
            }

            if (count($httpRequest->Headers) > 0)
                curl_setopt($curl, CURLOPT_HTTPHEADER, $httpRequest->Headers);

            Console::outDebug(sprintf(' => %s request %s', $httpRequest->Type, $httpRequest->Url));
            if($httpRequest->Headers !== null && count($httpRequest->Headers) > 0)
                Console::outDebug(sprintf(' => headers: %s', implode(', ', $httpRequest->Headers)));
            if($httpRequest->Body !== null)
                Console::outDebug(sprintf(' => body: %s', $httpRequest->Body));

            $response = curl_exec($curl);

            if ($response === false)
            {
                $error = curl_error($curl);
                curl_close($curl);
                throw new HttpException($error);
            }

            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);

            $httpResponse = new HttpResponse();
            $httpResponse->StatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $httpResponse->Headers = self::parseHeaders($headers);
            $httpResponse->Body = $body;

            Console::outDebug(sprintf(' <= %s response', $httpResponse->StatusCode));/** @noinspection PhpConditionAlreadyCheckedInspection */
            if($httpResponse->Headers !== null && count($httpResponse->Headers) > 0)
                Console::outDebug(sprintf(' <= headers: %s', implode(', ', $httpResponse->Headers)));
            /** @noinspection PhpConditionAlreadyCheckedInspection */
            if($httpResponse->Body !== null)
                Console::outDebug(sprintf(' <= body: %s', $httpResponse->Body));

            curl_close($curl);

            return $httpResponse;
        }

        /**
         * Takes the return headers of a cURL request and parses them into an array.
         *
         * @param string $headers
         * @return array
         */
        private static function parseHeaders(string $headers): array
        {
            $headers = explode("\r", $headers);
            $headers = array_filter($headers, function ($header)
            {
                return !empty($header);
            });
            $headers = array_map(function ($header) {
                return explode(':', $header, 2);
            }, $headers);

            return array_combine(array_map(function ($header) { return strtolower($header[0]); }, $headers),
                array_map(function ($header) { return trim($header[1]); }, $headers)
            );
        }
    }