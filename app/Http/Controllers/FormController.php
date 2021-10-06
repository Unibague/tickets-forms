<?php

namespace App\Http\Controllers;

use App\Csv;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    private $mantisBaseUrl = 'http://172.19.24.12/tickets';
    private $tableName = 'l88kc_convertforms_conversions';

    public function index()
    {

    }

    public function generateResults(int $conversion_id)
    {

        $conversion = $this->show($conversion_id);
        $params = json_decode($conversion->params, true);
        //dd($params);
        $csv = new Csv('Resultado de encuenta', $params, ';');
        $file = $csv->buildCSV();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . 'hola' );
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
