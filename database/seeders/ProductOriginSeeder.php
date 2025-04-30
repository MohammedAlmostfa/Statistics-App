<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductOrigin;

class ProductOriginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            "صيني",
            "امريكي",
            "كندي",
            "روسي",
            "هندي",
            "ياباني",
            "تركي",
            "ماليزي",
            "تايلندي",
            "خليجي",
            "مصري",
            "سوري",
            "عراقي",
            "اردني",
            "اسباني",
            "الماني",
            "فرنسي",
        ];

        foreach ($countries as $country) {
            ProductOrigin::create([
                'name' => $country,
            ]);
        }
    }
}
