<?php

namespace App\Exports;

use App\Models\Survey;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SurveyResultsExport implements FromCollection, WithHeadings
{
    protected Survey $survey;
    protected $chartUrl;
    public function __construct(Survey $survey)
    {
        $this->survey = $survey->load('questions', 'responses');
    }

    public function headings(): array
    {
        return $this->survey->questions->pluck('text')->toArray();
    }

    public function collection()
    {
        $rows = [];

        foreach ($this->survey->responses as $response) {
            $row = [];

            foreach ($this->survey->questions as $question) {
                $value = $response->answers[$question->id] ?? '';

                if (is_array($value)) {
                    $isAssoc = array_keys($value) !== range(0, count($value) - 1);

                    if ($isAssoc) {
                        $parts = [];
                        foreach ($value as $k => $v) {
                            $parts[] = "$k: $v";
                        }
                        $value = implode('; ', $parts);
                    } else {
                        $value = implode(', ', $value);
                    }
                }

                $row[] = $value;
            }

            $rows[] = $row;
        }

        return collect($rows);
    }
}