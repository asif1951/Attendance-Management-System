<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('academic_streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('stream_name');
            $table->enum('status', ['Assigned', 'UnAssigned'])->default('UnAssigned');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_streams');
    }
};