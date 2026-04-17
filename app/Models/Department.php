<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // Relationship with AcademicStream
    public function academicStreams()
    {
        return $this->hasMany(AcademicStream::class);
    }

    // Relationship with Students
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Relationship with Courses
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // Relationship with CourseTeachers
    public function courseTeachers()
    {
        return $this->hasMany(CourseTeacher::class);
    }
}