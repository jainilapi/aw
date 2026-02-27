<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomePageSetting;

class HomePageFooterSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $footerContent = [
            'about_title' => 'About Anjo Wholesale',
            'about_subtitle' => 'Mon-Fri 8AM-4PM',
            'about_description' => "Our goods are delivered at no \n extra charge once our delivery \n requirements are met! Give us a \n call to find out more information.",
            'phone_title' => 'Phone',
            'phone_numbers' => "(268) 480-3080 (AR)\n(268) 736 5814 (Whatsapp)\n(268) 480-3046/7 (Coolidge)\n(268) 480-3086 (Fax)",
            'email_title' => 'Email',
            'emails' => "anjo.w@candw.ag\nHR@anjowholesale.com\ninfo@anjowholesale.com\nmario.winter@anjowholesale.com",
            'address_title' => 'P.O. Box',
            'address_content' => "Anjo Wholesale \n P.O. Box 104 St. John's, \n Antigua & Barbuda",
            'facebook_url' => '',
            'instagram_url' => '',
            'bottom_text' => 'Anjo Wholesale',
            'bottom_address' => "American Road St. John's, Antigua & Barbuda\nCoolidge, Corner of Sir George Walter Highway & Powells Main Road"
        ];

        if (!HomePageSetting::where('key', 'footer')->exists()) {
            $maxOrder = HomePageSetting::max('ordering') ?? 0;

            HomePageSetting::create([
                'key' => 'footer',
                'value' => $footerContent,
                'ordering' => $maxOrder + 1,
            ]);
        }
    }
}
