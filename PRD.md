# PRD — NgeRute (Product Requirements Document)

> **Versi Dokumen:** 1.0.0  
> **Status:** Approved / Base Specification  
> **Terakhir Diperbarui:** 2026-10-23  
> **Target Wilayah Awal:** Jabodetabek (Jakarta, Bogor, Depok, Tangerang, Bekasi)  
> **Tagline:** *"Bandingkan rute KRL, MRT, LRT, bus, dan jalan kaki dalam satu pencarian transparan."*

---

## 1. Latar Belakang & Visi Produk

### 1.1 Problem Statement
1. **Fragmentasi Informasi:** Pengguna transportasi publik di Jabodetabek harus berpindah-pindah aplikasi (KAI Access/Commuter, MRT-J App, TIJE, Google Maps) untuk merencanakan perjalanan multimoda.
2. **Transfer Antarmoda Tidak Transparan:** Banyak rute aplikasi umum tidak memperhitungkan jarak jalan kaki fisik, akses jembatan penyeberangan multiguna (JPO/JPS), dan waktu perpindahan realistis antar-stasiun (misal: perpindahan Stasiun MRT Dukuh Atas BNI ke KRL Sudirman atau LRT Dukuh Atas).
3. **Ketidakpastian Biaya:** Integrasi tarif antar-operator sering kali membingungkan, dan aplikasi luar sering kali tidak memperhitungkan tarif riil per moda di Jabodetabek.
4. **Data Tidak Transparan:** Pengguna tidak tahu apakah jadwal dan rute yang ditampilkan merupakan data live atau perkiraan.

### 1.2 Visi & Nilai Utama NgeRute
NgeRute hadir sebagai **web app pembanding perjalanan antarmoda (transit aggregator)** yang independen, cepat, ringan, dan **jujur terhadap data**.
- **Perbandingan Multi-Kriteria:** Pengguna disajikan 4 perspektif rute: Tercepat, Termurah, Minim Jalan Kaki, dan Minim Transit.
- **Transparansi Data (Data Provenance):** Tanggal update dataset, sumber data resmi, dan indikator kepastian tarif/waktu ditampilkan secara jelas.
- **Zero Cost Local First:** Dibangun dengan arsitektur mandiri (PHP Laravel + PostgreSQL + Leaflet OSM) tanpa ketergantungan wajib pada API berbayar komersial selama pengembangan.

---

## 2. Pengguna Sasaran (User Personas)

### Persona 1: Dimas — Commuter Harian (Daily Worker)
- **Karakteristik:** Bekerja di Sudirman/SCBD, tinggal di Depok/Bekasi.
- **Kebutuhan Utama:** Mengetahui opsi tercepat dan titik transit paling efisien saat jam sibuk. Ingin melihat estimasi total waktu dan alternatif moda bila salah satu jalur sedang padat.

### Persona 2: Sarah — Pelajar / Pengguna Hemat (Budget-Conscious)
- **Karakteristik:** Mahasiswa yang bepergian antar kampus atau tempat magang.
- **Kebutuhan Utama:** Mencari opsi termurah (misal: kombinasi KRL + TransJakarta tarif tetap Rp3.500 vs MRT).

### Persona 3: Budi — Pejalan Santai / Pengguna Baru (Comfort-Conscious)
- **Karakteristik:** Jarang naik transportasi umum atau membawa barang bawaan.
- **Kebutuhan Utama:** Menghindari jalan kaki terlalu jauh di area transfer terbuka dan meminimalkan jumlah perpindahan moda (Direct/Minim Transit).

---

## 3. Ruang Lingkup Produk (Product Scope)

### 3.1 Fitur In-Scope (MVP v1.0)
1. **Interactive Map & Station Explorer:**
   - Peta Leaflet interaktif dengan layer stasiun/halte terverifikasi.
   - Deteksi lokasi asal dan tujuan via:
     - Search autocomplete stasiun/halte internal.
     - Klik langsung pada peta (reverse lookup ke stasiun terdekat).
2. **Multimodal Journey Planner:**
   - Perhitungan alternatif rute berbasis Graph & Algoritma Shortest Path (Dijkstra terbobot).
   - Dukungan moda: KRL Commuter Line, MRT Jakarta, LRT Jabodebek, LRT Jakarta, dan Segmen Jalan Kaki (Walking/Transfer).
3. **Komparasi 4 Kategori Rekomendasi:**
   - ⚡ **Tercepat:** Total waktu minimum.
   - 💰 **Termurah:** Total estimasi rupiah terendah.
   - 🚶 **Paling Sedikit Jalan Kaki:** Total meter walking terpendek.
   - 🔄 **Paling Sedikit Transit:** Jumlah perpindahan moda paling minim.
4. **Rincian Segmen Perjalanan (Step-by-Step Timeline):**
   - Rincian step: moda yang dinaiki, arah rute (*headsign*), jumlah stasiun yang dilewati, titik transit, durasi berjalan kaki, dan tarif per segmen.
   - Polyline rute terpilih digambar jelas di atas peta dengan warna khas masing-masing operator.
5. **Data Provenance & Disclaimer:**
   - Tanggal dan status validitas dataset.
   - Label khusus jika jadwal/tarif bersifat estimasi atau belum tersedia.
6. **Admin Dataset Console:**
   - Validasi data stasiun, rute, transfer antarmoda, dan tabel tarif.
   - Riwayat import data lokal.

### 3.2 Fitur Out-of-Scope (Bukan Target MVP Awal)
- Posisi GPS bus/kereta live (Real-time vehicle tracking) sebelum API/GTFS-RT resmi tersedia publik dengan lisensi legal.
- Pembelian tiket dan payment gateway integrasi.
- Akun pengguna, login sosial, bookmark rute cloud (dapat digantikan dengan `localStorage` untuk MVP).
- Cakupan nasional luar Jabodetabek.

---

## 4. Kebutuhan Fungsional (Functional Requirements)

| ID | Modul | Deskripsi Kebutuhan | Prioritas |
|---|---|---|---|
| **FR-01** | Pencarian | Pengguna dapat mengetikkan nama stasiun/halte asal dan tujuan dengan debounce autocomplete. | P0 |
| **FR-02** | Pencarian | Pengguna dapat menukar (*swap*) titik asal dan tujuan dengan 1 klik. | P0 |
| **FR-03** | Peta | Peta otomatis bergeser (*fitBounds*) untuk menampilkan rute dan kedua titik ujung perjalanan. | P0 |
| **FR-04** | Peta | Klik pada stasiun di peta dapat langsung dijadikan titik "Set as Origin" atau "Set as Destination". | P1 |
| **FR-05** | Routing | Sistem menghasilkan minimal 1 hingga 4 alternatif rute yang valid sesuai kriteria pembanding. | P0 |
| **FR-06** | Segmen | Setiap alternatif rute menyajikan rincian per segmen (waktu tunggu asumsi, waktu perjalanan, transfer walking). | P0 |
| **FR-07** | Tarif | Sistem menghitung tarif sesuai aturan per operator (flat/jarak) dan menampilkan label "Estimasi" bila ada asumsi. | P0 |
| **FR-08** | Offline/Fallback | Jika rute tidak ditemukan dalam dataset yang ada, aplikasi menampilkan pesan edukatif dan menyarankan stasiun terdekat yang tercakup. | P1 |
| **FR-09** | Provenance | Setiap hasil pencarian mencantumkan informasi waktu sinkronisasi dataset terakhir. | P1 |

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

1. **Performa:**
   - Autocomplete stasiun merespons dalam < 100ms untuk dataset lokal.
   - Kalkulasi rute perencana perjalanan selesai dalam < 350ms untuk graf koridor Jabodetabek.
2. **Aksesibilitas & Responsivitas:**
   - Desain **Mobile-First**: Tampilan bawah (*bottom sheet*) yang dapat digeser di perangkat mobile, dan kartu panel mengambang di desktop.
   - Mendukung input sentuh (touch) pada peta dan navigasi keyboard pada formulir pencarian.
3. **Kemandirian Infrastruktur (Cost = Rp0):**
   - Tidak ada ketergantungan berbayar pada Google Maps Platform API (menggunakan Leaflet + OpenStreetMap CartoDB Tiles).
   - Database berjalan di PostgreSQL lokal (Laragon) dengan ekstensi geospasial `cube` & `earthdistance`.
4. **Keamanan & Validasi:**
   - Sanitasi input pencarian untuk mencegah injeksi SQL / XSS.
   - Rate limiting standar pada endpoint API routing untuk mencegah DoS lokal.

---

## 6. Metrik Keberhasilan (Success Metrics)

- **Akurasi Rute:** 100% rute uji coba pada koridor PoC (Dukuh Atas, Sudirman, Manggarai, Lebak Bulus) menghasilkan segmen transfer fisik yang masuk akal dan dapat dilalui secara nyata.
- **Kejelasan Tarif:** 0 tebakan fiktif; bila tarif belum terdata di sistem, wajib menampilkan "Belum tersedia" daripada angka acak.
- **Kecepatan Muat:** Skor Lighthouse Performance Desktop > 90 dan Mobile > 85.
