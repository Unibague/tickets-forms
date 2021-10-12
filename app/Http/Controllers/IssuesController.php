<?php

namespace App\Http\Controllers;

use App\MantisApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssuesController extends Controller
{
    private $mantisBaseUrl = 'http://172.19.24.12/tickets';
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
        foreach ($user_issues as $user_issue) {
            $full_response[] = json_decode($mantisApi->getIssueById($user_issue->issue_id), false)->issues[0];
        }
        return $full_response;
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
                'code_user' => explode('@', $request->input('code_user'))[0],
                'form_id' => $this->createIssueData['form_id'],
                'user_responses' => json_encode($this->createIssueData['answers'],JSON_UNESCAPED_UNICODE),
                'created_at' => $time,
                'updated_at' => $time
            ]);

        //Create Mantis Api Instance create an issue
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
        $issue = $mantisApi->createIssue($this->createIssueData,$user_issues_form_id);

        //Convert the issue to object in order to get it's id.
        $issue_object = json_decode($issue);
        $issue_id =  $issue_object->issue->id;
        DB::table('user_issues_form')
            ->where('id', $user_issues_form_id)
            ->update(['issue_id' => $issue_id]);

        //Now, lets parse all the questions and create a note with that information.
        $mantisApi->AddNoteToIssue($request->input('questions'),$request->input('answers'),$issue_id);
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

        $this->createIssueData = [
            'code_user' => explode('@', $code_user)[0],
            'project' => $project,
            'category' => $category,
            'issue_name' => $issue_name,
            'answers' => $request->input('answers'),
            'form_id' => $request->input('form_id')
        ];
        return $errors;
    }


    public function createById(Request $request)
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
        $response = $mantisApi->getAllIssues()->getBody();
        return $response;
        die();
        $form_id = $request->input('form_id');

        return DB::table('tickets_convertforms_conversions')
            ->where('id', '=', $form_id)
            ->get();
    }
}
