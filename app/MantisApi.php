<?php

namespace App;

use GuzzleHttp\Client;

class MantisApi
{
    private $authorizationToken;
    private $mantisApiBaseUrl;
    private $endpoint = '';
    private $fullEndpointUrl;
    private $httpClient;

    public function __construct(string $mantisBaseUrl, string $authorization_token)
    {
        $this->mantisApiBaseUrl = $mantisBaseUrl . '/api/rest/';
        $this->authorizationToken = $authorization_token;
    }

    private function buildHttpClient(): void
    {
        $this->httpClient = new Client ([
            'base_uri' => $this->mantisApiBaseUrl,
        ]);
    }

    /**
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    public function getAllIssues()
    {
        $this->buildHttpClient();
        $headers = ['Authorization' => $this->authorizationToken];
        $response = $this->httpClient->request('GET', 'issues', ['headers' => $headers]);
        return $response;

    }

    public function createIssue($data)
    {
        $this->buildHttpClient();
        $headers = ['Authorization' => $this->authorizationToken,
            'Content-Type' => 'application/json'];
        $body = [
            'summary' => 'Objeto',
            'description' => 'descricion',
            'category' => [
                'name' => 'otra'
            ],
            'project' => [
                'name' => 'G3'
            ],
            'custom_fields' => [
                [
                    'field' => ['name' => 'usuario_encuesta'],
                    'value' => 'hodla'

                ],
            ]
        ];

        $raw_data = json_encode($body);
        $response = $this->httpClient->request('POST', 'issues', [
            'headers' => $headers,
            'body' => $raw_data
        ]);
        return $response;

    }


    public function getIssueById(int $id)
    {
        $this->buildHttpClient();
        $headers = ['Authorization' => $this->authorizationToken];
        $response = $this->httpClient->request('GET', "issues/{$id}", ['headers' => $headers]);
        return $response;

    }

    private function buildUrl(): void
    {
        $this->fullEndpointUrl = $this->mantisApiBaseUrl . '/' . $this->endpoint;
    }

    /**
     * @param string $authorizationToken
     */
    public function setAuthorizationToken(string $authorizationToken): void
    {
        $this->authorizationToken = $authorizationToken;
    }

    /**
     * @return string
     */
    public function getMantisApiBaseUrl(): string
    {
        return $this->mantisApiBaseUrl;
    }

    /**
     * @param string $mantisApiBaseUrl
     */
    public function setMantisApiBaseUrl(string $mantisApiBaseUrl): void
    {
        $this->mantisApiBaseUrl = $mantisApiBaseUrl;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return mixed
     */
    public function getFullEndpointUrl()
    {
        return $this->fullEndpointUrl;
    }

    /**
     * @param mixed $fullEndpointUrl
     */
    public function setFullEndpointUrl($fullEndpointUrl): void
    {
        $this->fullEndpointUrl = $fullEndpointUrl;
    }


}
