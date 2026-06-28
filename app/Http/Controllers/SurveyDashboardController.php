<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SurveyResultsExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class SurveyDashboardController extends Controller
{
    public function show(Survey $survey)
    {
        $stats = $this->buildStats($survey);
        return view('dashboard.show', compact('survey', 'stats'));
    }

    public function exportPdf(Survey $survey)
    {
        $stats = $this->buildStats($survey);
        $pdf = Pdf::loadView('exports.survey-pdf', compact('survey', 'stats'));
        return $pdf->download('summary.pdf');
    }
    
    public function exportExcel(Survey $survey)
    {
        $fileName = 'survey_report_' . $survey->id . '.xlsx';

        return Excel::download(new SurveyResultsExport($survey), $fileName, ExcelFormat::XLSX);
    }

    public function exportCsv(Survey $survey)
    {
        $fileName = 'survey_report_' . $survey->id . '.csv';

        return Excel::download(new SurveyResultsExport($survey), $fileName, ExcelFormat::CSV);
    }

    private function buildStats(Survey $survey)
    {
        $survey->load('questions', 'responses');
            
        $stats = [];

        foreach ($survey->questions as $question) {

            // ТЕкст
            if ($question->type === 'text') {
                $textAnswers = [];

                foreach ($survey->responses as $response) {
                    $value = $response->answers[$question->id] ?? null;
                    if ($value !== null && $value !== '') {
                        $textAnswers[] = $value;
                    }
                }

                $stats[] = [
                    'question' => $question->text,
                    'type' => 'text',
                    'total' => count($textAnswers),
                    'avg' => null,
                    'breakdown' => [],
                    'text_answers' => $textAnswers,
                ];

                continue;
            }
            if ($question->type === 'matrix') {
            $rows = $question->options['rows'] ?? [];
            $cols = $question->options['cols'] ?? [];

            $grid = [];
            foreach ($rows as $rowLabel) {
                foreach ($cols as $colValue) {
                    $grid[$rowLabel][$colValue] = 0;
                }
            }

           
            $rowValues = [];
            foreach ($rows as $rowLabel) {
                $rowValues[$rowLabel] = [];
            }

            foreach ($survey->responses as $response) {
                $answerValue = $response->answers[$question->id] ?? null;
                if (!is_array($answerValue)) {
                    continue;
                }
                foreach ($answerValue as $rowLabel => $colValue) {
                    if (isset($grid[$rowLabel][$colValue])) {
                        $grid[$rowLabel][$colValue]++;
                        $rowValues[$rowLabel][] = $colValue; 
                    }
                }
            }

           
            $rowAverages = [];
            foreach ($rows as $rowLabel) {
                $values = $rowValues[$rowLabel];
                $rowAverages[$rowLabel] = count($values) > 0
                    ? round(array_sum(array_map('floatval', $values)) / count($values), 2)
                    : null;
            }

            $maxCount = 0;
            foreach ($grid as $row) {
                foreach ($row as $count) {
                    $maxCount = max($maxCount, $count);
                }
            }
            $chartType = in_array($question->type, ['scale', 'checkbox']) ? 'bar' : 'pie';
            $chartConfig = [
                'type' => $chartType,
                'data' => [
                    'labels' => array_column($breakdown, 'label'),
                    'datasets' => [[
                        'label' => 'Количество',
                        'data' => array_column($breakdown, 'count'),
                    ]],
                ],
            ];
            $stats[] = [
                'question' => $question->text,
                'type' => 'matrix',
                'total' => count($survey->responses),
                'avg' => null,
                'breakdown' => [],
                'matrix_rows' => $rows,
                'matrix_cols' => $cols,
                'matrix_grid' => $grid,
                'matrix_max' => $maxCount,
                'matrix_averages' => $rowAverages,

            ];

            continue;
        }

           

            $valuesForThisQuestion = [];

            foreach ($survey->responses as $response) {
                $answerValue = $response->answers[$question->id] ?? null;

                if ($answerValue === null) {
                    continue;
                }

                if (is_array($answerValue)) {
                    foreach ($answerValue as $singleValue) {
                        $valuesForThisQuestion[] = $singleValue;
                    }
                } else {
                    $valuesForThisQuestion[] = $answerValue;
                }
            }

            $total = count($valuesForThisQuestion);
            $counts = array_count_values($valuesForThisQuestion);

            $breakdown = [];
            foreach ($counts as $label => $count) {
                $breakdown[] = [
                    'label' => $label,
                    'count' => $count,
                    'percent' => $total ? round($count / $total * 100, 1) : 0,
                ];
            }

            $avg = null;
            if ($question->type === 'scale' && $total > 0) {
                $numericValues = array_map('floatval', $valuesForThisQuestion);
                $avg = round(array_sum($numericValues) / count($numericValues), 2);
            }

            $stats[] = [
                'question' => $question->text,
                'type' => $question->type,
                'total' => $total,
                'avg' => $avg,
                'breakdown' => $breakdown,
            ];
        }

        return $stats;
        
    }
    public function exportSummaryExcel(Survey $survey)
{
    $stats = $this->buildStats($survey);
    return Excel::download(new \App\Exports\SurveySummaryExport($survey, $stats), 'summary.xlsx');
}
    
}