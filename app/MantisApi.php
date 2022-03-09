<?php

namespace App;


/**
 *
 */
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


    /**
     * @var array
     */
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
                'name' => 'General',
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

    /**
     * @param int $issue_id
     * @param string $category_name
     * @return bool|string
     */
    public function changeIssueCategory(int $issue_id, string $category_name)
    {

        $this->buildHttpClient();
        $headers = ['Authorization: ' . $this->authorizationToken,
            'Content-Type: ' . 'application/json'];
        $body = [
            'category' => [
                'name' => $category_name
            ]
        ];

        $rawBody = json_encode($body);
        $options = [
            'headers' => $headers,
            'body' => $rawBody
        ];
        return $this->makeRequest('PATCH', 'issues/' . $issue_id, $options);
    }

    /**
     * @param array $questions
     * @param array $answers
     * @param string $user
     * @param int $issue_id
     * @return bool|string
     */
    public function addUserNoteToIssue(array $questions, array $answers, string $user, int $issue_id)
    {
        $questionsAsText = $this->getQuestionsAsText($questions, $answers);
        $finalText = "Respuestas proporcionadas por el usuario " . $user . " en el formulario de comentarios: \r"
            . $questionsAsText;
        //Make the request
        return $this->postIssueNote($issue_id, $finalText);
    }


    /**
     * @param String $issue_id
     * @param String $text
     * @return bool|string
     */
    public function postIssueNote(string $issue_id, string $text)
    {

        $this->buildHttpClient();
        $headers = ['Authorization: ' . $this->authorizationToken,
            'Content-Type: ' . 'application/json'];
        $body = [
            'text' => $text,
        ];
        $rawBody = json_encode($body);
        $options = [
            'headers' => $headers,
            'body' => $rawBody
        ];
        return $this->makeRequest('POST', 'issues/' . $issue_id . '/notes', $options);
    }

    /**
     * @param array $questions
     * @param array $answers
     * @param int $issue_id
     * @return bool|string
     */
    public function AddNoteToIssue(array $questions, array $answers, int $issue_id)
    {
        //lets see
        $issueTransferUrl = 'https://docs.google.com/forms/d/e/1FAIpQLSeZYKy3Ich2_OgHiDyy5nA9SJJpvUmBMZl4rYtid7_7p6BkQQ/viewform?entry.2109776235=' . $issue_id;
        $url_to_comment = "https://tickets.unibague.edu.co/tickets-forms/comments/issue/{$issue_id}/new";
        $questionsAsText = $this->getQuestionsAsText($questions, $answers);
        $issueBody = "Respuestas proporcionadas por el usuario en el formulario: \n" .
            $questionsAsText . "\nURL para notificar comentarios al usuario: {$url_to_comment}"
            . "\nURL para solicitar traslado del servicio a otra dependencia: " . $issueTransferUrl;

        $this->buildHttpClient();
        $headers = ['Authorization: ' . $this->authorizationToken,
            'Content-Type: ' . 'application/json'];
        $body = [
            'text' => $issueBody,
        ];
        $rawBody = json_encode($body);
        $options = [
            'headers' => $headers,
            'body' => $rawBody
        ];
        return $this->makeRequest('POST', 'issues/' . $issue_id . '/notes', $options);
    }

    /**
     * @param array $questions
     * @param array $answers
     * @return string
     */
    private function getQuestionsAsText(array $questions, array $answers): string
    {
        $text = '';
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
