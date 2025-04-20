<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\PboLevel;

class UserSeeder extends Seeder
{
    public function run()
    {
        Log::info('Starting user import from JSON');

        try {
            // Get the first PboLevel ID
            $firstPboLevel = PboLevel::first();

            if (!$firstPboLevel) {
                Log::error('No PboLevel found in the database');
                $this->command->error('No PboLevel found. Please create PboLevel records first.');
                return;
            }

            $pboLevelId = $firstPboLevel->id;
            Log::info("Using PboLevel ID: {$pboLevelId}");

            // Get the JSON file from public directory
            $jsonPath = public_path('users.json');

            if (!file_exists($jsonPath)) {
                Log::error('user.json file not found in public directory');
                $this->command->error('user.json file not found in public directory');
                return;
            }

            $jsonString = file_get_contents($jsonPath);
            $jsonData = json_decode($jsonString, true);

            if (!isset($jsonData[2]['data'])) {
                Log::error('Expected data structure not found in JSON file');
                $this->command->error('JSON structure is not as expected');
                return;
            }

            $users = $jsonData[2]['data'];
            Log::info('Found ' . count($users) . ' total users in JSON');

            // Filter to only include users with role = agent
            $agentUsers = array_filter($users, function($user) {
                return isset($user['role']) && $user['role'] === 'agent';
            });

            Log::info('Filtered down to ' . count($agentUsers) . ' agent users to import');

            // Get actual table columns
            $columns = Schema::getColumnListing('users');

            // Field mappings
            $fieldMappings = [
                'agent_code' => 'pbo_code',
                'agent_level_id' => 'pbo_level_id'
            ];

            $successCount = 0;
            $errorCount = 0;

            foreach ($agentUsers as $index => $user) {
                try {
                    $importData = [];

                    // Map fields and only include fields that exist in the database
                    foreach ($user as $field => $value) {
                        // Set role to "pbo" for all agent users
                        if ($field === 'role' && in_array($field, $columns)) {
                            $importData[$field] = 'pbo';
                        }
                        // Check if this field needs to be mapped to a new name
                        else if (isset($fieldMappings[$field]) && in_array($fieldMappings[$field], $columns)) {
                            $importData[$fieldMappings[$field]] = $value;
                        }
                        // Otherwise use the original field if it exists in the table
                        else if (in_array($field, $columns)) {
                            $importData[$field] = $value;
                        }
                    }

                    // Add pbo_level_id field with the first PboLevel ID
                    if (in_array('pbo_level_id', $columns)) {
                        $importData['pbo_level_id'] = $pboLevelId;
                    }

                    // Handle languages_spoken JSON field
                    if (isset($importData['languages_spoken']) && !is_null($importData['languages_spoken'])) {
                        if (is_string($importData['languages_spoken'])) {
                            $importData['languages_spoken'] = json_decode($importData['languages_spoken'], true);
                        }
                        if (is_array($importData['languages_spoken'])) {
                            $importData['languages_spoken'] = json_encode($importData['languages_spoken']);
                        }
                    }

                    // Insert into database
                    DB::table('users')->insert($importData);
                    $successCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Error importing user {$index} ({$user['email']}): " . $e->getMessage());
                    $this->command->error("Failed to import user {$user['email']}: " . $e->getMessage());
                }
            }

            Log::info("PBO import completed. Successful: {$successCount}, Failed: {$errorCount}");
            $this->command->info("PBO import completed. Successful: {$successCount}, Failed: {$errorCount}");

        } catch (\Exception $e) {
            Log::error('Exception in UserSeeder: ' . $e->getMessage());
            $this->command->error('Error in UserSeeder: ' . $e->getMessage());
        }
    }
}