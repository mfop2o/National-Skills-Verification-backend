<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create admin
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@skilltrust.et',
            'phone' => '251911111111',
            'password' => bcrypt('Admin@123'),
            'role' => 'admin',
            'status' => 'active',
            'region' => 'Addis Ababa',
            'city' => 'Addis Ababa',
        ]);

        // Create sample institution
        $institution = User::create([
            'name' => 'Addis Ababa University',
            'email' => 'aau@edu.et',
            'phone' => '251911111112',
            'password' => bcrypt('Inst@123'),
            'role' => 'institution',
            'status' => 'active',
            'region' => 'Addis Ababa',
            'city' => 'Addis Ababa',
            'institution_name' => 'Addis Ababa University',
            'institution_type' => 'university',
            'accreditation_number' => 'AAU-001-2024',
            'is_verified_institution' => true,
        ]);

        \App\Models\Institution::create([
            'user_id' => $institution->id,
            'institution_name' => 'Addis Ababa University',
            'type' => 'university',
            'accreditation_number' => 'AAU-001-2024',
            'accrediting_body' => 'Ministry of Education',
            'accreditation_date' => now(),
            'contact_person' => 'Dr. Bekele',
            'contact_email' => 'registrar@aau.edu.et',
            'contact_phone' => '251911111112',
            'address' => 'Sidist Kilo',
            'region' => 'Addis Ababa',
            'city' => 'Addis Ababa',
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }
}