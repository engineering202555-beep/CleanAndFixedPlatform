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

        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            ServiceAreaSeeder::class,
            ServiceCategorySeeder::class,
            SubscriptionSeeder::class,
            CustomerSeeder::class,
            ServiceProviderSeeder::class,
            SubscriptionProviderSeeder::class,
        ]);


    }
}
