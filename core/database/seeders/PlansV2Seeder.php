<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansV2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asia Region Plans
        $asiaPlans = [
            [
                'name' => 'Asia Starter',
                'display_code' => 'H1',
                'region' => 'ASIA',
                'icon' => 'fa-seedling',
                'color_scheme' => 'green',
                'price' => 5000,
                'daily_limit' => 20,
                'ptc_view_amount' => 500.00,
                'ref_level' => 3,
                'validity' => 365,
                'commission_level_a_rate' => 12.00,
                'commission_level_a_max' => 1800.00,
                'commission_level_b_rate' => 4.00,
                'commission_level_b_max' => 600.00,
                'commission_level_c_rate' => 1.00,
                'commission_level_c_max' => 150.00,
                'task_commission_a_rate' => 5.00,
                'task_commission_b_rate' => 2.00,
                'task_commission_c_rate' => 1.00,
                'anytime_withdraw_limit' => 10,
                'weekly_withdraw_day' => 5,
                'weekly_withdraw_enabled' => true,
                'package_number' => 1,
                'is_premium_package' => false,
                'status' => true,
                'sort_order' => 1,
                'is_featured' => false,
                'is_popular' => true,
            ],
            [
                'name' => 'Asia Basic',
                'display_code' => 'H2',
                'region' => 'ASIA',
                'icon' => 'fa-gem',
                'color_scheme' => 'blue',
                'price' => 10000,
                'daily_limit' => 30,
                'ptc_view_amount' => 600.00,
                'ref_level' => 3,
                'validity' => 365,
                'commission_level_a_rate' => 12.00,
                'commission_level_a_max' => 3600.00,
                'commission_level_b_rate' => 4.00,
                'commission_level_b_max' => 1200.00,
                'commission_level_c_rate' => 1.00,
                'commission_level_c_max' => 300.00,
                'task_commission_a_rate' => 5.00,
                'task_commission_b_rate' => 2.00,
                'task_commission_c_rate' => 1.00,
                'anytime_withdraw_limit' => 15,
                'weekly_withdraw_day' => 5,
                'weekly_withdraw_enabled' => true,
                'package_number' => 2,
                'is_premium_package' => false,
                'status' => true,
                'sort_order' => 2,
                'is_featured' => true,
                'is_popular' => false,
            ],
            [
                'name' => 'Asia Premium',
                'display_code' => 'H3',
                'region' => 'ASIA',
                'icon' => 'fa-crown',
                'color_scheme' => 'orange',
                'price' => 15000,
                'daily_limit' => 40,
                'ptc_view_amount' => 500.00,
                'ref_level' => 3,
                'validity' => 365,
                'commission_level_a_rate' => 12.00,
                'commission_level_a_max' => 1800.00,
                'commission_level_b_rate' => 4.00,
                'commission_level_b_max' => 600.00,
                'commission_level_c_rate' => 1.00,
                'commission_level_c_max' => 150.00,
                'task_commission_a_rate' => 5.00,
                'task_commission_b_rate' => 2.00,
                'task_commission_c_rate' => 1.00,
                'anytime_withdraw_limit' => 20,
                'weekly_withdraw_day' => 5,
                'weekly_withdraw_enabled' => true,
                'package_number' => 3,
                'is_premium_package' => true,
                'status' => true,
                'sort_order' => 3,
                'is_featured' => true,
                'is_popular' => true,
            ],
        ];

        // Europe Region Plans
        $europePlans = [
            [
                'name' => 'Europe Standard',
                'display_code' => 'H4',
                'region' => 'EUROPE',
                'icon' => 'fa-star',
                'color_scheme' => 'blue',
                'price' => 52000,
                'daily_limit' => 40,
                'ptc_view_amount' => 2000.00,
                'ref_level' => 3,
                'validity' => 365,
                'commission_level_a_rate' => 12.00,
                'commission_level_a_max' => 6240.00,
                'commission_level_b_rate' => 4.00,
                'commission_level_b_max' => 2080.00,
                'commission_level_c_rate' => 1.00,
                'commission_level_c_max' => 520.00,
                'task_commission_a_rate' => 5.00,
                'task_commission_b_rate' => 2.00,
                'task_commission_c_rate' => 1.00,
                'anytime_withdraw_limit' => 25,
                'weekly_withdraw_day' => 5,
                'weekly_withdraw_enabled' => true,
                'package_number' => 4,
                'is_premium_package' => true,
                'status' => true,
                'sort_order' => 4,
                'is_featured' => true,
                'is_popular' => false,
            ],
            [
                'name' => 'Europe Elite',
                'display_code' => 'H5',
                'region' => 'EUROPE',
                'icon' => 'fa-trophy',
                'color_scheme' => 'red',
                'price' => 100000,
                'daily_limit' => 60,
                'ptc_view_amount' => 3000.00,
                'ref_level' => 3,
                'validity' => 365,
                'commission_level_a_rate' => 12.00,
                'commission_level_a_max' => 12000.00,
                'commission_level_b_rate' => 4.00,
                'commission_level_b_max' => 4000.00,
                'commission_level_c_rate' => 1.00,
                'commission_level_c_max' => 1000.00,
                'task_commission_a_rate' => 5.00,
                'task_commission_b_rate' => 2.00,
                'task_commission_c_rate' => 1.00,
                'anytime_withdraw_limit' => 30,
                'weekly_withdraw_day' => 5,
                'weekly_withdraw_enabled' => true,
                'package_number' => 5,
                'is_premium_package' => true,
                'status' => true,
                'sort_order' => 5,
                'is_featured' => true,
                'is_popular' => true,
            ],
        ];

        $allPlans = array_merge($asiaPlans, $europePlans);

        foreach ($allPlans as $planData) {
            Plan::updateOrCreate(
                ['display_code' => $planData['display_code']],
                $planData
            );
        }

        $this->command->info('Plans V2 seeded successfully with ' . count($allPlans) . ' plans!');
    }
}
