<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    // Разрешаем массовое заполнение для полей вопросов
    protected $fillable = ['survey_id', 'text', 'type', 'options', 'order_index', 'is_required'];

    protected $casts = [
        'options' => 'array',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}