<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->title }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 py-10 px-4">

    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6 md:p-8">
        
        @if(session('success'))
            <div class="text-center py-8">
                <div class="text-6xl text-green-500 mb-4">✓</div>
                <h2 class="text-2xl font-bold text-green-600">Успешно!</h2>
                <p class="mt-2 text-gray-600">{{ session('success') }}</p>
            </div>
        @else

            <header class="mb-8 border-b pb-4">
                <h1 class="text-3xl font-extrabold text-gray-900">{{ $survey->title }}</h1>
                @if($survey->description)
                    <p class="mt-2 text-gray-600 text-lg">{{ $survey->description }}</p>
                @endif
            </header>

            <form action="{{ route('survey.submit', $survey->id) }}" method="POST" class="space-y-8">
                @csrf

                @foreach($survey->questions as $question)
                    <div class="p-5 border rounded-xl bg-gray-50 border-gray-200">
                        
                        <fieldset class="mb-2">
                            <legend class="text-xl font-semibold text-gray-900 mb-4">
                                {{ $question->text }}
                                @if($question->is_required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </legend>

                            @if($question->type === 'radio')
                                <div class="space-y-2">
                                    @foreach($question->options['choices'] ?? [] as $choice)
                                        <label class="flex items-center space-x-3 cursor-pointer">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $choice }}" class="h-5 w-5 text-indigo-600 border-gray-300">
                                            <span class="text-gray-700">{{ $choice }}</span>
                                        </label>
                                    @endforeach
                                </div>

                            @elseif($question->type === 'checkbox')
                                <div class="space-y-2">
                                    @foreach($question->options['choices'] ?? [] as $choice)
                                        <label class="flex items-center space-x-3 cursor-pointer">
                                            <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $choice }}" class="h-5 w-5 text-indigo-600 border-gray-300 rounded">
                                            <span class="text-gray-700">{{ $choice }}</span>
                                        </label>
                                    @endforeach
                                </div>

                            @elseif($question->type === 'text')
                                <textarea name="answers[{{ $question->id }}]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white" placeholder="Введите ответ..."></textarea>

                            @elseif($question->type === 'scale')
                                <div class="flex justify-between gap-2 overflow-x-auto py-2">
                                    @for($i = 1; $i <= 10; $i++)
                                        <label class="flex flex-col items-center p-2 min-w-[40px] border border-gray-300 rounded-lg cursor-pointer bg-white hover:bg-indigo-50">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}" class="h-4 w-4 text-indigo-600">
                                            <span class="text-sm font-medium mt-1 text-gray-700">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>

                            @elseif($question->type === 'matrix')
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-gray-200">
                                                <th class="p-2 text-sm font-medium text-gray-500">Критерий</th>
                                                @foreach($question->options['columns'] ?? [] as $col)
                                                    <th class="p-2 text-sm font-medium text-gray-500 text-center">{{ $col }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($question->options['rows'] ?? [] as $rowIndex => $row)
                                                <tr class="border-b border-gray-100">
                                                    <td class="p-2 font-medium text-gray-900">{{ $row }}</td>
                                                    @foreach($question->options['columns'] ?? [] as $col)
                                                        <td class="p-2 text-center">
                                                            <input type="radio" name="answers[{{ $question->id }}][{{ $rowIndex }}]" value="{{ $col }}" class="h-4 w-4 text-indigo-600">
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        </fieldset>

                        @error("answers.{$question->id}")
                            <p class="mt-1 text-sm text-red-600">Это обязательный вопрос.</p>
                        @enderror

                    </div>
                @endforeach

                <div class="pt-4">
                    <button type="submit" class="w-full md:w-auto px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition-all cursor-pointer">
                        Отправить ответы
                    </button>
                </div>
            </form>
        @endif
    </div>

</body>
</html>