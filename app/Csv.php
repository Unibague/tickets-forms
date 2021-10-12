<?php

namespace App;

class Csv
{
    private array $columnNames;
    private array $values;
    private string $name;

    public function __construct(string $name, array $content, string $separator = ';')
    {
        $this->name = $name;
        $this->columnNames = $this->getColumns($content);
        $this->values = $this->getValues($content);

    }

    private function getColumns(array $content): array
    {
        $columns = [];
        foreach ($content as $column => $value) {
            $columns[] = $column;
        }
        return $columns;
    }

    private function getValues(array $content): array
    {
        $values = [];
        foreach ($content as $column => $value) {
            $values[] = $value;
        }
        return $values;
    }

    public function buildCSV()
    {
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); //Allow UTF-8 encoding
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
