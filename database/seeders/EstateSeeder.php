<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Estate;
use App\Models\EstatePlotType;
use App\Models\Location;
use App\Models\Plot;
use App\Models\Promo;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get estate managers to assign to estates
        $estateManagers = User::where('admin_role', 'estate_manager')->get();

        if ($estateManagers->isEmpty()) {
            // Create a default estate manager if none exists
            $manager = User::create([
                'name' => 'Estate Manager',
                'email' => 'narayananbtech@gmail.com',
                'phone' => '08012345678',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'admin_role' => 'estate_manager',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $estateManagers = collect([$manager]);
        }

        $estateData = [];
        // Real estate data
        // $estateData = [
        //     [
        //         'name' => 'Champions Bay',
        //         'location' => 'Ode-Omi',
        //         'city' => 'Lagos Island',
        //         'state' => 'Lagos',
        //         'total_plots' => 214,
        //         'plots_sold' => 2,
        //         'available_plots' => 212,
        //         'plot_types' => [
        //             [
        //                 'name' => 'Standard 600 SQM',
        //                 'size_sqm' => 600,
        //                 'outright_price' => 1500000,
        //                 'six_month_price' => 1650000,
        //                 'twelve_month_price' => 1800000,
        //             ],
        //             [
        //                 'name' => 'Standard 300 SQM',
        //                 'size_sqm' => 300,
        //                 'outright_price' => 750000,
        //                 'six_month_price' => 825000,
        //                 'twelve_month_price' => 900000,
        //             ],
        //         ],
        //         'plot_distributions' => [
        //             ['plot_type' => 'Standard 600 SQM', 'count' => 114, 'starting_number' => 1],
        //             ['plot_type' => 'Standard 300 SQM', 'count' => 100, 'starting_number' => 115],
        //         ],
        //         'promos' => [
        //             [
        //                 'name' => 'Buy 5 Get 1 Free',
        //                 'description' => 'Purchase 5 plots and get 1 plot free in Champions Bay',
        //                 'buy_quantity' => 5,
        //                 'free_quantity' => 1,
        //                 'valid_from' => now(),
        //                 'valid_to' => now()->addMonths(3),
        //             ],
        //         ],
        //         'promo_codes' => [
        //             [
        //                 'code' => 'CHAMP2025',
        //                 'discount_type' => 'percentage',
        //                 'discount_amount' => 5.00,
        //                 'valid_from' => now(),
        //                 'valid_until' => now()->addMonths(2),
        //                 'usage_limit' => 50,
        //             ]
        //         ],
        //         'faq' => [
        //             [
        //                 'question' => 'Is this estate fenced?',
        //                 'answer' => 'Yes, the entire estate is fenced for security and privacy.'
        //             ],
        //             [
        //                 'question' => 'What amenities are available in the estate?',
        //                 'answer' => 'Champions Bay features good roads, drainage systems, security, and recreational areas.'
        //             ],
        //         ],
        //         'terms' => [
        //             [
        //                 'title' => 'Payment Terms',
        //                 'content' => 'Full payment must be made before physical allocation. Installment options are available.'
        //             ],
        //         ],
        //         'refund_policy' => [
        //             [
        //                 'title' => 'Refund Conditions',
        //                 'content' => 'Refunds are subject to a 10% administrative fee and must be requested within 30 days of payment.'
        //             ],
        //         ],
        //     ],
        //     [
        //         'name' => 'Elevation City',
        //         'location' => 'Epe',
        //         'city' => 'Epe',
        //         'state' => 'Lagos',
        //         'total_plots' => 410,
        //         'plots_sold' => 15,
        //         'available_plots' => 395,
        //         'plot_types' => [
        //             [
        //                 'name' => 'Premium 600 SQM',
        //                 'size_sqm' => 600,
        //                 'outright_price' => 2500000,
        //                 'six_month_price' => 2750000,
        //                 'twelve_month_price' => 3000000,
        //             ],
        //             [
        //                 'name' => 'Premium 300 SQM',
        //                 'size_sqm' => 300,
        //                 'outright_price' => 1250000,
        //                 'six_month_price' => 1375000,
        //                 'twelve_month_price' => 1500000,
        //             ],
        //         ],
        //         'plot_distributions' => [
        //             ['plot_type' => 'Premium 600 SQM', 'count' => 220, 'starting_number' => 1],
        //             ['plot_type' => 'Premium 300 SQM', 'count' => 190, 'starting_number' => 221],
        //         ],
        //         'promos' => [
        //             [
        //                 'name' => 'Buy 10 Get 2 Free',
        //                 'description' => 'Purchase 10 plots and get 2 plots free in Elevation City',
        //                 'buy_quantity' => 10,
        //                 'free_quantity' => 2,
        //                 'valid_from' => now(),
        //                 'valid_to' => now()->addMonths(3),
        //             ],
        //         ],
        //         'promo_codes' => [
        //             [
        //                 'code' => 'ELEVATE2025',
        //                 'discount_type' => 'fixed',
        //                 'discount_amount' => 50000.00,
        //                 'valid_from' => now(),
        //                 'valid_until' => now()->addMonths(2),
        //                 'usage_limit' => 100,
        //             ]
        //         ],
        //         'faq' => [
        //             [
        //                 'question' => 'What infrastructures are available?',
        //                 'answer' => 'Elevation City includes paved roads, drainage, electricity, and security.'
        //             ],
        //             [
        //                 'question' => 'How far is it from major landmarks?',
        //                 'answer' => 'The estate is approximately 15 minutes from Epe town center and 45 minutes from Lekki.'
        //             ],
        //         ],
        //         'terms' => [
        //             [
        //                 'title' => 'Allocation Policy',
        //                 'content' => 'Plot allocation is done on a first-come, first-served basis after completion of payment.'
        //             ],
        //         ],
        //         'refund_policy' => [
        //             [
        //                 'title' => 'Cancellation Terms',
        //                 'content' => 'Cancellation of purchase is subject to a 15% administrative charge of the total amount paid.'
        //             ],
        //         ],
        //     ],
        //     [
        //         'name' => 'Champions Town',
        //         'location' => 'Benin City',
        //         'city' => 'Benin City',
        //         'state' => 'Edo',
        //         'total_plots' => 50,
        //         'plots_sold' => 3,
        //         'available_plots' => 47,
        //         'plot_types' => [
        //             [
        //                 'name' => 'Standard Plot 464 SQM',
        //                 'size_sqm' => 464,
        //                 'outright_price' => 15999000,
        //                 'six_month_price' => 17600000,
        //                 'twelve_month_price' => 19200000,
        //             ],
        //         ],
        //         'plot_distributions' => [
        //             ['plot_type' => 'Standard Plot 464 SQM', 'count' => 50, 'starting_number' => 1],
        //         ],
        //         'promos' => [
        //             [
        //                 'name' => 'Buy 3 Get 1 Free',
        //                 'description' => 'Purchase 3 plots and get 1 plot free in Champions Town Benin',
        //                 'buy_quantity' => 3,
        //                 'free_quantity' => 1,
        //                 'valid_from' => now(),
        //                 'valid_to' => now()->addMonths(6),
        //             ],
        //         ],
        //         'promo_codes' => [
        //             [
        //                 'code' => 'BENIN2025',
        //                 'discount_type' => 'percentage',
        //                 'discount_amount' => 10.00,
        //                 'valid_from' => now(),
        //                 'valid_until' => now()->addMonths(3),
        //                 'usage_limit' => 20,
        //             ]
        //         ],
        //         'faq' => [
        //             [
        //                 'question' => 'What is the title document for this estate?',
        //                 'answer' => 'The estate has a Certificate of Occupancy (C of O).'
        //             ],
        //             [
        //                 'question' => 'Is there a development levy?',
        //                 'answer' => 'Yes, a one-time development levy is charged to cover infrastructure costs.'
        //             ],
        //         ],
        //         'terms' => [
        //             [
        //                 'title' => 'Documentation',
        //                 'content' => 'All buyers receive a deed of assignment and official receipt upon completion of payment.'
        //             ],
        //         ],
        //         'refund_policy' => [
        //             [
        //                 'title' => 'Refund Timeline',
        //                 'content' => 'All refund requests are processed within 30 working days of approval.'
        //             ],
        //         ],
        //     ],
        //     [
        //         'name' => 'Royal Gardens',
        //         'location' => 'Asaba',
        //         'city' => 'Asaba',
        //         'state' => 'Delta',
        //         'total_plots' => 80,
        //         'plots_sold' => 5,
        //         'available_plots' => 75,
        //         'plot_types' => [
        //             [
        //                 'name' => 'Royal 500 SQM',
        //                 'size_sqm' => 500,
        //                 'outright_price' => 8500000,
        //                 'six_month_price' => 9350000,
        //                 'twelve_month_price' => 10200000,
        //             ],
        //         ],
        //         'plot_distributions' => [
        //             ['plot_type' => 'Royal 500 SQM', 'count' => 80, 'starting_number' => 1],
        //         ],
        //         'promos' => [
        //             [
        //                 'name' => 'Buy 4 Get 1 Free',
        //                 'description' => 'Purchase 4 plots and get 1 plot free in Royal Gardens',
        //                 'buy_quantity' => 4,
        //                 'free_quantity' => 1,
        //                 'valid_from' => now(),
        //                 'valid_to' => now()->addMonths(4),
        //             ],
        //         ],
        //         'faq' => [
        //             [
        //                 'question' => 'Is the estate in a flood-free zone?',
        //                 'answer' => 'Yes, Royal Gardens is situated on elevated land with proper drainage systems.'
        //             ],
        //         ],
        //     ],
        //     [
        //         'name' => 'Sunshine Valley',
        //         'location' => 'Owerri',
        //         'city' => 'Owerri',
        //         'state' => 'Imo',
        //         'total_plots' => 120,
        //         'plots_sold' => 10,
        //         'available_plots' => 110,
        //         'plot_types' => [
        //             [
        //                 'name' => 'Valley Plot 400 SQM',
        //                 'size_sqm' => 400,
        //                 'outright_price' => 5500000,
        //                 'six_month_price' => 6050000,
        //                 'twelve_month_price' => 6600000,
        //             ],
        //         ],
        //         'plot_distributions' => [
        //             ['plot_type' => 'Valley Plot 400 SQM', 'count' => 120, 'starting_number' => 1],
        //         ],
        //     ],
        // ];

        // Create estates with real data
        foreach ($estateData as $estate) {
            // Find the state
            $state = DB::table('states')->where('name', $estate['state'])->first();

            if (!$state) {
                $this->command->error("State not found: {$estate['state']}. Make sure CustomGeoSeeder has been run.");
                continue;
            }

            // Find the city
            $city = City::whereHas('state', function ($query) use ($state) {
                $query->where('id', $state->id);
            })->where('name', $estate['city'])->first();

            if (!$city) {
                $this->command->error("City not found: {$estate['city']} in {$estate['state']}. Make sure CustomGeoSeeder has been run.");
                continue;
            }

            // Find the location
            $location = Location::where('city_id', $city->id)
                              ->where('name', $estate['location'])
                              ->first();

            if (!$location) {
                $this->command->error("Location not found: {$estate['location']} in {$estate['city']}. Make sure CustomGeoSeeder has been run.");
                continue;
            }

            // Check if estate already exists
            $existingEstate = Estate::where('name', $estate['name'])
                                  ->where('city_id', $city->id)
                                  ->first();

            if ($existingEstate) {
                $this->command->info("Estate {$estate['name']} in {$estate['location']} already exists, skipping");
                continue;
            }

            // Select random manager for this estate
            $manager = $estateManagers->random();

            // Create description
            $description = "Located in {$estate['location']}, {$estate['name']} offers {$estate['total_plots']} plots ";
            $description .= "with various plot sizes available. ";
            $description .= "This premium estate development provides an excellent investment opportunity ";
            $description .= "in one of Nigeria's most promising areas. ";
            $description .= "Developed with modern infrastructure and amenities, this property comes with verified documentation ";
            $description .= "and secure land titles.";

            // Create the estate
           // Create the estate
           $createdAt = now()->subMonths(rand(1, 24)); // Between 1 month and 2 years ago

           // Add FAQ, terms, and refund policy data
           $faq = isset($estate['faq']) ? $estate['faq'] : [];
           $terms = isset($estate['terms']) ? $estate['terms'] : [];
           $refundPolicy = isset($estate['refund_policy']) ? $estate['refund_policy'] : [];

           $newEstate = Estate::create([
               'name' => $estate['name'],
               'description' => $description,
               'city_id' => $city->id,
               'location_id' => $location->id,
               'address' => "{$estate['location']}, {$city->name}, {$state->name}, Nigeria",
               'total_area' => $estate['total_plots'] * (isset($estate['plot_types'][0]) ? $estate['plot_types'][0]['size_sqm'] : 500),
               'status' => 'active',
               'manager_id' => $manager->id,
               'commercial_plot_premium_percentage' => 10.00,
               'corner_plot_premium_percentage' => 10.00,
               'faq' => $faq,
               'terms' => $terms,
               'refund_policy' => $refundPolicy,
               'created_at' => $createdAt,
               'updated_at' => $createdAt,
               'created_by' => $manager->id,
               'updated_by' => $manager->id,
           ]);

           $this->command->info("Created estate: {$estate['name']} in {$estate['location']}");

           // Create plot types
           $plotTypeMap = [];
           if (isset($estate['plot_types'])) {
               foreach ($estate['plot_types'] as $plotTypeData) {
                   $plotType = EstatePlotType::create([
                       'estate_id' => $newEstate->id,
                       'name' => $plotTypeData['name'],
                       'size_sqm' => $plotTypeData['size_sqm'],
                       'outright_price' => $plotTypeData['outright_price'],
                       'six_month_price' => $plotTypeData['six_month_price'],
                       'twelve_month_price' => $plotTypeData['twelve_month_price'],
                       'is_active' => true,
                       'created_by' => $manager->id,
                       'updated_by' => $manager->id,
                   ]);

                   $plotTypeMap[$plotTypeData['name']] = $plotType->id;
                   $this->command->info("  - Created plot type: {$plotTypeData['name']} for {$estate['name']}");
               }
           }

           // Create plots
           if (isset($estate['plot_distributions'])) {
               foreach ($estate['plot_distributions'] as $distribution) {
                   $plotTypeId = isset($plotTypeMap[$distribution['plot_type']]) ? $plotTypeMap[$distribution['plot_type']] : null;

                   if (!$plotTypeId) {
                       $this->command->error("  - Plot type '{$distribution['plot_type']}' not found");
                       continue;
                   }

                   // Get plot type details
                   $plotType = EstatePlotType::find($plotTypeId);

                   $plotsCreated = 0;
                   $cornerPlots = rand(2, 4); // Number of corner plots to create
                   $commercialPlots = rand(2, 4); // Number of commercial plots to create

                   // Generate arrays of plot indices that will be corner/commercial
                   $cornerPlotIndices = [];
                   $commercialPlotIndices = [];

                   for ($i = 0; $i < $cornerPlots; $i++) {
                       $cornerPlotIndices[] = rand(0, $distribution['count'] - 1);
                   }

                   for ($i = 0; $i < $commercialPlots; $i++) {
                       $commercialPlotIndices[] = rand(0, $distribution['count'] - 1);
                   }

                   for ($i = 0; $i < $distribution['count']; $i++) {
                       $plotNumber = $distribution['starting_number'] + $i;

                       // Determine if this is a corner or commercial plot
                       $isCorner = in_array($i, $cornerPlotIndices);
                       $isCommercial = in_array($i, $commercialPlotIndices);

                       // Generate random status - mostly available, some reserved or sold
                       $randomStatus = rand(1, 10);
                       if ($randomStatus == 1) {
                           $status = 'sold';
                       } elseif ($randomStatus == 2) {
                           $status = 'reserved';
                       } else {
                           $status = 'available';
                       }

                       Plot::create([
                           'estate_id' => $newEstate->id,
                           'plot_number' => $plotNumber,
                           'estate_plot_type_id' => $plotTypeId,
                           'area' => $plotType->size_sqm,
                           'dimensions' => sqrt($plotType->size_sqm) . 'm x ' . sqrt($plotType->size_sqm) . 'm',
                           'price' => $plotType->outright_price,
                           'status' => $status,
                           'is_commercial' => $isCommercial,
                           'is_corner' => $isCorner,
                           'created_at' => $createdAt,
                           'updated_at' => $createdAt,
                           'created_by' => $manager->id,
                           'updated_by' => $manager->id,
                       ]);

                       $plotsCreated++;
                   }

                   $this->command->info("  - Created {$plotsCreated} plots of type {$distribution['plot_type']}");
               }
           }

           // Create promotions
           if (isset($estate['promos'])) {
               foreach ($estate['promos'] as $promoData) {
                   Promo::create([
                       'estate_id' => $newEstate->id,
                       'name' => $promoData['name'],
                       'description' => $promoData['description'],
                       'buy_quantity' => $promoData['buy_quantity'],
                       'free_quantity' => $promoData['free_quantity'],
                       'valid_from' => $promoData['valid_from'],
                       'valid_to' => $promoData['valid_to'],
                       'is_active' => true,
                       'created_at' => $createdAt,
                       'updated_at' => $createdAt,
                       'created_by' => $manager->id,
                       'updated_by' => $manager->id,
                   ]);

                   $this->command->info("  - Created promotion: {$promoData['name']} for {$estate['name']}");
               }
           }

           // Create promo codes
           if (isset($estate['promo_codes'])) {
               foreach ($estate['promo_codes'] as $promoCodeData) {
                   PromoCode::create([
                       'estate_id' => $newEstate->id,
                       'code' => $promoCodeData['code'],
                       'discount_type' => $promoCodeData['discount_type'],
                       'discount_amount' => $promoCodeData['discount_amount'],
                       'valid_from' => $promoCodeData['valid_from'],
                       'valid_until' => $promoCodeData['valid_until'],
                       'usage_limit' => $promoCodeData['usage_limit'],
                       'times_used' => 0,
                       'status' => 'active',
                       'created_at' => $createdAt,
                       'updated_at' => $createdAt,
                       'created_by' => $manager->id,
                       'updated_by' => $manager->id,
                   ]);

                   $this->command->info("  - Created promo code: {$promoCodeData['code']} for {$estate['name']}");
               }
           }
       }
   }
}