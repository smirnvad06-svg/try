<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Answer;

class QuestionController extends Controller
{
    // Показ формы создания опроса (файл resources/views/quiz/create.blade.php)
    public function create()
    {
        return view('quiz.create');
    }

    public function store(Request $request)
    {
        // 1. Валидация: добавляем новые типы 'radio' и 'checkbox'
        $validated = $request->validate([
            'quiz_title' => 'required|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string|max:255',
            'questions.*.type' => 'required|string|in:text,scale,radio,checkbox', // Расширили список типов
            'questions.*.options' => 'nullable|array', // Изменили string на array!
            'questions.*.options.*' => 'nullable|string|max:255',
        ]);

        // 2. Создаем опрос
        $quiz = Quiz::create([
            'title' => $validated['quiz_title']
        ]);

        // 3. Сохраняем вопросы с учетом их Drag-and-Drop позиции
        foreach (array_values($validated['questions']) as $index => $questionData) {
        
        // Очищаем массив вариантов от пустых значений, если пользователь оставил поля незаполненными
        $options = null;
        if (in_array($questionData['type'], ['radio', 'checkbox']) && !empty($questionData['options'])) {
            $filteredOptions = array_filter($questionData['options'], fn($value) => !is_null($value) && $value !== '');
            // Сохраняем в базу как JSON-строку
            $options = json_encode(array_values($filteredOptions), JSON_UNESCAPED_UNICODE);
        }

        $quiz->questions()->create([
            'text' => $questionData['text'],
            'type' => $questionData['type'],
            'position' => $index,
            'options' => $options, // Записываем JSON массив вариантов или null
        ]);
    }

    return redirect()->route('index')->with('success', 'Опрос успешно создан!');
    }

    public function show($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        return view('quiz.show', compact('quiz'));
    }

    // 2. Сохранение ответов пользователя
    public function submitAnswer(Request $request, $id)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        foreach ($request->answers as $questionId => $value) {
            Answer::create([
                'question_id' => $questionId,
                // Если пришли чекбоксы (массив), они автоматически сохранятся в JSON благодаря casts в модели Answer
                'value' => is_array($value) ? array_values($value) : $value,
            ]);
        }

        // Перенаправляем обратно на эту же страницу опроса с флагом успеха
        return redirect()->route('quiz.show', $id)->with('success', 'Спасибо! Ваши ответы успешно сохранены.');
    }
}


