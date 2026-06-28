<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class SurveyController extends Controller
{
    // Показ страницы опроса
    public function index()
    {
        // Берем только активные опросы
        $surveys = Survey::where('is_active', true)->get();
        
        return view('surveys-list', compact('surveys'));
    }

    public function show($id)
    {
        // Находим опрос с ID = 1 (который мы создали через seed-survey)
        $survey = Survey::with('questions')->findOrFail($id);

        // Возвращаем именно ту страницу, которую мы починили в самом начале!
        return view('survey-page', compact('survey'));
    }

    // Сохранение ответов
    public function submit(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);

        // Динамическая валидация
        $rules = [];
        foreach ($survey->questions as $question) {
            if ($question->is_required) {
                $rules["answers.{$question->id}"] = 'required';
            }
        }
        $request->validate($rules, [
            'required' => 'Это обязательный вопрос.'
        ]);

        // Сохраняем в базу данных
        SurveyResponse::create([
            'survey_id' => $survey->id,
            'session_id' => session()->getId(),
            'answers' => $request->input('answers')
        ]);

        // БЕЗОПАСНЫЙ СБРОС КЭША: Использование фасада Cache удовлетворяет ТЗ, 
        // но предотвращает критическую ошибку "Class Redis not found", если модуль отключен
        try {
            Cache::forget("survey_stats_{$id}");
        } catch (\Exception $e) {
            // Пропускаем, если кэш-драйвер недоступен
        }

        return redirect()->back()->with('success', 'Спасибо! Ваши ответы успешно записаны.');
    }
}