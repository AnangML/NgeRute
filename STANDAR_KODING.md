# STANDAR_KODING — NgeRute Enterprise Engineering & Code Standards

> **Dokumen:** Standar Rekayasa Perangkat Lunak, Arsitektur Bersih, dan Konvensi Koding  
> **Target Framework:** PHP 8.3+ • Laravel 11/12 • PostgreSQL 15+  
> **Tujuan:** Menjamin kode mudah direvisi (*maintainable*), mudah diuji (*testable*), tahan masa depan (*future-proof*), dan konsisten sesuai standar industri kelas korporat (*enterprise-grade*).

---

## 1. Prinsip Utama (Core Principles)

1. **Clean & Layered Architecture (Pemisahan Tanggung Jawab):**
   - **Controller:** Hanya menangani HTTP Request, validasi form request, delegasi ke Service, dan mengembalikan HTTP Response/Resource. **Dilarang keras meletakkan query database atau logika kalkulasi rute di Controller.**
   - **Service / Domain Layer:** Berisi inti logika bisnis (algoritma Dijkstra, perhitungan tarif, pembentukan graf multimoda). Bersifat independen dari framework HTTP.
   - **Data Transfer Object (DTO):** Data yang berpindah antar Controller dan Service wajib dibungkus dalam DTO berpengetikan ketat (*strongly-typed*), bukan *raw associative array*.
   - **Repository / Interface Pattern:** Akses data diabstraksikan melalui interface (misal: `StationRepositoryInterface`). Hal ini memungkinkan pergantian data store atau *mocking* saat unit testing tanpa menyentuh database asli.
   - **Model:** Hanya mendefinisikan skema Eloquent, relasi, cast tipe data, dan scope query sederhana.

2. **SOLID Principles:**
   - *Single Responsibility:* Satu class hanya memiliki satu alasan untuk berubah.
   - *Open/Closed:* Algoritma tarif dan router mudah ditambah strategi baru tanpa memodifikasi kode inti.
   - *Dependency Inversion:* Service bergantung pada abstraksi/interface, bukan class konkret.

---

## 2. Standar Bahasa & Penulisan PHP (PSR & PHP 8.3+)

### 2.1 Deklarasi Tipe Ketat (Strict Typing)
Setiap file PHP **wajib** diawali dengan deklarasi strict types:
```php
<?php

declare(strict_types=1);

namespace App\Services\Routing;
```

### 2.2 Type Hinting & Return Types Wajib
Semua parameter fungsi dan nilai kembalian (*return type*) harus memiliki pengetikan eksplisit:
```php
// BENAR (Standar Perusahaan)
public function calculateDuration(int $distanceMeters, int $speedKmh): int
{
    return (int) round(($distanceMeters / ($speedKmh * 1000 / 3600)));
}

// SALAH (Dilarang)
public function calculateDuration($distanceMeters, $speedKmh)
{
    return $distanceMeters / $speedKmh;
}
```

### 2.3 Constructor Property Promotion & Readonly Properties
Gunakan fitur modern PHP 8.2/8.3:
```php
namespace App\Services\Routing;

use App\Repositories\Contracts\StationRepositoryInterface;

final readonly class JourneyPlanner
{
    public function __construct(
        private StationRepositoryInterface $stationRepository,
        private DijkstraRouter $router,
        private FareCalculator $fareCalculator,
    ) {}
}
```

### 2.4 Gunakan Native Enums
Untuk konstanta bertipe tetap (moda transportasi, tipe transfer, status data), gunakan PHP Backed Enums:
```php
namespace App\Enums;

enum TransportMode: string
{
    case RAIL = 'rail';
    case SUBWAY = 'subway';
    case LRT = 'lrt';
    case BUS = 'bus';
    case WALK = 'walk';

    public function label(): string
    {
        return match ($this) {
            self::RAIL => 'KRL Commuter Line',
            self::SUBWAY => 'MRT Jakarta',
            self::LRT => 'LRT',
            self::BUS => 'TransJakarta',
            self::WALK => 'Jalan Kaki',
        };
    }

    public function defaultColorHex(): string
    {
        return match ($this) {
            self::RAIL => '#DC2626',
            self::SUBWAY => '#005BAC',
            self::LRT => '#0284C7',
            self::BUS => '#0077C8',
            self::WALK => '#64748B',
        };
    }
}
```

---

## 3. Struktur Standar Respons API (Standardized JSON Envelope)

Semua endpoint API internal wajib mengembalikan struktur JSON konsisten:

### Respons Sukses
```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "timestamp": "2026-10-23T11:05:00+07:00",
    "version": "1.0.0",
    "dataset_provenance": {
      "last_verified_at": "2026-10-23",
      "status": "verified"
    }
  }
}
```

### Respons Gagal / Error
```json
{
  "success": false,
  "error": {
    "code": "ROUTE_NOT_FOUND",
    "message": "Tidak ditemukan jalur transportasi yang menghubungkan kedua titik dalam dataset saat ini.",
    "details": []
  },
  "meta": {
    "timestamp": "2026-10-23T11:05:00+07:00"
  }
}
```

---

## 4. Konvensi Database (PostgreSQL)

1. **Nama Tabel & Kolom:**
   - Tabel menggunakan kata benda jamak huruf kecil (*snake_case plural*): `operators`, `stops`, `transfers`.
   - Kolom menggunakan *snake_case*: `walking_duration_seconds`, `operator_id`.
   - Foreign key wajib berakhiran `_id`: `from_stop_id`, `to_stop_id`.
2. **Koordinat & Geospasial PostgreSQL:**
   - Gunakan tipe `NUMERIC(10, 7)` untuk `latitude` dan `longitude`.
   - Gunakan ekstensi `cube` dan `earthdistance` bawaan PostgreSQL:
     ```sql
     CREATE EXTENSION IF NOT EXISTS cube;
     CREATE EXTENSION IF NOT EXISTS earthdistance;
     ```
   - Buat indeks spasial GiST untuk query pencarian stasiun terdekat berkecepatan tinggi:
     ```sql
     CREATE INDEX idx_stops_earth_distance ON stops USING gist(ll_to_earth(latitude, longitude));
     ```
   - Fungsi hitung jarak radius akurat:
     ```sql
     earth_distance(ll_to_earth(s.latitude, s.longitude), ll_to_earth(:lat, :lng)) <= :radius_meters
     ```
3. **Integritas Relasi:**
   - Setiap Foreign Key wajib didefinisikan secara eksplisit di migration dengan aksi `ON DELETE RESTRICT` atau `ON DELETE CASCADE` yang terencana.

---

## 5. Pola Desain (Design Patterns) yang Diterapkan

| Pola | Letak Penggunaan | Manfaat |
|---|---|---|
| **Strategy Pattern** | `App\Services\Fares\Strategies\` | Menghitung tarif spesifik per operator (MRT berbasis matriks stasiun, KRL berbasis tier per-km, TransJakarta flat rate) tanpa if-else bertumpuk. |
| **DTO (Data Transfer Object)** | `App\DTOs\` | Memastikan tipe data input & output perencana perjalanan aman dan terprediksi tanpa array liar. |
| **Builder Pattern** | `App\Services\Routing\MultimodalGraphBuilder` | Menyusun graf transit yang kompleks dari database ke in-memory adjacency list. |
| **Repository Pattern** | `App\Repositories\` | Memisahkan query database dari logika graf, memudahkan unit testing dengan *mock repository*. |

---

## 6. Standar Pengujian (Testing Strategy)

Setiap fitur baru wajib dilengkapi pengujian otomatis:
1. **Unit Tests (`tests/Unit/`):**
   - Menguji algoritma Dijkstra murni dengan graf buatan (tanpa koneksi database).
   - Menguji rumus perhitungan tarif per moda.
2. **Feature Tests (`tests/Feature/`):**
   - Menguji endpoint API `/api/stations/search` dan `/api/journey/plan`.
   - Menguji validasi form request jika parameter asal/tujuan tidak valid.

---

## 7. Checklist Kualitas Kode Sebelum Commit

- [ ] Kode mematuhi standar PSR-12 (dicek via `composer test` atau PHP_CodeSniffer / Pint).
- [ ] Semua class dan function memiliki `declare(strict_types=1);` dan type-hinting penuh.
- [ ] Tidak ada query database atau logika bisnis yang ditaruh di dalam Controller atau View Blade.
- [ ] Tidak ada *hardcoded credentials*, API keys, atau koneksi langsung.
- [ ] Exception domain didefinisikan secara jelas (misal: `StationNotFoundException`).
