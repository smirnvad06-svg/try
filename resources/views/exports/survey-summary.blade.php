<table style="border-collapse: collapse; font-family: Calibri, Arial, sans-serif;">
    <colgroup>
        <col style="width: 220px;">
        <col style="width: 120px;">
        <col style="width: 100px;">
    </colgroup>

    <tr>
        <th colspan="3" style="font-weight: bold; font-size: 16px; text-align: left; padding: 10px; border: 1px solid #999;">
            {{ $survey->title }}
        </th>
    </tr>
    <tr><td colspan="3" style="height: 10px; border: none;"></td></tr>

    @foreach($stats as $stat)
        <tr>
            <td colspan="3" style="font-weight: bold; background-color: #4472C4; color: white; padding: 6px; border: 1px solid #999;">
                {{ $stat['question'] }}
            </td>
        </tr>
        <tr>
            <td colspan="3" style="padding: 4px; border: 1px solid #ccc;">Всего ответов: {{ $stat['total'] }}</td>
        </tr>

        @if($stat['avg'] !== null)
            <tr>
                <td colspan="3" style="padding: 4px; border: 1px solid #ccc;">Среднее значение: {{ $stat['avg'] }}</td>
            </tr>
        @endif

        @if(!empty($stat['breakdown']))
            <tr>
                <th style="font-weight: bold; border: 1px solid #999; background-color: #D9E1F2; padding: 5px;">Вариант</th>
                <th style="font-weight: bold; border: 1px solid #999; background-color: #D9E1F2; padding: 5px;">Количество</th>
                <th style="font-weight: bold; border: 1px solid #999; background-color: #D9E1F2; padding: 5px;">%</th>
            </tr>
            @foreach($stat['breakdown'] as $row)
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;">{{ $row['label'] }}</td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;">{{ $row['count'] }}</td>
                    <td style="border: 1px solid #ccc; padding: 5px; text-align: center;">{{ $row['percent'] }}%</td>
                </tr>
            @endforeach
        @endif

        @if($stat['type'] === 'text' && !empty($stat['text_answers']))
            @foreach($stat['text_answers'] as $answer)
                <tr>
                    <td colspan="3" style="color: #555; font-style: italic; border: 1px solid #ccc; padding: 4px;">— {{ $answer }}</td>
                </tr>
            @endforeach
        @endif

        @if($stat['type'] === 'matrix')
            <tr>
                <th style="border: 1px solid #999; background-color: #D9E1F2; padding: 5px;"></th>
                @foreach($stat['matrix_cols'] as $col)
                    <th style="border: 1px solid #999; background-color: #D9E1F2; padding: 5px;">{{ $col }}</th>
                @endforeach
                <th style="border: 1px solid #999; background-color: #D9E1F2; padding: 5px;">Среднее</th>
            </tr>
            @foreach($stat['matrix_rows'] as $row)
                <tr>
                    <td style="border: 1px solid #ccc; font-weight: bold; padding: 5px;">{{ $row }}</td>
                    @foreach($stat['matrix_cols'] as $col)
                        <td style="border: 1px solid #ccc; text-align: center; padding: 5px;">
                            {{ $stat['matrix_grid'][$row][$col] ?? 0 }}
                        </td>
                    @endforeach
                    <td style="border: 1px solid #ccc; text-align: center; font-weight: bold; padding: 5px;">
                        {{ $stat['matrix_averages'][$row] ?? '—' }}
                    </td>
                </tr>
            @endforeach
        @endif

        <tr><td colspan="3" style="height: 15px; border: none;"></td></tr>
    @endforeach
</table>