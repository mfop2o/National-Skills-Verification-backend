<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // technical, soft, language, trade
            $table->string('sector')->nullable(); // construction, ICT, agriculture, etc.
            $table->json('alternative_names')->nullable(); // Amharic, Oromiffa names
            $table->text('description')->nullable();
            $table->json('occupational_standards')->nullable(); // Ethiopian standards
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['name', 'category']);
            $table->index('category');
            $table->index('sector');
        });
    }

    public function down()
    {
        Schema::dropIfExists('skills');
    }
};