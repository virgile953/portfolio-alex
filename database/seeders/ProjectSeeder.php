<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('project')->insert([
            [
                'title' => '3D Printed Smartphone Stand',
                'description' => 'A versatile smartphone stand with adjustable angles, designed for stability and easy assembly.',
                'type' => 'Functional',
                'url' => 'https://example.com/smartphone-stand',
                'images' => json_encode(['smartphone_stand_1.jpg', 'smartphone_stand_2.jpg']),
                'stl_files' => json_encode(['smartphone_stand.stl', 'stand_base.stl']),
                'materials' => 'PLA, TPU for non-slip base',
                'specifications' => json_encode([
                    'dimensions' => '120mm x 80mm x 100mm',
                    'weight' => '75g',
                    'print_time' => '3.5 hours'
                ]),
                'completion_date' => '2024-11-15',
                'featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Articulated Dragon',
                'description' => 'A fully articulated dragon model with 20 joints that can be posed in various positions.',
                'type' => 'Artistic',
                'url' => 'https://example.com/articulated-dragon',
                'images' => json_encode(['dragon_1.jpg', 'dragon_2.jpg', 'dragon_3.jpg']),
                'stl_files' => json_encode(['dragon_full.stl', 'dragon_parts.zip']),
                'materials' => 'PLA, multiple colors',
                'specifications' => json_encode([
                    'dimensions' => '250mm x 120mm x 60mm',
                    'weight' => '120g',
                    'print_time' => '14 hours',
                    'print_settings' => '0.15mm layer height, 15% infill'
                ]),
                'completion_date' => '2025-01-22',
                'featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Custom Gear System',
                'description' => 'A demonstration model of a planetary gear system with smooth operation and educational value.',
                'type' => 'Mechanical',
                'url' => null,
                'images' => json_encode(['gear_system_1.jpg', 'gear_system_2.jpg']),
                'stl_files' => json_encode(['gear_system.stl']),
                'materials' => 'PETG',
                'specifications' => json_encode([
                    'dimensions' => '100mm diameter x 30mm height',
                    'gear_ratio' => '5:1',
                    'print_settings' => '0.10mm layer height, 25% infill'
                ]),
                'completion_date' => '2025-02-10',
                'featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
