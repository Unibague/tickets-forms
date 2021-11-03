<?php

namespace App\Http\Controllers;

use App\MantisApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssuesController extends Controller
{
    private $mantisBaseUrl = 'https://tickets.unibague.edu.co/tickets';
    private $createIssueData = [];

    public function index()
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
        $response = $mantisApi->getAllIssues();
        return response()->json(json_decode($response));
        return DB::table('tickets_convertforms_conversions')->get();
    }

    public function show(int $issue_id)
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
        $response = $mantisApi->getIssueById($issue_id);
        return response()->json(json_decode($response));
        return DB::table('tickets_convertforms_conversions')->get();
    }

    public function getUserIssues(string $code_user)
    {

        $user_issues = DB::table('user_issues_form')
            ->where('code_user', '=', $code_user)
            ->get();
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
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
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
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

    public function addUserNoteToIssue($issue_id, Request $request)
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
        $issue = $mantisApi->addUserNoteToIssue('Comentario ingresado por el usuario a través del sitio web: ' . $request->input('user_comment'), $issue_id);
        return response()->json(['message' => 'Estimado usuario, su comentario añadido exitosamente'], 200);
    }


    public function sendMessageToUserForm(int $issue_id)
    {
        return view('sendMessageToUser');
    }

    public function sendMessageToUserByEmail(Request $request, int $issue_id)
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
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
        $mantisApi->addUserNoteToIssue('Mensaje añadido por el técnico y enviado al usuario via correo electrónico: ' . $message, $issue_id);

        //Send email to user
        \Illuminate\Support\Facades\Mail::to($user_email)->send(new \App\Mail\userMessageNotification($issue_id, $message));

        return response()->json(['message' => 'Estimado usuario, su comentario añadido exitosamente'], 200);


    }

}
