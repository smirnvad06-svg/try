<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\QuizResultsExport;
use App\Exports\QuizSummaryExport;
use Maatwebsite\Excel\Facades\Excel;

class SurveyDashboardController extends Controller
{
    // Страница дашборда с графиками
    public function show(Quiz $quiz)
    {
        $stats = $this->buildStats($quiz);
        return view('dashboard.show', ['survey' => $quiz, 'stats' => $stats]);
    }

    // Экспорт PDF
    public function exportPdf(Quiz $quiz)
    {
        $stats = $this->buildStats($quiz);
        $pdf = Pdf::loadView('exports.survey-pdf', ['survey' => $quiz, 'stats' => $stats]);
        return $pdf->download('summary.pdf');
    }

    // Экспорт Excel (сырые данные)
    public function exportExcel(Quiz $quiz)
    {
        return Excel::download(new QuizResultsExport($quiz), 'results.xlsx');
    }

    // Экспорт CSV
    public function exportCsv(Quiz $quiz)
    {
        return Excel::download(new QuizResultsExport($quiz), 'results.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    // Экспорт Excel (сводный отчёт)
    public function exportSummaryExcel(Quiz $quiz)
    {
        $stats = $this->buildStats($quiz);
        return Excel::download(new QuizSummaryExport($quiz, $stats), 'summary.xlsx');
    }

    // API: статистика в JSON (для Sanctum)
    public function apiStats(Quiz $quiz)
    {
        $stats = $this->buildStats($quiz);
        return response()->json([
            'quiz' => $quiz->only('id', 'title'),
            'stats' => $stats,
        ]);
    }

    // Основная логика сбора статистики
    private function buildStats(Quiz $quiz)
    {
        $quiz->load('questions.responses', 'questions.options');

        $stats = [];

        foreach ($quiz->questions as $question) {
            $answers = $question->responses;

            // ТИП: text — список текстовых ответов
            if ($question->type === 'text') {
                $textAnswers = $answers->pluck('answer_text')->filter()->values()->all();

                $stats[] = [
                    'question'     => $question->text,
                    'type'         => 'text',
                    'total'        => count($textAnswers),
                    'avg'          => null,
                    'breakdown'    => [],
                    'text_answers' => $textAnswers,
                ];
                continue;
            }

            // ТИП: matrix — тепловая карта
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

                foreach ($quiz->responses ?? [] as $response) {
                    $answerValue = $response->answers[$question->id] ?? null;
                    if (!is_array($answerValue)) continue;
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

                $stats[] = [
                    'question'        => $question->text,
                    'type'            => 'matrix',
                    'total'           => count($answers),
                    'avg'             => null,
                    'breakdown'       => [],
                    'matrix_rows'     => $rows,
                    'matrix_cols'     => $cols,
                    'matrix_grid'     => $grid,
                    'matrix_max'      => $maxCount,
                    'matrix_averages' => $rowAverages,
                ];
                continue;
            }

            // ТИП: radio / checkbox / scale
            $valuesForThisQuestion = $answers->pluck('answer_text')->filter()->values()->all();

            $total  = count($valuesForThisQuestion);
            $counts = array_count_values($valuesForThisQuestion);

            $breakdown = [];
            foreach ($counts as $label => $count) {
                $breakdown[] = [
                    'label'   => $label,
                    'count'   => $count,
                    'percent' => $total ? round($count / $total * 100, 1) : 0,
                ];
            }

            $avg = null;
            if ($question->type === 'scale' && $total > 0) {
                $numericValues = array_map('floatval', $valuesForThisQuestion);
                $avg = round(array_sum($numericValues) / count($numericValues), 2);
            }

            $chartType   = in_array($question->type, ['scale', 'checkbox']) ? 'bar' : 'pie';
            $chartConfig = [
                'type' => $chartType,
                'data' => [
                    'labels'   => array_column($breakdown, 'label'),
                    'datasets' => [['label' => 'Количество', 'data' => array_column($breakdown, 'count')]],
                ],
            ];
            $chartUrl = 'https://quickchart.io/chart?width=500&height=250&c=' . urlencode(json_encode($chartConfig));

            $stats[] = [
                'question'  => $question->text,
                'type'      => $question->type,
                'total'     => $total,
                'avg'       => $avg,
                'breakdown' => $breakdown,
                'chart_url' => $chartUrl,
            ];
        }

        return $stats;
    }
}