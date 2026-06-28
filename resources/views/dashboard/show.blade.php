<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: center; }
        .text-answer { background: #f5f5f5; padding: 8px; margin-bottom: 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>{{ $survey->title }}</h1>
    <a href="{{ route('survey.export.pdf', $survey) }}">Скачать PDF</a> |
    <a href="{{ route('survey.export.excel', $survey) }}">Скачать Excel</a> |
    <a href="{{ route('survey.export.csv', $survey) }}">Скачать CSV</a>

    @foreach($stats as $index => $stat)
        <hr>
        <h3>{{ $stat['question'] }}</h3>

        {{-- ТИП: text — просто список ответов --}}
        @if($stat['type'] === 'text')
            <p>Всего ответов: {{ $stat['total'] }}</p>
            @forelse($stat['text_answers'] as $answer)
                <div class="text-answer">{{ $answer }}</div>
            @empty
                <p><em>Нет ответов</em></p>
            @endforelse

        {{-- ТИП: matrix — таблица строк со средним и разбивкой --}}
        @elseif($stat['type'] === 'matrix')
    <table border="1" cellpadding="8" style="border-collapse: collapse;">
        <tr>
            <th></th>
            @foreach($stat['matrix_cols'] as $col)
                <th>{{ $col }}</th>
            @endforeach
            <th>Среднее</th> {{-- новая колонка --}}
        </tr>
        @foreach($stat['matrix_rows'] as $row)
            <tr>
                <td><strong>{{ $row }}</strong></td>
                @foreach($stat['matrix_cols'] as $col)
                    @php
                        $count = $stat['matrix_grid'][$row][$col] ?? 0;
                        $intensity = $stat['matrix_max'] > 0 ? $count / $stat['matrix_max'] : 0;
                        $bg = 'rgba(255, 99, 71, ' . round($intensity, 2) . ')';
                    @endphp
                    <td style="background-color: {{ $bg }}; text-align:center; min-width: 40px;">
                        {{ $count }}
                    </td>
                @endforeach
                <td style="text-align:center; font-weight:bold;">
                    {{ $stat['matrix_averages'][$row] ?? '—' }}
                </td>
            </tr>
        @endforeach
    </table>

        {{-- ОБЫЧНЫЙ ГРАФИК: radio / checkbox / scale --}}
        @else
            @php
                $chartType = in_array($stat['type'], ['scale', 'checkbox']) ? 'bar' : 'pie';
            @endphp

            <p>Всего ответов: {{ $stat['total'] }}</p>
            @if($stat['avg'] !== null)
                <p>Среднее значение: {{ $stat['avg'] }}</p>
            @endif

            <div style="max-width: 400px;">
                <canvas id="chart-{{ $index }}"></canvas>
            </div>

            <script>
                new Chart(document.getElementById('chart-{{ $index }}'), {
                    type: '{{ $chartType }}',
                    data: {
                        labels: {!! json_encode(array_column($stat['breakdown'], 'label')) !!},
                        datasets: [{
                            label: 'Количество ответов',
                            data: {!! json_encode(array_column($stat['breakdown'], 'count')) !!},
                        }]
                    }
                });
            </script>
        @endif
    @endforeach
</body>
</html>