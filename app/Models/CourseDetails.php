<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseDetails extends Model
{
    use HasFactory;

    protected $table = 'students';

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
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function academicStream()
    {
        return $this->belongsTo(AcademicStream::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeOfSession($query, $session)
    {
        return $query->where('session', $session);
    }
}