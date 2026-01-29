<?php

namespace Database\Seeders;

use App\Helper;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Helper::initProject();

        $json = File::get(base_path('collections_export.json'));
        collect(json_decode($json, true))->each(fn($d) => \App\Models\Collection::create($d));

        $json = File::get(base_path('collection_fields_export.json'));
        collect(json_decode($json, true))->each(fn($d) => \App\Models\CollectionField::create($d));

        $json = File::get(base_path('records_export.json'));
        collect(json_decode($json, true))->each(fn($d) => \App\Models\Record::create($d));
    }
}
