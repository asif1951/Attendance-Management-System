<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('academic_stream_id')->constrained('academic_streams')->onDelete('cascade');
            $table->string('student_id')->unique();
            $table->string('level'); // e.g., "4/2", "3/1"
            $table->string('session'); // e.g., "Spring-2022"
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for better performance
            $table->index(['department_id', 'academic_stream_id']);
            $table->index('student_id');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};