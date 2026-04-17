<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_name',
        'course_code',
        'level',
        'department_id',
        'academic_stream_id',
        'department_name',
        'stream_name',
    ];

    // Department relationship
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Academic Stream relationship
    public function academicStream(): BelongsTo
    {
        return $this->belongsTo(AcademicStream::class);
    }
}