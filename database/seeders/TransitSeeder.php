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
                'name' => 'MRT Lin Utara (Lebak Bulus ➔ Bundaran HI)',
                'headsign' => 'Bundaran HI',
                'mode' => TransportMode::SUBWAY,
                'color_hex' => '#005BAC',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $mrtRouteSouth = Route::create([
                'operator_id' => $mrtOperator->id,
                'code' => 'MRT-M1-SB',
                'name' => 'MRT Lin Selatan (Bundaran HI ➔ Lebak Bulus)',
                'headsign' => 'Lebak Bulus',
                'mode' => TransportMode::SUBWAY,
                'color_hex' => '#005BAC',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $mrtHops = [
                // LBB -> FTW (curving east along elevated viaduct of TB Simatupang)
                [
                    'from' => 'LBB', 'to' => 'FTW', 'dist' => 2100, 'dur' => 180,
                    'coords' => [[-6.289167, 106.774444], [-6.290800, 106.782000], [-6.292300, 106.788500], [-6.292500, 106.793889]],
                ],
                // FTW -> CPR (turn north onto Jl. RS Fatmawati)
                [
                    'from' => 'FTW', 'to' => 'CPR', 'dist' => 1500, 'dur' => 140,
                    'coords' => [[-6.292500, 106.793889], [-6.285500, 106.795800], [-6.278611, 106.797222]],
                ],
                // CPR -> HJN
                [
                    'from' => 'CPR', 'to' => 'HJN', 'dist' => 1300, 'dur' => 130,
                    'coords' => [[-6.278611, 106.797222], [-6.272500, 106.797350], [-6.266667, 106.797500]],
                ],
                // HJN -> BLK
                [
                    'from' => 'HJN', 'to' => 'BLK', 'dist' => 1200, 'dur' => 120,
                    'coords' => [[-6.266667, 106.797500], [-6.261000, 106.797350], [-6.255556, 106.797222]],
                ],
                // BLK -> BLM
                [
                    'from' => 'BLK', 'to' => 'BLM', 'dist' => 1200, 'dur' => 130,
                    'coords' => [[-6.255556, 106.797222], [-6.250000, 106.797600], [-6.244444, 106.798056]],
                ],
                // BLM -> ASE (along Jl Sisingamangaraja)
                [
                    'from' => 'BLM', 'to' => 'ASE', 'dist' => 700, 'dur' => 90,
                    'coords' => [[-6.244444, 106.798056], [-6.241500, 106.798400], [-6.238611, 106.798889]],
                ],
                // ASE -> SNY (via Bundaran Senayan onto Jl Jendral Sudirman)
                [
                    'from' => 'ASE', 'to' => 'SNY', 'dist' => 1500, 'dur' => 150,
                    'coords' => [[-6.238611, 106.798889], [-6.232000, 106.800000], [-6.225278, 106.801667]],
                ],
                // SNY -> IST
                [
                    'from' => 'SNY', 'to' => 'IST', 'dist' => 1400, 'dur' => 130,
                    'coords' => [[-6.225278, 106.801667], [-6.222500, 106.804000], [-6.219722, 106.806667]],
                ],
                // IST -> BKM
                [
                    'from' => 'IST', 'to' => 'BKM', 'dist' => 1200, 'dur' => 120,
                    'coords' => [[-6.219722, 106.806667], [-6.216200, 106.811500], [-6.212778, 106.816667]],
                ],
                // BKM -> STB
                [
                    'from' => 'BKM', 'to' => 'STB', 'dist' => 900, 'dur' => 100,
                    'coords' => [[-6.212778, 106.816667], [-6.210800, 106.818800], [-6.208889, 106.820833]],
                ],
                // STB -> DKA
                [
                    'from' => 'STB', 'to' => 'DKA', 'dist' => 1100, 'dur' => 120,
                    'coords' => [[-6.208889, 106.820833], [-6.204800, 106.821800], [-6.200833, 106.822778]],
                ],
                // DKA -> BHI
                [
                    'from' => 'DKA', 'to' => 'BHI', 'dist' => 1100, 'dur' => 120,
                    'coords' => [[-6.200833, 106.822778], [-6.196800, 106.823200], [-6.192778, 106.823611]],
                ],
            ];

            foreach ($mrtHops as $seqIdx => $hop) {
                // Northbound
                RouteSegment::create([
                    'route_id' => $mrtRouteNorth->id,
                    'from_stop_id' => $mrtStops[$hop['from']]->id,
                    'to_stop_id' => $mrtStops[$hop['to']]->id,
                    'sequence_order' => $seqIdx + 1,
                    'distance_meters' => $hop['dist'],
                    'duration_seconds' => $hop['dur'],
                    'path_coordinates' => $hop['coords'],
                ]);

                // Southbound
                RouteSegment::create([
                    'route_id' => $mrtRouteSouth->id,
                    'from_stop_id' => $mrtStops[$hop['to']]->id,
                    'to_stop_id' => $mrtStops[$hop['from']]->id,
                    'sequence_order' => $seqIdx + 1,
                    'distance_meters' => $hop['dist'],
                    'duration_seconds' => $hop['dur'],
                    'path_coordinates' => array_reverse($hop['coords']),
                ]);
            }

            // KRL Commuter Line Segments
            // 3a. KRL Lin Cikarang / Loop Line (Karet <-> Sudirman <-> Manggarai)
            $krlRouteCikLoopEB = Route::create([
                'operator_id' => $krlOperator->id,
                'code' => 'KRL-LOOP-EB',
                'name' => 'KRL Lin Cikarang (Arah Manggarai / Cikarang)',
                'headsign' => 'Manggarai / Cikarang',
                'mode' => TransportMode::RAIL,
                'color_hex' => '#DC2626',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $krlRouteCikLoopWB = Route::create([
                'operator_id' => $krlOperator->id,
                'code' => 'KRL-LOOP-WB',
                'name' => 'KRL Lin Cikarang (Arah Kampung Bandan / Angke)',
                'headsign' => 'Kampung Bandan / Angke',
                'mode' => TransportMode::RAIL,
                'color_hex' => '#DC2626',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $krlCikHops = [
                // KMT -> SDR (along Banjir Kanal Barat corridor)
                [
                    'from' => 'KMT', 'to' => 'SDR', 'dist' => 800, 'dur' => 120,
                    'coords' => [
                        [-6.200639, 106.816222],
                        [-6.201667, 106.819889],
                        [-6.202333, 106.823611],
                    ],
                ],
                // SDR -> MRI (curving gracefully along south bank of Banjir Kanal Barat & Halimun rail corridor)
                [
                    'from' => 'SDR', 'to' => 'MRI', 'dist' => 3100, 'dur' => 300,
                    'coords' => [
                        [-6.202333, 106.823611],
                        [-6.203600, 106.829000],
                        [-6.205200, 106.834500],
                        [-6.206500, 106.839800],
                        [-6.207800, 106.844500],
                        [-6.209889, 106.850194],
                    ],
                ],
            ];

            foreach ($krlCikHops as $seqIdx => $hop) {
                // Eastbound (Arah Manggarai / Cikarang)
                RouteSegment::create([
                    'route_id' => $krlRouteCikLoopEB->id,
                    'from_stop_id' => $krlStops[$hop['from']]->id,
                    'to_stop_id' => $krlStops[$hop['to']]->id,
                    'sequence_order' => $seqIdx + 1,
                    'distance_meters' => $hop['dist'],
                    'duration_seconds' => $hop['dur'],
                    'path_coordinates' => $hop['coords'],
                ]);

                // Westbound (Arah Kampung Bandan / Angke)
                RouteSegment::create([
                    'route_id' => $krlRouteCikLoopWB->id,
                    'from_stop_id' => $krlStops[$hop['to']]->id,
                    'to_stop_id' => $krlStops[$hop['from']]->id,
                    'sequence_order' => $seqIdx + 1,
                    'distance_meters' => $hop['dist'],
                    'duration_seconds' => $hop['dur'],
                    'path_coordinates' => array_reverse($hop['coords']),
                ]);
            }

            // 3b. KRL Lin Bogor (Juanda <-> Gondangdia <-> Cikini <-> Manggarai <-> Tebet)
            $krlRouteBogorSB = Route::create([
                'operator_id' => $krlOperator->id,
                'code' => 'KRL-BOGOR-SB',
                'name' => 'KRL Lin Bogor (Arah Bogor)',
                'headsign' => 'Bogor',
                'mode' => TransportMode::RAIL,
                'color_hex' => '#DC2626',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $krlRouteBogorNB = Route::create([
                'operator_id' => $krlOperator->id,
                'code' => 'KRL-BOGOR-NB',
                'name' => 'KRL Lin Bogor (Arah Jakarta Kota)',
                'headsign' => 'Jakarta Kota',
                'mode' => TransportMode::RAIL,
                'color_hex' => '#DC2626',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $krlBogorHops = [
                // JUA -> GDD (elevated viaduct past Gambir)
                [
                    'from' => 'JUA', 'to' => 'GDD', 'dist' => 2200, 'dur' => 240,
                    'coords' => [
                        [-6.166667, 106.828611],
                        [-6.171000, 106.829500],
                        [-6.176500, 106.830500],
                        [-6.186111, 106.832778],
                    ],
                ],
                // GDD -> CKI
                [
                    'from' => 'GDD', 'to' => 'CKI', 'dist' => 1600, 'dur' => 180,
                    'coords' => [
                        [-6.186111, 106.832778],
                        [-6.192500, 106.837500],
                        [-6.198611, 106.842778],
                    ],
                ],
                // CKI -> MRI (crossing Kali Ciliwung approaching Manggarai elevated hub)
                [
                    'from' => 'CKI', 'to' => 'MRI', 'dist' => 1800, 'dur' => 240,
                    'coords' => [
                        [-6.198611, 106.842778],
                        [-6.202500, 106.845200],
                        [-6.206500, 106.848000],
                        [-6.209889, 106.850194],
                    ],
                ],
                // MRI -> TEB (heading south along Saharjo / Bukit Duri corridor to Tebet)
                [
                    'from' => 'MRI', 'to' => 'TEB', 'dist' => 2600, 'dur' => 240,
                    'coords' => [
                        [-6.209889, 106.850194],
                        [-6.213500, 106.852200],
                        [-6.218500, 106.854800],
                        [-6.223000, 106.856800],
                        [-6.226389, 106.858056],
                    ],
                ],
            ];

            foreach ($krlBogorHops as $seqIdx => $hop) {
                // Southbound (Arah Bogor)
                RouteSegment::create([
                    'route_id' => $krlRouteBogorSB->id,
                    'from_stop_id' => $krlStops[$hop['from']]->id,
                    'to_stop_id' => $krlStops[$hop['to']]->id,
                    'sequence_order' => $seqIdx + 1,
                    'distance_meters' => $hop['dist'],
                    'duration_seconds' => $hop['dur'],
                    'path_coordinates' => $hop['coords'],
                ]);

                // Northbound (Arah Jakarta Kota)
                RouteSegment::create([
                    'route_id' => $krlRouteBogorNB->id,
                    'from_stop_id' => $krlStops[$hop['to']]->id,
                    'to_stop_id' => $krlStops[$hop['from']]->id,
                    'sequence_order' => $seqIdx + 1,
                    'distance_meters' => $hop['dist'],
                    'duration_seconds' => $hop['dur'],
                    'path_coordinates' => array_reverse($hop['coords']),
                ]);
            }

            // 3c. LRT Jabodebek Segments (Dukuh Atas ⇄ Setiabudi ⇄ Rasuna Said ⇄ Kuningan ⇄ Pancoran ⇄ Cikoko)
            $lrtRouteOB = Route::create([
                'operator_id' => $lrtJbdOperator->id,
                'code' => 'LRT-JBD-OB',
                'name' => 'LRT Lin Cibubur / Bekasi (Arah Harjamukti / Jatimulya)',
                'headsign' => 'Harjamukti / Jatimulya',
                'mode' => TransportMode::LRT,
                'color_hex' => '#0284C7',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $lrtRouteIB = Route::create([
                'operator_id' => $lrtJbdOperator->id,
                'code' => 'LRT-JBD-IB',
                'name' => 'LRT Lin Cibubur / Bekasi (Arah Dukuh Atas)',
                'headsign' => 'Dukuh Atas',
                'mode' => TransportMode::LRT,
                'color_hex' => '#0284C7',
                'is_active' => true,
                'source_updated_at' => now(),
            ]);

            $lrtHops = [
                // DUK -> STM (curving east into Rasuna Said corridor)
                [
                    'from' => 'DUK', 'to' => 'STM', 'dist' => 1100, 'dur' => 120,
                    'coords' => [
                        [-6.203889, 106.822222],
                        [-6.208500, 106.827000],
                        [-6.211667, 106.830278],
                    ],
                ],
                // STM -> RAS (along Jl HR Rasuna Said median)
                [
                    'from' => 'STM', 'to' => 'RAS', 'dist' => 1200, 'dur' => 120,
                    'coords' => [
                        [-6.211667, 106.830278],
                        [-6.216500, 106.831200],
                        [-6.221667, 106.832222],
                    ],
                ],
                // RAS -> KNG
                [
                    'from' => 'RAS', 'to' => 'KNG', 'dist' => 1700, 'dur' => 180,
                    'coords' => [
                        [-6.221667, 106.832222],
                        [-6.229000, 106.832800],
                        [-6.236667, 106.833333],
                    ],
                ],
                // KNG -> PNC (curving east onto Jl Gatot Subroto)
                [
                    'from' => 'KNG', 'to' => 'PNC', 'dist' => 1300, 'dur' => 140,
                    'coords' => [
                        [-6.236667, 106.833333],
                        [-6.241000, 106.837000],
                        [-6.243333, 106.845000],
                    ],
                ],
                // PNC -> CIB (elevated along Jl MT Haryono)
                [
                    'from' => 'PNC', 'to' => 'CIB', 'dist' => 2200, 'dur' => 220,
                    'coords' => [
                        [-6.243333, 106.845000],
                        [-6.246500, 106.855000],
                        [-6.248889, 106.863889],
                    ],
                ],
            ];

            foreach ($lrtHops as $seqIdx => $hop) {
                // Outbound
                RouteSegment::create([
                    'route_id' => $lrtRouteOB->id,
                    'from_stop_id' => $lrtStops[$hop['from']]->id,
                    'to_stop_id' => $lrtStops[$hop['to']]->id,
                    'sequence_order' => $seqIdx + 1,
                    'distance_meters' => $hop['dist'],
                    'duration_seconds' => $hop['dur'],
                    'path_coordinates' => $hop['coords'],
                ]);

                // Inbound
                RouteSegment::create([
                    'route_id' => $lrtRouteIB->id,
                    'from_stop_id' => $lrtStops[$hop['to']]->id,
                    'to_stop_id' => $lrtStops[$hop['from']]->id,
                    'sequence_order' => $seqIdx + 1,
                    'distance_meters' => $hop['dist'],
                    'duration_seconds' => $hop['dur'],
                    'path_coordinates' => array_reverse($hop['coords']),
                ]);
            }

            // 4. Physical Transfers at Dukuh Atas Hub (Verified Intermodal Pedestrian Links)
            $terowonganKendalPath = [
                [-6.200833, 106.822778],
                [-6.201400, 106.822900],
                [-6.201700, 106.823200],
                [-6.202333, 106.823611],
            ];

            $jpmMrtLrtPath = [
                [-6.200833, 106.822778],
                [-6.202000, 106.822600],
                [-6.203889, 106.822222],
            ];

            $jpmKrlLrtPath = [
                [-6.202333, 106.823611],
                [-6.203100, 106.822900],
                [-6.203889, 106.822222],
            ];

            $transfersData = [
                // MRT Dukuh Atas BNI <-> KRL Sudirman (via Terowongan Kendal)
                [
                    'from' => $mrtStops['DKA']->id,
                    'to' => $krlStops['SDR']->id,
                    'dist' => 220,
                    'dur' => 210, // ~3.5 min
                    'type' => TransferType::STREET_WALK,
                    'notes' => 'Terowongan Kendal pedestrian walkway',
                    'coords' => $terowonganKendalPath,
                ],
                [
                    'from' => $krlStops['SDR']->id,
                    'to' => $mrtStops['DKA']->id,
                    'dist' => 220,
                    'dur' => 210,
                    'type' => TransferType::STREET_WALK,
                    'notes' => 'Terowongan Kendal pedestrian walkway',
                    'coords' => array_reverse($terowonganKendalPath),
                ],
                // MRT Dukuh Atas BNI <-> LRT Dukuh Atas (via JPO / JPM Dukuh Atas)
                [
                    'from' => $mrtStops['DKA']->id,
                    'to' => $lrtStops['DUK']->id,
                    'dist' => 350,
                    'dur' => 300, // ~5 min
                    'type' => TransferType::BRIDGE_JPO,
                    'notes' => 'Jembatan Penyeberangan Multiguna (JPM) Dukuh Atas',
                    'coords' => $jpmMrtLrtPath,
                ],
                [
                    'from' => $lrtStops['DUK']->id,
                    'to' => $mrtStops['DKA']->id,
                    'dist' => 350,
                    'dur' => 300,
                    'type' => TransferType::BRIDGE_JPO,
                    'notes' => 'Jembatan Penyeberangan Multiguna (JPM) Dukuh Atas',
                    'coords' => array_reverse($jpmMrtLrtPath),
                ],
                // KRL Sudirman <-> LRT Dukuh Atas (via JPM)
                [
                    'from' => $krlStops['SDR']->id,
                    'to' => $lrtStops['DUK']->id,
                    'dist' => 240,
                    'dur' => 220, // ~3.7 min
                    'type' => TransferType::BRIDGE_JPO,
                    'notes' => 'Akses JPM Sudirman - LRT Dukuh Atas',
                    'coords' => $jpmKrlLrtPath,
                ],
                [
                    'from' => $lrtStops['DUK']->id,
                    'to' => $krlStops['SDR']->id,
                    'dist' => 240,
                    'dur' => 220,
                    'type' => TransferType::BRIDGE_JPO,
                    'notes' => 'Akses JPM Sudirman - LRT Dukuh Atas',
                    'coords' => array_reverse($jpmKrlLrtPath),
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
                    'path_coordinates' => $t['coords'],
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
