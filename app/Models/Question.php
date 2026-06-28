<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    protected $casts = [
    'options' => 'array',
];

    protected $fillable = ['text', 'quiz_id', 'type', 'position', 'options'];
}
