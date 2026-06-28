@extends('layouts.app')
<title>Опрос</title>
@section('content')
    
<div class="container py-5" style="max-width: 700px;">
        <!-- Хедер панели -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-secondary fw-bold">Доступные опросы</h1>
            <a href="{{ route('questions.create') }}" class="btn btn-primary fw-semibold shadow-sm">+ Создать опрос</a>
        </div>

        <!-- Уведомления об успехе -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Проверка наличия опросов -->
        @if($quizzes->isEmpty())
            <div class="text-center py-5 bg-white rounded-3 shadow-sm border">
                <p class="text-muted mb-0 fs-5">Опросы еще не созданы. Нажмите кнопку выше, чтобы добавить первый опрос!</p>
            </div>
        @else
            <!-- Список карточек опросов -->
            <div class="d-flex flex-column gap-3">
                @foreach($quizzes as $quiz)
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div class="text-truncate me-3">
                                <a href="{{ route('quiz.show', $quiz->id) }}" class="text-decoration-none text-dark fw-bold fs-5 hover-primary">
                                    {{ $quiz->title }}
                                </a>
                            </div>
                            
                            <div class="d-flex gap-2 flex-shrink-0">
                                <a href="{{ route('quiz.show', $quiz->id) }}" class="btn btn-success fw-semibold px-3">Пройти →</a>
                                
                                <form action="{{ route('quiz.destroy', $quiz->id) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить этот опрос и все ответы на него? Все данные будут потеряны безвозвратно.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger fw-semibold">Удалить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Подключение Bootstrap 5 JS для работы анимаций и закрытия алертов -->
    <script src="https://jsdelivr.net"></script>

@endsection