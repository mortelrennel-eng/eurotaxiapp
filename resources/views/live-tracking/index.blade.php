@extends('layouts.app')

@section('title', 'Live Tracking - Euro System')
@section('page-heading', 'Live Tracking')
@section('page-subheading', 'Real-time GPS monitoring of all taxi units')

@push('styles')
    <style>
        .unit-item { cursor: pointer; transition: background-color 0.2s; }
        .unit-item:hover { background-color: #fefce8; }
        .unit-item.selected { background-color: #fef9c3; border-left: 3px solid #ca8a04; }
        .unit-panel { height: calc(100vh - 260px); min-height: 400px; overflow-y: auto; }
        .map-container { height: calc(100vh - 260px); min-height: 400px; background-color: #f3f4f6; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        iframe.gps-frame { width: 100%; height: 100%; border: none; }
    </style>
@endpush

@section('content')

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-3 text-center">
                <p class="text-xs text-gray-500">Total Units</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-3 text-center">
                <p class="text-xs text-gray-500">With GPS Link</p>
                <p class="text-xl font-bold text-green-600">{{ $stats['active'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-3 text-center">
                <p class="text-xs text-gray-500">No GPS Data</p>
                <p class="text-xl font-bold text-gray-400">{{ $stats['offline'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-3 text-center">
                <p class="text-xs text-gray-500">Active Drivers</p>
                <p class="text-xl font-bold text-blue-600">{{ $tracked_units->where('current_driver', '!=', 'None')->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Tracking Controls --}}
    <div class="bg-white rounded-lg shadow p-3 mb-3">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="text" id="unitSearchInput" onkeyup="filterUnits()"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                        placeholder="Search unit number...">
                </div>
            </div>
            <div class="sm:w-48">
                <select id="statusFilterSelect" onchange="filterUnits()" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                    <option value="">All Units</option>
                    <option value="active" selected>With GPS Link</option>
                    <option value="offline">No GPS Link</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Map + Unit List --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
        
        {{-- Unit List (1/4) --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col h-full">
                <div class="px-4 py-3 border-b bg-gray-50 shrink-0">
                    <h3 class="font-semibold text-gray-800">Fleet Units</h3>
                </div>
                <div class="unit-panel flex-1" id="unitList">
                    @forelse($tracked_units as $unit)
                        <div
                            class="unit-item p-4 border-b {{ $unit->gps_status === 'offline' ? 'opacity-70' : '' }}"
                            data-unit-id="{{ $unit->id }}"
                            data-unit-number="{{ $unit->unit_number }}"
                            data-status="{{ $unit->gps_status }}"
                            data-link="{{ $unit->gps_link ?? '' }}"
                            onclick="selectUnit(this)"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-gray-900 text-sm">{{ $unit->unit_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $unit->plate_number }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <i data-lucide="user" class="inline w-3 h-3"></i> {{ $unit->current_driver ?? 'None' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($unit->gps_link)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 flex items-center gap-1">
                                            <i data-lucide="wifi" class="w-3 h-3"></i> Linked
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                            No Link
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 text-sm">No units found</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Map / iframe Embed (3/4) --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow overflow-hidden h-full">
                <div class="px-4 py-3 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i data-lucide="map" class="w-4 h-4 text-blue-600"></i>
                        Live Map Viewer
                    </h3>
                    <span id="mapTitle" class="text-sm font-medium text-gray-500">Select a unit</span>
                </div>
                <div class="map-container" id="mapViewer">
                    <div class="text-center text-gray-400 flex flex-col items-center" id="mapPlaceholder">
                        <i data-lucide="map-pin" class="w-12 h-12 mb-3 text-gray-300"></i>
                        <p class="text-lg font-medium">No Unit Selected</p>
                        <p class="text-sm mt-1">Select a unit from the list on the left to view its TracksolidPro location.</p>
                    </div>
                    <iframe id="gpsIframe" class="gps-frame hidden" allowfullscreen></iframe>
                </div>
            </div>
        </div>
        
    </div>

@endsection

@push('scripts')
    <script>
        // Apply default filter on load
        document.addEventListener('DOMContentLoaded', function() {
            filterUnits();
        });

        function selectUnit(el) {
            // Remove selection styling from all
            document.querySelectorAll('.unit-item').forEach(i => i.classList.remove('selected'));
            
            // Add to clicked
            el.classList.add('selected');

            const link = el.dataset.link;
            const unitNum = el.dataset.unitNumber;
            const iframe = document.getElementById('gpsIframe');
            const placeholder = document.getElementById('mapPlaceholder');
            const title = document.getElementById('mapTitle');

            if (link) {
                // Determine if it's already an embed link. Tracksolid share links can be embedded directly.
                // It usually accepts iframe embed.
                placeholder.classList.add('hidden');
                iframe.classList.remove('hidden');
                
                // Show loading indicator briefly if desired, or just set src
                if(iframe.src !== link) {
                    iframe.src = link;
                }
                
                title.textContent = "Viewing Unit: " + unitNum;
            } else {
                // Unit has no link
                iframe.classList.add('hidden');
                iframe.src = '';
                placeholder.classList.remove('hidden');
                placeholder.innerHTML = `
                    <i data-lucide="link-2-off" class="w-12 h-12 mb-3 text-red-300"></i>
                    <p class="text-lg font-medium text-gray-600">No GPS Link</p>
                    <p class="text-sm mt-1">This unit does not have a TracksolidPro share link connected.</p>
                `;
                if(typeof lucide !== 'undefined') lucide.createIcons();
                title.textContent = unitNum + " (Offline)";
            }
        }

        function filterUnits() {
            const search = document.getElementById('unitSearchInput').value.toLowerCase();
            const status = document.getElementById('statusFilterSelect').value;

            document.querySelectorAll('.unit-item').forEach(el => {
                const unitNum = el.dataset.unitNumber.toLowerCase();
                const unitStatus = el.dataset.status;
                
                const matchSearch = !search || unitNum.includes(search);
                const matchStatus = !status || unitStatus === status;
                
                el.style.display = (matchSearch && matchStatus) ? '' : 'none';
            });
        }
    </script>
@endpush