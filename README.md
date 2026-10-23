# NgeRute 🚆🗺️

> **Tagline:** Bandingkan rute KRL, MRT, LRT, bus, dan jalan kaki dalam satu pencarian transparan.  
> **Wilayah:** Jakarta dan Jabodetabek  
> **Filosofi:** Jujur pada data, zero-cost development lokal, dan berorientasi pada kemudahan pengguna.

---

## 📚 Dokumentasi Arsitektur & Perancangan

Proyek ini dirancang secara terstruktur dengan acuan dokumen berikut:

1. 📋 **[PRD.md](PRD.md)** — *Product Requirements Document*: Latar belakang masalah, persona pengguna, batasan ruang lingkup, kebutuhan fungsional (FR) & non-fungsional (NFR), serta metrik keberhasilan.
2. 🎨 **[DESIGN.md](DESIGN.md)** — *UI/UX Design System*: Filosofi desain map-centric, tokens warna resmi operator moda, tipografi modern, arsitektur tata letak (Desktop Floating Panel & Mobile Bottom Sheet), dan panduan micro-interactions.
3. 🏛️ **[ARSITEKTUR.md](ARSITEKTUR.md)** — *Technical Architecture & Engineering Specification*: Enterprise Clean Monolith Laravel 11/12, skema database PostgreSQL 15+ dengan ekstensi `cube` & `earthdistance` (indeks GiST), pemodelan graf multimoda terbobot, multi-criteria Dijkstra router, dan spesifikasi kontrak API.
4. 📐 **[STANDAR_KODING.md](STANDAR_KODING.md)** — *Enterprise Engineering Standards*: Panduan arsitektur bersih, PSR-12, `declare(strict_types=1);`, DTO pattern, Strategy pattern, Repository pattern, dan standar respons JSON envelope.
5. 📄 **[NgeRute_MVP.md](NgeRute_MVP.md)** — *Spesifikasi Dasar MVP*: Analisis kelayakan biaya, strategi implementasi bertahap, dan checklist pra-rilis.
6. 🔍 **[docs/DATA_SOURCES.md](docs/DATA_SOURCES.md)** — *Transit Registry & Provenance*: Registri sumber data resmi operator (MRT, KRL, LRT, TransJakarta), lisensi, dan log audit verifikasi lapangan (termasuk transfer hub Dukuh Atas).

---

## 🛠️ Stack Teknologi

- **Backend:** PHP 8.3 + Laravel 11/12
- **Database:** PostgreSQL 15+ (Ekstensi `cube` & `earthdistance` dengan Spatial GiST Index) bawaan Laragon
- **Frontend:** Laravel Blade + Vanilla CSS modern + Vanilla JS (Modular, no heavy build overhead)
- **Peta Interaktif:** Leaflet.js + OpenStreetMap (CartoDB Positron tiles)
- **Routing Engine:** In-memory Multimodal Transit Graph + Multi-Criteria Dijkstra Algorithm

---

## 🚀 Status & Roadmap

- [x] **Fase 0:** Penyusunan Dokumen Spesifikasi (`PRD.md`, `DESIGN.md`, `ARSITEKTUR.md`, `DATA_SOURCES.md`).
- [ ] **Fase 1:** Inisialisasi Proyek Laravel, Migrasi Tabel Database Spasial, dan Seeder Koridor Dukuh Atas (MRT + KRL + LRT).
- [ ] **Fase 2:** Antarmuka Peta Interaktif Leaflet + Floating Search Drawer + Autocomplete Stasiun.
- [ ] **Fase 3:** Implementasi Routing Engine Multimoda (Tercepat, Termurah, Minim Jalan, Minim Transit).
- [ ] **Fase 4:** Step-by-Step Segment Timeline & Integrasi Tarif Nyata.