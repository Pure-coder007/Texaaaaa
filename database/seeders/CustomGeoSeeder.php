<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Location;
use App\Models\State;
use Illuminate\Database\Seeder;

class CustomGeoSeeder extends Seeder
{
    /**
     * Run the database seeds for locations relevant to the real estate application.
     */
    public function run(): void
    {
        // Primary countries needed for the application
        $countries = [
            [
                'name' => 'Nigeria',
                'code' => 'NG',
                'phone_code' => '234',
                'currency' => 'NGN',
                'currency_symbol' => '₦',
                'status' => 'active'
            ],
            [
                'name' => 'Ghana',
                'code' => 'GH',
                'phone_code' => '233',
                'currency' => 'GHS',
                'currency_symbol' => '₵',
                'status' => 'active'
            ]
        ];

        foreach ($countries as $countryData) {
            // Check if country already exists to avoid duplicates
            $country = Country::where('name', $countryData['name'])->first();

            if (!$country) {
                $country = Country::create($countryData);
                $this->command->info("Created country: {$countryData['name']}");
            } else {
                $this->command->info("Country {$countryData['name']} already exists, using existing record");
            }

            // Seed states for this country
            $this->seedStates($country);
        }
    }

    /**
     * Seed states for a specific country.
     */
    private function seedStates(Country $country): void
    {
        $statesData = [];

        // Define states based on country
        if ($country->name === 'Nigeria') {
            $statesData = [
                ['name' => 'Lagos', 'code' => 'LAG'],
                ['name' => 'FCT (Abuja)', 'code' => 'FCT'],
                ['name' => 'Rivers', 'code' => 'RIV'],
                ['name' => 'Delta', 'code' => 'DEL'],
                ['name' => 'Edo', 'code' => 'EDO'],
                ['name' => 'Imo', 'code' => 'IMO'],
                ['name' => 'Akwa Ibom', 'code' => 'AKW']
            ];
        } elseif ($country->name === 'Ghana') {
            $statesData = [
                ['name' => 'Greater Accra', 'code' => 'GAR'],
                ['name' => 'Ashanti', 'code' => 'ASH'],
                ['name' => 'Central', 'code' => 'CEN']
            ];
        }

        foreach ($statesData as $stateData) {
            // Check if state already exists
            $state = State::where('country_id', $country->id)
                          ->where('name', $stateData['name'])
                          ->first();

            if (!$state) {
                $state = State::create([
                    'country_id' => $country->id,
                    'name' => $stateData['name'],
                    'code' => $stateData['code'],
                    'status' => 'active'
                ]);
                $this->command->info("Created state: {$stateData['name']} in {$country->name}");
            } else {
                $this->command->info("State {$stateData['name']} already exists, using existing record");
            }

            // Seed cities for this state
            $this->seedCities($state);
        }
    }

    /**
     * Seed cities for a specific state.
     */
    private function seedCities(State $state): void
    {
        $citiesData = [];

        // Define cities based on state
        switch ($state->name) {
            case 'Lagos':
                $citiesData = [
                    ['name' => 'Lagos Island', 'locations' => ['Ode-Omi', 'Abijo GRA Phase 2', 'Victoria Island']],
                    ['name' => 'Epe', 'locations' => ['Epe', 'Eredo', 'Ibeju-Lekki']],
                    ['name' => 'Ikorodu', 'locations' => ['Ikorodu', 'Ijede']]
                ];
                break;
            case 'FCT (Abuja)':
                $citiesData = [
                    ['name' => 'Abuja', 'locations' => ['Karshi', 'Maitama', 'Wuse', 'Garki']],
                    ['name' => 'Gwagwalada', 'locations' => ['Gwagwalada Central']]
                ];
                break;
            case 'Delta':
                $citiesData = [
                    ['name' => 'Asaba', 'locations' => ['Asaba', 'Okpanam']],
                    ['name' => 'Warri', 'locations' => ['Warri']]
                ];
                break;
            case 'Edo':
                $citiesData = [
                    ['name' => 'Benin City', 'locations' => ['Benin City', 'GRA']],
                    ['name' => 'Auchi', 'locations' => ['Auchi']]
                ];
                break;
            case 'Imo':
                $citiesData = [
                    ['name' => 'Owerri', 'locations' => ['Owerri', 'New Owerri']]
                ];
                break;
            case 'Akwa Ibom':
                $citiesData = [
                    ['name' => 'Uyo', 'locations' => ['Uyo', 'Itu']]
                ];
                break;
            case 'Greater Accra':
                $citiesData = [
                    ['name' => 'Accra', 'locations' => ['Airport Residential Area', 'East Legon', 'Cantonments']],
                    ['name' => 'Tema', 'locations' => ['Tema']]
                ];
                break;
            case 'Ashanti':
                $citiesData = [
                    ['name' => 'Kumasi', 'locations' => ['Kumasi', 'Ejisu']]
                ];
                break;
            case 'Central':
                $citiesData = [
                    ['name' => 'Cape Coast', 'locations' => ['Cape Coast']]
                ];
                break;
        }

        foreach ($citiesData as $cityData) {
            // Check if city already exists
            $city = City::where('state_id', $state->id)
                         ->where('name', $cityData['name'])
                         ->first();

            if (!$city) {
                $city = City::create([
                    'state_id' => $state->id,
                    'name' => $cityData['name'],
                    'status' => 'active'
                ]);
                $this->command->info("Created city: {$cityData['name']} in {$state->name}");
            } else {
                $this->command->info("City {$cityData['name']} already exists, using existing record");
            }

            // Seed locations for this city
            $this->seedLocations($city, $cityData['locations']);
        }
    }

    /**
     * Seed locations for a specific city.
     */
    private function seedLocations(City $city, array $locationNames): void
    {
        foreach ($locationNames as $locationName) {
            // Generate consistent coordinates based on location name (for reproducibility)
            $hash = crc32($locationName . $city->name . $city->state->name);
            $latitude = 4.3 + (($hash & 0xFFFF) % 9700) / 1000; // between 4.3 and 14.0
            $longitude = 2.5 + ((($hash >> 16) & 0xFFFF) % 12500) / 1000; // between 2.5 and 15.0

            // Check if location already exists
            $location = Location::where('city_id', $city->id)
                                ->where('name', $locationName)
                                ->first();

            if (!$location) {
                Location::create([
                    'city_id' => $city->id,
                    'name' => $locationName,
                    'description' => "A prime location in {$locationName}, {$city->name}, {$city->state->name}",
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'status' => 'active'
                ]);
                $this->command->info("Created location: {$locationName} in {$city->name}");
            } else {
                $this->command->info("Location {$locationName} already exists, using existing record");
            }
        }
    }
}