<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'برادات',
            'سخانات',
            'غسالات ملابس',
            'غسالات صحون',
            'ميكروويف',
            'مكواة',
            'خلاطات',
            'أجهزة تصفيف الشعر (سشوار)',
            'مبردات هواء',
            'مراوح',
            'مبردات مياه',
            'مكاوي بخار',
            'محضرات الطعام',
            'أفران كهربائية',
            'أجهزة قهوة',
            'ماكنات قهوة',
            'أجهزة تنقية الهواء',
            'أجهزة الطهي بالبخار',
            'مكنسة كهربائية',
            'مكنسة روبوتية',
            'أجهزة تبريد وتدفئة',
            'أجهزة صوتية (سماعات، مكبرات صوت)',
            'أجهزة لياقة بدنية كهربائية',
            'أجهزة الكمبيوتر المحمولة (لابتوب)',
            'أجهزة الكمبيوتر المكتبية',
            'أدوات الإضاءة الكهربائية'
        ];

        foreach ($categories as $category) {
            ProductCategory::create([
                'name' => $category,
                'dollar_exchange' => rand(5, 100),

            ]);
        }
    }
}
