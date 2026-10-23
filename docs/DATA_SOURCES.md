# DATA_SOURCES — NgeRute Transit Registry & Provenance Log

> **Dokumen:** Pencatatan Sumber Data Resmi, Lisensi, dan Status Verifikasi  
> **Aplikasi:** NgeRute  
> **Terakhir Diverifikasi:** 2026-10-23  

---

## 1. Pedoman Penggunaan & Etika Data

1. **Prinsip Kejujuran Data:**
   - Tidak melakukan estimasi acak atau *hardcoded fictitious timing*.
   - Jika jadwal pasti per menit tidak tersedia dari feed resmi, gunakan label **"Estimasi berbasis headway"** dengan rincian rentang menit.
   - Jika tarif belum terverifikasi secara resmi, tandai sebagai **"Belum tersedia"**.
2. **Kewajiban Atribusi:**
   - Cantumkan atribusi **OpenStreetMap contributors** (`© OpenStreetMap contributors`) pada peta Leaflet.
   - Cantumkan nama operator resmi pada setiap rute yang ditampilkan.
3. **Penyimpanan Kunci & Kredensial:**
   - Tidak menyimpan API key, token rahasia, atau kredensial internal di repositori publik.

---

## 2. Tabel Registri Sumber Data

| Operator / Moda | URL Resmi | Format Sumber | Cakupan Wilayah | Status Lisensi / Ketentuan | Terakhir Diverifikasi | Frekuensi Update |
|---|---|---|---|---|---|---|
| **MRT Jakarta (PT MRT Jakarta Perseroda)** | [jakartamrt.co.id](https://jakartamrt.co.id) | Publikasi Resmi / Tabel Tarif & Stasiun | 13 Stasiun (Lebak Bulus Grab ⇄ Bundaran HI) | Informasi Publik Resmi Operator | 2026-10-23 | Berkala / Saat ada perubahan tarif |
| **KAI Commuter (PT Kereta Commuter Indonesia)** | [commuterline.id](https://commuterline.id) | Publikasi Peta Rute & Skema Tarif KM | Seluruh Lin Jabodetabek (Bogor, Cikarang, Rangkasbitung, Tangerang, Tanjung Priok) | Informasi Publik Resmi Operator | 2026-10-23 | Berkala / Gapeka resmi |
| **LRT Jabodebek (KAI)** | [lrtjabodebek.kai.id](https://lrtjabodebek.kai.id) | Publikasi Resmi Stasiun & Tarif | Lin Cibubur & Lin Bekasi | Informasi Publik Resmi Operator | 2026-10-23 | Berkala |
| **LRT Jakarta (PT LRT Jakarta)** | [lrtjakarta.co.id](https://lrtjakarta.co.id) | Publikasi Resmi Lin Pegangsaan Dua ⇄ Velodrome | Lin Fase 1 (Kelapa Gading - Rawamangun) | Informasi Publik Resmi Operator | 2026-10-23 | Berkala |
| **TransJakarta (PT Transportasi Jakarta)** | [transjakarta.co.id](https://transjakarta.co.id) | Koridor Utama BRT & Peta Halte | Koridor BRT Utama 1-14 | Informasi Publik Pemprov DKI / Operator | 2026-10-23 | Berkala |
| **Interchange Pedestrian (Dukuh Atas Hub)** | Peninjauan Fisik & Peta Jembatan Penyeberangan Multiguna (JPM) Dukuh Atas | Survey Pengukuran Jarak & Waktu Jalan Kaki | Dukuh Atas BNI (MRT) ⇄ Sudirman (KRL) ⇄ Dukuh Atas (LRT) ⇄ BNI City (Bandara) | Data Terbuka Verifikasi Mandiri (NgeRute Internal Field Audit) | 2026-10-23 | Diverifikasi manual |

---

## 3. Sampel Data Terverifikasi: Koridor PoC Dukuh Atas

### 3.1 Hubungan Transfer Fisik (Pedestrian Link)
- **Stasiun MRT Dukuh Atas BNI ⇄ Stasiun KRL Sudirman:**
  - Jarak: ~220 meter
  - Durasi jalan santai: ~3 - 4 menit
  - Jalur: Trotoar Terbuka & Terowongan Kendal (Ramah Pejalan Kaki)
  - Aksesibilitas: Datar, dilengkapi guiding block
- **Stasiun MRT Dukuh Atas BNI ⇄ Stasiun LRT Jabodebek Dukuh Atas:**
  - Jarak: ~350 meter via Jembatan Penyeberangan Multiguna (JPM) Dukuh Atas
  - Durasi jalan santai: ~5 menit
  - Aksesibilitas: Elevator, eskalator, jembatan tertutup
- **Stasiun KRL Sudirman ⇄ Stasiun BNI City:**
  - Jarak: ~280 meter via Terowongan Kendal
  - Durasi jalan santai: ~4 menit

### 3.2 Sampel Stasiun MRT Jakarta (Fase 1)
| No | Kode | Nama Stasiun | Latitude | Longitude | Tipe |
|---|---|---|---|---|---|
| 1 | LBB | Lebak Bulus Grab | -6.289167 | 106.774444 | Elevated Terminus |
| 2 | FTW | Fatmawati Indomaret | -6.292500 | 106.793889 | Elevated |
| 3 | CPR | Cipete Raya | -6.278611 | 106.797222 | Elevated |
| 4 | HJN | Haji Nawi | -6.266667 | 106.797500 | Elevated |
| 5 | BLK | Blok A | -6.255556 | 106.797222 | Elevated |
| 6 | BLM | Blok M BCA | -6.244444 | 106.798056 | Elevated Interchange |
| 7 | ASE | ASEAN | -6.238611 | 106.798889 | Elevated |
| 8 | SNY | Senayan Mastercard | -6.225278 | 106.801667 | Underground |
| 9 | IST | Istora Mandiri | -6.219722 | 106.806667 | Underground |
| 10 | BKM | Bendungan Hilir | -6.212778 | 106.816667 | Underground |
| 11 | STB | Setiabudi Astra | -6.208889 | 106.820833 | Underground |
| 12 | DKA | Dukuh Atas BNI | -6.200833 | 106.822778 | Underground Interchange |
| 13 | BHI | Bundaran HI Bank DKI | -6.192778 | 106.823611 | Underground Terminus |

---

## 4. Log Audit & Perubahan Dataset (Audit Trail)

| Tanggal | Versi Dataset | Pelaku Verifikasi | Catatan |
|---|---|---|---|
| 2026-10-23 | v1.0.0-poc | Tim Pengembang NgeRute | Inisialisasi data stasiun MRT Jakarta (13 stasiun) dan transfer hub Dukuh Atas terintegrasi. |
