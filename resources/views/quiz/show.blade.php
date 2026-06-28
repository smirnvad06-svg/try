@extends('layouts.app')

@section('content')
    
<div class="container py-5" style="max-width: 650px;">
        
    <!-- Ссылка Назад -->
    <a href="{{ route('index') }}" class="btn btn-link link-primary p-0 mb-4 text-decoration-none fw-semibold">
        ← Вернуться к списку опросов
    </a>

    <!-- Уведомления об успехе -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Контейнер формы опроса -->
    <div class="card shadow border-0 rounded-3 p-4 bg-white">
        <h2 class="card-title fw-bold text-dark mb-2">{{ $quiz->title }}</h2>
        <p class="text-muted small mb-4">Пожалуйста, ответьте на все вопросы ниже.</p>
        
        <hr class="text-muted my-3 opacity-25">
        
        <form action="{{ route('quiz.store', $quiz->id) }}" method="POST">
            @csrf

            <!-- Рендеринг вопросов -->
            @foreach($quiz->questions as $question)
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary mb-2">{{ $question->text }}</label>
                    
                    {{-- 1. Оценка по шкале --}}
                    @if($question->type === 'scale')
                        <select name="answers[{{ $question->id }}]" class="form-select form-select-lg shadow-sm" required>
                            <option value="" disabled selected>Выберите оценку от 1 до 10...</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>

                    {{-- 2. Один из списка (Radio) --}}
                    @elseif($question->type === 'radio')
                        @php
                            // Безопасное декодирование, если пришла строка
                            $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
                        @endphp
                        @if(!empty($options) && is_array($options))
                            <div class="d-flex flex-column gap-2">
                                @foreach($options as $index => $option)
                                    <div class="form-check border rounded p-2 px-3 bg-light shadow-sm">
                                        <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}" id="q_{{ $question->id }}_{{ $index }}" required>
                                        <label class="form-check-label w-100 ms-1 text-dark" for="q_{{ $question->id }}_{{ $index }}">{{ $option }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    {{-- 3. Несколько из списка (Checkbox) --}}
                    @elseif($question->type === 'checkbox')
                        @php
                            // Безопасное декодирование, если пришла строка
                            $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
                        @endphp
                        @if(!empty($options) && is_array($options))
                            <div class="d-flex flex-column gap-2">
                                @foreach($options as $index => $option)
                                    <div class="form-check border rounded p-2 px-3 bg-light shadow-sm">
                                        <input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option }}" id="q_{{ $question->id }}_{{ $index }}">
                                        <label class="form-check-label w-100 ms-1 text-dark" for="q_{{ $question->id }}_{{ $index }}">{{ $option }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    {{-- 4. Текстовый ответ (по умолчанию) --}}
                    @else
                        <input type="text" name="answers[{{ $question->id }}]" class="form-control form-control-lg shadow-sm" placeholder="Введите ваш ответ..." required>
                    @endif
                </div>
            @endforeach

            <!-- Кнопка отправки формы -->
            <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm mt-3 py-2">
                Отправить мои ответы
            </button>
        </form>
    </div>
</div>
    
@if(session('success'))
    <div class="alert alert-success shadow-sm mb-4 rounded-3 p-3" role="alert">
        <h5 class="alert-heading fw-bold mb-1">👍 Отлично!</h5>
        <p class="mb-2 small">{{ session('success') }}</p>
        <hr class="my-2 opacity-25">
        <!-- Ссылка на страницу аналитики -->
        <a href="{{ route('quiz.stats', $quiz->id) }}" class="btn btn-success btn-sm fw-bold">
            Посмотреть общие результаты опроса →
        </a>
    </div>
@endif
    
@endsection