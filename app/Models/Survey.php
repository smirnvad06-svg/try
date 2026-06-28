<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    // ЭТА СТРОКА ИСПРАВИТ ОШИБКУ: разрешаем заполнять title, description и статус
    protected $fillable = ['title', 'description', 'is_active'];

    // Связь с вопросами
    public function questions() 
    {
        return $this->hasMany(Question::class);
    }

    protected $casts = [
        'options' => 'array',
    ];
}