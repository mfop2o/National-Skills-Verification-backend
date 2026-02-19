<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['project', 'certificate', 'work_experience', 'education', 'assessment']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('organization')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('credential_id')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['draft', 'pending', 'verified', 'rejected'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['portfolio_id', 'type', 'status']);
            $table->index('credential_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('portfolio_items');
    }
};