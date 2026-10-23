<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TransferType;
use App\Enums\TransportMode;
use App\Models\DataProvenanceLog;
use App\Models\FareRule;
use App\Models\Operator;
use App\Models\Route;
use App\Models\RouteSegment;
use App\Models\Stop;
use App\Models\Transfer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class TransitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            // 1. Operators
            $mrtOperator = Operator::create([
                'code' => 'MRTJ',
                'name' => 'MRT Jakarta',
                'mode' => TransportMode::SUBWAY,
                'color_hex' => '#005BAC',
                'official_url' => 'https://jakartamrt.co.id',
                'data_source_url' => 'https://jakartamrt.co.id/id/peta-jalur-dan-jadwal',
                'license_notes' => 'Informasi Publik Resmi PT MRT Jakarta (Perseroda)',
            ]);

            $krlOperator = Operator::create([
                'code' => 'KCI',
                'name' => 'KAI Commuter',
                'mode' => TransportMode::RAIL,
                'color_hex' => '#DC2626',
                'official_url' => 'https://commuterline.id',
                'data_source_url' => 'https://commuterline.id/perjalanan-krl/peta-rute',
                'license_notes' => 'Informasi Publik Resmi PT Kereta Commuter Indonesia',
            ]);

            $lrtJbdOperator = Operator::create([
                'code' => 'LRTJBD',
                'name' => 'LRT Jabodebek',
                'mode' => TransportMode::LRT,
                'color_hex' => '#0284C7',
                'official_url' => 'https://lrtjabodebek.kai.id',
                'data_source_url' => 'https://lrtjabodebek.kai.id/informasi/peta-rute',
                'license_notes' => 'Informasi Publik Resmi LRT Jabodebek KAI',
            ]);

            // 2. Stops (MRT Jakarta - 13 Stations)
            $mrtStopsData = [
                ['code' => 'LBB', 'name' => 'Lebak Bulus Grab', 'lat' => -6.289167, 'lng' => 106.774444, 'type' => 'station'],
                ['code' => 'FTW', 'name' => 'Fatmawati Indomaret', 'lat' => -6.292500, 'lng' => 106.793889, 'type' => 'station'],
                ['code' => 'CPR', 'name' => 'Cipete Raya', 'lat' => -6.278611, 'lng' => 106.797222, 'type' => 'station'],
                ['code' => 'HJN', 'name' => 'Haji Nawi', 'lat' => -6.266667, 'lng' => 106.797500, 'type' => 'station'],
                ['code' => 'BLK', 'name' => 'Blok A', 'lat' => -6.255556, 'lng' => 106.797222, 'type' => 'station'],
                ['code' => 'BLM', 'name' => 'Blok M BCA', 'lat' => -6.244444, 'lng' => 106.798056, 'type' => 'interchange'],
                ['code' => 'ASE', 'name' => 'ASEAN', 'lat' => -6.238611, 'lng' => 106.798889, 'type' => 'station'],
                ['code' => 'SNY', 'name' => 'Senayan Mastercard', 'lat' => -6.225278, 'lng' => 106.801667, 'type' => 'station'],
                ['code' => 'IST', 'name' => 'Istora Mandiri', 'lat' => -6.219722, 'lng' => 106.806667, 'type' => 'station'],
                ['code' => 'BKM', 'name' => 'Bendungan Hilir', 'lat' => -6.212778, 'lng' => 106.816667, 'type' => 'station'],
                ['code' => 'STB', 'name' => 'Setiabudi Astra', 'lat' => -6.208889, 'lng' => 106.820833, 'type' => 'station'],
                ['code' => 'DKA', 'name' => 'Dukuh Atas BNI', 'lat' => -6.200833, 'lng' => 106.822778, 'type' => 'interchange'],
                ['code' => 'BHI', 'name' => 'Bundaran HI Bank DKI', 'lat' => -6.192778, 'lng' => 106.823611, 'type' => 'station'],
            ];

            /** @var array<string, Stop> $mrtStops */
            $mrtStops = [];
            foreach ($mrtStopsData as $data) {
                $mrtStops[$data['code']] = Stop::create([
                    'operator_id' => $mrtOperator->id,
                    'code' => $data['code'],
                    'name' => $data['name'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'location_type' => $data['type'],
                    'is_active' => true,
                ]);
            }

            // Stops (KRL Commuter Line Central Hub)
            $krlStopsData = [
                ['code' => 'SDR', 'name' => 'Sudirman', 'lat' => -6.202333, 'lng' => 106.823611, 'type' => 'interchange'],
                ['code' => 'KMT', 'name' => 'Karet', 'lat' => -6.200639, 'lng' => 106.816222, 'type' => 'station'],
                ['code' => 'MRI', 'name' => 'Manggarai', 'lat' => -6.209889, 'lng' => 106.850194, 'type' => 'interchange'],
                ['code' => 'TEB', 'name' => 'Tebet', 'lat' => -6.226389, 'lng' => 106.858056, 'type' => 'station'],
                ['code' => 'CKI', 'name' => 'Cikini', 'lat' => -6.198611, 'lng' => 106.842778, 'type' => 'station'],
                ['code' => 'GDD', 'name' => 'Gondangdia', 'lat' => -6.186111, 'lng' => 106.832778, 'type' => 'station'],
                ['code' => 'JUA', 'name' => 'Juanda', 'lat' => -6.166667, 'lng' => 106.828611, 'type' => 'station'],
            ];

            /** @var array<string, Stop> $krlStops */
            $krlStops = [];
            foreach ($krlStopsData as $data) {
                $krlStops[$data['code']] = Stop::create([
                    'operator_id' => $krlOperator->id,
                    'code' => $data['code'],
                    'name' => $data['name'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'location_type' => $data['type'],
                    'is_active' => true,
                ]);
            }

            // Stops (LRT Jabodebek)
            $lrtStopsData = [
                ['code' => 'DUK', 'name' => 'Dukuh Atas LRT', 'lat' => -6.203889, 'lng' => 106.822222, 'type' => 'interchange'],
                ['code' => 'STM', 'name' => 'Setiabudi LRT', 'lat' => -6.211667, 'lng' => 106.830278, 'type' => 'station'],
                ['code' => 'RAS', 'name' => 'Rasuna Said LRT', 'lat' => -6.221667, 'lng' => 106.832222, 'type' => 'station'],
                ['code' => 'KNG', 'name' => 'Kuningan LRT', 'lat' => -6.236667, 'lng' => 106.833333, 'type' => 'station'],
                ['code' => 'PNC', 'name' => 'Pancoran LRT', 'lat' => -6.243333, 'lng' => 106.845000, 'type' => 'station'],
                ['code' => 'CIB', 'name' => 'Cikoko / Cawang LRT', 'lat' => -6.248889, 'lng' => 106.863889, 'type' => 'station'],
            ];

            /** @var array<string, Stop> $lrtStops */
            $lrtStops = [];
            foreach ($lrtStopsData as $data) {
                $lrtStops[$data['code']] = Stop::create([
                    'operator_id' => $lrtJbdOperator->id,
                    'code' => $data['code'],
                    'name' => $data['name'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'location_type' => $data['type'],
                    'is_active' => true,
                ]);
            }

            // 3. Routes & Segments (MRT Jakarta Lin Utara-Selatan)
            $mrtRouteNorth = Route::create([
                'operator_id' => $mrtOperator->id,
                'code' => 'MRT-M1-NB',
                'name' => 'MRT Lin Utara (Lebak Bulus -> Bundaran HI)',
                'mode' => TransportMode::SUBWAY,
                'color_hex' => '#005BAC',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $mrtRouteSouth = Route::create([
                'operator_id' => $mrtOperator->id,
                'code' => 'MRT-M1-SB',
                'name' => 'MRT Lin Selatan (Bundaran HI -> Lebak Bulus)',
                'mode' => TransportMode::SUBWAY,
                'color_hex' => '#005BAC',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $mrtSequence = ['LBB', 'FTW', 'CPR', 'HJN', 'BLK', 'BLM', 'ASE', 'SNY', 'IST', 'BKM', 'STB', 'DKA', 'BHI'];
            $mrtDistances = [2100, 1500, 1300, 1200, 1200, 700, 1500, 1400, 1200, 900, 1100, 1100]; // 12 intervals
            $mrtDurations = [180, 140, 130, 120, 130, 90, 150, 130, 120, 100, 120, 120]; // seconds (~28 min total)

            for ($i = 0; $i < count($mrtSequence) - 1; $i++) {
                $fromCode = $mrtSequence[$i];
                $toCode = $mrtSequence[$i + 1];
                $dist = $mrtDistances[$i];
                $dur = $mrtDurations[$i];

                // Northbound
                RouteSegment::create([
                    'route_id' => $mrtRouteNorth->id,
                    'from_stop_id' => $mrtStops[$fromCode]->id,
                    'to_stop_id' => $mrtStops[$toCode]->id,
                    'sequence_order' => $i + 1,
                    'distance_meters' => $dist,
                    'duration_seconds' => $dur,
                ]);

                // Southbound
                RouteSegment::create([
                    'route_id' => $mrtRouteSouth->id,
                    'from_stop_id' => $mrtStops[$toCode]->id,
                    'to_stop_id' => $mrtStops[$fromCode]->id,
                    'sequence_order' => $i + 1,
                    'distance_meters' => $dist,
                    'duration_seconds' => $dur,
                ]);
            }

            // KRL Segments: Sudirman <-> Manggarai (Cikarang Loop)
            $krlRouteCikLoop = Route::create([
                'operator_id' => $krlOperator->id,
                'code' => 'KRL-LOOP',
                'name' => 'KRL Lin Cikarang / Loop Line',
                'mode' => TransportMode::RAIL,
                'color_hex' => '#DC2626',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            // Karet <-> Sudirman <-> Manggarai <-> Tebet
            $krlSeq1 = ['KMT', 'SDR', 'MRI', 'TEB'];
            $krlDist1 = [800, 3100, 2600];
            $krlDur1 = [120, 300, 240];

            for ($i = 0; $i < count($krlSeq1) - 1; $i++) {
                $from = $krlStops[$krlSeq1[$i]]->id;
                $to = $krlStops[$krlSeq1[$i + 1]]->id;
                $dist = $krlDist1[$i];
                $dur = $krlDur1[$i];

                RouteSegment::create([
                    'route_id' => $krlRouteCikLoop->id,
                    'from_stop_id' => $from,
                    'to_stop_id' => $to,
                    'sequence_order' => $i + 1,
                    'distance_meters' => $dist,
                    'duration_seconds' => $dur,
                ]);

                RouteSegment::create([
                    'route_id' => $krlRouteCikLoop->id,
                    'from_stop_id' => $to,
                    'to_stop_id' => $from,
                    'sequence_order' => $i + 1,
                    'distance_meters' => $dist,
                    'duration_seconds' => $dur,
                ]);
            }

            // KRL Bogor Line: Manggarai <-> Cikini <-> Gondangdia <-> Juanda
            $krlRouteBogor = Route::create([
                'operator_id' => $krlOperator->id,
                'code' => 'KRL-BOGOR',
                'name' => 'KRL Lin Bogor',
                'mode' => TransportMode::RAIL,
                'color_hex' => '#DC2626',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $krlSeq2 = ['MRI', 'CKI', 'GDD', 'JUA'];
            $krlDist2 = [1800, 1600, 2200];
            $krlDur2 = [240, 180, 240];

            for ($i = 0; $i < count($krlSeq2) - 1; $i++) {
                $from = $krlStops[$krlSeq2[$i]]->id;
                $to = $krlStops[$krlSeq2[$i + 1]]->id;
                $dist = $krlDist2[$i];
                $dur = $krlDur2[$i];

                RouteSegment::create([
                    'route_id' => $krlRouteBogor->id,
                    'from_stop_id' => $from,
                    'to_stop_id' => $to,
                    'sequence_order' => $i + 1,
                    'distance_meters' => $dist,
                    'duration_seconds' => $dur,
                ]);

                RouteSegment::create([
                    'route_id' => $krlRouteBogor->id,
                    'from_stop_id' => $to,
                    'to_stop_id' => $from,
                    'sequence_order' => $i + 1,
                    'distance_meters' => $dist,
                    'duration_seconds' => $dur,
                ]);
            }

            // LRT Jabodebek Segments: Dukuh Atas -> Setiabudi -> Rasuna Said -> Kuningan -> Pancoran -> Cikoko
            $lrtRoute = Route::create([
                'operator_id' => $lrtJbdOperator->id,
                'code' => 'LRT-JBD-CB',
                'name' => 'LRT Lin Cibubur / Bekasi',
                'mode' => TransportMode::LRT,
                'color_hex' => '#0284C7',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $lrtSeq = ['DUK', 'STM', 'RAS', 'KNG', 'PNC', 'CIB'];
            $lrtDist = [1100, 1200, 1700, 1300, 2200];
            $lrtDur = [120, 120, 180, 140, 220];

            for ($i = 0; $i < count($lrtSeq) - 1; $i++) {
                $from = $lrtStops[$lrtSeq[$i]]->id;
                $to = $lrtStops[$lrtSeq[$i + 1]]->id;
                $dist = $lrtDist[$i];
                $dur = $lrtDur[$i];

                RouteSegment::create([
                    'route_id' => $lrtRoute->id,
                    'from_stop_id' => $from,
                    'to_stop_id' => $to,
                    'sequence_order' => $i + 1,
                    'distance_meters' => $dist,
                    'duration_seconds' => $dur,
                ]);

                RouteSegment::create([
                    'route_id' => $lrtRoute->id,
                    'from_stop_id' => $to,
                    'to_stop_id' => $from,
                    'sequence_order' => $i + 1,
                    'distance_meters' => $dist,
                    'duration_seconds' => $dur,
                ]);
            }

            // 4. Physical Transfers at Dukuh Atas Hub (Verified Intermodal Pedestrian Links)
            $transfersData = [
                // MRT Dukuh Atas BNI <-> KRL Sudirman (via Terowongan Kendal)
                [
                    'from' => $mrtStops['DKA']->id,
                    'to' => $krlStops['SDR']->id,
                    'dist' => 220,
                    'dur' => 210, // ~3.5 min
                    'type' => TransferType::STREET_WALK,
                    'notes' => 'Terowongan Kendal pedestrian walkway',
                ],
                [
                    'from' => $krlStops['SDR']->id,
                    'to' => $mrtStops['DKA']->id,
                    'dist' => 220,
                    'dur' => 210,
                    'type' => TransferType::STREET_WALK,
                    'notes' => 'Terowongan Kendal pedestrian walkway',
                ],
                // MRT Dukuh Atas BNI <-> LRT Dukuh Atas (via JPO / JPM Dukuh Atas)
                [
                    'from' => $mrtStops['DKA']->id,
                    'to' => $lrtStops['DUK']->id,
                    'dist' => 350,
                    'dur' => 300, // ~5 min
                    'type' => TransferType::BRIDGE_JPO,
                    'notes' => 'Jembatan Penyeberangan Multiguna (JPM) Dukuh Atas',
                ],
                [
                    'from' => $lrtStops['DUK']->id,
                    'to' => $mrtStops['DKA']->id,
                    'dist' => 350,
                    'dur' => 300,
                    'type' => TransferType::BRIDGE_JPO,
                    'notes' => 'Jembatan Penyeberangan Multiguna (JPM) Dukuh Atas',
                ],
                // KRL Sudirman <-> LRT Dukuh Atas (via JPM)
                [
                    'from' => $krlStops['SDR']->id,
                    'to' => $lrtStops['DUK']->id,
                    'dist' => 240,
                    'dur' => 220, // ~3.7 min
                    'type' => TransferType::BRIDGE_JPO,
                    'notes' => 'Akses JPM Sudirman - LRT Dukuh Atas',
                ],
                [
                    'from' => $lrtStops['DUK']->id,
                    'to' => $krlStops['SDR']->id,
                    'dist' => 240,
                    'dur' => 220,
                    'type' => TransferType::BRIDGE_JPO,
                    'notes' => 'Akses JPM Sudirman - LRT Dukuh Atas',
                ],
            ];

            foreach ($transfersData as $t) {
                Transfer::create([
                    'from_stop_id' => $t['from'],
                    'to_stop_id' => $t['to'],
                    'walking_distance_meters' => $t['dist'],
                    'walking_duration_seconds' => $t['dur'],
                    'transfer_type' => $t['type'],
                    'source_notes' => $t['notes'],
                    'verified_at' => '2026-10-23',
                ]);
            }

            // 5. Fare Rules
            // MRT Jakarta Matrix Rule
            FareRule::create([
                'operator_id' => $mrtOperator->id,
                'fare_scheme' => 'od_matrix',
                'base_fare' => 3000.00,
                'base_distance_km' => 1.0,
                'fare_per_next_km' => 1000.00,
                'max_fare' => 14000.00,
                'matrix_payload' => [
                    'min_fare' => 3000,
                    'per_station_step' => 1000,
                    'max_fare' => 14000,
                ],
                'valid_from' => '2024-01-01',
                'source_url' => 'https://jakartamrt.co.id/id/tarif-tiket',
            ]);

            // KRL Commuter Line Distance Rule
            FareRule::create([
                'operator_id' => $krlOperator->id,
                'fare_scheme' => 'distance_tier',
                'base_fare' => 3000.00,
                'base_distance_km' => 25.0,
                'fare_per_next_km' => 100.00, // Rp1.000 per 10km
                'max_fare' => 10000.00,
                'valid_from' => '2024-01-01',
                'source_url' => 'https://commuterline.id/perjalanan-krl/tarif-krl',
            ]);

            // LRT Jabodebek Rule
            FareRule::create([
                'operator_id' => $lrtJbdOperator->id,
                'fare_scheme' => 'distance_tier',
                'base_fare' => 5000.00,
                'base_distance_km' => 1.0,
                'fare_per_next_km' => 700.00,
                'max_fare' => 20000.00,
                'valid_from' => '2024-01-01',
                'source_url' => 'https://lrtjabodebek.kai.id/informasi/tarif',
            ]);

            // 6. Data Provenance Log
            DataProvenanceLog::create([
                'dataset_name' => 'Transit PoC Dukuh Atas Hub (MRT, KRL, LRT)',
                'operator_code' => 'ALL_POC',
                'records_count' => count($mrtStopsData) + count($krlStopsData) + count($lrtStopsData),
                'status' => 'success',
                'verified_by' => 'NgeRute Engineering Team',
                'notes' => 'Inisialisasi koridor terintegrasi Dukuh Atas (MRT Jakarta 13 stasiun, KRL koridor pusat, LRT Jabodebek, dan 6 link transfer pejalan kaki terverifikasi).',
            ]);
        });
    }
}
