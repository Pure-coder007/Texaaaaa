<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'admin',
        //     'email' => 'admin@admin.com',
        //     'password' => 'password'
        // ]);

        // Run the Custom Geo Seeder
        $this->call(CustomGeoSeeder::class);

        // Run the Estate Seeder after the Geo Seeder
        $this->call(EstateSeeder::class);

        $this->call(AgentSeeder::class);

        $this->call(ClientSeeder::class);

    }
}
