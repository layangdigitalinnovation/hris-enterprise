{{-- Real-Time GPS Attendance & Visit Tracking Map Partial --}}
<script>
    window.initialGpsMapData = @js($gpsMapData);

    window.dashboardGpsTracker = function() {
        return {
            gpsData: window.initialGpsMapData || {},
            selectedDate: (window.initialGpsMapData && window.initialGpsMapData.date) ? window.initialGpsMapData.date : '',
            filterCategory: 'all',
            searchQuery: '',
            rosterTab: 'active',
            isRefreshing: false,
            autoRefresh: false,
            refreshInterval: null,
            map: null,
            branchLayer: null,
            visitLayer: null,
            gpsLayer: null,
            routeLayer: null,
            leafletMarkers: {},
            selectedMarkerId: null,

            init() {
                const tryInit = () => {
                    if (window.L && this.$refs.mapContainer) {
                        this.initLeafletMap();
                    } else {
                        setTimeout(tryInit, 80);
                    }
                };
                this.$nextTick(tryInit);
            },

            initLeafletMap() {
                if (!window.L) return;

                if (this.map) {
                    this.map.remove();
                    this.map = null;
                }

                // Initialize map with custom options
                this.map = L.map(this.$refs.mapContainer, {
                    zoomControl: false,
                    attributionControl: false
                });

                // Standard OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    subdomains: ['a', 'b', 'c']
                }).addTo(this.map);

                // Zoom control on top-right
                L.control.zoom({ position: 'topright' }).addTo(this.map);

                // Create layer groups
                this.branchLayer = L.layerGroup().addTo(this.map);
                this.routeLayer = L.layerGroup().addTo(this.map);
                this.visitLayer = L.layerGroup().addTo(this.map);
                this.gpsLayer = L.layerGroup().addTo(this.map);

                this.renderAll();
                this.fitAllBounds();

                // Smooth resize after DOM stabilization
                setTimeout(() => {
                    if (this.map) this.map.invalidateSize();
                }, 300);
            },

            renderAll() {
                if (!this.map) return;

                this.branchLayer.clearLayers();
                this.routeLayer.clearLayers();
                this.visitLayer.clearLayers();
                this.gpsLayer.clearLayers();
                this.leafletMarkers = {};

                // 1. Render Branch Offices & Geofence Perimeter Circles
                if (this.filterCategory === 'all' || this.filterCategory === 'branch') {
                    (this.gpsData.branches || []).forEach(b => {
                        const branchHtml = `
                            <div style="position: relative; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                <div style="width: 32px; height: 32px; border-radius: 10px; background: #0f172a; border: 2px solid #ffffff; box-shadow: 0 4px 10px rgba(15,23,42,0.35); display: flex; align-items: center; justify-content: center; color: #ffffff;">
                                    <span style="font-size: 14px;">🏢</span>
                                </div>
                                <span style="position: absolute; top: -4px; right: -4px; font-size: 11px;">${b.country_flag || ''}</span>
                            </div>
                        `;

                        const branchIcon = L.divIcon({
                            className: 'custom-branch-marker',
                            html: branchHtml,
                            iconSize: [36, 36],
                            iconAnchor: [18, 18],
                            popupAnchor: [0, -18]
                        });

                        const popupHtml = `
                            <div style="padding: 14px; font-family: inherit; font-size: 12px; max-width: 280px; line-height: 1.4;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 8px;">
                                    <span style="font-weight: bold; color: #0f172a; font-size: 13px;">${b.country_flag || '🏢'} ${b.name}</span>
                                    <span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-family: monospace; font-weight: bold; background: #f1f5f9; color: #334155;">${b.code}</span>
                                </div>
                                <div style="color: #475569; font-size: 11px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <span style="color: #94a3b8;">{{ __('Zona Waktu:') }}</span>
                                        <span style="font-family: monospace; font-weight: 600; color: #1e293b;">${b.timezone}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <span style="color: #94a3b8;">{{ __('Radius Geofence:') }}</span>
                                        <span style="font-family: monospace; font-weight: bold; color: #2563eb;">${b.radius_meters} {{ __('meter') }}</span>
                                    </div>
                                    ${b.address ? `<p style="color: #64748b; font-size: 10px; margin-top: 6px; border-top: 1px solid #f1f5f9; padding-top: 6px;">📍 ${b.address}</p>` : ''}
                                </div>
                            </div>
                        `;

                        const m = L.marker([b.latitude, b.longitude], { icon: branchIcon }).bindPopup(popupHtml);
                        m.addTo(this.branchLayer);

                        // Geofence Circle
                        L.circle([b.latitude, b.longitude], {
                            radius: b.radius_meters || 100,
                            color: '#2563eb',
                            weight: 1.5,
                            opacity: 0.6,
                            fillColor: '#3b82f6',
                            fillOpacity: 0.12,
                            dashArray: '4, 4'
                        }).addTo(this.branchLayer);
                    });
                }

                // 2. Render Travel Routes (Visit Check-In to Check-Out)
                if (this.filterCategory === 'all' || this.filterCategory === 'visit') {
                    (this.gpsData.routes || []).forEach(r => {
                        const line = L.polyline([r.from, r.to], {
                            color: '#10b981',
                            weight: 3,
                            opacity: 0.85,
                            dashArray: '6, 8',
                            lineCap: 'round'
                        });
                        line.bindTooltip(`${r.employee_name}: Rute Kunjungan ${r.client_name}`, { sticky: true });
                        line.addTo(this.routeLayer);
                    });
                }

                // 3. Render Employee Attendance & Visit Markers
                const visibleMarkers = this.getFilteredMarkers();
                visibleMarkers.forEach(item => {
                    const isVisit = item.category === 'visit';
                    const isOngoing = isVisit && item.status === 'ongoing';
                    const isCheckOut = isVisit && item.sub_type === 'check_out';

                    let pinBg = '#2563eb';
                    let pinIcon = '📍';

                    if (isVisit) {
                        if (isOngoing) {
                            pinBg = '#f59e0b';
                            pinIcon = '🚗';
                        } else if (isCheckOut) {
                            pinBg = '#0d9488';
                            pinIcon = '🏁';
                        } else {
                            pinBg = '#10b981';
                            pinIcon = '🚗';
                        }
                    }

                    const markerHtml = `
                        <div style="position: relative; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            ${isOngoing ? `<span style="position: absolute; inset: -4px; border-radius: 9999px; background: rgba(245, 158, 11, 0.4); animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></span>` : ''}
                            <div style="position: relative; width: 36px; height: 36px; border-radius: 12px; background: ${pinBg}; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: center; color: #ffffff;">
                                <span style="font-size: 15px;">${pinIcon}</span>
                            </div>
                            <span style="position: absolute; bottom: -3px; right: -3px; width: 16px; height: 16px; border-radius: 9999px; background: #ffffff; border: 1.5px solid #e2e8f0; font-size: 9px; font-weight: bold; color: #0f172a; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                ${(item.employee && item.employee.initials) ? item.employee.initials.substring(0, 2) : '•'}
                            </span>
                        </div>
                    `;

                    const customIcon = L.divIcon({
                        className: 'custom-emp-marker',
                        html: markerHtml,
                        iconSize: [40, 40],
                        iconAnchor: [20, 20],
                        popupAnchor: [0, -20]
                    });

                    const gmapsUrl = `https://www.google.com/maps?q=${item.latitude},${item.longitude}`;
                    const categoryLabel = isVisit
                        ? (isOngoing ? '{{ __('🚗 Kunjungan Sedang Berlangsung') }}' : (isCheckOut ? '{{ __('🏁 Kunjungan Selesai (Check-Out)') }}' : '{{ __('🚗 Kunjungan Klien (Check-In)') }}'))
                        : `📍 {{ __('Presensi Mandiri GPS') }} (${item.sub_type === 'check_in' ? '{{ __('Masuk') }}' : '{{ __('Keluar') }}'})`;

                    const headerColor = isVisit
                        ? (isOngoing ? '#f59e0b' : (isCheckOut ? '#0d9488' : '#059669'))
                        : '#2563eb';

                    const popupHtml = `
                        <div style="width: 280px; font-family: inherit; font-size: 12px; background: #ffffff; border-radius: 14px; overflow: hidden; line-height: 1.4;">
                            <!-- Header -->
                            <div style="background: ${headerColor}; color: #ffffff; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-weight: bold; font-size: 11px;">${categoryLabel}</span>
                                <span style="font-family: monospace; font-size: 10px; background: rgba(0,0,0,0.2); padding: 2px 6px; border-radius: 9999px;">${item.time}</span>
                            </div>

                            <!-- Body -->
                            <div style="padding: 14px;">
                                <!-- Employee Info -->
                                <div style="display: flex; align-items: center; gap: 10px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
                                    <div style="width: 34px; height: 34px; border-radius: 9999px; background: #eff6ff; color: #1d4ed8; font-weight: bold; font-size: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #bfdbfe;">
                                        ${item.employee.initials || 'EM'}
                                    </div>
                                    <div style="min-width: 0; flex: 1;">
                                        <div style="font-weight: bold; color: #0f172a; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${item.employee.name}</div>
                                        <div style="font-size: 10px; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${item.employee.code} · ${item.employee.department}</div>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div style="margin-top: 10px; font-size: 11px; color: #334155;">
                                    ${isVisit ? `
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                            <span style="color: #94a3b8;">{{ __('Klien:') }}</span>
                                            <span style="font-weight: bold; color: #0f172a;">${item.client_name || '-'}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                            <span style="color: #94a3b8;">{{ __('Keperluan:') }}</span>
                                            <span style="color: #475569; max-width: 170px; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${item.purpose || '-'}</span>
                                        </div>
                                        ${item.outcome ? `
                                            <div style="padding: 6px 8px; border-radius: 6px; background: #ecfdf5; color: #065f46; font-size: 10px; border: 1px solid #a7f3d0; margin-top: 6px;">
                                                <strong>{{ __('Hasil:') }}</strong> ${item.outcome}
                                            </div>
                                        ` : ''}
                                    ` : `
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                            <span style="color: #94a3b8;">{{ __('Lokasi Kantor:') }}</span>
                                            <span style="font-weight: 600; color: #0f172a;">${item.branch_name || '{{ __('Kantor Cabang') }}'}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                            <span style="color: #94a3b8;">{{ __('Jarak ke Kantor:') }}</span>
                                            <span style="font-family: monospace; font-weight: bold; color: #2563eb;">${item.distance_meters} m</span>
                                        </div>
                                    `}

                                    <div style="display: flex; justify-content: space-between; margin-top: 6px; padding-top: 6px; border-top: 1px solid #f1f5f9;">
                                        <span style="color: #94a3b8;">{{ __('Koordinat:') }}</span>
                                        <span style="font-family: monospace; font-size: 10px; color: #475569;">${item.latitude.toFixed(5)}, ${item.longitude.toFixed(5)}</span>
                                    </div>

                                    ${item.address ? `<p style="font-size: 10px; color: #64748b; font-style: italic; margin-top: 4px;">📍 ${item.address}</p>` : ''}
                                </div>

                                <!-- Action -->
                                <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 10px; color: #94a3b8; font-family: monospace;">${item.timezone}</span>
                                    <a href="${gmapsUrl}" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; background: #eff6ff; color: #1d4ed8; text-decoration: none; font-size: 10px; font-weight: bold;">
                                        <span>Google Maps</span>
                                        <span>&nearr;</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;

                    const marker = L.marker([item.latitude, item.longitude], { icon: customIcon }).bindPopup(popupHtml);

                    if (isVisit) {
                        marker.addTo(this.visitLayer);
                    } else {
                        marker.addTo(this.gpsLayer);
                    }

                    this.leafletMarkers[item.id] = marker;
                });
            },

            getFilteredMarkers() {
                let list = this.gpsData.markers || [];

                if (this.filterCategory === 'visit') {
                    list = list.filter(m => m.category === 'visit');
                } else if (this.filterCategory === 'gps') {
                    list = list.filter(m => m.category === 'gps');
                } else if (this.filterCategory === 'branch') {
                    return [];
                }

                if (this.searchQuery && this.searchQuery.trim()) {
                    const q = this.searchQuery.toLowerCase().trim();
                    list = list.filter(m =>
                        (m.employee && m.employee.name && m.employee.name.toLowerCase().includes(q)) ||
                        (m.employee && m.employee.code && m.employee.code.toLowerCase().includes(q)) ||
                        (m.client_name && m.client_name.toLowerCase().includes(q)) ||
                        (m.title && m.title.toLowerCase().includes(q))
                    );
                }

                return list;
            },

            getFilteredRoster() {
                let list = this.gpsData.roster || [];

                if (this.rosterTab === 'active') {
                    list = list.filter(e => e.has_activity);
                }

                if (this.searchQuery && this.searchQuery.trim()) {
                    const q = this.searchQuery.toLowerCase().trim();
                    list = list.filter(e =>
                        e.name.toLowerCase().includes(q) ||
                        e.code.toLowerCase().includes(q) ||
                        (e.department && e.department.toLowerCase().includes(q))
                    );
                }

                return list;
            },

            fitAllBounds() {
                if (!this.map) return;
                const bounds = L.latLngBounds();
                let count = 0;

                (this.gpsData.markers || []).forEach(m => {
                    bounds.extend([m.latitude, m.longitude]);
                    count++;
                });

                (this.gpsData.branches || []).forEach(b => {
                    bounds.extend([b.latitude, b.longitude]);
                    count++;
                });

                if (count > 0) {
                    this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
                } else {
                    this.map.setView([-6.2088, 106.8456], 10);
                }
            },

            focusRegion(region) {
                if (!this.map) return;
                if (region === 'all') {
                    this.fitAllBounds();
                } else if (region === 'indonesia') {
                    this.map.setView([-6.7, 107.8], 8, { animate: true });
                } else if (region === 'riyadh') {
                    this.map.setView([24.7136, 46.6753], 12, { animate: true });
                } else if (region === 'jakarta') {
                    this.map.setView([-6.2238, 106.8242], 13, { animate: true });
                }
            },

            focusEmployee(emp) {
                if (!this.map) return;
                if (emp.has_activity && emp.latitude && emp.longitude) {
                    this.map.flyTo([emp.latitude, emp.longitude], 15, { duration: 1.2 });
                    if (emp.latest_marker_id && this.leafletMarkers[emp.latest_marker_id]) {
                        setTimeout(() => {
                            this.leafletMarkers[emp.latest_marker_id].openPopup();
                        }, 1200);
                    }
                } else {
                    const msgTemplate = '{{ __('Karyawan :name belum memiliki rekaman presensi GPS / visitasi pada tanggal :date.') }}';
                    alert(msgTemplate.replace(':name', emp.name).replace(':date', this.gpsData.date));
                }
            },

            async fetchData(date) {
                this.isRefreshing = true;
                try {
                    const res = await fetch(`{{ route('dashboard.gps_map_data') }}?date=${date}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error('Network error');
                    const json = await res.json();
                    this.gpsData = json;
                    this.selectedDate = json.date;
                    this.renderAll();
                    this.fitAllBounds();
                } catch (err) {
                    console.error('Error fetching GPS map data:', err);
                } finally {
                    this.isRefreshing = false;
                }
            },

            onDateChange(event) {
                const date = event.target.value;
                if (date) {
                    this.fetchData(date);
                }
            },

            prevDay() {
                const d = new Date(this.selectedDate);
                d.setDate(d.getDate() - 1);
                const str = d.toISOString().split('T')[0];
                this.fetchData(str);
            },

            nextDay() {
                const d = new Date(this.selectedDate);
                d.setDate(d.getDate() + 1);
                const str = d.toISOString().split('T')[0];
                this.fetchData(str);
            },

            goToToday() {
                const today = new Date().toISOString().split('T')[0];
                this.fetchData(today);
            },

            toggleAutoRefresh() {
                this.autoRefresh = !this.autoRefresh;
                if (this.autoRefresh) {
                    this.refreshInterval = setInterval(() => {
                        this.fetchData(this.selectedDate);
                    }, 30000);
                } else {
                    if (this.refreshInterval) {
                        clearInterval(this.refreshInterval);
                        this.refreshInterval = null;
                    }
                }
            }
        };
    };
</script>

<div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden"
    x-data="dashboardGpsTracker()">

    <!-- Map Top Header & Controls -->
    <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>{{ __('Monitoring Presensi Mandiri GPS & Visitasi Lapangan') }}</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" title="{{ __('Real-Time Active') }}"></span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Pemetaan koordinat real-time seluruh karyawan di kantor cabang (ID & KSA) serta titik dinas klien') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Date Navigator & Quick Actions -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
            <!-- Date Navigator -->
            <div class="flex items-center rounded-xl bg-slate-50 border border-slate-200/90 p-1 shadow-2xs">
                <button type="button" @click="prevDay()" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-white transition cursor-pointer" title="{{ __('Hari Sebelumnya') }}">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="flex items-center gap-1.5 px-2">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <input type="date" :value="selectedDate" @change="onDateChange($event)" class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none font-mono cursor-pointer">
                </div>
                <button type="button" @click="nextDay()" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-white transition cursor-pointer" title="{{ __('Hari Berikutnya') }}">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <button type="button" @click="goToToday()" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition cursor-pointer">
                {{ __('Hari Ini') }}
            </button>

            <!-- Manual Refresh Button -->
            <button type="button" @click="fetchData(selectedDate)" :disabled="isRefreshing"
                class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" :class="{ 'animate-spin': isRefreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span x-text="isRefreshing ? '{{ __('Memuat...') }}' : '{{ __('Segarkan') }}'"></span>
            </button>

            <!-- Auto-refresh Toggle -->
            <button type="button" @click="toggleAutoRefresh()"
                :class="autoRefresh ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                class="px-3 py-2 rounded-xl border text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer"
                :title="autoRefresh ? '{{ __('Auto-refresh aktif setiap 30 detik') }}' : '{{ __('Klik untuk aktifkan auto-refresh 30 detik') }}'">
                <span class="w-2 h-2 rounded-full" :class="autoRefresh ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                <span>Live 30s</span>
            </button>
        </div>
    </div>

    <!-- Quick Stat Badges Strip -->
    <div class="px-5 py-3 bg-slate-50/70 border-b border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">👥</span>
            <div>
                <span class="text-slate-400 text-[10px] uppercase font-semibold tracking-wider block">{{ __('Karyawan Terlacak') }}</span>
                <span class="font-bold font-mono text-slate-900 text-sm">
                    <span x-text="gpsData.stats ? gpsData.stats.total_tracked_employees : 0"></span>
                    <span class="text-slate-400 font-normal text-xs">/ <span x-text="gpsData.stats ? gpsData.stats.total_active_employees : 0"></span></span>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">🚗</span>
            <div>
                <span class="text-slate-400 text-[10px] uppercase font-semibold tracking-wider block">{{ __('Kunjungan Lapangan') }}</span>
                <span class="font-bold font-mono text-emerald-700 text-sm">
                    <span x-text="gpsData.stats ? gpsData.stats.total_visits : 0"></span>
                    <span class="text-slate-400 font-normal text-[11px]" x-show="gpsData.stats && gpsData.stats.ongoing_visits > 0">
                        (<span class="text-amber-600 font-bold" x-text="gpsData.stats.ongoing_visits"></span> {{ __('aktif') }})
                    </span>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">📍</span>
            <div>
                <span class="text-slate-400 text-[10px] uppercase font-semibold tracking-wider block">{{ __('Presensi Mandiri GPS') }}</span>
                <span class="font-bold font-mono text-indigo-700 text-sm" x-text="gpsData.stats ? gpsData.stats.total_gps_punches : 0"></span>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">🏢</span>
            <div>
                <span class="text-slate-400 text-[10px] uppercase font-semibold tracking-wider block">{{ __('Kantor & Geofence') }}</span>
                <span class="font-bold font-mono text-slate-800 text-sm">
                    <span x-text="gpsData.stats ? gpsData.stats.total_branches : 0"></span>
                    <span class="text-slate-400 font-normal text-[11px]">{{ __('Cabang (ID & KSA)') }}</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Filter Buttons & Region Focus Controls -->
    <div class="px-5 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
        <!-- Filter Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs py-0.5">
            <button type="button" @click="filterCategory = 'all'; renderAll(); fitAllBounds();"
                :class="filterCategory === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap cursor-pointer">
                {{ __('Semua Titik') }} (<span x-text="gpsData.stats ? gpsData.stats.total_markers : 0"></span>)
            </button>
            <button type="button" @click="filterCategory = 'visit'; renderAll();"
                :class="filterCategory === 'visit' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium'"
                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <span>🚗</span>
                <span>{{ __('Kunjungan Lapangan') }}</span>
            </button>
            <button type="button" @click="filterCategory = 'gps'; renderAll();"
                :class="filterCategory === 'gps' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 font-medium'"
                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <span>📍</span>
                <span>{{ __('Presensi Mandiri GPS') }}</span>
            </button>
            <button type="button" @click="filterCategory = 'branch'; renderAll();"
                :class="filterCategory === 'branch' ? 'bg-slate-800 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <span>🏢</span>
                <span>{{ __('Kantor Cabang (Geofence)') }}</span>
            </button>
        </div>

        <!-- Regional Focus Pills -->
        <div class="flex items-center gap-1.5 text-xs">
            <span class="text-slate-400 text-[11px] font-semibold hidden sm:inline">{{ __('Fokus Wilayah:') }}</span>
            <button type="button" @click="focusRegion('all')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer" title="{{ __('Semua Titik') }}">
                🌐 {{ __('Semua') }}
            </button>
            <button type="button" @click="focusRegion('indonesia')" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold border border-blue-200 transition cursor-pointer" title="{{ __('Indonesia (Jakarta, Bandung, Surabaya)') }}">
                🇮🇩 Indonesia
            </button>
            <button type="button" @click="focusRegion('riyadh')" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold border border-emerald-200 transition cursor-pointer" title="{{ __('Arab Saudi (Riyadh)') }}">
                🇸🇦 Riyadh
            </button>
        </div>
    </div>

    <!-- Main Content: Interactive Map & Employee Roster Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-[500px]">
        <!-- Map Canvas (Col 8 / 9) -->
        <div class="lg:col-span-8 xl:col-span-8 relative h-[440px] sm:h-[480px] lg:h-[540px] bg-slate-100">
            <div x-ref="mapContainer" id="dashboardGpsMapContainer" style="width: 100%; height: 100%; min-height: 440px; position: relative; z-index: 1;"></div>

            <!-- Map Overlay Legend -->
            <div style="position: absolute; bottom: 16px; left: 16px; z-index: 500;" class="bg-white/95 backdrop-blur-md px-3.5 py-2.5 rounded-2xl border border-slate-200/90 shadow-md text-[11px] space-y-1.5 hidden sm:block">
                <span class="font-bold text-slate-800 block text-[10px] uppercase tracking-wider">{{ __('Petunjuk Simbol Titik:') }}</span>
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5 text-emerald-700 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                        {{ __('Check-In Visit') }}
                    </span>
                    <span class="flex items-center gap-1.5 text-amber-600 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block animate-ping"></span>
                        {{ __('Visit Aktif') }}
                    </span>
                    <span class="flex items-center gap-1.5 text-blue-600 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
                        {{ __('Presensi GPS') }}
                    </span>
                    <span class="flex items-center gap-1.5 text-slate-700 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-800 inline-block"></span>
                        {{ __('Kantor') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Employee Roster Sidebar (Col 4) -->
        <div class="lg:col-span-4 xl:col-span-4 border-t lg:border-t-0 lg:border-s border-slate-200/80 bg-slate-50/50 flex flex-col h-[400px] lg:h-[540px]">
            <!-- Sidebar Header & Search -->
            <div class="p-3.5 border-b border-slate-200/80 bg-white space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>{{ __('Daftar Karyawan') }}</span>
                    </h4>
                    <!-- Sub-tabs -->
                    <div class="flex items-center bg-slate-100 p-0.5 rounded-lg text-[10px] font-semibold">
                        <button type="button" @click="rosterTab = 'active'"
                            :class="rosterTab === 'active' ? 'bg-white text-blue-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2 py-1 rounded-md transition cursor-pointer">
                            {{ __('Terlacak') }} (<span x-text="gpsData.stats ? gpsData.stats.total_tracked_employees : 0"></span>)
                        </button>
                        <button type="button" @click="rosterTab = 'all'"
                            :class="rosterTab === 'all' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2 py-1 rounded-md transition cursor-pointer">
                            {{ __('Semua') }} (<span x-text="gpsData.stats ? gpsData.stats.total_active_employees : 0"></span>)
                        </button>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="relative">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute start-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" x-model="searchQuery" @input="renderAll()" placeholder="{{ __('Cari nama, NIK, divisi, klien...') }}"
                        class="w-full ps-8 pe-3 py-1.5 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Employee List Scrollable -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2 text-xs">
                <template x-for="emp in getFilteredRoster()" :key="emp.id">
                    <div @click="focusEmployee(emp)"
                        :class="emp.has_activity ? 'bg-white hover:border-blue-400 shadow-2xs border-slate-200/90' : 'bg-slate-100/70 border-slate-200/50 opacity-60'"
                        class="p-2.5 rounded-xl border transition cursor-pointer group hover:shadow-xs">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="relative shrink-0">
                                    <div class="w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center border"
                                        :class="emp.has_activity ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-200 text-slate-600 border-slate-300'">
                                        <span x-text="emp.initials"></span>
                                    </div>
                                    <span x-show="emp.has_activity" class="absolute -bottom-0.5 -end-0.5 w-2.5 h-2.5 rounded-full border border-white"
                                        :class="emp.category === 'visit' ? (emp.status === 'ongoing' ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500') : 'bg-blue-600'">
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <h5 class="font-bold text-slate-900 truncate leading-tight group-hover:text-blue-600 transition" x-text="emp.name"></h5>
                                    <p class="text-[10px] text-slate-500 truncate" x-text="emp.code + ' · ' + emp.department"></p>
                                </div>
                            </div>

                            <span class="text-[10px] shrink-0 font-mono font-bold"
                                :class="emp.has_activity ? 'text-slate-700' : 'text-slate-400'"
                                x-text="emp.time || '--:--'">
                            </span>
                        </div>

                        <!-- Activity Status Badge -->
                        <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <template x-if="emp.has_activity">
                                <div class="flex items-center gap-1.5 truncate">
                                    <span class="text-xs" x-text="emp.category === 'visit' ? '🚗' : '📍'"></span>
                                    <span class="truncate font-medium text-slate-700" x-text="emp.activity_title"></span>
                                </div>
                            </template>
                            <template x-if="!emp.has_activity">
                                <span class="text-[10px] text-slate-400 italic">{{ __('Belum ada presensi GPS') }}</span>
                            </template>

                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold shrink-0"
                                :class="{
                                    'bg-amber-50 text-amber-700 border border-amber-200': emp.category === 'visit' && emp.status === 'ongoing',
                                    'bg-emerald-50 text-emerald-700 border border-emerald-200': emp.category === 'visit' && emp.status === 'completed',
                                    'bg-blue-50 text-blue-700 border border-blue-200': emp.category === 'gps',
                                    'bg-slate-100 text-slate-500': !emp.has_activity
                                }"
                                x-text="emp.has_activity ? (emp.category === 'visit' ? (emp.status === 'ongoing' ? '{{ __('Visit Aktif') }}' : '{{ __('Visit Selesai') }}') : '{{ __('GPS Masuk') }}') : '{{ __('Offline') }}'">
                            </span>
                        </div>
                    </div>
                </template>

                <div x-show="getFilteredRoster().length === 0" class="text-center py-8 text-xs text-slate-400">
                    <p>{{ __('Tidak ada karyawan yang sesuai filter.') }}</p>
                </div>
            </div>

            <!-- Footer Link -->
            <div class="p-3 bg-white border-t border-slate-200/80 flex items-center justify-between text-xs">
                <span class="text-slate-400 text-[11px]" x-text="gpsData.date_formatted"></span>
                <a href="{{ route('attendance.visits.index') }}" class="text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 text-[11px]">
                    <span>{{ __('Lihat Log Visit') }}</span>
                    <span class="rtl:rotate-180">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
</div>
