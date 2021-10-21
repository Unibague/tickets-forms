<?php

namespace App;


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


    private $createIssueData = [];

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
        $url = 'https://tickets.unibague.edu.co/tickets-forms/conversions/';

        $this->buildHttpClient();
        $headers = ['Authorization: ' . $this->authorizationToken,
            'Content-Type: ' . 'application/json'];
        $body = [
            'summary' => $request_data['issue_name'] . ' - ' . $request_data['code_user'] . ' - ' . $request_data['descriptive_question'],
            'description' => 'Formulario llenado desde el sitio web: ' . $url . $user_issues_form_id,
            'category' => [
                'name' => $request_data['category'],
            ],
            'project' => [
                'name' => $request_data['project']
            ]
        ];
        $rawBody = json_encode($body);
        $options = [
            'headers' => $headers,
            'body' => $rawBody
        ];
        return $this->makeRequest('POST', 'issues', $options);
    }

    public function addUserNoteToIssue(string $userComment, int $issue_id)
    {
        $questionsAsText = $userComment;
        $this->buildHttpClient();
        $headers = ['Authorization: ' . $this->authorizationToken,
            'Content-Type: ' . 'application/json'];
        $body = [
            'text' => $questionsAsText,
        ];
        $rawBody = json_encode($body);
        $options = [
            'headers' => $headers,
            'body' => $rawBody
        ];
        return $this->makeRequest('POST', 'issues/' . $issue_id . '/notes', $options);
    }

    public function AddNoteToIssue(array $questions, array $answers, int $issue_id)
    {
        $questionsAsText = $this->getQuestionsAsText($questions, $answers);
        $this->buildHttpClient();
        $headers = ['Authorization: ' . $this->authorizationToken,
            'Content-Type: ' . 'application/json'];
        $body = [
            'text' => $questionsAsText,
        ];
        $rawBody = json_encode($body);
        $options = [
            'headers' => $headers,
            'body' => $rawBody
        ];
        return $this->makeRequest('POST', 'issues/' . $issue_id . '/notes', $options);
    }

    private function getQuestionsAsText(array $questions, array $answers): string
    {
        $text = "Respuestas proporcionadas por el usuario en el formulario: \n";
        foreach ($questions as $question => $type) {

            //Fist, verify that the user answer the particular question
            if (isset($answers[$question])) {

                //Check if is file upload, in order to parse all the provided answers
                if (is_array($answers[$question])) {

                    $file_counter = 1;
                    foreach ($answers[$question] as $part_of_answer) {

                        if ($type === 'FILE_UPLOAD') {
                            $text .= $question . " " . $file_counter . ": https://drive.google.com/file/d/" . $part_of_answer . "\n";
                        } else {
                            $text .= $question . " " . $file_counter . ": " . $part_of_answer . "\n";

                        }
                        $file_counter++;
                    }
                } //If not, just print it as text.
                else {
                    $text .= $question . ": " . $answers[$question] . "\n";
                }
            }
            //If the user didnt answer, there is a possibility that forms api didnt send the answer in the arry, so lets
            //leave it blank
            else {
                $text .= $question . ": \n";
            }

        }
        return $text;
    }

    /**
     * @param int $id
     * @return bool|string
     */
    public function getIssueById($id)
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
