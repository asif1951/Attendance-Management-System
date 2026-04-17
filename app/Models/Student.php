<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'department_id',
        'academic_stream_id',
        'student_id',
        'level',
        'session',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the department that owns the student.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the academic stream that owns the student.
     */
    public function academicStream(): BelongsTo
    {
        return $this->belongsTo(AcademicStream::class);
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope a query to only include active students.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search students.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('student_id', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%");
    }
}