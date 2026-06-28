<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\QuizResultsExport;
use App\Exports\QuizSummaryExport;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;

class SurveyDashboardController extends Controller
{
    public function show(Quiz $quiz)
    {
        $stats = $this->buildStats($quiz);
        return view('dashboard.show', ['survey' => $quiz, 'stats' => $stats]);
    }

    public function exportPdf(Quiz $quiz)
    {
        $stats = $this->buildStats($quiz);
        $pdf = Pdf::loadView('exports.survey-pdf', ['survey' => $quiz, 'stats' => $stats]);
        return $pdf->download('summary.pdf');
    }

    public function exportExcel(Quiz $quiz)
    {
        $fileName = 'survey_raw_' . $quiz->id . '.xlsx';

        return Excel::download(new QuizResultsExport($quiz), $fileName);
    }


    public function exportSummaryExcel(Quiz $quiz)
    {
    $stats = $this->buildStats($quiz); 

    // Сборка ссылок для QuickChart графиков
    foreach ($stats as $key => $stat) {
        if (!empty($stat['breakdown'])) {
            $labels = [];
            $counts = [];
            foreach ($stat['breakdown'] as $item) {
                $labels[] = $item['label'];
                $counts[] = $item['count'];
            }
            $chartConfig = [
                'type' => 'bar',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [['label' => 'Ответы', 'data' => $counts, 'backgroundColor' => '#3490dc']]
                ]
            ];
            $stats[$key]['chart_url'] = "https://quickchart.io/chart?w=500&h=250&c=" . urlencode(json_encode($chartConfig));
        }
    }

    $fileName = 'survey_summary_' . $quiz->id . '.xlsx';

    
        return Excel::download(new QuizSummaryExport($quiz, $stats), $fileName);
    }
    public function exportCsv(Quiz $quiz)
    {
        return Excel::download(new QuizResultsExport($quiz), 'results.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    private function buildStats(Quiz $quiz)
    {
        $quiz->load('questions.responses', 'questions.options');

        $stats = [];

        foreach ($quiz->questions as $question) {
            $answers = $question->responses;

            //text
            if ($question->type === 'text') {
                $textAnswers = $answers->pluck('answer_text')->filter()->values()->all();

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

            // radio / checkbox / scale
            $valuesForThisQuestion = $answers->pluck('answer_text')->filter()->values()->all();

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

            $chartType = in_array($question->type, ['scale', 'checkbox']) ? 'bar' : 'pie';
            $chartConfig = [
                'type' => $chartType,
                'data' => [
                    'labels' => array_column($breakdown, 'label'),
                    'datasets' => [['label' => 'Количество', 'data' => array_column($breakdown, 'count')]],
                ],
            ];
            $chartUrl = 'https://quickchart.io/chart?width=500&height=250&c=' . urlencode(json_encode($chartConfig));

            $stats[] = [
                'question' => $question->text,
                'type' => $question->type,
                'total' => $total,
                'avg' => $avg,
                'breakdown' => $breakdown,
                'chart_url' => $chartUrl,
            ];
        }

        return $stats;
    }
}