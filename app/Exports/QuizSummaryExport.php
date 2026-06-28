<?php

namespace App\Exports;

use App\Models\Quiz;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class QuizSummaryExport implements FromView
{
    protected Quiz $quiz;
    protected array $stats;

    public function __construct(Quiz $quiz, array $stats)
    {
        $this->quiz = $quiz;
        $this->stats = $stats;
    }

    public function view(): View
    {
        return view('exports.survey-summary', [
            'survey' => $this->quiz,
            'stats' => $this->stats,
        ]);
    }
}