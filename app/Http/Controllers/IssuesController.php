<?php

namespace App\Http\Controllers;

use App\MantisApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssuesController extends Controller
{
    private $mantisBaseUrl = 'http://172.19.24.12/tickets';

    public function index()
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
        $response = $mantisApi->getAllIssues()->getBody();
        return $response;
        return DB::table('tickets_convertforms_conversions')->get();
    }

    public function show(int $issue_id)
    {
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
        $response = $mantisApi->getIssueById($issue_id)->getBody();
        return $response;
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
            $full_response[] = json_decode($mantisApi->getIssueById($user_issue->issue_id)->getBody(), false)->issues[0];
        }
        return $full_response;

        return DB::table('tickets_convertforms_conversions')->get();
    }

    public function createIssue(Request $request)
    {
        $data = [
            'code_user' => $request->input('code_user'),
            'conversion_id' => $request->input('conversion_id'),
            'issue_id' => 1
        ];
        DB::table('user_issues_form')
            ->insert($data);
        $mantisApi = new MantisApi($this->mantisBaseUrl, 'VZP_UUm6aJyvwx6HfZvk8_wNGe0l80Xl');
        $mantisApi->createIssue($data);


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
