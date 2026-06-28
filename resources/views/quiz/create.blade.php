@extends('layouts.app')
<title>Создать опрос</title>
@section('content')
    
<title>Создать опрос</title>

    <div class="container py-5" style="max-width: 750px;">
    <a href="{{ route('index') }}" class="btn btn-link link-primary p-0 mb-4 text-decoration-none fw-semibold">
        ← На главную к опросам
    </a>

    <div class="card shadow border-0 rounded-3 p-4 bg-white">
        <h1 class="h3 card-title fw-bold text-dark mb-4">Создание нового опроса</h1>

        <form action="{{ route('questions.store') }}" method="POST" id="quiz-form">
            @csrf

            <div class="mb-4">
                <label for="quiz_title" class="form-label fw-bold text-secondary">Название всего опроса / анкеты:</label>
                <input type="text" id="quiz_title" name="quiz_title" class="form-control form-control-lg shadow-sm" placeholder="Например: Оценка качества работы" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold text-secondary mb-2">
                    Вопросы в этом опросе (перетаскивайте блоки для сортировки):
                </label>
                
                <div id="questions-container" class="d-flex flex-column gap-3 mb-3">
                    <!-- Первый дефолтный вопрос -->
                    <div class="question-row card border shadow-sm rounded-3 bg-light p-3" draggable="true">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="drag-handle text-muted fw-semibold bg-white border rounded px-2 py-1 shadow-sm fs-7">
                                ☰ Перетащить
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeQuestion(this)">Удалить вопрос</button>
                        </div>
                        
                        <div class="row g-3 align-items-center">
                            <div class="col">
                                <input type="text" class="form-control question-text" placeholder="Вопрос 1" required>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select type-select" onchange="toggleOptionsField(this)">
                                    <option value="text">Текстовый ответ (строка)</option>
                                    <option value="scale">Оценка по шкале (от 1 до 10)</option>
                                    <option value="radio">Один из списка (выбрать один)</option>
                                    <option value="checkbox">Несколько из списка (выбрать несколько)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="options-block mt-3 d-none">
                            <label class="form-label small fw-bold text-muted d-block mb-2">Варианты ответов:</label>
                            <div class="options-list d-flex flex-column gap-2 mb-2">
                                <div class="d-flex gap-2 option-item">
                                    <input type="text" class="form-control form-control-sm option-input" placeholder="Вариант ответа">
                                    <button type="button" class="btn btn-outline-danger btn-sm px-2 py-0" onclick="removeOption(this)">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-3 mt-1" onclick="addOptionField(this)">
                                + Добавить вариант
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="add-question" class="btn btn-outline-primary fw-semibold w-100 py-2 shadow-sm">
                    + Добавить еще вопрос
                </button>
            </div>

            <hr class="text-muted my-4 opacity-25">

            <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm py-2">
                Опубликовать опрос
            </button>
        </form>
    </div>
</div>

<!-- Подключение скрипта -->
<script>
// Жестко инициализируем контейнер
const container = document.getElementById('questions-container');

document.addEventListener("DOMContentLoaded", function() {
    updateInputsOrder();
    
    // Прямая привязка клика без inline-атрибутов
    const addBtn = document.getElementById('add-question');
    if (addBtn) {
        addBtn.onclick = function() {
            const div = document.createElement('div');
            div.className = 'question-row card border shadow-sm rounded-3 bg-light p-3';
            div.draggable = true;
            
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="drag-handle text-muted fw-semibold bg-white border rounded px-2 py-1 shadow-sm fs-7">
                        ☰ Перетащить
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeQuestion(this)">Удалить вопрос</button>
                </div>
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <input type="text" class="form-control question-text" required>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select type-select" onchange="toggleOptionsField(this)">
                            <option value="text">Текстовый ответ (строка)</option>
                            <option value="scale">Оценка по шкале (от 1 до 10)</option>
                            <option value="radio">Один из списка (выбрать один)</option>
                            <option value="checkbox">Несколько из списка (выбрать несколько)</option>
                        </select>
                    </div>
                </div>
                <div class="options-block mt-3 d-none">
                    <label class="form-label small fw-bold text-muted d-block mb-2">Варианты ответов:</label>
                    <div class="options-list d-flex flex-column gap-2 mb-2">
                        <div class="d-flex gap-2 option-item">
                            <input type="text" class="form-control form-control-sm option-input" placeholder="Вариант ответа">
                            <button type="button" class="btn btn-outline-danger btn-sm px-2 py-0" onclick="removeOption(this)">✕</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-3 mt-1" onclick="addOptionField(this)">
                        + Добавить вариант
                    </button>
                </div>
            `;
            
            container.appendChild(div);
            if (typeof addDragEvents === 'function') addDragEvents(div);
            updateInputsOrder();
        };
    }
});

function toggleOptionsField(selectElement) {
    const questionRow = selectElement.closest('.question-row');
    const optionsBlock = questionRow.querySelector('.options-block');
    const optionsInputs = optionsBlock.querySelectorAll('.option-input');
    
    if (selectElement.value === 'radio' || selectElement.value === 'checkbox') {
        optionsBlock.classList.remove('d-none');
        optionsInputs.forEach(input => input.required = true);
    } else {
        optionsBlock.classList.add('d-none');
        optionsInputs.forEach(input => {
            input.required = false;
            input.value = '';
        });
    }
}

function addOptionField(buttonElement) {
    const questionRow = buttonElement.closest('.question-row');
    const optionsList = questionRow.querySelector('.options-list');
    const div = document.createElement('div');
    div.className = 'd-flex gap-2 option-item';
    div.innerHTML = `
        <input type="text" class="form-control form-control-sm option-input" placeholder="Вариант ответа" required>
        <button type="button" class="btn btn-outline-danger btn-sm px-2 py-0" onclick="removeOption(this)">✕</button>
    `;
    optionsList.appendChild(div);
    updateInputsOrder();
}

function removeOption(buttonElement) {
    const optionItem = buttonElement.closest('.option-item');
    const optionsList = optionItem.closest('.options-list');
    if (optionsList.querySelectorAll('.option-item').length > 1) {
        optionItem.remove();
        updateInputsOrder();
    } else {
        optionItem.querySelector('input').value = '';
    }
}

function removeQuestion(buttonElement) {
    const questionRow = buttonElement.closest('.question-row');
    if (container.querySelectorAll('.question-row').length > 1) {
        questionRow.remove();
        updateInputsOrder();
    } else {
        alert('Опрос должен содержать хотя бы один вопрос.');
    }
}

function updateInputsOrder() {
    const rows = container.querySelectorAll('.question-row');
    rows.forEach((row, qIndex) => {
        const textInput = row.querySelector('.question-text');
        const typeSelect = row.querySelector('.type-select');
        if(textInput && typeSelect) {
            textInput.name = `questions[${qIndex}][text]`;
            typeSelect.name = `questions[${qIndex}][type]`;
            const optionInputs = row.querySelectorAll('.option-input');
            optionInputs.forEach(input => {
                input.name = `questions[${qIndex}][options][]`;
            });
            textInput.placeholder = `Вопрос ${qIndex + 1}`;
        }
    });
}
</script>
    
@endsection