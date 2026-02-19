<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['user', 'institution', 'employer', 'admin'])->default('user');
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            
            // Ethiopia-specific fields
            $table->string('fayda_id')->nullable()->unique();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('woreda')->nullable();
            $table->string('kebele')->nullable();
            $table->json('languages')->nullable();
            
            // Institution specific
            $table->string('institution_name')->nullable();
            $table->string('institution_type')->nullable(); // university, TVET, training
            $table->string('accreditation_number')->nullable();
            $table->boolean('is_verified_institution')->default(false);
            
            // Employer specific
            $table->string('company_name')->nullable();
            $table->string('company_registration')->nullable();
            $table->boolean('is_verified_employer')->default(false);
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['role', 'status']);
            $table->index('fayda_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};