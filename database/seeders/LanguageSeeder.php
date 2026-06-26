<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['language_code' => 'ZH', 'language_name' => 'Mandarin Chinese (普通話)'],
            ['language_code' => 'TW', 'language_name' => 'Taiwanese (台語)'],
            ['language_code' => 'EN', 'language_name' => 'English'],
            ['language_code' => 'ID', 'language_name' => 'Bahasa Indonesia'],
            ['language_code' => 'VI', 'language_name' => 'Vietnamese (Tiếng Việt)'],
            ['language_code' => 'TH', 'language_name' => 'Thai (ภาษาไทย)'],
            ['language_code' => 'PH', 'language_name' => 'Filipino (Tagalog)'],
            ['language_code' => 'MY', 'language_name' => 'Burmese (မြန်မာဘာသာ)'],
            ['language_code' => 'KH', 'language_name' => 'Khmer (ភាសាខ្មែរ)'],
            ['language_code' => 'JP', 'language_name' => 'Japanese (日本語)'],
            ['language_code' => 'KR', 'language_name' => 'Korean (한국어)'],
        ];

        foreach ($languages as $lang) {
            Language::updateOrCreate(['language_code' => $lang['language_code']], $lang);
        }
    }
}
