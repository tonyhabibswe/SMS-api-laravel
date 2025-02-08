<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 100)->index();
            $table->string('first_name', 50)->index();
            $table->string('father_name', 50)->index();
            $table->string('last_name', 50)->index();
            $table->string('major', 50)->index();
            $table->string('email', 255)->index()->unique();
            $table->string('campus', 50)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
