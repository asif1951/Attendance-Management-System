<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'stream_name',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    // Relationship with Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}