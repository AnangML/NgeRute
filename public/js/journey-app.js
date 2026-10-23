/**
 * NgeRute — Client Journey Application Controller
 * Handles user interactions, search autocomplete, routing API calls, and UI state.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Map
    const mapEngine = new window.MapEngine('mapCanvas');
    mapEngine.init();

    // Load initial stations from script tag
    const stationsDataTag = document.getElementById('initialStationsData');
    let stationsList = [];
    if (stationsDataTag) {
        try {
            stationsList = JSON.parse(stationsDataTag.textContent);
            mapEngine.renderStations(stationsList);
        } catch (e) {
            console.error('Failed to parse initial stations:', e);
        }
    }

    // 2. DOM Elements
    const originInput = document.getElementById('originInput');
    const originId = document.getElementById('originId');
    const originAutocomplete = document.getElementById('originAutocomplete');
    const btnClearOrigin = document.getElementById('btnClearOrigin');

    const destInput = document.getElementById('destinationInput');
    const destId = document.getElementById('destinationId');
    const destAutocomplete = document.getElementById('destAutocomplete');
    const btnClearDest = document.getElementById('btnClearDestination');

    const btnSwap = document.getElementById('btnSwapWaypoints');
    const btnFind = document.getElementById('btnFindRoutes');
    const btnReset = document.getElementById('btnResetView');
    const btnRecenter = document.getElementById('btnRecenterHub');

    const criteriaContainer = document.getElementById('criteriaFilterContainer');
    const filterChips = document.querySelectorAll('.btn-filter-chip');
    const presetShortcuts = document.getElementById('presetShortcuts');

    const loadingState = document.getElementById('loadingState');
    const errorBox = document.getElementById('errorAlertBox');
    const errorTitle = document.getElementById('errorTitle');
    const errorDesc = document.getElementById('errorDesc');

    const routesList = document.getElementById('routesResultsList');
    const routeDetailView = document.getElementById('routeDetailView');
    const btnBackToList = document.getElementById('btnBackToList');
    const detailBadge = document.getElementById('detailBadge');
    const detailSummary = document.getElementById('detailSummaryCard');
    const timelineContainer = document.getElementById('timelineContainer');
    const provenanceTime = document.getElementById('provenanceTime');

    // Help Modal
    const btnHelp = document.getElementById('btnHelp');
    const btnCloseHelp = document.getElementById('btnCloseHelp');
    const helpModal = document.getElementById('helpModal');

    // State
    let currentRoutes = [];
    let activeFilter = 'all';
    let selectedRouteIndex = 0;

    // 3. Event Listeners for Map Custom Actions
    window.addEventListener('station:set-origin', (e) => {
        setOrigin(e.detail.id, e.detail.name);
        if (destId.value && destId.value !== String(e.detail.id)) {
            findRoutes();
        }
    });

    window.addEventListener('station:set-destination', (e) => {
        setDestination(e.detail.id, e.detail.name);
        if (originId.value && originId.value !== String(e.detail.id)) {
            findRoutes();
        }
    });

    // 4. Autocomplete Logic
    setupAutocomplete(originInput, originId, originAutocomplete, btnClearOrigin, (id, name) => setOrigin(id, name));
    setupAutocomplete(destInput, destId, destAutocomplete, btnClearDest, (id, name) => setDestination(id, name));

    function setupAutocomplete(inputEl, idEl, dropdownEl, clearBtn, onSelect) {
        let debounceTimer = null;

        inputEl.addEventListener('input', () => {
            const query = inputEl.value.trim();
            clearBtn.style.display = query ? 'flex' : 'none';

            if (!query) {
                idEl.value = '';
                dropdownEl.style.display = 'none';
                return;
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/v1/stations/search?q=${encodeURIComponent(query)}&limit=6`);
                    const json = await res.json();
                    if (json.success && json.data.length > 0) {
                        renderAutocompleteItems(json.data, dropdownEl, inputEl, idEl, clearBtn, onSelect);
                    } else {
                        dropdownEl.innerHTML = '<div class="autocomplete-item"><span class="item-name">Tidak ada stasiun cocok</span></div>';
                        dropdownEl.style.display = 'block';
                    }
                } catch (err) {
                    console.error('Autocomplete error:', err);
                }
            }, 200);
        });

        // Hide on click outside
        document.addEventListener('click', (e) => {
            if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
                dropdownEl.style.display = 'none';
            }
        });

        clearBtn.addEventListener('click', () => {
            inputEl.value = '';
            idEl.value = '';
            clearBtn.style.display = 'none';
            dropdownEl.style.display = 'none';
            inputEl.focus();
        });
    }

    function renderAutocompleteItems(items, dropdownEl, inputEl, idEl, clearBtn, onSelect) {
        dropdownEl.innerHTML = '';
        items.forEach((item) => {
            const div = document.createElement('div');
            div.className = 'autocomplete-item';
            div.innerHTML = `
                <div class="item-main">
                    <span class="item-mode-badge" style="background-color: ${item.color_hex || '#0284c7'}">
                        ${item.mode ? item.mode.toUpperCase() : 'TRANSIT'}
                    </span>
                    <span class="item-name">${item.name}</span>
                </div>
                <span class="item-distance">${item.operator_name || ''}</span>
            `;
            div.addEventListener('click', () => {
                inputEl.value = item.name;
                idEl.value = item.id;
                clearBtn.style.display = 'flex';
                dropdownEl.style.display = 'none';
                if (onSelect) onSelect(item.id, item.name);
            });
            dropdownEl.appendChild(div);
        });
        dropdownEl.style.display = 'block';
    }

    function setOrigin(id, name) {
        originInput.value = name;
        originId.value = id;
        btnClearOrigin.style.display = 'flex';
        originAutocomplete.style.display = 'none';
    }

    function setDestination(id, name) {
        destInput.value = name;
        destId.value = id;
        btnClearDest.style.display = 'flex';
        destAutocomplete.style.display = 'none';
    }

    // 5. Swap Waypoints
    btnSwap.addEventListener('click', () => {
        const tempName = originInput.value;
        const tempId = originId.value;

        originInput.value = destInput.value;
        originId.value = destId.value;
        btnClearOrigin.style.display = originInput.value ? 'flex' : 'none';

        destInput.value = tempName;
        destId.value = tempId;
        btnClearDest.style.display = destInput.value ? 'flex' : 'none';

        if (originId.value && destId.value) {
            findRoutes();
        }
    });

    // 6. Preset Shortcuts
    document.querySelectorAll('.chip-shortcut').forEach((btn) => {
        btn.addEventListener('click', () => {
            setOrigin(btn.dataset.origin, btn.dataset.originName);
            setDestination(btn.dataset.dest, btn.dataset.destName);
            findRoutes();
        });
    });

    // 7. Find Routes
    btnFind.addEventListener('click', findRoutes);

    async function findRoutes() {
        const oId = originId.value;
        const dId = destId.value;

        if (!oId || !dId) {
            showError('Pilih Titik Asal & Tujuan', 'Silakan pilih stasiun asal dan tujuan terlebih dahulu.');
            return;
        }

        if (oId === dId) {
            showError('Titik Asal & Tujuan Sama', 'Stasiun asal dan tujuan tidak boleh sama.');
            return;
        }

        // UI Loading
        hideError();
        routesList.innerHTML = '';
        routesList.style.display = 'none';
        routeDetailView.style.display = 'none';
        criteriaContainer.style.display = 'none';
        loadingState.style.display = 'flex';
        btnReset.style.display = 'flex';

        try {
            const url = `/api/v1/journey/plan?origin_id=${encodeURIComponent(oId)}&destination_id=${encodeURIComponent(dId)}`;
            const res = await fetch(url);
            const json = await res.json();

            loadingState.style.display = 'none';

            if (!json.success) {
                showError('Rute Tidak Ditemukan', json.error?.message || 'Tidak ada lintasan yang menghubungkan kedua stasiun.');
                return;
            }

            currentRoutes = json.data.routes || [];
            if (currentRoutes.length === 0) {
                showError('Rute Tidak Ditemukan', 'Tidak ada alternatif rute yang tersedia untuk pasangan ini.');
                return;
            }

            // Update Provenance Box
            if (json.data.provenance) {
                provenanceTime.textContent = `Terakhir diverifikasi: ${json.data.provenance.last_verified_at} • ${json.data.provenance.verified_by || 'Data Resmi Operator'}`;
            }

            // Show Criteria Filter & Render Cards
            criteriaContainer.style.display = 'flex';
            routesList.style.display = 'flex';
            renderRouteCards(currentRoutes);

            // Auto-select fastest route (first)
            selectRoute(0);
        } catch (err) {
            loadingState.style.display = 'none';
            showError('Terjadi Kesalahan', 'Gagal memuat rute dari server. Periksa koneksi lokal.');
            console.error('Plan route error:', err);
        }
    }

    // 8. Render Route Cards
    function renderRouteCards(routes) {
        routesList.innerHTML = '';

        routes.forEach((route, idx) => {
            // Apply filter
            if (activeFilter !== 'all') {
                const matchesFilter = route.primary_criteria === activeFilter ||
                    (activeFilter === 'fastest' && route.badges.includes('TERCEPAT')) ||
                    (activeFilter === 'cheapest' && route.badges.includes('TERMURAH')) ||
                    (activeFilter === 'least_walking' && route.badges.includes('MINIM JALAN KAKI')) ||
                    (activeFilter === 'fewest_transfers' && route.badges.includes('LANGSUNG'));
                if (!matchesFilter) return;
            }

            const card = document.createElement('div');
            card.className = `route-card ${idx === selectedRouteIndex ? 'selected' : ''}`;
            card.dataset.index = idx;

            // Badges HTML — Clean Minimalist 60-30-10
            const badgesHtml = route.badges.map((b) => {
                const isFastest = b.toUpperCase() === 'TERCEPAT';
                return `<span class="badge-tag ${isFastest ? 'badge-tercepat' : ''}">${b}</span>`;
            }).join('');

            // Mode Chain Pills HTML
            const modeChainHtml = route.segments.map((seg, sIdx) => {
                const isTransfer = seg.type === 'transfer';
                const color = seg.color_hex || (isTransfer ? '#64748b' : '#0284c7');
                const label = isTransfer ? `🚶 ${seg.duration_minutes}m` : (seg.mode ? seg.mode.toUpperCase() : 'TRANSIT');
                const arrow = sIdx < route.segments.length - 1 ? '<span class="mode-arrow">➔</span>' : '';
                return `<span class="mode-pill" style="background-color: ${color}">${label}</span>${arrow}`;
            }).join('');

            // Directions Summary HTML (e.g. MRT: Arah Bundaran HI • KRL: Arah Manggarai)
            const transitWithHeadsign = route.segments.filter((s) => s.type === 'transit' && s.headsign);
            const directionsHtml = transitWithHeadsign.length > 0
                ? `<div class="route-directions-summary">
                    ${transitWithHeadsign.map((s) => `<span class="dir-item"><strong>${s.operator_code || s.operator_name || 'Transit'}:</strong> Arah ${s.headsign}</span>`).join('<span class="dir-divider">•</span>')}
                   </div>`
                : '';

            card.innerHTML = `
                <div class="route-card-header">
                    <div class="route-metrics-main">
                        <span class="route-duration">${route.total_duration_minutes} mnt</span>
                        <span class="route-fare">${route.formatted_fare}</span>
                    </div>
                    <div class="route-badges">${badgesHtml}</div>
                </div>

                <div class="route-mode-chain">${modeChainHtml}</div>
                ${directionsHtml}

                <div class="route-card-footer">
                    <div class="footer-transfer-info">
                        <span>${route.transfer_count} Transit</span>
                        <span>•</span>
                        <span>🚶 ${route.walking_distance_meters}m jalan (${route.walking_duration_minutes} mnt)</span>
                    </div>
                    <span class="footer-action-link">Lihat Detail ➔</span>
                </div>
            `;

            card.addEventListener('click', () => {
                selectRoute(idx);
                showRouteDetail(route);
            });

            routesList.appendChild(card);
        });
    }

    // 9. Select & Display Route
    function selectRoute(index) {
        selectedRouteIndex = index;
        const route = currentRoutes[index];
        if (!route) return;

        // Highlight card
        document.querySelectorAll('.route-card').forEach((c) => {
            c.classList.toggle('selected', parseInt(c.dataset.index) === index);
        });

        // Render on Leaflet Map
        mapEngine.renderRoutePolyline(route);
    }

    function showRouteDetail(route) {
        routesList.style.display = 'none';
        criteriaContainer.style.display = 'none';
        routeDetailView.style.display = 'flex';

        // Set Top Badge
        detailBadge.textContent = route.badges[0] || 'REKOMENDASI';

        // Summary Card
        detailSummary.innerHTML = `
            <div class="summary-col">
                <span class="summary-val">${route.total_duration_minutes} mnt</span>
                <span class="summary-lbl">Total Waktu</span>
            </div>
            <div class="summary-col">
                <span class="summary-val">${route.formatted_fare}</span>
                <span class="summary-lbl">Total Biaya</span>
            </div>
            <div class="summary-col">
                <span class="summary-val">${route.transfer_count}</span>
                <span class="summary-lbl">Transit</span>
            </div>
            <div class="summary-col">
                <span class="summary-val">${route.walking_distance_meters}m</span>
                <span class="summary-lbl">Jalan Kaki</span>
            </div>
        `;

        // Render Steps Timeline
        timelineContainer.innerHTML = '';
        route.segments.forEach((seg, sIdx) => {
            const isTransfer = seg.type === 'transfer';
            const color = seg.color_hex || (isTransfer ? '#64748b' : '#0284c7');
            const stepEl = document.createElement('div');
            stepEl.className = `timeline-step ${isTransfer ? 'transfer' : 'transit'}`;

            let contentHtml = '';
            if (isTransfer) {
                contentHtml = `
                    <div class="step-axis">
                        <div class="step-node" style="border-color: #64748b; background: #f8fafc;"></div>
                        <div class="step-line"></div>
                    </div>
                    <div class="step-content">
                        <div class="step-title-row">
                            <span class="step-title">🚶 Jalan Kaki (${seg.distance_meters} m)</span>
                            <span class="step-time-badge">${seg.duration_minutes} mnt</span>
                        </div>
                        <span class="step-desc">${seg.transfer_notes || 'Jalur transfer pejalan kaki'}</span>
                        <span class="step-desc" style="color: var(--text-muted); font-size: 11.5px;">
                            ${seg.from_stop.name} ➔ ${seg.to_stop.name}
                        </span>
                    </div>
                `;
            } else {
                let intermediateHtml = '';
                if (seg.intermediate_stops && seg.intermediate_stops.length > 0) {
                    const stopsListHtml = seg.intermediate_stops.map((s) => `<span>• ${s.name}</span>`).join('');
                    intermediateHtml = `
                        <button type="button" class="btn-toggle-intermediate" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'flex' : 'none'">
                            ▼ Lewati ${seg.stops_count} stasiun (${seg.intermediate_stops.length} antara)
                        </button>
                        <div class="intermediate-list" style="display: none;">${stopsListHtml}</div>
                    `;
                }

                const headsignHtml = seg.headsign
                    ? `<div class="step-headsign-badge"><span>Arah:</span> <strong>${seg.headsign}</strong></div>`
                    : '';

                contentHtml = `
                    <div class="step-axis">
                        <div class="step-node" style="border-color: ${color}"></div>
                        <div class="step-line" style="background-color: ${color}"></div>
                    </div>
                    <div class="step-content">
                        <div class="step-title-row">
                            <span class="step-title" style="color: ${color}">
                                ${seg.operator_name || 'Transit'} (${seg.route_name || seg.route_code || ''})
                            </span>
                            <span class="step-time-badge">${seg.duration_minutes} mnt</span>
                        </div>
                        ${headsignHtml}
                        <span class="step-desc"><strong>Naik dari:</strong> ${seg.from_stop.name}</span>
                        ${intermediateHtml}
                        <span class="step-desc"><strong>Turun di:</strong> ${seg.to_stop.name}</span>
                        <span class="step-fare-tag">${seg.fare_idr > 0 ? 'Tarif: Rp' + Number(seg.fare_idr).toLocaleString('id-ID') : 'Tarif Termasuk'}</span>
                    </div>
                `;
            }

            stepEl.innerHTML = contentHtml;
            timelineContainer.appendChild(stepEl);
        });

        // Add Arrival Final Node
        const lastSeg = route.segments[route.segments.length - 1];
        if (lastSeg) {
            const finalNode = document.createElement('div');
            finalNode.className = 'timeline-step';
            finalNode.innerHTML = `
                <div class="step-axis">
                    <div class="step-node" style="border-color: #ef4444; background: #ef4444;"></div>
                </div>
                <div class="step-content">
                    <span class="step-title" style="color: #ef4444;">Tiba di ${lastSeg.to_stop.name}</span>
                </div>
            `;
            timelineContainer.appendChild(finalNode);
        }
    }

    btnBackToList.addEventListener('click', () => {
        routeDetailView.style.display = 'none';
        criteriaContainer.style.display = 'flex';
        routesList.style.display = 'flex';
    });

    // 10. Filter Chips Click
    filterChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            filterChips.forEach((c) => c.classList.remove('active'));
            chip.classList.add('active');
            activeFilter = chip.dataset.criteria;
            renderRouteCards(currentRoutes);
        });
    });

    // 11. Reset View
    btnReset.addEventListener('click', () => {
        mapEngine.clearRoutePolylines();
        mapEngine.recenterToHub();
        routesList.innerHTML = '';
        routesList.style.display = 'none';
        routeDetailView.style.display = 'none';
        criteriaContainer.style.display = 'none';
        btnReset.style.display = 'none';
        hideError();
    });

    btnRecenter.addEventListener('click', () => {
        mapEngine.recenterToHub();
    });

    // 12. Help Modal Controls
    btnHelp.addEventListener('click', () => { helpModal.style.display = 'flex'; });
    btnCloseHelp.addEventListener('click', () => { helpModal.style.display = 'none'; });
    helpModal.addEventListener('click', (e) => {
        if (e.target === helpModal) helpModal.style.display = 'none';
    });

    // Helpers
    function showError(title, message) {
        errorTitle.textContent = title;
        errorDesc.textContent = message;
        errorBox.style.display = 'flex';
    }

    function hideError() {
        errorBox.style.display = 'none';
    }
});
