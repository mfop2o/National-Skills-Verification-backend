<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, check if table exists
        if (Schema::hasTable('users')) {
            // Add columns one by one with error handling
            try {
                if (!Schema::hasColumn('users', 'languages')) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->json('languages')->nullable();
                    });
                    echo "Added languages column\n";
                }
            } catch (\Exception $e) {
                echo "Error adding languages: " . $e->getMessage() . "\n";
            }

            try {
                if (!Schema::hasColumn('users', 'phone')) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->string('phone')->nullable()->unique();
                    });
                    echo "Added phone column\n";
                }
            } catch (\Exception $e) {
                echo "Error adding phone: " . $e->getMessage() . "\n";
            }

            try {
                if (!Schema::hasColumn('users', 'role')) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->string('role')->default('user');
                    });
                    echo "Added role column\n";
                }
            } catch (\Exception $e) {
                echo "Error adding role: " . $e->getMessage() . "\n";
            }

            try {
                if (!Schema::hasColumn('users', 'status')) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->string('status')->default('active');
                    });
                    echo "Added status column\n";
                }
            } catch (\Exception $e) {
                echo "Error adding status: " . $e->getMessage() . "\n";
            }

            try {
                if (!Schema::hasColumn('users', 'region')) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->string('region')->nullable();
                    });
                    echo "Added region column\n";
                }
            } catch (\Exception $e) {
                echo "Error adding region: " . $e->getMessage() . "\n";
            }

            try {
                if (!Schema::hasColumn('users', 'city')) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->string('city')->nullable();
                    });
                    echo "Added city column\n";
                }
            } catch (\Exception $e) {
                echo "Error adding city: " . $e->getMessage() . "\n";
            }

            try {
                if (!Schema::hasColumn('users', 'fayda_id')) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->string('fayda_id')->nullable()->unique();
                    });
                    echo "Added fayda_id column\n";
                }
            } catch (\Exception $e) {
                echo "Error adding fayda_id: " . $e->getMessage() . "\n";
            }
        } else {
            // Create the table from scratch if it doesn't exist
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('user');
                $table->string('status')->default('active');
                $table->string('fayda_id')->nullable()->unique();
                $table->string('region')->nullable();
                $table->string('city')->nullable();
                $table->string('woreda')->nullable();
                $table->string('kebele')->nullable();
                $table->json('languages')->nullable();
                $table->string('institution_name')->nullable();
                $table->string('institution_type')->nullable();
                $table->string('accreditation_number')->nullable();
                $table->boolean('is_verified_institution')->default(false);
                $table->string('company_name')->nullable();
                $table->string('company_registration')->nullable();
                $table->boolean('is_verified_employer')->default(false);
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
            echo "Created users table from scratch\n";
        }
    }

    public function down(): void
    {
        // Don't drop columns in down to prevent data loss
    }
};