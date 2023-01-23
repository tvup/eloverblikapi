<?php

namespace Tvup\ElOverblikApi;

use Carbon\Carbon;
use ErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;

class ElOverblikApiBase
{
    const BASE_URL = 'https://api.eloverblik.dk/customerapi/api/';
    const TOKEN_FILENAME = 'eloverblik-token.serialized';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var bool
     */
    public $cachedToken = false;

    /**
     * @var string
     */
    private $storage_path;

    private $accessToken;

    protected $meteringPointId;

    private string $refreshToken;
    private string $md5RefreshToken;


    public function __construct($refreshToken = null)
    {
        if (function_exists('storage_path')) {
            $this->storage_path = storage_path() . '/refresh_tokens';

            if(!is_dir($this->storage_path)) {
                mkdir($this->storage_path);
            }

        } else {
            $this->storage_path = getcwd();
        }

        if ($refreshToken) {
            $this->md5RefreshToken = md5($refreshToken);
        }

        try {
            $this->getAccessTokenFromFile();
        } catch (ErrorException $e) {
        }

        $this->client = new Client();
    }

    public function makeErrorHandledRequest(string $verb, string $endpoint, ?array $parameters, ?array $payload, bool $returnResponse = false)
    {
        try {
            try {
                if ($endpoint == 'token' && $this->cachedToken) {
                    return;
                } else if ($endpoint == 'token' && !$this->cachedToken) {
                    $this->accessToken = $this->refreshToken;
                }

                $response = $this->makeRequest($verb, $endpoint, $parameters, $payload);

                if ($endpoint == 'token') {
                    $response = $this->decode($response->getBody()->getContents());
                    $accessToken = json_decode($response, true)['result'];
                    $this->accessToken = $accessToken;
                    $dataAccessToken = new ElOverblikApiDataAccessToken($accessToken, Carbon::now()->toDateTimeString());
                    $this->saveAccessTokenToFile($dataAccessToken);
                    return;
                }

                $decodedResponse = $this->decode($response->getBody()->getContents());

                $errorCode = null;
                if (isset($decodedResponse['Message'])) {
                    $errorCode = $decodedResponse['Message'];
                }

                if (isset($errorCode) && $errorCode !== 0) {
                    $messages = [
                        'Verb' => $verb,
                        'Endpoint' => $endpoint,
                        'Payload' => $payload,
                    ];
                    $energiOverblikApiException = new ElOverblikApiException(
                        $decodedResponse['ErrorTxt'],
                        $decodedResponse['runInfo'],
                        $errorCode
                    );
                    $messages['Errors'] = $energiOverblikApiException->getErrors();
                    $messages['ErrorCode'] = $energiOverblikApiException->getCode();
                    throw $energiOverblikApiException;
                }
                if ($returnResponse) {
                    return $decodedResponse;
                } else {
                    return [];
                }
            } catch (ClientException $e) {
                $exceptionBody = $e->getResponse()->getBody()->getContents();
                $messages = [
                    'Verb' => $verb,
                    'Endpoint' => $endpoint,
                    'Payload' => $payload,
                    'Message' => $e->getMessage(),
                    'Response' => $exceptionBody,
                    'Code' => $e->getCode(),
                ];

                //Retry with without data-access token
                $code = $e->getCode();
                if($code == 401 && $this->cachedToken) {
                    //Clear data-access token
                    $this->cachedToken = false;
                    //Login
                    $this->makeErrorHandledRequest('GET', 'token', null, null);
                    //Retry
                    return $this->makeErrorHandledRequest($verb, $endpoint, $parameters, $payload, $returnResponse);
                }

                $errorCode = null;
                if (isset($decodedExceptionBody['Message'])) {
                    $errorCode = $decodedExceptionBody['Message'];
                }

                if (isset($errorCode) && $errorCode !== 0) {
                    $energiOverblikApiException = new ElOverblikApiException(
                        $decodedExceptionBody['ErrorTxt'],
                        isset($decodedExceptionBody['runInfo']) ? $decodedExceptionBody['runInfo'] : null,
                        $errorCode
                    );
                    $messages['Errors'] = $energiOverblikApiException->getErrors();
                    $messages['ErrorCode'] = $energiOverblikApiException->getCode();
                    throw $energiOverblikApiException;
                } else {
                    $energiOverblikApiException = new ElOverblikApiException(['Unknown error: ' . $e->getMessage()], [], $e->getCode());
                    event(new EloverblikRequestFailed($verb, $endpoint, $code));
                    throw $energiOverblikApiException;
                }
            }
        } catch (TransferException $e) {
            //503 goes here

            //Retry with without data-access token - actually I don't get why 4xx should ever come here
            $code = $e->getCode();
            if($code == 401 && $this->cachedToken) {
                //Clear data-access token
                $this->cachedToken = false;
                //Login
                $this->makeErrorHandledRequest('GET', 'token', null, null);
                //Retry
                return $this->makeErrorHandledRequest($verb, $endpoint, $parameters, $payload, $returnResponse);
            }


            $response = $e->getResponse()->getBody()->getContents();
            $message = $e->getMessage();
            $messages = [
                'Verb' => $verb,
                'Endpoint' => $endpoint,
                'Payload' => $payload,
                'Message' => $message,
                'Response' => $response,
                'Code' => $code,
                'Class' => get_class($e)
            ];
            logger('An TransferException occured. Code is ' . $code . ' and data is as follows:');
            logger($message);
            logger($response);
            event(new EloverblikRequestFailed($verb, $endpoint, $code));
            $energiOverblikApiException = new ElOverblikApiException($messages, [], $code);
            throw $energiOverblikApiException;
        }
    }

    private function makeRequest(string $verb, string $endpoint, ?array $parameters, ?array $payload): ResponseInterface
    {
        if (null !== $parameters) {
            $parameters = '?' . http_build_query($parameters);
        } else {
            $parameters = '';
        }
        $url = self::BASE_URL . $endpoint . $parameters;

        $options = [
            'headers' => ['Authorization' => 'Bearer ' . $this->accessToken],
        ];

        if ($this->debug) {
            $options = array_merge($options, ['debug' => true,]);
        }

        if (null !== $payload) {
            $options = array_merge($options, ['json' => $payload]);
        }
        event(new EloverblikRequestMade($verb, $endpoint));
        return $this->client->request($verb, $url, $options);
    }

    /**
     * Decode
     *
     * The intention was that decoding should happen here..
     * NotYetImplemented
     *
     * @param string $getContents
     * @return string
     */
    private function decode(string $getContents)
    {
        return $getContents;
    }

    /**
     * @return FileCookieJar
     * @throws ErrorException
     */
    private function getAccessTokenFromFile(): void
    {
        $file = null;
        if(isset($this->md5RefreshToken)) {
            $file = file_get_contents($this->storage_path . '/' . ($this->md5RefreshToken ? $this->md5RefreshToken . '-' : '') . self::TOKEN_FILENAME);
        } /** @var ElOverblikApiDataAccessToken $dataAccessToken */
        $dataAccessToken = $file ? unserialize($file) : null;
        if ($dataAccessToken && Carbon::parse($dataAccessToken->getIssuedAt())->greaterThanOrEqualTo(Carbon::now()->subDay())) {
            $this->accessToken = $dataAccessToken->getDataAccessToken();
            $this->cachedToken = true;
        }
    }

    private function saveAccessTokenToFile(ElOverblikApiDataAccessToken $dataAccessToken): void
    {
        $file = null;
        if(isset($this->md5RefreshToken)) {
            $file = fopen($this->storage_path . '/' . ($this->md5RefreshToken ? $this->md5RefreshToken . '-' : '') . self::TOKEN_FILENAME, "w") or die("Unable to open file!");
        }
        if($file) {
            fwrite($file, serialize($dataAccessToken));
            fclose($file);
        }
    }

    protected function setRefreshToken(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }


}