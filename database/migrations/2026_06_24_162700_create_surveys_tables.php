<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Таблица для самих опросов
        Schema::create('surveys', function (Blueprint $table) {
            $table->id(); // Уникальный ID опроса
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Таблица для вопросов (связана с surveys)
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->string('text');
            $table->enum('type', ['radio', 'checkbox', 'text', 'scale', 'matrix']);
            $table->json('options')->nullable(); // Варианты ответов (для радио, чекбоксов, шкал и матриц)
            $table->integer('order_index')->default(0); // Для drag-and-drop порядка вопросов
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        // 3. Таблица для ответов пользователей (связана с surveys)
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable(); // Для аналитики и контроля уникальности
            $table->json('answers'); // Все ответы пользователя в формате JSON
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('surveys');
    }
};