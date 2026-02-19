<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->string('verification_number')->unique();
            $table->foreignId('portfolio_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('institution_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected', 'revoked']);
            $table->text('remarks')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('verification_data')->nullable(); // Store verification context
            $table->timestamps();
            
            $table->unique('verification_number');
            $table->index(['portfolio_item_id', 'status']);
            $table->index('institution_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('verifications');
    }
};