<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Province;
use App\Models\District;
use App\Models\Municipality;
use App\Models\Ward;

class NepalAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enData = json_decode(
            File::get(storage_path('app/data/provinces_en.json')),
            true
        );

        $npData = json_decode(
            File::get(storage_path('app/data/provinces_np.json')),
            true
        );

        foreach ($enData as $index => $provinceEn) {

            $provinceNp = $npData[$index];

            // ------------------
            // Province
            // ------------------
            $province = Province::updateOrCreate(
                ['id' => $provinceEn['id']],
                [
                    'name_en' => $provinceEn['name'],
                    'name_np' => $provinceNp['name'],

                    'area_sq_km_en' => $provinceEn['area_sq_km'] ?? null,
                    'area_sq_km_np' => $provinceNp['area_sq_km'] ?? null,

                    'website' => $provinceEn['website'] ?? null,

                    'headquarter_en' => $provinceEn['headquarter'] ?? null,
                    'headquarter_np' => $provinceNp['headquarter'] ?? null,
                ]
            );

            // ------------------
            // Districts
            // ------------------
            foreach ($provinceEn['districts'] as $dIndex => $districtEn) {

                $districtNp = $provinceNp['districts'][$dIndex];

                $district = District::updateOrCreate(
                    ['id' => $districtEn['id']],
                    [
                        'province_id' => $province->id,

                        'name_en' => $districtEn['name'],
                        'name_np' => $districtNp['name'],

                        'area_sq_km_en' => $districtEn['area_sq_km'] ?? null,
                        'area_sq_km_np' => $districtNp['area_sq_km'] ?? null,

                        'website' => $districtEn['website'] ?? null,

                        'headquarter_en' => $districtEn['headquarter'] ?? null,
                        'headquarter_np' => $districtNp['headquarter'] ?? null,
                    ]
                );

                // ------------------
                // Municipalities
                // ------------------
                foreach ($districtEn['municipalities'] as $mIndex => $municipalityEn) {

                    $municipalityNp = $districtNp['municipalities'][$mIndex];

                    $municipality = Municipality::updateOrCreate(
                        ['id' => $municipalityEn['id']],
                        [
                            'district_id' => $district->id,
                            'category_id' => $municipalityEn['category_id'],

                            'name_en' => trim($municipalityEn['name']),
                            'name_np' => trim($municipalityNp['name']),

                            'area_sq_km_en' => $municipalityEn['area_sq_km'] ?? null,
                            'area_sq_km_np' => $municipalityNp['area_sq_km'] ?? null,

                            'website' => $municipalityEn['website'] ?? null,
                        ]
                    );

                    // ------------------
                    // Wards (same for both)
                    // ------------------
                    foreach ($municipalityEn['wards'] as $wardNumber) {
                        Ward::updateOrCreate(
                            [
                                'municipality_id' => $municipality->id,
                                'ward_number' => (int) $wardNumber,
                            ]
                        );
                    }
                }
            }
        }
    }
}
