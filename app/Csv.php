<?php

namespace App;

class Csv
{
    private array $columnNames;
    private array $values;
    private array $questions;
    private string $name;

    public function __construct(string $name, array $answers, array $questions, string $separator = ';')
    {
        $this->columnNames = $this->getColumns($questions);
        $this->questions = $questions;
        $this->values = $this->getValues($answers);
    }

    private function getColumns(array $questions): array
    {
        $columns = [];
        foreach ($questions as $questionName => $type) {
            $columns[] = $questionName;
        }
        return $columns;
    }

    private function getValues(array $answers): array
    {
        $values = [];
        foreach ($this->questions as $questionName => $questionType) {
            if (isset($answers[$questionName]) && $answers[$questionName] !== '') {
                if (is_array($answers[$questionName])) {
                    $final_formatted_answer = '';
                    if ($questionType === 'FILE_UPLOAD') {
                        foreach ($answers[$questionName] as $individualAnswer) {
                            $final_formatted_answer .= "https://drive.google.com/file/d/" . $individualAnswer . ",";
                        }
                    } else {
                        foreach ($answers[$questionName] as $individualAnswer) {
                            $final_formatted_answer .= $individualAnswer . ",";
                        }
                    }
                    $values[] = $final_formatted_answer;
                } else {
                    $values[] = $answers[$questionName];
                }
            } else {
                $values[] = 'Sin respuesta por parte del usuario';
            }
        }
        return $values;
    }

    public function buildCSV()
    {
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); //Allow UTF-8 encoding
        $this->constructCSVHeader($handle);
        $this->construcCSVRows($handle);
        return $handle;
    }

    private function constructCSVHeader($handle)
    {
        fputcsv($handle, $this->columnNames, ';');
    }

    private function construcCSVRows($handle)
    {
        $finalArray = [];
        $aux = true;
        foreach ($this->values as $value) {

            if (is_array($value)) {
                $finalValue = '';

                foreach ($value as $arrayItem) {
                    $finalValue .= $arrayItem . ',';
                }
                $finalArray[] = $finalValue;

            } else {
                $finalArray[] = $value;
            }
        }

        fputcsv($handle, $finalArray, ';');

    }

}
