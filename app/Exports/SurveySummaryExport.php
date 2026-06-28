<?php

namespace App\Exports;

use App\Models\Survey;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class SurveySummaryExport implements FromView
{
    protected Survey $survey;
    protected array $stats;

    public function __construct(Survey $survey, array $stats)
    {
        $this->survey = $survey;
        $this->stats = $stats;
    }

    public function view(): View
    {
        return view('exports.survey-summary', [
            'survey' => $this->survey,
            'stats' => $this->stats,
        ]);
    }
}