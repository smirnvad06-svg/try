<?php

namespace App\Exports;

use App\Models\Quiz;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuizResultsExport implements FromCollection, WithHeadings
{
    protected Quiz $quiz;

    public function __construct(Quiz $quiz)
    {
        $this->quiz = $quiz->load('questions.responses');
    }

    public function headings(): array
    {
        return ['Вопрос', 'Ответ'];
    }

    public function collection()
    {
        $rows = [];
        foreach ($this->quiz->questions as $question) {
            foreach ($question->responses as $response) {
                $rows[] = [$question->text, $response->answer_text];
            }
        }
        return collect($rows);
    }
}