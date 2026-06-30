<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Quiz;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\QuizRequest; 

Route::middleware('throttle:5,1')->group(function () {
Route::post('/login', function (LoginRequest $request) {
    $request->validate([
        'email'=>'required|email',
        'password'=>'required',
    ]);

    $user = User::where('email', $request->email)->first(); 

    if (!$user || !Hash::check($request->password, $user->password)){
        return response()->json(['message'=>'Неверный email или пароль'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json(['token'=>$token]);
});
});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
Route::middleware('auth:sanctum')->group(function (){

    Route::get('/quizzes', function(){
        return Quiz::all(['id', 'title']);
    });

    Route::get('/quizzes/{quiz}/stats', [App\Http\Controllers\SurveyDashboardController::class, 'apiStats']);


    //создание опроса
    Route::post('/quizzes', function (QuizRequest $request) {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $quiz = Quiz::create([
            'title' => $request->title,
        ]);

        return response()->json($quiz, 201);
    });

    //редактирование опроса
    Route::put('/quizzes/{quiz}', function (QuizRequest $request, Quiz $quiz) {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $quiz->update([
            'title' => $request->title,
        ]);

        return response()->json($quiz);
    });

    //удаление опроса
    Route::delete('/quizzes/{quiz}', function (Quiz $quiz) {
        $quiz->delete();
        return response()->json(['message' => 'Опрос удалён']);
    });
});
});