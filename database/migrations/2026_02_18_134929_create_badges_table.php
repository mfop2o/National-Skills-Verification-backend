<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('badge_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('issuer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('verification_id')->nullable()->constrained();
            $table->string('name');
            $table->string('skill_name');
            $table->text('description')->nullable();
            $table->string('badge_image')->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable();
            $table->json('criteria')->nullable();
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->string('revoke_reason')->nullable();
            $table->timestamps();
            
            $table->unique('badge_id');
            $table->index(['user_id', 'status']);
            $table->index(['skill_name', 'level']);
            $table->index('issuer_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('badges');
    }
};