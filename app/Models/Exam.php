<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject',
        'duration_minutes',
        'total_marks',
        'pass_mark',
        'instructions',
        'created_by',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'exam_class');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isAvailable(): bool
    {
        return $this->is_active && 
               now()->between($this->start_date, $this->end_date);
    }
}
