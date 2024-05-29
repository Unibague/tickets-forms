<?php

namespace App\Http\Controllers;

use App\MantisApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IssuesController extends Controller
{
    private $mantisBaseUrl = 'https://tickets.unibague.edu.co/tickets';
    private $createIssueData = [];


/*    public function test()
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'juan.gonzalez'
    }*/


    public function index()
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'UQtABq7GR0OevYz7zRvuQIueRcddQAx8');
        $response = $mantisApi->getAllIssues();
        return response()->json(json_decode($response));
    }

    public function show(int $issue_id)
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'UQtABq7GR0OevYz7zRvuQIueRcddQAx8');
        $response = $mantisApi->getIssueById($issue_id);
        return response()->json(json_decode($response));
        return DB::table('tickets_convertforms_conversions')->get();
    }

    public function getUserIssues(string $code_user)
    {
        $user_issues = DB::table('user_issues_form')
            ->where('code_user', '=', $code_user)
            ->whereNotNull('issue_id')
            ->latest()->get();
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'UQtABq7GR0OevYz7zRvuQIueRcddQAx8');

        $full_response = [];
        //Receive all the user issue's as object
        foreach ($user_issues as $user_issue) {
            $mantisIssue = json_decode($mantisApi->getIssueById($user_issue->issue_id), true); //Get the issue from the mantis api
            if (!isset($mantisIssue['code'])) { //check if has error code, if not ...
                $full_response[] = $mantisIssue['issues'][0];
            }
        }
        //Let's format in the correct way, for not showing unecessary fields.
        $final_response = [];
        foreach ($full_response as $response_item) {
            unset($response_item['reporter'], $response_item['resolution'], $response_item['priority'], $response_item['reproducibility']
                , $response_item['sticky'], $response_item['view_state'], $response_item['severity'], $response_item['notes'], $response_item['custom_fields'], $response_item['history']);

            //Format hours
            $created_at = explode('T', $response_item['created_at']);
            $created_at_date = $created_at[0];
            $created_at_time = explode('-', $created_at[1])[0];

            $updated_at = explode('T', $response_item['updated_at']);
            $updated_at_date = $updated_at[0];
            $updated_at_time = explode('-', $updated_at[1])[0];

            $response_item['created_at'] = $created_at_date . ' ' . $created_at_time;
            $response_item['updated_at'] = $updated_at_date . ' ' . $updated_at_time;

            $final_response[] = $response_item;
        }
        return response($final_response, 200);

    }


    public function createIssue(Request $request)
    {
        //First, verify the request.

        $errors = $this->verifyCreateIssueRequest($request);
        if (count($errors) > 0) {
            return response()->json($errors, 400);
        }
        //Now that the request its properly made, save the user responses
        $time = Carbon::now()->toDateTimeString();
        $user_issues_form_id = DB::table('user_issues_form')
            ->insertGetId([
                'code_user' => $request->input('code_user'),
                'form_id' => $this->createIssueData['form_id'],
                'user_responses' => json_encode($this->createIssueData['answers'], JSON_UNESCAPED_UNICODE),
                'questions' => json_encode($this->createIssueData['questions'], JSON_UNESCAPED_UNICODE),
                'created_at' => $time,
                'updated_at' => $time
            ]);

        //Create Mantis Api Instance create an issue
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'UQtABq7GR0OevYz7zRvuQIueRcddQAx8');
        $issue = $mantisApi->createIssue($this->createIssueData, $user_issues_form_id);
        $issue_object = json_decode($issue);
        $issue_id = $issue_object->issue->id;

        //Change category to the provided by the user
        $mantisApi->changeIssueCategory($issue_id, $this->createIssueData['category']);

        //Convert the issue to object in order to get it's id.

        DB::table('user_issues_form')
            ->where('id', $user_issues_form_id)
            ->update(['issue_id' => $issue_id]);

        //Now, lets parse all the questions and create a note with that information.
        $mantisApi->AddNoteToIssue($request->input('questions'), $request->input('answers'), $issue_id);
        return response('issue with id ' . $issue_id . ' created successfully.', 200);

    }

    private function verifyCreateIssueRequest(Request $request)
    {
        $code_user = $request->input('code_user');

        $errors = [];
        if (!$code_user) {
            $errors[] = 'No code_user provided';
        }
        $project = $request->input('project');
        if (!$project) {
            $errors[] = 'No project provided';
        }
        $category = $request->input('category');
        if (!$category) {
            $errors[] = 'No category provided';
        }
        $issue_name = $request->input('issue_name');
        if (!$issue_name) {
            $errors[] = 'No issue_name provided';
        }

        $descriptive_question = $request->input('answers')[$request->input('descriptive_question')] ?? '';

        $this->createIssueData = [
            'code_user' => $code_user,
            'project' => $project,
            'category' => $category,
            'issue_name' => $issue_name,
            'answers' => $request->input('answers'),
            'questions' => $request->input('questions'),
            'form_id' => $request->input('form_id'),
            'descriptive_question' => $descriptive_question
        ];
        return $errors;
    }

    public function addUserNoteToIssue($issue_id, Request $request): \Illuminate\Http\JsonResponse
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'UQtABq7GR0OevYz7zRvuQIueRcddQAx8');

        $questions = $request->input('questions');
        $answers = $request->input('answers');
        $code_user = $request->input('code_user');

        $mantisApi->addUserNoteToIssue($questions, $answers, $code_user, $issue_id);
        return response()->json(['message' => 'Estimado usuario, su comentario fue añadido exitosamente'], 200);
    }

    public function sendMessageToUserForm(int $issue_id)
    {
        return view('sendMessageToUser');
    }

    public function sendMessageToUserByEmail(Request $request, $issue_id)
    {
        $user_issues = DB::table('user_issues_form')
            ->where('issue_id', '=', $issue_id)
            ->first();

        //If the issue it's not found, return a 404 error
        if (!$user_issues) {
            return response()->json([], 404);
        }
        //Get the user email.
        $user_email = $user_issues->code_user;
        $message = $request->input('message');
        //Connect to mantis API to add the note.
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'UQtABq7GR0OevYz7zRvuQIueRcddQAx8');
        $mantisApi->postIssueNote($issue_id, 'Mensaje añadido por la persona asignada a resolver la solicitud y enviado al usuario via correo electrónico: ' . $message);

//        //Send email to user
//        \Illuminate\Support\Facades\Mail::to($user_email)->send(new \App\Mail\userMessageNotification($issue_id, $message));

        $data= ['issue_id' => $issue_id, 'message' => $message];

        //Send email to user
        \Illuminate\Support\Facades\Mail::to($user_email)->send(new \App\Mail\UserMessageNotificationEnhanced($data));



        return response()->json(['message' => 'Estimado usuario, su comentario fue añadido exitosamente'], 200);
    }


    public function testingSendMessageToUserEmail(Request $request, $issue_id){
        $user_issues = DB::table('user_issues_form')
            ->where('issue_id', '=', $issue_id)
            ->first();

        //If the issue it's not found, return a 404 error
        if (!$user_issues) {
            return response()->json([], 404);
        }
        //Get the user email.
        $user_email = $user_issues->code_user;
        $message = $request->input('message');
//        //Connect to mantis API to add the note.
//        $mantisApi = new MantisApi($this->mantisBaseUrl, 'UQtABq7GR0OevYz7zRvuQIueRcddQAx8');
//        $mantisApi->postIssueNote($issue_id, 'Mensaje añadido por la persona asignada a resolver la solicitud y enviado al usuario via correo electrónico: ' . $message);

        $data= ['issue_id' => $issue_id, 'message' => $message];

        //Send email to user
        \Illuminate\Support\Facades\Mail::to($user_email)->send(new \App\Mail\UserMessageNotificationEnhanced($data));

        return response()->json(['message' => 'Estimado usuario, su comentario fue añadido exitosamente'], 200);
    }

    public function previewSendMessageToUserEmail()
    {
        $data = [
            'issue_id' => 123,
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque quis enim magna. Curabitur vestibulum iaculis ante, eget tempor eros congue in. Aliquam eget euismod arcu. Sed sodales, est sit amet suscipit blandit, lorem dui faucibus est, vitae maximus purus massa eget nunc. Curabitur dignissim vestibulum lacus at volutpat.',
        ];

        // Render the email template
        $emailContent = (new \App\Mail\UserMessageNotificationEnhanced($data))->render();

        // Return the email content as HTML response
        return response()->make($emailContent, 200, [
            'Content-Type' => 'text/html',
        ]);
    }


}
