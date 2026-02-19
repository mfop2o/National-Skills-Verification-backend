<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->enum('proficiency', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
            $table->integer('years_experience')->nullable();
            $table->text('description')->nullable();
            $table->enum('verification_status', ['unverified', 'pending', 'verified'])->default('unverified');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'skill_id']);
            $table->index(['skill_id', 'verification_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_skills');
    }
};