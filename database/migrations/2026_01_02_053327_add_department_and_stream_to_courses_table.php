<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('course_code');
            $table->unsignedBigInteger('academic_stream_id')->nullable()->after('department_id');
            $table->string('department_name')->nullable()->after('academic_stream_id');
            $table->string('stream_name')->nullable()->after('department_name');
            
            // Foreign keys
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('academic_stream_id')->references('id')->on('academic_streams')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['academic_stream_id']);
            $table->dropColumn(['department_id', 'academic_stream_id', 'department_name', 'stream_name']);
        });
    }
};