<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\District;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class UpsertGadm extends Command
{
    protected $signature = 'upsert:gadm';

    protected $description = 'Upserts GADM data located in data/gadm (geo polygons)';

    public function handle()
    {
        if (! file_exists(base_path('data/gadm'))) {
            $this->Error('GADM data not found in data/gadm');

            return;
        }

        $this->info('Upserting GADM data');
        $files = glob(base_path('data/gadm').'/*.json');
        $this->info('Found '.count($files).' files in data/gadm');

        DB::transaction(function () use ($files) {
            foreach ($files as $key => $file) {
                $this->info('Upserting '.$file.' '.$key + 1 .' of '.count($files));
                $json = json_decode(file_get_contents($file), true);

                foreach ($json['features'] as $key => $feature) {
                    $_city = $feature['properties']['NAME_1'];
                    $_district = $feature['properties']['NAME_2'];
                    $this->info('Upserting '.$_city.':'.$_district.' '.$key + 1 .' of '.count($json['features']));

                    $points = array_map(
                        fn ($coordinate) => new Point($coordinate[1], $coordinate[0]),
                        $feature['geometry']['coordinates'][0][0],
                    );

                    $linestring = new LineString($points);

                    $city = City::firstOrCreate([
                        'name' => ['hu' => $_city, 'en' => $_city],
                    ]);

                    District::firstOrCreate([
                        'name' => ['en' => $_district],
                        'city_id' => $city->id,
                    ], ['boundaries' => new Polygon([$linestring])]);
                }
            }
        });

        $this->info('GADM data upserted successfully');
    }
}
