<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'marks',
        'order',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function isObjective(): bool
    {
        return in_array($this->question_type, ['multiple_choice', 'fill_blank']);
    }

    // Get full image URL
    public function getImageUrl(): ?string
    {
        if ($this->image_path) {
            return asset('public/' . $this->image_path);
        }
        return null;
    }
}
