<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdditionalInfo;

class AdditionalInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $additionalInfos = [
            [
                'info_name' => 'GitHub Repository',
                'is_active' => true,
            ],
            [
                'info_name' => 'Portfolio Website',
                'is_active' => true,
            ],
            [
                'info_name' => 'Resume Link',
                'is_active' => true,
            ],
            [
                'info_name' => 'Personal Website',
                'is_active' => true,
            ],
        ];

        foreach ($additionalInfos as $info) {
            AdditionalInfo::updateOrCreate(
                ['info_name' => $info['info_name']],
                $info
            );
        }
    }
}
