<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Response;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::all(); // Загружаем только сами опросы, без вопросов
        return view('quiz.index', compact('quizzes'));
    }

    // Страница конкретного опроса
    public function show($id)
    {
        // Находим опрос по ID вместе с его отсортированными вопросами. Если не найден — выдаем ошибку 404
        $quiz = Quiz::with('questions')->findOrFail($id);
        return view('quiz.show', compact('quiz'));
    }

    // Сохранение текстовых и шкальных ответов пользователей
    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string', 
        ]);

        foreach ($validated['answers'] as $questionId => $answerText) {
            Response::create([
                'question_id' => $questionId,
                'answer_text' => $answerText,
            ]);
        }

        // Возвращаем пользователя обратно на страницу этого же опроса с успехом
        return redirect()->route('quiz.show', $id)->with('success', 'Ваши ответы успешно сохранены!');
    }

    public function destroy($id)
{
    $quiz = Quiz::findOrFail($id);

    // Удаляем все связанные ответы пользователей, чтобы не нарушать связи в БД
    foreach ($quiz->questions as $question) {
        $question->responses()->delete(); // Удаляем ответы на этот вопрос
    }
    
    // Удаляем сами вопросы
    $quiz->questions()->delete();

    // Удаляем опрос
    $quiz->delete();

    // Возвращаем пользователя на главную с сообщением
    return redirect()->route('index')->with('success', 'Опрос успешно удален!');
}
}
