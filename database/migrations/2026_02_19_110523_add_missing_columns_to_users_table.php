<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('users', 'languages')) {
                $table->json('languages')->nullable()->after('password');
            }
            
            if (!Schema::hasColumn('users', 'fayda_id')) {
                $table->string('fayda_id')->nullable()->unique()->after('languages');
            }
            
            if (!Schema::hasColumn('users', 'region')) {
                $table->string('region')->nullable()->after('fayda_id');
            }
            
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('region');
            }
            
            if (!Schema::hasColumn('users', 'woreda')) {
                $table->string('woreda')->nullable()->after('city');
            }
            
            if (!Schema::hasColumn('users', 'kebele')) {
                $table->string('kebele')->nullable()->after('woreda');
            }
            
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['user', 'institution', 'employer', 'admin'])->default('user')->after('kebele');
            }
            
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'suspended', 'pending'])->default('pending')->after('role');
            }
            
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->unique()->after('email');
            }
            
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('phone');
            }
            
            if (!Schema::hasColumn('users', 'institution_name')) {
                $table->string('institution_name')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('users', 'institution_type')) {
                $table->string('institution_type')->nullable()->after('institution_name');
            }
            
            if (!Schema::hasColumn('users', 'accreditation_number')) {
                $table->string('accreditation_number')->nullable()->after('institution_type');
            }
            
            if (!Schema::hasColumn('users', 'is_verified_institution')) {
                $table->boolean('is_verified_institution')->default(false)->after('accreditation_number');
            }
            
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable()->after('is_verified_institution');
            }
            
            if (!Schema::hasColumn('users', 'company_registration')) {
                $table->string('company_registration')->nullable()->after('company_name');
            }
            
            if (!Schema::hasColumn('users', 'is_verified_employer')) {
                $table->boolean('is_verified_employer')->default(false)->after('company_registration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'languages',
                'fayda_id',
                'region',
                'city',
                'woreda',
                'kebele',
                'role',
                'status',
                'phone',
                'email_verified_at',
                'institution_name',
                'institution_type',
                'accreditation_number',
                'is_verified_institution',
                'company_name',
                'company_registration',
                'is_verified_employer'
            ]);
        });
    }
};