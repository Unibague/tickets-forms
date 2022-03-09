<?php

namespace App\Http\Controllers;

use App\Csv;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    private string $tableName = 'user_issues_form';

    public function generateResults(int $conversion_id)
    {
        $conversion = $this->show($conversion_id);
        $answers = json_decode($conversion->user_responses, true);
        $questions = json_decode($conversion->questions, true);
        $csv = new Csv('Resultado de encuesta', $answers, $questions, ';');
        $file = $csv->buildCSV();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . 'Respuestas de cuestionario.csv' );
        if(fclose($file)){
            die();
        }

        return 'Something went wrong';

    }

    public function show($conversion_id)
    {
        return DB::table($this->tableName)
            ->find($conversion_id);
    }
}
