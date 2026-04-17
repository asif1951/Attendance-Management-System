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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late'])->default('absent');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
            
            $table->foreign('marked_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Unique constraint to prevent duplicate attendance for same student on same date
            $table->unique(['student_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};