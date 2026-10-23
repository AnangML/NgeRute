/**
 * NgeRute — Leaflet Map Engine
 * Manages map lifecycle, custom markers, polylines, and spatial interactions.
 */

class MapEngine {
    constructor(containerId = 'mapCanvas') {
        this.containerId = containerId;
        this.map = null;
        this.stationsLayer = null;
        this.routesLayer = null;
        this.hubCoords = [-6.200833, 106.822778]; // Dukuh Atas Hub
        this.defaultZoom = 13;
        this.stationMarkers = new Map();
        this.routePolylines = [];
    }

    init() {
        if (!document.getElementById(this.containerId)) {
            console.error(`Map container #${this.containerId} not found.`);
            return;
        }

        // Initialize Leaflet Map
        this.map = L.map(this.containerId, {
            center: this.hubCoords,
            zoom: this.defaultZoom,
            zoomControl: false, // Reposition or use custom
        });

        // Add Zoom Control on Top-Right (below navbar)
        L.control.zoom({ position: 'topright' }).addTo(this.map);

        // Add Clean OpenStreetMap Tile Layer
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(this.map);

        // Layer Groups
        this.routesLayer = L.layerGroup().addTo(this.map);
        this.stationsLayer = L.layerGroup().addTo(this.map);

        console.log('NgeRute Map Engine initialized successfully.');
    }

    recenterToHub() {
        if (this.map) {
            this.map.flyTo(this.hubCoords, this.defaultZoom, { duration: 1.2 });
        }
    }

    renderStations(stations) {
        if (!this.map || !stations) return;

        this.stationsLayer.clearLayers();
        this.stationMarkers.clear();

        stations.forEach((station) => {
            const isInterchange = station.location_type === 'interchange';
            const color = station.color_hex || '#0284c7';
            const iconSymbol = this.getModeSymbol(station.mode);

            let markerHtml = '';
            if (isInterchange) {
                markerHtml = `
                    <div class="interchange-marker" style="--marker-color: ${color}">
                        <div class="interchange-pulse-ring"></div>
                        <div class="interchange-pin-inner">${iconSymbol}</div>
                    </div>
                `;
            } else {
                markerHtml = `
                    <div class="custom-station-marker" style="--marker-color: ${color}">
                        <div class="marker-pin-inner">${iconSymbol}</div>
                    </div>
                `;
            }

            const customIcon = L.divIcon({
                html: markerHtml,
                className: 'leaflet-div-marker-wrapper',
                iconSize: isInterchange ? [34, 34] : [26, 26],
                iconAnchor: isInterchange ? [17, 17] : [13, 13],
            });

            const marker = L.marker([station.latitude, station.longitude], { icon: customIcon });

            // Popup HTML with Action Buttons
            const popupContent = `
                <div class="popup-station-card">
                    <div class="popup-header">
                        <span class="popup-mode-badge" style="background-color: ${color}">
                            ${station.mode ? station.mode.toUpperCase() : 'TRANSIT'}
                        </span>
                        <span class="popup-station-name">${station.name}</span>
                    </div>
                    <div class="popup-operator-info">${station.operator_name || 'Operator'} (${station.code || '-'})</div>
                    <div class="popup-actions-grid">
                        <button type="button" class="btn-popup-action btn-set-origin" 
                            onclick="window.dispatchEvent(new CustomEvent('station:set-origin', { detail: { id: ${station.id}, name: '${station.name.replace(/'/g, "\\'")}' } }))">
                            🟢 Jadi Asal
                        </button>
                        <button type="button" class="btn-popup-action btn-set-destination" 
                            onclick="window.dispatchEvent(new CustomEvent('station:set-destination', { detail: { id: ${station.id}, name: '${station.name.replace(/'/g, "\\'")}' } }))">
                            🔴 Jadi Tujuan
                        </button>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent, { maxWidth: 260, offset: [0, -6] });
            this.stationsLayer.addLayer(marker);
            this.stationMarkers.set(station.id, marker);
        });
    }

    renderRoutePolyline(route) {
        if (!this.map || !route || !route.segments) return;

        this.clearRoutePolylines();
        const allLatLngs = [];

        route.segments.forEach((seg) => {
            if (!seg.polyline || seg.polyline.length === 0) return;

            const latLngs = seg.polyline.map((coord) => [coord[0], coord[1]]);
            allLatLngs.push(...latLngs);

            const isTransfer = seg.type === 'transfer';
            const color = seg.color_hex || (isTransfer ? '#64748b' : '#0284c7');

            if (isTransfer) {
                // Dashed walking polyline
                const walkLine = L.polyline(latLngs, {
                    color: '#475569',
                    weight: 4.5,
                    dashArray: '6, 8',
                    className: 'transit-polyline-walking',
                });
                this.routesLayer.addLayer(walkLine);
                this.routePolylines.push(walkLine);
            } else {
                // 1. White casing underlayer for high contrast
                const casingLine = L.polyline(latLngs, {
                    color: '#ffffff',
                    weight: 8,
                    opacity: 0.95,
                    className: 'transit-polyline-casing',
                });

                // 2. Core colored transit line
                const coreLine = L.polyline(latLngs, {
                    color: color,
                    weight: 5.5,
                    opacity: 1,
                    className: 'transit-polyline-core',
                });

                this.routesLayer.addLayer(casingLine);
                this.routesLayer.addLayer(coreLine);
                this.routePolylines.push(casingLine, coreLine);
            }
        });

        // Fit map bounds to show complete journey
        if (allLatLngs.length > 0) {
            this.map.fitBounds(allLatLngs, {
                paddingTopLeft: [window.innerWidth > 768 ? 460 : 30, 80],
                paddingBottomRight: [50, 50],
                maxZoom: 16,
            });
        }
    }

    clearRoutePolylines() {
        if (this.routesLayer) {
            this.routesLayer.clearLayers();
        }
        this.routePolylines = [];
    }

    getModeSymbol(mode) {
        switch (mode) {
            case 'subway': return 'M';
            case 'rail': return 'K';
            case 'lrt': return 'L';
            case 'bus': return 'B';
            default: return '●';
        }
    }
}

// Attach to window
window.MapEngine = MapEngine;
