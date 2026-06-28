<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доступные опросы</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 py-10 px-4">

    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">Список доступных опросов</h1>
            <p class="mt-2 text-gray-600">Выберите опрос из списка ниже для прохождения теста.</p>
        </header>

        <!-- Проверяем, есть ли вообще опросы в базе -->
        @if($surveys->isEmpty())
            <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
                Опросы пока не созданы. Перейдите по адресу <code class="bg-gray-100 px-2 py-1 rounded text-red-600">/seed-survey</code>, чтобы сгенерировать тестовый опрос.
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2">
                @foreach($surveys as $survey)
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $survey->title }}</h2>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                {{ $survey->description ?? 'Описание отсутствует.' }}
                            </p>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Активен
                            </span>
                            <!-- Динамическая ссылка на конкретную страницу опроса по его ID -->
                            <a href="{{ route('survey.show', $survey->id) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Пройти опрос
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</body>
</html>