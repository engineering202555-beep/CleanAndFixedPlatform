<?php

namespace Database\Seeders;

use App\Models\ServiceArea;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServiceArea::insert([
            ['city' => 'دمشق', 'area_name' => 'المزة'],
            ['city' => 'دمشق', 'area_name' => 'أبو رمانة'],
            ['city' => 'دمشق', 'area_name' => 'المالكي'],
            ['city' => 'دمشق', 'area_name' => 'مشروع دمر'],
            ['city' => 'دمشق', 'area_name' => 'كفرسوسة'],
            ['city' => 'دمشق', 'area_name' => 'القصاع'],
            ['city' => 'دمشق', 'area_name' => 'التجارة'],
            ['city' => 'دمشق', 'area_name' => 'الميدان'],
            ['city' => 'دمشق', 'area_name' => 'الشعلان'],
            ['city' => 'دمشق', 'area_name' => 'برامكة'],
            ['city' => 'دمشق', 'area_name' => 'بستان الدور'],
            ['city' => 'دمشق', 'area_name' => 'المهاجرين'],
        ]);
    }
}
