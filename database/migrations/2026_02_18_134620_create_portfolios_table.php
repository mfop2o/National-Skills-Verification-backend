<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('cover_photo')->nullable();
            $table->json('social_links')->nullable();
            $table->enum('visibility', ['public', 'private', 'verified_only'])->default('public');
            $table->integer('views_count')->default(0);
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('portfolios');
    }
};