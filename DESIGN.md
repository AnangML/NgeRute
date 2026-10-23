# DESIGN — NgeRute UI/UX Design System & Specification

> **Aplikasi:** NgeRute — Web App Pembanding Perjalanan Antarmoda  
> **Pendekatan Desain:** Modern, Clean, Mobile-First, Glassmorphism Sleek, High Information Density.  
> **Prinsip Utama:** *Map-Centric, Instant Clarity, Tactile Micro-interactions, Zero Clutter.*

---

## 1. Filosofi Desain & Karakter Visual

NgeRute dirancang dengan mengedepankan **pengalaman seperti aplikasi navigasi kelas dunia (Citymapper / Apple Maps)**, namun disesuaikan dengan konteks transportasi lokal Jabodetabek.
1. **Map-Centric Canvas:** Peta adalah kanvas utama yang selalu hidup di latar belakang layar penuh (*full viewport*).
2. **Layered Elevation:** Komponen kontrol (pencarian, kartu rute, detail segmen) melayang di atas peta dengan efek kaca lembut (*glassmorphism / backdrop-blur*), bayangan halus (*ambient shadows*), dan sudut membulat (*smooth border-radius*).
3. **Identitas Warna Resmi Moda:** Setiap moda transportasi menggunakan palet warna khas yang diakui publik agar pengguna langsung mengenali jenis transportasinya secara intuitif.
4. **Responsif & Adaptif:** 
   - **Desktop:** Floating Sidebar Panel di sisi kiri dengan animasi geser dan kartu collapsible.
   - **Mobile:** Interactive Bottom Sheet dengan 3 posisi snap (*peek*, *half*, *expanded*).

---

## 2. Sistem Warna Modern & Minimalis (Aturan 60-30-10)

Untuk menciptakan antarmuka yang bersih, elegan, dan tidak berantakan (*visual clutter-free*), NgeRute menerapkan **Aturan Warna 60-30-10**:

- **60% Warna Dominan (Kanvas & Latar Belakang):**
  - Surface base cerah dan bersih: `#F8FAFC` (Slate 50) & Frosted Glass `rgba(255, 255, 255, 0.92)`.
  - Ruang bernapas (*whitespace*) luas tanpa garis pembatas tebal yang mengganggu.
- **30% Warna Sekunder (Struktur, Tipografi, & Kartu):**
  - Charcoal / Slate dalam: `#0F172A` (Slate 900) untuk teks utama, `#475569` untuk teks pendukung, dan border halus `#E2E8F0` / `#CBD5E1`.
  - Digunakan untuk hierarki konten, card background, dan chip netral agar tampilan terkesan matang dan kokoh.
- **10% Warna Aksen (Fokus & Interaksi Kunci):**
  - *Electric Sky Blue* `#0284C7` (dengan hover `#0369A1`).
  - Digunakan secara selektif dan berdampak tinggi hanya untuk tombol aksi utama (*Primary CTA*), ring fokus aktif, pin asal, dan highlight angka durasi tercepat.

```css
:root {
  /* 60% — Dominant Canvas & Clean Whitespace */
  --color-dominant-bg: #f8fafc;
  --surface-glass: rgba(255, 255, 255, 0.92);
  --surface-card: #ffffff;
  --surface-card-subtle: #f8fafc;

  /* 30% — Secondary Structure & Typography */
  --color-secondary-text: #0f172a;
  --color-secondary-muted: #475569;
  --color-secondary-border: #e2e8f0;
  --color-secondary-border-hover: #cbd5e1;

  /* 10% — Precise Accent (Action & Focus) */
  --color-accent: #0284c7;
  --color-accent-hover: #0369a1;
  --color-accent-subtle: rgba(2, 132, 199, 0.08);

  /* Shadows & Radius */
  --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.07);
  --shadow-floating: 0 20px 25px -5px rgba(15, 23, 42, 0.1);
  --radius-md: 10px;
  --radius-lg: 14px;
  --radius-xl: 18px;
}
```

### 2.2 Warna Resmi Moda Transportasi (Transit Route Colors)

| Moda | Kode Warna | Label Hex | Visual Style di Peta |
|---|---|---|---|
| **MRT Jakarta (North-South)** | `--color-mrt: #005bac;` | `#005BAC` (Deep Blue) | Garis solid 6px dengan inner glow |
| **KRL Commuter Line** | `--color-krl: #dc2626;` | `#DC2626` (Commuter Red) | Garis solid 5px tebal |
| **LRT Jabodebek** | `--color-lrt-jbd: #0284c7;`| `#0284C7` (Sky Blue) | Garis solid 5px |
| **LRT Jakarta (Velodrome)** | `--color-lrt-jkt: #e11d48;`| `#E11D48` (Rose Red) | Garis solid 5px |
| **TransJakarta (BRT)** | `--color-tj: #0284c7;` | `#0077C8` (TJ Blue) | Garis solid 4px dengan casing putih |
| **Jalan Kaki (Pedestrian)** | `--color-walk: #64748b;` | `#64748B` (Slate Dash) | Garis putus-putus (*dashed line*) 4px |

---

## 3. Tipografi & Skala Teks

Menggunakan font keluarga modern **Inter** atau **Plus Jakarta Sans** via Google Fonts untuk keterbacaan tinggi pada teks kecil dan angka stasiun.

- **Display Title:** 24px / 32px — Bold (700)
- **Section Heading:** 18px / 24px — SemiBold (600)
- **Card Title / Stasiun:** 15px / 20px — SemiBold (600)
- **Body Text:** 14px / 20px — Regular (400)
- **Sub-caption / Time Badge:** 12px / 16px — Medium (500)
- **Badge Label / Micro:** 11px / 14px — Bold (700) uppercase, letter-spacing +0.5px.

---

## 4. Arsitektur Layout & Komponen UI

### 4.1 Layout Desktop (Split-screen Floating Overlay)

```text
┌────────────────────────────────────────────────────────────────────────┐
│  [Logo] NgeRute      [Search Stasiun / Halte]       [Tentang] [Bantuan]│
├────────────────────────────────────────────────────────────────────────┤
│ ┌───────────────────────────┐                                          │
│ │ 🔍 Floating Drawer Panel  │                                          │
│ │ ┌───────────────────────┐ │                                          │
│ │ │ 🟢 Asal: Dukuh Atas   │ │                                          │
│ │ │ 🔄 [Tukar Titik]      │ │              Interactive Leaflet         │
│ │ │ 🔴 Tujuan: Lebak Bulus│ │                   Map Canvas             │
│ │ └───────────────────────┘ │                                          │
│ │                           │          (Peta Fullscreen Latar)         │
│ │ [⚡Tercepat][💰Termurah]  │                                          │
│ │ [🚶Minim Walk][🔄Direct]  │              📍 Origin Marker            │
│ │                           │               │                          │
│ │ ┌───────────────────────┐ │               ▼ (MRT Blue Polyline)      │
│ │ │ ⚡ Rekomendasi Utama  │ │               │                          │
│ │ │ 28 Menit • Rp14.000   │ │              📍 Destination Marker       │
│ │ │ 0 Transit • 240m Jalan│ │                                          │
│ │ │ --------------------- │ │                                          │
│ │ │ [Lihat Rincian Step]  │ │                                          │
│ │ └───────────────────────┘ │                                          │
│ └───────────────────────────┘                                          │
└────────────────────────────────────────────────────────────────────────┘
```

### 4.2 Layout Mobile (Interactive Bottom Sheet)
- **State 1: Collapsed / Peek (Tinggi ~90px):** Menampilkan input asal-tujuan ringkas dan estimasi waktu tercepat. Peta terlihat luas.
- **State 2: Half-Expanded (Tinggi ~50vh):** Menampilkan kartu komparasi 4 opsi rute (Tercepat, Termurah, Minim Jalan, Minim Transit).
- **State 3: Fully Expanded (Tinggi ~90vh):** Menampilkan detail perjalanan langkah-demi-langkah (*step-by-step timeline*), daftar stasiun yang dilalui, waktu transfer, dan rincian tarif.

---

## 5. Komponen Utama

### 5.1 Search & Waypoint Input
- Input dengan ikon titik bulat warna hijau untuk **Titik Asal (Origin)** dan titik merah untuk **Titik Tujuan (Destination)**.
- Tombol **Swap Waypoints** (animasi putar 180° saat diklik).
- Dropdown autocomplete instan dengan kategori ikon (Stasiun Kereta, Halte Bus, Titik Populer).
- Tombol aksi *"Pilih di Peta"* untuk memilih koordinat langsung.

### 5.2 Kartu Rekomendasi Rute (Route Comparison Card)
Setiap kartu rute menyajikan:
1. **Badges:** `TERCEPAT` (Hijau), `TERMURAH` (Kuning Emas), `MINIM JALAN KAKI` (Ungu), atau `LANGSUNG` (Biru).
2. **Metrik Inti:**
   - Total durasi (misal: **32 mnt** - font besar tebal).
   - Total tarif (misal: **Rp7.000**).
   - Ringkasan moda: Deretan pill moda berturutan: `[🚶 3m] → [🚆 KRL] → [🚶 Transfer 2m] → [🚇 MRT]`.
   - Total jarak jalan kaki (misal: `320 m`).
3. **Interaktivitas:** Hover atau klik kartu akan langsung meng-highlight polyline rute tersebut di peta dan menyesuaikan tampilan (*fitBounds*).

### 5.3 Timeline Segmen Perjalanan (Step-by-Step Drawer)
- **Garis Vertikal Terhubung:** Sesuai warna moda masing-masing.
- **Segmen Transit:** Dilengkapi ikon pejalan kaki dengan estimasi jarak jalan kaki dalam meter dan menit.
- **Daftar Stasiun Antara (Collapsible):** Dapat dibuka untuk melihat daftar stasiun yang dilewati (misal: *"+ 5 stasiun berikutnya"*).
- **Data Provenance Box:** Menampilkan:
  > *"Data tarif & rute diverifikasi pada: 2026-10-15 • Sumber: Data Resmi MRT & KCI"*

### 5.4 Leaflet Custom Elements
- **Marker Kustom SVG:** Pin berbayangan halus dengan logo moda di tengahnya (bukan default blue pin jadul).
- **Interchange Marker:** Lingkaran putih tebal dengan outline ganda untuk titik integrasi (seperti interchange Dukuh Atas).
- **Active Polyline:** Garis moda dengan border putih kontras halus (*casing*) agar mudah terbaca di atas peta jalan OSM.

---

## 6. Micro-Interactions & Motion Guide

- **Panel Transition:** `cubic-bezier(0.16, 1, 0.3, 1)` durasi 300ms untuk efek *spring* yang lembut saat drawer dibuka/ditutup.
- **Card Hover:** `transform: translateY(-2px)` dengan `box-shadow` bertambah lembut untuk memberi sensasi kedalaman sentuhan.
- **Map Polyline Animation:** Polyline rute baru muncul dengan efek animasi garis mengalir (*dash-offset flow*) saat rute dihitung.
- **Skeleton Loading:** Tampilan shimmer halus saat perencana rute sedang memproses hasil perhitungan graf.
