<div class="container py-5" style="max-width: 850px;">
    <a href="{{ route('index') }}" class="btn btn-link link-primary p-0 mb-4 text-decoration-none fw-semibold">
        ← Вернуться к списку опросов
    </a>

    <div class="card shadow border-0 rounded-3 p-4 bg-white mb-4">
        <h1 class="h3 fw-bold text-dark mb-2">Аналитика результатов</h1>
        <h2 class="h5 text-muted mb-0">{{ $quiz->title }}</h2>
    </div>

    <!-- Перебор вопросов -->
    @foreach($quiz->questions->sortBy('position') as $question)
        @php $stats = $chartsData[$question->id]; @endphp

        <div class="card shadow-sm border-0 rounded-3 p-4 bg-white mb-4 question-chart-card" 
             data-id="{{ $question->id }}" 
             data-type="{{ $stats['type'] }}"
             data-labels='@json($stats['labels'])'
             data-values='@json($stats['data'])'>
            
            <h4 class="fw-bold text-secondary mb-3">{{ $question->text }}</h4>
            
            <!-- Информационные метрики -->
            <div class="d-flex gap-4 mb-4 border-bottom pb-3">
                <div>
                    <span class="text-muted small d-block">Всего ответов</span>
                    <strong class="fs-5 text-dark">{{ $stats['total'] }}</strong>
                </div>
                @if($stats['average'] !== null)
                    <div>
                        <span class="text-muted small d-block">Средняя оценка</span>
                        <strong class="fs-5 text-primary">{{ $stats['average'] }} / 10</strong>
                    </div>
                @endif
            </div>

            <!-- Область вывода графика -->
            @if(in_array($question->type, ['radio', 'checkbox', 'scale']))
                <div class="mx-auto" style="max-height: 320px; max-width: 500px;">
                    <canvas id="chart_{{ $question->id }}"></canvas>
                </div>
            @else
                <!-- Для обычных текстовых вопросов выводим список последних ответов -->
                <span class="text-muted small d-block mb-2">Последние текстовые ответы:</span>
                <div class="bg-light rounded p-3" style="max-height: 150px; overflow-y: auto;">
                    @forelse(\App\Models\Answer::where('question_id', $question->id)->latest()->take(5)->pluck('value') as $ans)
                        <div class="border-bottom py-1 text-dark small">{{ $ans }}</div>
                    @empty
                        <div class="text-muted small">Ответов пока нет</div>
                    @endforelse
                </div>
            @endif
        </div>
    @endforeach
</div>

<!-- Подключение Chart.js и внешнего JS-скрипта рендеринга -->
<script src="https://jsdelivr.net"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    const cards = document.querySelectorAll('.question-chart-card');

    cards.forEach(card => {
        const qId = card.getAttribute('data-id');
        const qType = card.getAttribute('data-type');
        const labels = JSON.parse(card.getAttribute('data-labels') || '[]');
        const values = JSON.parse(card.getAttribute('data-values') || '[]');
        
        const canvas = document.getElementById(`chart_${qId}`);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Конфигурация для круговых диаграмм (выбор вариантов)
        if (qType === 'radio' || qType === 'checkbox') {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            '#0d6efd', '#20c997', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.label}: ${context.raw}%`;
                                }
                            }
                        }
                    }
                }
            });
        } 
        // Конфигурация для столбчатых диаграмм (шкала оценок)
        else if (qType === 'scale') {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Количество распределения оценок',
                        data: values,
                        backgroundColor: '#0d6efd',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
    });
});
</script>