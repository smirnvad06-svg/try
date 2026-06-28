<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Auth;
use App\Models\Survey;
use App\Models\Question;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SurveyDashboardController as ControllersSurveyDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [QuizController::class, 'index'])->name('index');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::get('/quiz/{id}', [QuizController::class, 'show'])->name('quiz.show');
Route::post('/quiz', [QuizController::class, 'store'])->name('quiz.store');
Route::post('/quiz/{id}/vote', [QuizController::class, 'store'])->name('quiz.store');
Route::delete('/quiz/{id}', [QuizController::class, 'destroy'])->name('quiz.destroy');

Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
Route::post('/questions/store', [QuestionController::class, 'store'])->name('questions.store');

Route::get('/survey/{id}', [SurveyController::class, 'show'])->name('survey.show');
Route::post('/survey/{id}/submit', [SurveyController::class, 'submit'])->name('survey.submit');

Route::get('/surveys/{survey}/dashboard', [ControllersSurveyDashboardController::class, 'show'])->name('survey.dashboard');
Route::get('/surveys/{survey}/export/pdf', [ControllersSurveyDashboardController::class, 'exportPdf'])->name('survey.export.pdf');
Route::get('/surveys/{survey}/export-excel', [ControllersSurveyDashboardController::class, 'exportExcel'])->name('survey.export.excel');
Route::get('/surveys/{survey}/export-csv', [ControllersSurveyDashboardController::class, 'exportCsv'])->name('survey.export.csv');
Route::get('/surveys/{survey}/export/summary-excel', [ControllersSurveyDashboardController::class, 'exportSummaryExcel']);

Route::post('/survey/{id}/submit', [SurveyController::class, 'submit'])->name('survey.submit');

Route::get('/quiz/{id}/stats', [AnalyticsController::class, 'stats'])->name('quiz.stats');

Route::get('/quiz/{id}', [QuestionController::class, 'show'])->name('quiz.show');

// Обработка отправки ответов
Route::post('/quiz/{id}/submit', [QuestionController::class, 'submitAnswer'])->name('quiz.submit');
Route::get('/seed-survey', function () {
    // 1. Создаем сам опрос
    $survey = Survey::create([
        'title' => 'Тестирование нового веб-приложения',
        'description' => 'Пожалуйста, ответьте на вопросы ниже, чтобы мы могли проверить работу всех типов полей согласно ТЗ.',
        'is_active' => true,
    ]);

    // 2. Вопрос типа Radio (Одиночный выбор)
    Question::create([
        'survey_id' => $survey->id,
        'text' => 'Каким браузером вы пользуетесь чаще всего?',
        'type' => 'radio',
        'options' => ['choices' => ['Google Chrome', 'Yandex Browser', 'Firefox', 'Safari']],
        'order_index' => 1,
        'is_required' => true,
    ]);

    // 3. Вопрос типа Checkbox (Множественный выбор)
    Question::create([
        'survey_id' => $survey->id,
        'text' => 'Какие языки программирования вы знаете?',
        'type' => 'checkbox',
        'options' => ['choices' => ['PHP', 'JavaScript', 'Python', 'SQL']],
        'order_index' => 2,
        'is_required' => false,
    ]);

    // 4. Текстовый вопрос
    Question::create([
        'survey_id' => $survey->id,
        'text' => 'Опишите ваши впечатления от скорости работы интерфейса:',
        'type' => 'text',
        'order_index' => 3,
        'is_required' => true,
    ]);

    // 5. Шкала оценок (1-10)
    Question::create([
        'survey_id' => $survey->id,
        'text' => 'Оцените общую стабильность системы по шкале от 1 до 10:',
        'type' => 'scale',
        'order_index' => 4,
        'is_required' => true,
    ]);

    // 6. Матрица
    Question::create([
        'survey_id' => $survey->id,
        'text' => 'Оцените удобство отдельных модулей:',
        'type' => 'matrix',
        'options' => [
            'rows' => ['Конструктор опросов', 'Панель аналитики', 'Скорость экспорта'],
            'columns' => ['Плохо', 'Нормально', 'Отлично']
        ],
        'order_index' => 5,
        'is_required' => false,
    ]);

    return "Тестовый опрос успешно создан с ID: " . $survey->id . ". Теперь перейдите по адресу /survey/" . $survey->id;
});

Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');