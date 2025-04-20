<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PboLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Create or get an agent level for the agent
        $pboLevel = PboLevel::firstOrCreate(
            ['name' => 'Standard Agent'],
            [
                'direct_sale_commission_percentage' => 15.00,
                'referral_commission_percentage' => 2.00,
                'minimum_sales_count' => 0,
                'minimum_sales_value' => 0.00,
                'status' => 'active'
            ]
        );

        // Create an agent user with a mandatory agent code
        $agent = User::create([
            'id' => Str::uuid(),
            'name' => 'Agent Smith',
            'email' => 'roomgpt.sh@gmail.com',
            'phone' => '+2349023456789',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'pbo',
            'status' => 'active',
            'onboarding_completed' => true,
            'pbo_code' => '1215',
            'pbo_level_id' => $pboLevel->id,
        ]);


        // Log the creation
       // $this->command->info('Agent user created successfully with agent code: ' . $agent->pbo_code);
    }
}
