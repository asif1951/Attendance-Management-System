<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTeacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'academic_stream_id',
        'course_id',
        'level',
        'phone_number',
        'session' // Add this line
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function academicStream()
    {
        return $this->belongsTo(AcademicStream::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}