<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a client user with completed onboarding
        $client = User::create([
            'id' => Str::uuid(),
            'name' => 'John Doe',
            'email' => 'freshlance.io@gmail.com',
            'phone' => '+2349012345678',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'client',
            'status' => 'active',
            'onboarding_completed' => true,

            // Personal Information
            'date_of_birth' => '1985-05-15',
            'gender' => 'male',
            'marital_status' => 'married',
            'nationality' => 'Nigerian',
            'languages_spoken' => ['English', 'Yoruba'],

            // Contact Information
            'address' => '123 Main Street, Lekki, Lagos',
            'country_of_residence' => 'Nigeria',
            'mobile_number' => '+2349012345678',

            // Employment Details
            'occupation' => 'Software Engineer',
            'employer_name' => 'Tech Solutions Ltd',

            // Next of Kin Details
            'next_of_kin_name' => 'Jane Doe',
            'next_of_kin_relationship' => 'Spouse',
            'next_of_kin_address' => '123 Main Street, Lekki, Lagos',
            'next_of_kin_phone' => '+2349087654321',

            // Terms & Submission
            'terms_accepted' => true,
            'submission_date' => now(),
            'registration_completed' => true,
        ]);

        // Log the creation
        $this->command->info('Client user created successfully.');
    }
}
