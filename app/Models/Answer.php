<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['question_id', 'value'];

    protected $casts = [
        'value' => 'array', // Чтобы ответы-массивы от чекбоксов корректно читались
    ];
}
