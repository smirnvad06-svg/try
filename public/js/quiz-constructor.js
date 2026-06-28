const container = document.getElementById('questions-container');

// Первичная инициализация имен при загрузке страницы
document.addEventListener("DOMContentLoaded", function() {
    updateInputsOrder();
});

// Показ/скрытие блока вариантов ответов
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

// Добавление поля варианта ответа через кнопку Плюс
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
    updateInputsOrder(); // Синхронизируем name атрибуты
}

// Удаление конкретного варианта ответа
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

// Удаление вопроса целиком
function removeQuestion(buttonElement) {
    const questionRow = buttonElement.closest('.question-row');
    if (container.querySelectorAll('.question-row').length > 1) {
        questionRow.remove();
        updateInputsOrder();
    } else {
        alert('Опрос должен содержать хотя бы один вопрос.');
    }
}

// Инициализация Drag and Drop сортировки
function addDragEvents(row) {
    row.addEventListener('dragstart', () => row.classList.add('dragging'));
    row.addEventListener('dragend', () => {
        row.classList.remove('dragging');
        updateInputsOrder(); 
    });
}

// Навешиваем Drag-and-Drop на стартовые элементы
document.querySelectorAll('.question-row').forEach(addDragEvents);

container.addEventListener('dragover', e => {
    e.preventDefault();
    const afterElement = getDragAfterElement(container, e.clientY);
    const draggingRow = document.querySelector('.dragging');
    if (afterElement == null) {
        container.appendChild(draggingRow);
    } else {
        container.insertBefore(draggingRow, afterElement);
    }
});

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.question-row:not(.dragging)')];
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// ИСПРАВЛЕНО: Клик по кнопке "+ Добавить еще вопрос" теперь генерирует чистый HTML без серверных переменных
document.getElementById('add-question').addEventListener('click', function() {
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
    addDragEvents(div);
    updateInputsOrder(); // Автоматически выставит правильные name с нужным индексом
});

// Функция автоматической перестройки всех name-индексов
function updateInputsOrder() {
    const rows = container.querySelectorAll('.question-row');
    rows.forEach((row, qIndex) => {
        const textInput = row.querySelector('.question-text');
        const typeSelect = row.querySelector('.type-select');
        
        textInput.name = `questions[${qIndex}][text]`;
        typeSelect.name = `questions[${qIndex}][type]`;
        
        const optionInputs = row.querySelectorAll('.option-input');
        optionInputs.forEach(input => {
            input.name = `questions[${qIndex}][options][]`;
        });
        
        textInput.placeholder = `Вопрос ${qIndex + 1}`;
    });
}