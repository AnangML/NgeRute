@extends('layouts.app')

@section('title', 'NgeRute — Navigasi & Pembanding Rute Multimoda Jabodetabek')

@section('content')
<div class="app-layout">
    <!-- Fullscreen Interactive Leaflet Map -->
    <div id="mapCanvas" class="map-container"></div>

    <!-- Floating Map Controls -->
    <div class="map-floating-controls">
        <button type="button" class="btn-map-control" id="btnRecenterHub" title="Pusatkan ke Hub Dukuh Atas">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <span class="tooltip">Hub Dukuh Atas</span>
        </button>
        <button type="button" class="btn-map-control" id="btnResetView" title="Reset Rute & Peta" style="display: none;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                <path d="M3 3v5h5"></path>
            </svg>
            <span class="tooltip">Reset Tampilan</span>
        </button>
    </div>

    <!-- Map Floating Transit Legend -->
    <div class="map-legend-card" id="mapLegend">
        <span class="legend-title">Moda Terintegrasi</span>
        <div class="legend-items">
            <span class="legend-badge legend-mrt">● MRT Jakarta</span>
            <span class="legend-badge legend-krl">● KRL Commuter</span>
            <span class="legend-badge legend-lrt">● LRT Jabodebek</span>
            <span class="legend-badge legend-walk">┈┈ Jalan Kaki (JPO)</span>
        </div>
    </div>

    <!-- Floating Drawer / Bottom Sheet Panel -->
    <aside class="journey-panel" id="journeyPanel">
        <!-- Mobile Drag Handle -->
        <div class="panel-drag-handle" id="panelDragHandle">
            <div class="drag-bar"></div>
        </div>

        <div class="panel-content-scroll">
            <!-- Search & Waypoints Card -->
            <div class="search-card">
                <div class="waypoint-inputs-wrapper">
                    <!-- Origin Waypoint -->
                    <div class="waypoint-field" id="originField">
                        <div class="waypoint-marker origin-marker" title="Titik Asal"></div>
                        <div class="waypoint-input-box">
                            <label for="originInput" class="waypoint-label">Titik Asal</label>
                            <input 
                                type="text" 
                                id="originInput" 
                                class="waypoint-input" 
                                placeholder="Ketik nama stasiun atau klik peta..." 
                                autocomplete="off"
                            >
                            <input type="hidden" id="originId" value="">
                        </div>
                        <button type="button" class="btn-clear-input" id="btnClearOrigin" title="Hapus Asal" style="display: none;">&times;</button>
                        <!-- Autocomplete Dropdown -->
                        <div class="autocomplete-dropdown" id="originAutocomplete" style="display: none;"></div>
                    </div>

                    <!-- Swap Button -->
                    <div class="swap-button-row">
                        <div class="swap-line"></div>
                        <button type="button" class="btn-swap-waypoints" id="btnSwapWaypoints" title="Tukar Asal dan Tujuan">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M7 16V4M7 4L3 8M7 4L11 8M17 8v12M17 20l4-4M17 20l-4-4"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Destination Waypoint -->
                    <div class="waypoint-field" id="destinationField">
                        <div class="waypoint-marker destination-marker" title="Titik Tujuan"></div>
                        <div class="waypoint-input-box">
                            <label for="destinationInput" class="waypoint-label">Titik Tujuan</label>
                            <input 
                                type="text" 
                                id="destinationInput" 
                                class="waypoint-input" 
                                placeholder="Ketik nama stasiun atau klik peta..." 
                                autocomplete="off"
                            >
                            <input type="hidden" id="destinationId" value="">
                        </div>
                        <button type="button" class="btn-clear-input" id="btnClearDestination" title="Hapus Tujuan" style="display: none;">&times;</button>
                        <!-- Autocomplete Dropdown -->
                        <div class="autocomplete-dropdown" id="destAutocomplete" style="display: none;"></div>
                    </div>
                </div>

                <!-- Submit Plan Button -->
                <button type="button" class="btn-find-routes" id="btnFindRoutes">
                    <span class="btn-icon">⚡</span>
                    <span class="btn-text">Bandingkan Rute Sekarang</span>
                </button>
            </div>

            <!-- Preset Quick Shortcuts (Pilihan Populer) -->
            <div class="preset-shortcuts" id="presetShortcuts">
                <span class="shortcuts-title">Rute Rekomendasi:</span>
                <div class="shortcuts-chips">
                    <button type="button" class="chip-shortcut" data-origin="1" data-origin-name="Lebak Bulus Grab" data-dest="12" data-dest-name="Dukuh Atas BNI">
                        Lebak Bulus ➔ Dukuh Atas (MRT Langsung)
                    </button>
                    <button type="button" class="chip-shortcut" data-origin="1" data-origin-name="Lebak Bulus Grab" data-dest="16" data-dest-name="Manggarai">
                        Lebak Bulus ➔ Manggarai (MRT + KRL Multimoda)
                    </button>
                    <button type="button" class="chip-shortcut" data-origin="6" data-origin-name="Blok M BCA" data-dest="23" data-dest-name="Rasuna Said LRT">
                        Blok M ➔ Rasuna Said (MRT + LRT Multimoda)
                    </button>
                </div>
            </div>

            <!-- Criteria Filter Chips (Appears after route planned) -->
            <div class="criteria-filter-container" id="criteriaFilterContainer" style="display: none;">
                <span class="filter-heading">Prioritaskan:</span>
                <div class="filter-chips">
                    <button type="button" class="btn-filter-chip active" data-criteria="all">Semua</button>
                    <button type="button" class="btn-filter-chip" data-criteria="fastest">Tercepat</button>
                    <button type="button" class="btn-filter-chip" data-criteria="cheapest">Termurah</button>
                    <button type="button" class="btn-filter-chip" data-criteria="least_walking">Minim Jalan</button>
                    <button type="button" class="btn-filter-chip" data-criteria="fewest_transfers">Langsung</button>
                </div>
            </div>

            <!-- Loading Shimmer State -->
            <div class="loading-state" id="loadingState" style="display: none;">
                <div class="shimmer-card"></div>
                <div class="shimmer-card"></div>
            </div>

            <!-- Error Notification Alert -->
            <div class="error-alert-box" id="errorAlertBox" style="display: none;">
                <span class="error-icon">⚠️</span>
                <div class="error-content">
                    <span class="error-title" id="errorTitle">Rute Tidak Ditemukan</span>
                    <span class="error-desc" id="errorDesc">Coba pilih stasiun lain yang tercakup dalam dataset.</span>
                </div>
            </div>

            <!-- Routes Results List Container -->
            <div class="routes-results-list" id="routesResultsList"></div>

            <!-- Step-by-Step Route Timeline Drawer (Detail View) -->
            <div class="route-detail-view" id="routeDetailView" style="display: none;">
                <div class="detail-top-nav">
                    <button type="button" class="btn-back-to-list" id="btnBackToList">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M19 12H5M12 19l-7-7 7-7"></path>
                        </svg>
                        <span>Semua Rute</span>
                    </button>
                    <span class="detail-badge" id="detailBadge">TERCEPAT</span>
                </div>

                <div class="detail-summary-card" id="detailSummaryCard">
                    <!-- Populated by JS -->
                </div>

                <div class="timeline-container" id="timelineContainer">
                    <!-- Populated by JS -->
                </div>

                <!-- Provenance Verification Note -->
                <div class="detail-provenance-box">
                    <div class="provenance-indicator">
                        <span class="provenance-dot"></span>
                        <span class="provenance-label">Data Terverifikasi Resmi</span>
                    </div>
                    <p class="provenance-time" id="provenanceTime">
                        Terakhir diverifikasi: 2026-10-23 • PT MRT Jakarta, KAI Commuter, & LRT Jabodebek
                    </p>
                </div>
            </div>
        </div>
    </aside>
</div>

<!-- Initial Stations JSON for Map Bootstrap -->
<script id="initialStationsData" type="application/json">
    {!! json_encode($stations->map(fn($s) => $s->toArray())->values()) !!}
</script>
@endsection
