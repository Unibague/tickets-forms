<?php

namespace App;


use Illuminate\Http\Request;
use function PHPUnit\Framework\isNull;


class MantisApi
{
    /**
     * @var string
     */
    private $authorizationToken;
    /**
     * @var string
     */
    private $mantisApiBaseUrl;
    /**
     * @var string
     */
    private $endpoint = '';
    /**
     * @var
     */
    private $fullEndpointUrl;
    /**
     * @var
     */
    private $httpClient;


    private $createIssueData=[];

    /**
     * @param string $mantisBaseUrl
     * @param string $authorization_token
     */
    public function __construct(string $mantisBaseUrl, string $authorization_token)
    {
        $this->mantisApiBaseUrl = $mantisBaseUrl . '/api/rest';
        $this->authorizationToken = $authorization_token;
    }

    /**
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * @return mixed
     */
    public function getAllIssues()
    {
        $this->buildHttpClient();
        $response = $this->makeRequest('GET', 'issues');
        return $response;

    }

    /**
     *
     */
    private function buildHttpClient(): void
    {
        $this->httpClient = curl_init();
        $this->setDefaultClientOptions(); //This will set the default curl options

    }

    /**
     *
     */
    private function setDefaultClientOptions(): void
    {
        curl_setopt_array($this->httpClient, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $this->authorizationToken]
        ));
    }

    /**
     * @param String $method
     * @param String $endpoint
     * @param array $options
     * @return bool|string
     */
    private function makeRequest(string $method, string $endpoint, array $options = [])
    {
        $this->buildUrl($endpoint); //Set the full URL for making the request.
        curl_setopt($this->httpClient, CURLOPT_URL, $this->fullEndpointUrl); // Asign it to te client
        curl_setopt($this->httpClient, CURLOPT_CUSTOMREQUEST, $method); //Make http method to post
        if (isset($options['body'])) {
            curl_setopt($this->httpClient, CURLOPT_POSTFIELDS, $options['body']); //Make http method to post
        }
        if (isset($options['headers'])) {
            curl_setopt($this->httpClient, CURLOPT_HTTPHEADER, $options['headers']); // Set auth header
        }
        $response = curl_exec($this->httpClient);
        curl_close($this->httpClient);
        return $response;
    }

    /**
     * @param string $newEndpoint
     */
    private function buildUrl(string $newEndpoint): void
    {
        $this->endpoint = $newEndpoint;
        $this->fullEndpointUrl = $this->mantisApiBaseUrl . '/' . $this->endpoint;
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function createIssue(array $request_data, int $user_issues_form_id)
    {
        $url = 'http://172.19.24.12/tickets-forms/conversions/';

        $this->buildHttpClient();
        $headers = ['Authorization: ' . $this->authorizationToken,
            'Content-Type: ' . 'application/json'];
        $body = [
            'summary' =>$request_data['issue_name'] . ' - ' .$request_data['code_user'],
            'description' => 'Formulario llenado desde el sitio web: ' . $url.$user_issues_form_id,
            'category' => [
                'name' =>$request_data['category'],
            ],
            'project' => [
                'name' =>$request_data['project']
            ],
            'custom_fields' => [
                [
                    'field' => ['name' => 'usuario_encuesta'],
                    'value' =>$request_data['code_user'], //Get user email
                ],
            ]
        ];
        $rawBody = json_encode($body);
        $options = [
            'headers' => $headers,
            'body' => $rawBody
        ];
        return $this->makeRequest('POST', 'issues', $options);
    }


    /**
     * @param int $id
     * @return bool|string
     */
    public function getIssueById(int $id)
    {
        $this->buildHttpClient();
        $response = $this->makeRequest('GET', "issues/{$id}");
        return $response;

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
