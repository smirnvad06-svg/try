<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Answer;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function stats($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $chartsData = [];

        foreach ($quiz->questions as $question) {
            // Получаем все сырые ответы из базы для данного вопроса
            $rawAnswers = Answer::where('question_id', $question->id)->pluck('value')->toArray();
            $totalResponses = count($rawAnswers);

            $questionStats = [
                'type' => $question->type,
                'total' => $totalResponses,
                'labels' => [],
                'data' => [],
                'average' => null
            ];

            if ($question->type === 'scale') {
                // Вычисляем среднее значение для шкалы 1-10
                $numericAnswers = array_filter($rawAnswers, 'is_numeric');
                $questionStats['average'] = count($numericAnswers) > 0 ? round(array_sum($numericAnswers) / count($numericAnswers), 2) : 0;
                
                // Подготовка данных для столбчатой диаграммы (распределение оценок 1-10)
                $scaleCounts = array_fill(1, 10, 0);
                foreach ($numericAnswers as $val) {
                    $scaleCounts[(int)$val]++;
                }
                $questionStats['labels'] = array_keys($scaleCounts);
                $questionStats['data'] = array_values($scaleCounts);

            } elseif (in_array($question->type, ['radio', 'checkbox'])) {
                // Для выбора вариантов (варианты хранятся в модели как массив)
                $options = $question->options ?? [];
                $counts = array_fill_keys($options, 0);

                foreach ($rawAnswers as $answer) {
                    // Если это checkbox, данные могут прийти как массив
                    if (is_array($answer)) {
                        foreach ($answer as $subValue) {
                            if (isset($counts[$subValue])) $counts[$subValue]++;
                        }
                    } else {
                        if (isset($counts[$answer])) $counts[$answer]++;
                    }
                }

                $questionStats['labels'] = array_keys($counts);
                // Считаем проценты выбора для каждого варианта
                foreach ($counts as $opt => $count) {
                    $percentage = $totalResponses > 0 ? round(($count / $totalResponses) * 100, 1) : 0;
                    $questionStats['data'][] = $percentage;
                }
            }

            $chartsData[$question->id] = $questionStats;
        }

        return view('quiz.stats', compact('quiz', 'chartsData'));
    }
}