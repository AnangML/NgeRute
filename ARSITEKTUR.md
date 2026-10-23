# ARSITEKTUR — NgeRute Technical Architecture & Engineering Specification

> **Aplikasi:** NgeRute — Web App Pembanding Perjalanan Antarmoda  
> **Teknologi Inti:** Laravel 11/12 (PHP 8.3) • PostgreSQL 15+ (Cube & Earthdistance) • Leaflet.js • Vanilla JS/CSS  
> **Standar Rekayasa:** Enterprise Clean Architecture • PSR-12 • Strict Types • DTO Pattern • Repository Pattern  
> **Status:** Approved Architectural Blueprint  
> **Terakhir Diperbarui:** 2026-10-23

---

## 1. Ikhtisar Arsitektur Sistem (Enterprise Clean Monolith)

NgeRute menerapkan pendekatan **Enterprise Clean Monolith with Decoupled Routing Engine**. Arsitektur ini menjamin kemudahan pemeliharaan (*maintainability*), kemudahan pengujian mandiri (*unit-testability*), dan pemisahan logika domain yang ketat dari framework HTTP.

```text
┌────────────────────────────────────────────────────────────────────────┐
│                        Klien Web (Browser)                             │
│   ├── Antarmuka Blade (Modern Layout, Glassmorphism, Responsive)       │
│   ├── Peta Leaflet (Custom Tiles, Polyline Renderer, Dynamic Markers)  │
│   └── Client State Controller (Fetch API, Debounce, Drawer Controller) │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ HTTP / JSON API (Strict DTO Contract)
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                      Laravel Backend Core Engine                       │
│                                                                        │
│  [ HTTP / CONTROLLER LAYER ]                                           │
│  ├── Requests: StationSearchRequest, PlanJourneyRequest (Validation)   │
│  ├── Controllers: JourneyApiController, StationApiController           │
│  └── Resources: JourneyPlanResource, StationResource (JSON Envelope)   │
│                                                                        │
│  [ DOMAIN / SERVICE LAYER (SOLID & Clean Architecture) ]               │
│  ├── Services\Routing\                                                 │
│  │   ├── JourneyPlanner (Orchestrator Komparasi 4 Kriteria)            │
│  │   ├── MultimodalGraphBuilder (Penyusun In-Memory Adjacency List)    │
│  │   └── DijkstraRouter (Priority-Queue Shortest Path Multi-Bobot)     │
│  ├── Services\Fares\                                                   │
│  │   ├── FareCalculator (Context)                                      │
│  │   └── Strategies\ (MrtFareStrategy, KrlFareStrategy, TjFareStrategy)│
│  ├── Services\TransitData\                                             │
│  │   ├── DatasetImporter & DatasetValidator                            │
│  │   └── ProvenanceTracker (Audit Waktu & Integritas Data)             │
│  │                                                                     │
│  [ REPOSITORY & ABSTRACTION LAYER ]                                    │
│  ├── Contracts: StationRepositoryInterface, RouteRepositoryInterface   │
│  └── EloquentRepositories: PgSqlStationRepository, PgSqlRouteRepo      │
│                                                                        │
│  [ DATA ACCESS LAYER ]                                                 │
│  └── Eloquent Models: Operator, Stop, Route, RouteSegment, Transfer... │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ PDO PgSQL / GiST Spatial Queries
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                  PostgreSQL Database Storage (Port 5432)               │
│  ├── Extensions: cube + earthdistance (Pencarian Jarak Radius Cepat)   │
│  ├── Spatial GiST Index: gist(ll_to_earth(latitude, longitude))        │
│  ├── Topologi Graf Transit: stops, routes, route_segments              │
│  ├── Transfer Antarmoda Terverifikasi: transfers (JPO, Walkway)        │
│  └── Skema Tarif & Data Provenance Logs: fare_rules (JSONB)            │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Desain Skema Database (PostgreSQL 15+)

Menggunakan tipe data standar PostgreSQL, integritas relasional penuh, dan ekstensi `cube` + `earthdistance` yang sudah aktif di lingkungan Laragon.

### 2.1 Persiapan Ekstensi PostgreSQL
```sql
CREATE EXTENSION IF NOT EXISTS cube;
CREATE EXTENSION IF NOT EXISTS earthdistance;
```

### 2.2 Tabel `operators`
```sql
CREATE TABLE operators (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(32) NOT NULL UNIQUE,       -- 'MRTJ', 'KCI', 'LRTJ', 'LRTJBD', 'TJ'
    name VARCHAR(128) NOT NULL,             -- 'MRT Jakarta', 'KAI Commuter'
    mode VARCHAR(32) NOT NULL,              -- 'rail', 'subway', 'lrt', 'bus', 'walk'
    color_hex VARCHAR(7) NOT NULL,          -- '#005BAC', '#DC2626'
    official_url VARCHAR(255),
    data_source_url VARCHAR(255),
    license_notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
```

### 2.3 Tabel `stops` (Stasiun, Halte & Interchange)
```sql
CREATE TABLE stops (
    id BIGSERIAL PRIMARY KEY,
    operator_id BIGINT REFERENCES operators(id) ON DELETE SET NULL,
    code VARCHAR(32),                       -- 'DKA', 'SDR', 'LBB'
    name VARCHAR(128) NOT NULL,             -- 'Dukuh Atas BNI', 'Sudirman'
    latitude NUMERIC(10, 7) NOT NULL,
    longitude NUMERIC(10, 7) NOT NULL,
    location_type VARCHAR(32) DEFAULT 'station', -- 'station', 'stop', 'interchange', 'entrance'
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Indeks GiST Spasial untuk pencarian radius cepat (Nearby Search)
CREATE INDEX idx_stops_earth_distance ON stops USING gist(ll_to_earth(latitude, longitude));
CREATE INDEX idx_stops_operator_id ON stops(operator_id);
CREATE INDEX idx_stops_name ON stops(name);
```

### 2.4 Tabel `routes`
```sql
CREATE TABLE routes (
    id BIGSERIAL PRIMARY KEY,
    operator_id BIGINT NOT NULL REFERENCES operators(id) ON DELETE CASCADE,
    code VARCHAR(32) NOT NULL,              -- 'MRT-M1', 'KRL-BOGOR'
    name VARCHAR(128) NOT NULL,             -- 'Lebak Bulus - Bundaran HI'
    mode VARCHAR(32) NOT NULL,
    color_hex VARCHAR(7),
    is_active BOOLEAN DEFAULT TRUE,
    source_updated_at DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_routes_operator_id ON routes(operator_id);
```

### 2.5 Tabel `route_segments` (Edges Perjalanan Antar-Stasiun)
```sql
CREATE TABLE route_segments (
    id BIGSERIAL PRIMARY KEY,
    route_id BIGINT NOT NULL REFERENCES routes(id) ON DELETE CASCADE,
    from_stop_id BIGINT NOT NULL REFERENCES stops(id) ON DELETE CASCADE,
    to_stop_id BIGINT NOT NULL REFERENCES stops(id) ON DELETE CASCADE,
    sequence_order INT NOT NULL,
    distance_meters INT NOT NULL,
    duration_seconds INT NOT NULL,          -- Durasi perjalanan kereta/bus
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_segment_from_stop ON route_segments(from_stop_id);
CREATE INDEX idx_segment_to_stop ON route_segments(to_stop_id);
CREATE INDEX idx_segment_route_seq ON route_segments(route_id, sequence_order);
```

### 2.6 Tabel `transfers` (Edges Transfer Fisik Antarmoda)
```sql
CREATE TABLE transfers (
    id BIGSERIAL PRIMARY KEY,
    from_stop_id BIGINT NOT NULL REFERENCES stops(id) ON DELETE CASCADE,
    to_stop_id BIGINT NOT NULL REFERENCES stops(id) ON DELETE CASCADE,
    walking_distance_meters INT NOT NULL,
    walking_duration_seconds INT NOT NULL,
    transfer_type VARCHAR(32) NOT NULL,     -- 'in_station', 'bridge_jpo', 'street_walk', 'underground'
    source_notes VARCHAR(255),              -- 'JPO Dukuh Atas Sudirman'
    verified_at DATE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_transfers_pair ON transfers(from_stop_id, to_stop_id);
```

### 2.7 Tabel `fare_rules` (Aturan Tarif)
```sql
CREATE TABLE fare_rules (
    id BIGSERIAL PRIMARY KEY,
    operator_id BIGINT NOT NULL REFERENCES operators(id) ON DELETE CASCADE,
    fare_scheme VARCHAR(32) NOT NULL,       -- 'flat', 'distance_tier', 'od_matrix'
    base_fare NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    base_distance_km NUMERIC(6, 2),
    fare_per_next_km NUMERIC(10, 2),
    max_fare NUMERIC(10, 2),
    matrix_payload JSONB,                   -- Format JSONB PostgreSQL untuk tarif OD tetap (MRT Jakarta)
    valid_from DATE NOT NULL,
    source_url VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_fare_rules_operator ON fare_rules(operator_id);
```

### 2.8 Tabel `data_provenance_logs`
```sql
CREATE TABLE data_provenance_logs (
    id BIGSERIAL PRIMARY KEY,
    dataset_name VARCHAR(128) NOT NULL,
    operator_code VARCHAR(32) NOT NULL,
    records_count INT NOT NULL,
    status VARCHAR(32) NOT NULL,            -- 'success', 'warning', 'failed'
    verified_by VARCHAR(64) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
```

---

## 3. Model Graf Multimoda & Algoritma Routing

### 3.1 Struktur Graf In-Memory (Adjacency List)
Graf dibentuk dari dua sumber relasi:
1. **Transit Segments:** Antar-stasiun berurutan dalam satu lintasan.
2. **Pedestrian Transfers:** Hubungan jalan kaki fisik yang tervalidasi di lapangan.

Setiap Edge dalam Graf memiliki properti DTO:
```php
final readonly class TransitEdge
{
    public function __construct(
        public int $targetStopId,
        public int $durationSeconds,
        public int $distanceMeters,
        public string $edgeType, // 'transit' | 'transfer'
        public ?int $operatorId = null,
        public ?int $routeId = null,
        public ?string $mode = null,
    ) {}
}
```

### 3.2 Multi-Criteria Dijkstra Algorithm
Kalkulasi rute dijalankan menggunakan **Priority Queue (`SplPriorityQueue`)** dengan 4 strategi pembobotan:

1. **Tercepat (Fastest Route):**
   Biaya = `durasi_perjalanan + durasi_jalan + (penalti_transfer_detik × jumlah_transit)`.
2. **Termurah (Cheapest Route):**
   Biaya = `total_estimasi_rupiah + (0.001 × durasi_perjalanan)`.
3. **Paling Sedikit Jalan Kaki (Least Walking):**
   Biaya = `(jarak_jalan_meter × 10) + durasi_perjalanan`.
4. **Paling Sedikit Transit (Fewest Transfers):**
   Biaya = `(jumlah_transit × 10000) + durasi_perjalanan`.

---

## 4. Pola Rekayasa & Standar Perusahaan (Enterprise Patterns)

### 4.1 Pattern: DTO (Data Transfer Objects)
Mencegah penggunaan *untyped array* di seluruh lapisan aplikasi.
- `App\DTOs\JourneyRequestDto`: Validasi waypoint asal & tujuan, koordinat, dan preferensi.
- `App\DTOs\JourneyResponseDto`: Mengemas alternatif rute, ringkasan waktu, tarif, dan segmen.
- `App\DTOs\RouteSegmentDto`: Rincian per langkah perjalanan (stasiun asal, stasiun tujuan, polyline, durasi).

### 4.2 Pattern: Strategy Pattern untuk Tarif
Setiap operator memiliki kalkulator tarif sendiri yang mengimplementasikan `FareStrategyInterface`:
- `MrtFareStrategy`: Membaca matriks OD dari tabel `fare_rules.matrix_payload` (JSONB).
- `KrlFareStrategy`: Menghitung tarif berbasis jarak kumulatif (Rp3.000 untuk 25 km pertama + Rp1.000 per 10 km berikutnya).
- `TransJakartaFareStrategy`: Flat fare Rp3.500 (Rp2.000 jam 05.00-07.00).

### 4.3 Pattern: Repository Pattern
- `StationRepositoryInterface` mempermudah pengujian unit / integrasi tanpa ketergantungan database fisik saat CI/CD pipeline dijalankan.

---

## 5. Struktur Direktori Proyek (Clean Enterprise Layout)

```text
app/
├── DTOs/
│   ├── JourneyRequestDto.php
│   ├── JourneyResponseDto.php
│   ├── RouteOptionDto.php
│   ├── SegmentDto.php
│   └── StationDto.php
├── Enums/
│   ├── TransportMode.php
│   ├── TransferType.php
│   └── RouteCriteria.php
├── Exceptions/
│   ├── RouteNotFoundException.php
│   └── StationNotFoundException.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── JourneyApiController.php
│   │   │   └── StationApiController.php
│   │   └── Web/
│   │       ├── HomeController.php
│   │       └── Admin/
│   ├── Requests/
│   │   ├── PlanJourneyRequest.php
│   │   └── SearchStationRequest.php
│   └── Resources/
│       ├── JourneyResource.php
│       └── StationResource.php
├── Models/
│   ├── Operator.php
│   ├── Stop.php
│   ├── Route.php
│   ├── RouteSegment.php
│   ├── Transfer.php
│   ├── FareRule.php
│   └── DataProvenanceLog.php
├── Repositories/
│   ├── Contracts/
│   │   ├── StationRepositoryInterface.php
│   │   ├── RouteRepositoryInterface.php
│   │   └── TransferRepositoryInterface.php
│   └── Eloquent/
│       ├── PgSqlStationRepository.php
│       ├── PgSqlRouteRepository.php
│       └── PgSqlTransferRepository.php
├── Services/
│   ├── Routing/
│   │   ├── JourneyPlanner.php
│   │   ├── MultimodalGraphBuilder.php
│   │   └── DijkstraRouter.php
│   ├── Fares/
│   │   ├── FareCalculator.php
│   │   ├── Contracts/
│   │   │   └── FareStrategyInterface.php
│   │   └── Strategies/
│   │       ├── MrtFareStrategy.php
│   │       ├── KrlFareStrategy.php
│   │       └── TjFareStrategy.php
│   └── TransitData/
│       ├── DatasetImporter.php
│       └── DatasetValidator.php
resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php
│   ├── components/
│   │   ├── map.blade.php
│   │   ├── search-drawer.blade.php
│   │   ├── route-card.blade.php
│   │   └── timeline-segment.blade.php
│   └── home.blade.php
public/
├── css/
│   ├── app.css
│   ├── map.css
│   └── drawer.css
└── js/
    ├── app.js
    ├── map-controller.js
    └── journey-search.js
database/
├── migrations/
└── seeders/
    ├── OperatorSeeder.php
    ├── DukuhAtasHubSeeder.php
    └── MrtJakartaLineSeeder.php
docs/
├── PRD.md
├── DESIGN.md
├── ARSITEKTUR.md
├── STANDAR_KODING.md
└── DATA_SOURCES.md
```

---

## 6. Spesifikasi Konfigurasi Lingkungan (`.env`)

```env
APP_NAME=NgeRute
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://ngerute.test

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ngerute
DB_USERNAME=postgres
DB_PASSWORD=
```
