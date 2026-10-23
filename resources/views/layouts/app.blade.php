<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NgeRute — Pembanding Rute KRL, MRT, LRT, dan Bus Jabodetabek')</title>
    <meta name="description" content="Bandingkan rute KRL, MRT, LRT, bus, dan jalan kaki dalam satu pencarian transparan di Jabodetabek dengan informasi waktu, tarif, dan transfer nyata.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚆</text></svg>">

    <!-- Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- App Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/map.css') }}">

    @stack('styles')
</head>
<body class="app-body">
    <!-- Top Navigation Bar -->
    <header class="top-navbar" id="mainNavbar">
        <div class="nav-brand">
            <div class="brand-logo">
                <span class="logo-icon">🚆</span>
                <div class="brand-text">
                    <span class="brand-name">NgeRute</span>
                    <span class="brand-badge">Jabodetabek</span>
                </div>
            </div>
        </div>

        <div class="nav-center-info">
            <div class="status-indicator">
                <span class="status-dot"></span>
                <span class="status-text">Hub Dukuh Atas Aktif (MRT • KRL • LRT)</span>
            </div>
        </div>

        <div class="nav-actions">
            <button type="button" class="btn-nav-outline" id="btnHelp" title="Bantuan & Petunjuk">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span class="btn-label">Panduan</span>
            </button>
        </div>
    </header>

    <!-- Main Viewport Canvas -->
    <main class="app-viewport">
        @yield('content')
    </main>

    <!-- Modal Help / Data Info -->
    <div class="modal-backdrop" id="helpModal" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Tentang NgeRute Jabodetabek</h3>
                <button type="button" class="btn-close-modal" id="btnCloseHelp">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>NgeRute</strong> adalah web app agregator perjalanan antarmoda di wilayah Jabodetabek yang membandingkan alternatif rute berdasarkan <strong>estimasi waktu</strong>, <strong>tarif resmi</strong>, <strong>jarak jalan kaki fisik</strong>, dan <strong>jumlah transfer</strong>.</p>
                
                <h4 class="modal-section-title">Fitur Unggulan</h4>
                <ul class="modal-features-list">
                    <li>⚡ <strong>4 Perspektif Rute:</strong> Tercepat, Termurah, Minim Jalan Kaki, dan Minim Transit.</li>
                    <li>🚶 <strong>Transfer Nyata:</strong> Memperhitungkan jembatan penyeberangan multiguna (JPM Dukuh Atas) dan Terowongan Kendal.</li>
                    <li>🗺️ <strong>Interaktif Peta:</strong> Klik stasiun di peta untuk langsung menentukan titik asal atau tujuan.</li>
                </ul>

                <div class="provenance-banner">
                    <span class="provenance-title">Status Integritas Data</span>
                    <p class="provenance-desc">Dataset MRT Jakarta, KAI Commuter, dan LRT Jabodebek diverifikasi per 23 Oktober 2026 berdasarkan publikasi resmi masing-masing operator.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- App Scripts -->
    <script src="{{ asset('js/map-engine.js') }}"></script>
    <script src="{{ asset('js/journey-app.js') }}"></script>

    @stack('scripts')
</body>
</html>
