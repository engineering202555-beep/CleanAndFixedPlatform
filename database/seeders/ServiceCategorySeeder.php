<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServiceCategory::insert([
            [
                'name' => 'السباكة والصحية',
                'description' => 'إصلاح أعطال شبكات المياه، تركيب وتغيير الخلاطات، المغاسل، الخزانات، ومعالجة التسريبات المنزلية.',
                'icon' => 'plumbing.svg'
            ],
            [
                'name' => 'الكهرباء المنزلي',
                'description' => 'صيانة الشبكات الكهربائية، تمديد وتصليح اللوحات، تركيب الإنارة، وحل مشاكل انقطاع التيار الداخلي.',
                'icon' => 'electricity.svg'
            ],
            [
                'name' => 'صيانة المولدات والطاقة الشمسية',
                'description' => 'تركيب وصيانة منظومات الطاقة الشمسية، صيانة الأنفيرترات والبطاريات، وإصلاح المولدات الكهربائية المنزلية.',
                'icon' => 'solar-energy.svg'
            ],
            [
                'name' => 'التكييف والتبريد',
                'description' => 'تنظيف وصيانة مكيفات السبلت، شحن غاز الفريون، وإصلاح البرادات والبرادات المنزلية والمحلات.',
                'icon' => 'cooling.svg'
            ],
            [
                'name' => 'النجارة والغالونات',
                'description' => 'تصليح وتركيب الأبواب والنوافذ، تبديل الغالونات والأقفال، وصيانة المطابخ وغرف النوم الخشبية.',
                'icon' => 'carpentry.svg'
            ],
            [
                'name' => 'النظافة الشاملة',
                'description' => 'شطف وتنظيف الشقق السكنية والمكاتب، غسيل السجاد والموكيت، وتلميع الأرضيات وجلي الرخام.',
                'icon' => 'cleaning.svg'
            ]
        ]);
    }
}
