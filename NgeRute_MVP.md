# NgeRute — MVP Web App Specification

> **Tagline:** Bandingkan rute KRL, MRT, LRT, bus, dan jalan kaki dalam satu pencarian.  
> **Wilayah awal:** Jakarta dan Jabodetabek  
> **Prioritas:** Prototipe dengan biaya operasional serendah mungkin, idealnya Rp0 selama pengembangan lokal.

## 1. Ringkasan Produk

NgeRute adalah web app pembanding perjalanan antarmoda. Pengguna memasukkan lokasi asal dan tujuan, lalu aplikasi menampilkan beberapa pilihan perjalanan berdasarkan estimasi waktu, tarif, jumlah transit, dan jarak jalan kaki.

### Masalah yang ingin diselesaikan

Informasi perjalanan Jabodetabek tersebar di berbagai operator. Pengguna perlu membandingkan beberapa moda dan memperkirakan perpindahan antarmoda secara manual.

### Nilai utama

- Membandingkan alternatif perjalanan dalam satu layar.
- Memperlihatkan rincian setiap segmen perjalanan.
- Menampilkan estimasi biaya, waktu, jumlah transit, dan jarak berjalan kaki.
- Menjelaskan sumber dan waktu pembaruan data.
- Menyediakan tautan ke kanal resmi operator untuk informasi lanjutan.

> **Batasan penting:** JalurAja bukan layanan resmi operator transportasi. Jangan menjanjikan jadwal langsung atau ETA real-time sebelum sumber data dan izin aksesnya benar-benar tersedia.

---

## 2. Sasaran MVP

### Fitur yang masuk MVP

1. Cari lokasi asal dan tujuan.
2. Tampilkan peta interaktif.
3. Cari stasiun dan halte yang ada dalam dataset.
4. Tampilkan alternatif perjalanan yang didukung data.
5. Rincian segmen: jalan kaki, kereta, atau bus.
6. Estimasi waktu, tarif, jumlah transit, dan jarak jalan kaki.
7. Tandai opsi paling cepat, paling murah, paling sedikit jalan kaki, dan transit paling sedikit.
8. Tampilkan sumber data, waktu sinkronisasi, dan indikator kualitas data.
9. Panel admin sederhana untuk mengimpor, memvalidasi, dan memperbarui dataset.

### Belum masuk MVP

- Posisi kendaraan real-time.
- Prediksi kemacetan.
- Pembelian tiket dan pembayaran.
- Akun pengguna, favorit, dan notifikasi.
- Klaim bahwa semua jadwal selalu akurat.
- Dukungan seluruh Indonesia.

### Kriteria keberhasilan awal

- Pengguna bisa mendapatkan hasil untuk pasangan asal-tujuan yang tercakup dataset.
- Setiap hasil memiliki rincian segmen yang masuk akal.
- Tarif dan waktu yang tidak diketahui ditampilkan sebagai “belum tersedia”, bukan ditebak.
- Aplikasi menunjukkan tanggal pembaruan dataset.
- Tidak ada ketergantungan wajib pada API berbayar saat development lokal.

---

## 3. Stack Teknologi

| Bagian | Pilihan | Catatan biaya |
|---|---|---|
| Backend | Laravel 12 atau versi stabil yang dipilih saat implementasi | Open source |
| Database | PostgreSQL | Open source |
| Data geospasial | PostGIS (opsional untuk MVP awal) | Open source; perlu ekstensi tersedia |
| Frontend | Laravel Blade + JavaScript | Pilihan paling sederhana |
| Peta | Leaflet | Library gratis dan open source |
| Basemap | OpenStreetMap melalui penyedia tile yang sesuai kebijakannya | Data terbuka; tile bukan layanan tanpa batas |
| Routing jalan | OSRM lokal/self-host, atau alternatif yang sesuai | Software gratis; self-host memerlukan sumber daya mesin |
| Import data | CSV/GTFS importer Laravel | Bisa dibuat sendiri |
| Queue | Laravel database queue | Tidak wajib memakai Redis untuk MVP |
| Deployment awal | Localhost | Gratis; deployment publik bisa menimbulkan biaya |

### Rekomendasi arsitektur

Mulai dengan **Laravel monolith + Blade + Leaflet**. Jangan menambah Next.js sebelum ada kebutuhan UI yang jelas. Arsitektur ini mengurangi kompleksitas deployment dan memudahkan pengembangan seorang diri.

```text
Browser
  ├── UI Laravel Blade
  └── Leaflet map
          │
          ▼
Laravel application
  ├── Search / Geocoding
  ├── Journey Planner
  ├── Fare Calculator
  ├── Dataset Importer
  └── Admin / Data Validation
          │
          ▼
PostgreSQL
  ├── Operators
  ├── Stops / Stations
  ├── Routes
  ├── Trips / Stop Times
  ├── Transfers
  └── Fare Rules
```

---

## 4. Biaya: Gratis, Bersyarat, atau Berpotensi Berbayar

| Kebutuhan | Opsi awal | Status |
|---|---|---|
| Framework Laravel | Laravel | Gratis |
| Database | PostgreSQL | Gratis |
| Peta frontend | Leaflet | Gratis |
| Data peta | OpenStreetMap | Data terbuka, dengan kewajiban lisensi/atribusi |
| Tile peta | Penyedia tile yang mengizinkan penggunaan sesuai trafik | Bisa gratis dengan batas; trafik besar dapat berbayar |
| Routing jalan | OSRM self-host | Software gratis, tetapi perlu resource mesin |
| Routing transit | Mesin routing transit/algoritma sendiri | Software bisa gratis; membutuhkan dataset berkualitas |
| Dataset operator | Portal data terbuka atau feed resmi | Bergantung ketersediaan, lisensi, dan kelengkapan |
| Geocoding | Dataset lokal atau layanan dengan kuota gratis | Batas dan ketentuan berbeda |
| Hosting publik | Tier gratis bila tersedia | Tidak dijamin gratis selamanya; batas dapat berubah |
| Domain | Subdomain hosting | Bisa gratis; domain sendiri biasanya berbayar |
| Email/SMS | Tidak dibutuhkan di MVP | Jika ditambah, bisa menimbulkan biaya |

### Catatan OpenStreetMap

- Cantumkan atribusi OpenStreetMap sesuai persyaratan.
- Jangan menganggap server tile publik OSM sebagai API gratis tanpa batas.
- Untuk prototipe, gunakan penyedia tile yang mengizinkan pola penggunaanmu dan patuhi kebijakannya.
- Jika pengguna dan trafik bertambah, evaluasi penyedia tile berbayar atau hosting tile sendiri.
- Lisensi data peta dan ketentuan layanan tile adalah dua hal berbeda.

### Catatan routing

OSRM merupakan mesin routing open source yang umumnya digunakan untuk rute jalan, terutama profil kendaraan yang dikonfigurasi. Jangan menganggap instance OSRM standar otomatis menyediakan rute pedestrian yang siap digunakan. Untuk jalan kaki, gunakan profil/data yang memang mendukungnya atau pilih mesin routing yang sesuai.

---

## 5. Sumber Data Transportasi dan Cara Memvalidasinya

Data transportasi adalah risiko terbesar proyek ini. Jangan mulai dengan mengasumsikan semua operator menyediakan API publik atau feed GTFS yang bebas digunakan.

### Urutan prioritas sumber

1. **Portal data resmi pemerintah/daerah** — cek dataset, tanggal, format, lisensi, dan syarat penggunaan.
2. **Portal resmi operator** — cari jadwal, stasiun/halte, tarif, peta rute, dan ketentuan penggunaan.
3. **GTFS atau GTFS Realtime resmi** — gunakan hanya bila feed benar-benar tersedia dan ketentuan aksesnya mengizinkan penggunaan.
4. **Dataset terbuka pihak ketiga** — cek asal-usul, lisensi, tanggal pembaruan, kelengkapan, dan ketentuan atribusi.
5. **Data input manual untuk prototipe** — boleh untuk sejumlah kecil stasiun/rute, tetapi beri label “dataset demo” dan jangan menyajikannya sebagai data live.

### Cakupan operator

| Moda | Data minimum | Hal yang harus diverifikasi |
|---|---|---|
| KRL Commuter Line | Stasiun, urutan jalur, jadwal, aturan tarif | Jadwal terbaru, perubahan layanan, lisensi data |
| MRT Jakarta | Stasiun, rute, jadwal, tarif | Feed resmi atau sumber publik yang dapat digunakan |
| LRT Jakarta | Stasiun, rute, jadwal, tarif | Ketersediaan dan tanggal pembaruan data |
| LRT Jabodebek | Stasiun, rute, jadwal, tarif | Ketersediaan data dan aturan tarif terkini |
| TransJakarta | Halte, rute, jadwal/headway jika tersedia, tarif | Dataset resmi, lisensi, cakupan layanan |
| Jalan kaki | Jaringan pedestrian, pintu masuk, penghubung ant stasiun | Apakah jaringan routing mendukung pejalan kaki dan aksesibilitas |

**Jangan mencantumkan lisensi seperti CC BY 4.0 atau ODbL untuk suatu dataset sebelum memeriksa lisensi dataset tersebut secara langsung.** Lisensi OpenStreetMap tidak otomatis berlaku untuk data jadwal atau tarif operator.

### Catatan tarif

Tarif dapat memiliki aturan berbeda, termasuk tarif tetap, jarak, transfer, atau integrasi antarmoda. Buat kalkulator tarif per operator terlebih dahulu. Jangan menjumlahkan tarif secara naif jika ada skema tarif integrasi yang berlaku.

### Template pencatatan sumber

Buat `docs/DATA_SOURCES.md` dengan tabel berikut:

| Dataset | URL resmi | Operator | Format | Lisensi/ketentuan | Terakhir diverifikasi | Frekuensi update |
|---|---|---|---|---|---|---|
| Contoh dataset | Isi setelah verifikasi | — | CSV/GTFS/API | Belum diverifikasi | YYYY-MM-DD | Belum diketahui |

Sebelum mengimplementasikan importer, pastikan URL, akses, format, lisensi, dan tanggal pembaruan telah diuji secara langsung. Jangan menyimpan API key di repository.

---

## 6. Strategi MVP yang Disarankan

Agar proyek tetap realistis, jangan mengintegrasikan seluruh moda sekaligus.

### Tahap 0 — Validasi data

- Cari dan dokumentasikan sumber resmi untuk setiap moda.
- Tentukan data yang boleh disimpan, diolah, dan ditampilkan.
- Kumpulkan sampel data untuk beberapa stasiun dan halte.
- Catat data yang tidak tersedia.
- Buat fixture/demo dataset terpisah dari data yang telah diverifikasi.

**Output:** `docs/DATA_SOURCES.md` dan dataset uji kecil.

### Tahap 1 — Peta dan pencarian lokasi

- Tampilkan peta Leaflet.
- Tampilkan marker stasiun/halte.
- Cari lokasi dari dataset internal.
- Pilih asal dan tujuan melalui input atau klik peta.
- Tampilkan koordinat dan jarak garis lurus hanya sebagai informasi awal.

**Output:** peta interaktif yang tidak berpura-pura sudah memahami seluruh jaringan transit.

### Tahap 2 — Satu moda kereta

- Import stasiun dan urutan rute dari data yang telah diverifikasi.
- Buat graph stasiun dan koneksi ant stasiun.
- Hitung rute kereta berdasarkan graph.
- Tampilkan waktu/tarif hanya jika data mendukungnya.
- Tambahkan waktu jalan kaki/transfer sebagai data eksplisit bila telah diverifikasi.

**Output:** perencana rute terbatas pada jaringan yang tersedia.

### Tahap 3 — Transfer dan multimoda

- Buat koneksi transfer antarstasiun/halte.
- Tambahkan rute bus.
- Tambahkan akses jalan kaki.
- Gunakan jadwal jika tersedia; gunakan headway hanya sebagai estimasi jika sumber mendukungnya.
- Tampilkan asumsi estimasi secara transparan.

**Output:** alternatif perjalanan multimoda pada area yang telah tercakup.

### Tahap 4 — Perbandingan alternatif

- Opsi tercepat.
- Opsi termurah.
- Opsi paling sedikit berjalan kaki.
- Opsi dengan transit paling sedikit.
- Rincian segmen dan waktu perpindahan.
- Peringatan bila jadwal/tarif tidak tersedia atau sudah usang.

### Tahap 5 — Uji coba pengguna

- Uji pasangan asal-tujuan yang benar-benar dikenal.
- Bandingkan hasil dengan informasi operator.
- Catat rute tidak masuk akal, transfer yang salah, dan tarif yang berbeda.
- Perbaiki data sebelum memperluas cakupan.

---

## 7. Desain Database Awal

Model ini merupakan rancangan awal, bukan representasi GTFS lengkap.

### `operators`

- `id`
- `name`
- `mode` — rail, bus, atau lainnya
- `official_url`
- `data_source_url`
- `license_notes`
- timestamps

### `stops`

- `id`
- `operator_id` nullable jika halte/stasiun terintegrasi
- `code` nullable
- `name`
- `latitude`
- `longitude`
- `location_type` — station, stop, entrance, interchange
- `is_active`
- timestamps

Tambahkan PostGIS bila pencarian spasial menjadi kebutuhan. Untuk prototipe kecil, latitude/longitude biasa dapat mencukupi.

### `routes`

- `id`
- `operator_id`
- `code`
- `name`
- `mode`
- `color` nullable
- `source_updated_at` nullable
- timestamps

### `trips`

- `id`
- `route_id`
- `service_code` nullable
- `trip_code` nullable
- `headsign` nullable
- timestamps

### `stop_times`

- `id`
- `trip_id`
- `stop_id`
- `stop_sequence`
- `arrival_time` nullable
- `departure_time` nullable
- timestamps

### `transfers`

- `id`
- `from_stop_id`
- `to_stop_id`
- `walking_distance_m` nullable
- `walking_duration_seconds` nullable
- `transfer_type`
- `source_note`
- `verified_at` nullable
- timestamps

Jangan menganggap dua stasiun dengan nama serupa sebagai titik transfer yang sama tanpa verifikasi akses fisik.

### `fare_rules`

- `id`
- `operator_id`
- `route_id` nullable
- `origin_stop_id` nullable
- `destination_stop_id` nullable
- `amount` nullable
- `currency` default `IDR`
- `valid_from` nullable
- `valid_until` nullable
- `source_note`
- `verified_at` nullable
- timestamps

Skema tarif nyata dapat lebih kompleks daripada tabel sederhana ini. Desain perlu disesuaikan setelah format data operator diketahui.

### `data_import_runs`

- `id`
- `dataset_name`
- `source_url`
- `started_at`
- `finished_at` nullable
- `status`
- `records_imported`
- `records_rejected`
- `error_summary` nullable

Gunakan tabel ini untuk menampilkan kapan data terakhir berhasil diperbarui.

---

## 8. Algoritma Perencana Perjalanan

### Representasi graph

- **Node:** stasiun, halte, pintu masuk, atau titik transfer.
- **Edge:** perjalanan antarhalte, perpindahan antarmoda, dan jalan kaki.
- **Bobot:** durasi, tarif, jarak, atau kombinasi sesuai tujuan optimasi.

### Pilihan algoritma

Mulai dengan Dijkstra untuk graph berbobot nonnegatif. Untuk alternatif multimoda berbasis jadwal, model graph perlu mempertimbangkan waktu kedatangan dan keberangkatan; algoritma shortest path sederhana tanpa jadwal tidak cukup untuk hasil yang akurat.

### Fungsi pembanding

- **Tercepat:** minimalkan total waktu perjalanan.
- **Termurah:** minimalkan total tarif berdasarkan aturan yang tervalidasi.
- **Jalan kaki paling sedikit:** minimalkan total jarak jalan kaki.
- **Transit paling sedikit:** minimalkan jumlah perpindahan moda.

Jangan menggabungkan menit, rupiah, dan jumlah transit menjadi satu bobot tanpa normalisasi dan penjelasan. Untuk MVP, hitung setiap kriteria secara terpisah, lalu tampilkan hasilnya.

### Estimasi waktu

Total waktu dapat terdiri dari:

`jalan kaki + menunggu + waktu kendaraan + perpindahan`

Jika waktu tunggu tidak berasal dari jadwal yang valid, tampilkan sebagai perkiraan dan jelaskan metode yang digunakan. Jangan menampilkan estimasi seolah-olah real-time.

---

## 9. Halaman UI

### Beranda

- Logo JalurAja.
- Input asal dan tujuan.
- Tombol tukar asal-tujuan.
- Peta.
- Pilihan moda yang didukung.
- Keterangan cakupan data.

### Hasil pencarian

Setiap kartu hasil menampilkan:

- total durasi estimasi;
- total tarif atau “belum tersedia”;
- jumlah transit;
- total jarak jalan kaki;
- urutan segmen perjalanan;
- waktu pembaruan data;
- tombol lihat rute di peta.

### Detail perjalanan

- Polyline pada peta.
- Timeline perjalanan.
- Nama halte/stasiun dan moda.
- Durasi jalan kaki dan transfer.
- Catatan ketidakpastian jadwal/tarif.

### Admin data

- Import dataset.
- Validasi format.
- Laporan duplikasi dan data invalid.
- Riwayat import.
- Status sumber data dan waktu pembaruan terakhir.

---

## 10. Struktur Folder yang Disarankan

```text
app/
├── Actions/
│   ├── ImportTransitDataset.php
│   └── PlanJourney.php
├── Http/
│   ├── Controllers/
│   │   ├── JourneyController.php
│   │   └── Admin/
│   └── Requests/
├── Models/
│   ├── Operator.php
│   ├── Stop.php
│   ├── Route.php
│   ├── Trip.php
│   ├── StopTime.php
│   ├── Transfer.php
│   └── FareRule.php
└── Services/
    ├── Routing/
    │   ├── JourneyPlanner.php
    │   └── DijkstraRouter.php
    ├── Fares/
    │   └── FareCalculator.php
    └── TransitData/
        ├── DatasetImporter.php
        └── DatasetValidator.php

resources/
├── views/
└── js/
    └── jaluraja-map.js

database/
├── migrations/
└── seeders/

docs/
├── DATA_SOURCES.md
├── MVP.md
└── ROUTING_NOTES.md
```

Struktur ini fleksibel; tidak perlu membuat seluruh kelas sekaligus. Bangun saat ada kebutuhan nyata.

---

## 11. Keamanan dan Keandalan Data

- Validasi input asal-tujuan dan koordinat.
- Batasi ukuran file import dan validasi CSV/GTFS.
- Gunakan transaction saat import dataset.
- Jangan mengekspos kredensial atau API key di frontend/repository.
- Simpan provenance: sumber, tanggal, dan lisensi data.
- Tampilkan data usang atau tidak terverifikasi.
- Cache hasil pencarian yang aman untuk mengurangi beban.
- Jangan scraping situs operator tanpa memeriksa ketentuan penggunaan dan izin yang relevan.

---

## 12. Checklist Sebelum Rilis

- [ ] Sumber data dan lisensi dicatat untuk setiap dataset.
- [ ] Atribusi OpenStreetMap terlihat pada peta.
- [ ] Kebijakan tile provider telah diperiksa.
- [ ] Data stasiun/halte tidak duplikat.
- [ ] Transfer antarmoda telah diverifikasi.
- [ ] Tarif tidak ditebak saat data tidak tersedia.
- [ ] Waktu perjalanan diberi label estimasi bila perlu.
- [ ] Timestamp sinkronisasi ditampilkan.
- [ ] Rute diuji dengan pasangan asal-tujuan nyata.
- [ ] Tidak ada klaim live tracking tanpa feed real-time.
- [ ] Prototipe berjalan lokal tanpa layanan berbayar wajib.

---

## 13. Keputusan yang Disarankan

Untuk versi pertama:

1. Gunakan Laravel + Blade + PostgreSQL + Leaflet.
2. Mulai lokal agar biaya hosting nol.
3. Validasi sumber data sebelum menulis importer untuk semua operator.
4. Buat peta dan pencarian stasiun terlebih dahulu.
5. Implementasikan satu jaringan kereta yang datanya sudah terverifikasi.
6. Tambahkan transfer, bus, dan perbandingan multimoda secara bertahap.
7. Jangan mengaktifkan fitur tarif/jadwal jika datanya belum dapat dipercaya.

**Prinsip utama JalurAja:** lebih baik menampilkan rute yang terbatas tetapi transparan dan dapat dipercaya daripada mengklaim seluruh Jabodetabek tercakup dengan data yang belum terverifikasi.
