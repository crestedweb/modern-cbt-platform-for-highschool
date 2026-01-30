<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_class', 'school_class_id', 'exam_id');
    }
}