<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('institution_name');
            $table->enum('type', ['university', 'tvet', 'training_center', 'professional_body', 'government']);
            $table->string('accreditation_number')->unique();
            $table->string('accrediting_body');
            $table->date('accreditation_date');
            $table->date('accreditation_expiry')->nullable();
            $table->string('contact_person');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->text('address');
            $table->string('region');
            $table->string('city');
            $table->json('verification_domains')->nullable(); // What they can verify
            $table->enum('approval_status', ['pending', 'approved', 'suspended', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('approval_status');
            $table->index('region');
        });
    }

    public function down()
    {
        Schema::dropIfExists('institutions');
    }
};