<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        h1 { font-size: 18px; }
        h3 { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #999; padding: 5px; text-align: center; }
        .text-answer { background: #f5f5f5; padding: 6px; margin-bottom: 4px; }
    </style>
</head>
<body>
    <h1>{{ $survey->title }}</h1>
    <p>{{ $survey->description }}</p>

    @foreach($stats as $stat)
        <h3>{{ $stat['question'] }}</h3>

        @if($stat['type'] === 'text')
            <p>Всего ответов: {{ $stat['total'] }}</p>
            @foreach($stat['text_answers'] as $answer)
                <div class="text-answer">{{ $answer }}</div>
            @endforeach

        @elseif($stat['type'] === 'matrix')
            <table>
                <tr>
                    <th></th>
                    @foreach($stat['matrix_cols'] as $col)
                        <th>{{ $col }}</th>
                    @endforeach
                    <th>Среднее</th>
                </tr>
                @foreach($stat['matrix_rows'] as $row)
                    <tr>
                        <td><strong>{{ $row }}</strong></td>
                        @foreach($stat['matrix_cols'] as $col)
                            @php
                                $count = $stat['matrix_grid'][$row][$col] ?? 0;
                                $intensity = $stat['matrix_max'] > 0 ? $count / $stat['matrix_max'] : 0;
                                $gray = 255 - round($intensity * 180); // dompdf rgba плохо умеет, делаем градации серого/оттенка
                            @endphp
                            <td style="background-color: rgb(255,{{ $gray }},{{ $gray }});">{{ $count }}</td>
                        @endforeach
                        <td><strong>{{ $stat['matrix_averages'][$row] ?? '—' }}</strong></td>
                    </tr>
                @endforeach
            </table>

        @else
            <p>Всего ответов: {{ $stat['total'] }}</p>
            @if($stat['avg'] !== null)
                <p>Среднее значение: {{ $stat['avg'] }}</p>
            @endif

            <table>
                <tr><th>Вариант</th><th>Количество</th><th>%</th></tr>
                @foreach($stat['breakdown'] as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td>{{ $row['count'] }}</td>
                        <td>{{ $row['percent'] }}%</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endforeach
</body>
</html>