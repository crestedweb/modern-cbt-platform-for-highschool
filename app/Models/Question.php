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
        'marks',
        'options',
        'correct_answer',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    const TYPE_THEORY = 'theory';
    const TYPE_CODING = 'coding';
    const TYPE_FILL_BLANK = 'fill_blank';

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
        return in_array($this->question_type, [self::TYPE_MULTIPLE_CHOICE, self::TYPE_FILL_BLANK]);
    }
}
