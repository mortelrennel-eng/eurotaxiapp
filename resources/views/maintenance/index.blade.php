@extends('layouts.app')

@section('title', 'Maintenance - Euro System')
@section('page-heading', 'Maintenance Management')
@section('page-subheading', 'Track unit maintenance records')

@section('content')

<style>
    .search-dropdown {
        display: none;
        position: absolute;
        z-index: 50;
        width: 100%;
        margin-top: 0.25rem;
        background-color: white;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        max-height: 10rem;
        overflow-y: auto;
        flex-direction: column;
    }
    .search-dropdown:not(.hidden) {
        display: flex;
    }
    .search-option {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
    }
    .search-option:last-child {
        border-bottom: none;
    }
    .search-option:hover {
        background-color: #fefce8;
    }
    .recommended-option {
        background-color: #f0fdf4 !important;
        border-left: 4px solid #22c55e !important;
        order: -1;
    }
    .recommended-option:hover {
        background-color: #dcfce7 !important;
    }
    .suggested-badge {
        background-color: #22c55e;
        color: white;
        font-size: 9px;
        font-weight: 800;
        padding: 1px 6px;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        margin-left: 0.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .parts-cart-item {
        border-bottom: 1px solid #f3f4f6;
        padding: 0.75rem 0.5rem;
    }
    .parts-cart-item:last-child {
        border-bottom: none;
    }
    .modern-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .parts-list-summary {
        max-height: 200px;
        overflow-y: auto;
    }
</style>
{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-gray-900">{{ $totals->total_count ?? 0 }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Records</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ $totals->pending_count ?? 0 }}</p>
        <p class="text-xs text-gray-500 mt-1">Pending</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $totals->in_progress_count ?? 0 }}</p>
        <p class="text-xs text-gray-500 mt-1">In Progress</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ formatCurrency($totals->total_cost ?? 0) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Cost</p>
    </div>
</div>

{{-- Filter + Add --}}
<div class="bg-white rounded-lg shadow p-4 mb-5">
    <form method="GET" action="{{ route('maintenance.index') }}" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search plate or mechanic..."
            class="flex-1 min-w-[150px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
            <option value="">All Status</option>
            <option value="pending" @selected($status=='pending')>Pending</option>
            <option value="in_progress" @selected($status=='in_progress')>In Progress</option>
            <option value="completed" @selected($status=='completed')>Completed</option>
            <option value="cancelled" @selected($status=='cancelled')>Cancelled</option>
        </select>
        <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
            <option value="">All Types</option>
            <option value="preventive" @selected($type=='preventive')>Preventive</option>
            <option value="corrective" @selected($type=='corrective')>Corrective</option>
            <option value="emergency" @selected($type=='emergency')>Emergency</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm flex items-center gap-2">
            <i data-lucide="search" class="w-4 h-4"></i> Filter
        </button>
        <button type="button" onclick="openPartsModal()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm flex items-center gap-2">
            <i data-lucide="package" class="w-4 h-4"></i> Manage Inventory
        </button>
        <button type="button" onclick="openAddMaint()"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Record
        </button>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit / Driver</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mechanic</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Started</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Done</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($records as $r)
                <tr class="hover:bg-yellow-50 cursor-pointer transition-all border-l-4 border-transparent hover:border-yellow-400 group"
                    onclick="openViewMaint({{ $r->id }})">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                           <p class="font-bold text-gray-900 group-hover:text-yellow-700 transition-colors">{{ $r->plate_number }}</p>
                           @if($r->date_started == date('Y-m-d') && $r->status != 'completed')
                               <span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-[9px] font-black uppercase rounded animate-pulse">Today</span>
                           @endif
                           <i data-lucide="external-link" class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                        </div>
                        @if($r->driver_name)
                            <p class="text-[10px] text-gray-400 font-bold uppercase truncate max-w-[120px] group-hover:text-yellow-600 transition-colors" title="{{ $r->driver_name }}">
                                {{ $r->driver_name }}
                            </p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $r->maintenance_type === 'emergency' ? 'bg-red-100 text-red-800' : ($r->maintenance_type === 'corrective' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($r->maintenance_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $r->mechanic_name ?? '—' }}
                        <div class="text-[10px] text-gray-400 mt-0.5">
                            <span title="Input by {{ $r->creator_name ?? 'System' }}">In: {{ $r->creator_name ?? 'System' }}</span>
                            @if(isset($r->editor_name) && $r->editor_name)
                                <span class="ml-1" title="Last edit by {{ $r->editor_name }}">Ed: {{ $r->editor_name }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ formatDate($r->date_started) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $r->date_completed ? formatDate($r->date_completed) : '—' }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-900">{{ formatCurrency($r->cost) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ statusBadge($r->status ?? 'pending') }}">
                            {{ ucwords(str_replace('_', ' ', $r->status ?? 'pending')) }}
                        </span>
                    </td>
                    <td class="px-4 py-3" onclick="event.stopPropagation()">
                        <div class="flex gap-2">
                            <button onclick="openEditMaint(this)" data-id="{{ $r->id }}" class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition"><i data-lucide="edit" class="w-4 h-4"></i></button>
                            <form method="POST" action="{{ route('maintenance.destroy', $r->id) }}" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i data-lucide="wrench" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                        <p>No maintenance records found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pagination['total_pages'] > 1)
    <div class="px-4 py-3 border-t flex items-center justify-between text-sm text-gray-600">
        <span>{{ $pagination['total_items'] }} total records</span>
        <div class="flex gap-2">
            @if($pagination['has_prev'])<a href="?page={{ $pagination['prev_page'] }}&search={{ $search }}&status={{ $status }}&type={{ $type }}" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">← Prev</a>@endif
            @if($pagination['has_next'])<a href="?page={{ $pagination['next_page'] }}&search={{ $search }}&status={{ $status }}&type={{ $type }}" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">Next →</a>@endif
        </div>
    </div>
    @endif
</div>

{{-- Add Modal --}}
<div id="addMaintenanceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between mb-4">
            <h3 class="text-lg font-semibold">Add Maintenance Record</h3>
            <button onclick="document.getElementById('addMaintenanceModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ route('maintenance.store') }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Unit *</label>
                    <div class="relative">
                        <input type="text" id="addUnitDisplay" placeholder="Type to search unit..." required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
                        <input type="hidden" name="unit_id" id="addUnitId" required>
                        <input type="hidden" name="driver_id" id="addDriverId">
                        <div id="addUnitDropdown" class="search-dropdown hidden">
                            @foreach($units as $u)
                            <div class="search-option unit-option" 
                                data-id="{{ $u->id }}" 
                                data-name="{{ $u->plate_number }}"
                                data-driver-id="{{ $u->driver_id }}"
                                data-secondary-id="{{ $u->secondary_driver_id }}"
                                data-driver-name="{{ $drivers->where('id', $u->driver_id)->first()->name ?? '' }}">
                                <div class="font-medium text-xs text-gray-900">{{ $u->plate_number }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver Assigned</label>
                    <div class="relative">
                        <input type="text" id="addDriverDisplay" placeholder="Auto-fills on unit select..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
                        <div id="addDriverDropdown" class="search-dropdown hidden">
                            @foreach($drivers as $d)
                            <div class="search-option driver-option" data-id="{{ $d->id }}" data-name="{{ $d->name }}">
                                <div class="font-medium text-xs text-gray-900">{{ $d->name }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Type *</label>
                        <select name="maintenance_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                            <option value="preventive">Preventive</option>
                            <option value="corrective">Corrective</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Started *</label>
                        <input type="date" name="date_started" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Completed</label>
                        <input type="date" name="date_completed" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-tight">Mechanic Name *</label>
                    <div class="space-y-2">
                        <div class="relative">
                            <input type="text" name="mechanic_name[]" id="addMechDisplay1" placeholder="Search primary mechanic..." required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                            <div id="addMechDropdown1" class="search-dropdown hidden">
                                @foreach($staff as $s)
                                <div class="search-option mech-option" data-name="{{ $s->name }}" onclick="selectMech('addMechDisplay1', '{{ $s->name }}', 'addMechDropdown1')">
                                    <div class="font-medium text-xs text-gray-900">{{ $s->name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $s->role }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div id="addSecondMechRow" class="hidden animate-fade-in">
                            <div class="relative">
                                <input type="text" name="mechanic_name[]" id="addMechDisplay2" placeholder="Search secondary mechanic..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                                <div id="addMechDropdown2" class="search-dropdown hidden">
                                    @foreach($staff as $s)
                                    <div class="search-option mech-option" data-name="{{ $s->name }}" onclick="selectMech('addMechDisplay2', '{{ $s->name }}', 'addMechDropdown2')">
                                        <div class="font-medium text-xs text-gray-900">{{ $s->name }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $s->role }}</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="button" id="btnAddSecondMech" onclick="toggleSecondMech('add')" class="text-[10px] text-blue-600 font-bold hover:underline flex items-center gap-1">
                            <i data-lucide="plus-circle" class="w-3 h-3"></i> Add Second Mechanic
                        </button>
                    </div>
                </div>

                <!-- Parts Selection -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-tight">Spare Parts Selection</label>
                        <button type="button" onclick="openQuickAddPart()" class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold hover:bg-blue-200 transition">
                            + New Part
                        </button>
                    </div>
                    
                    <div class="relative mb-3">
                        <input type="text" id="addPartDisplay" placeholder="Type to search parts..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <div id="addPartDropdown" class="search-dropdown hidden">
                            @foreach($spare_parts as $p)
                            <div class="search-option part-option" data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->price }}">
                                <div class="flex justify-between items-center">
                                    <div class="font-medium text-xs text-gray-900">{{ $p->name }}</div>
                                    <div class="text-[10px] font-bold text-blue-600">₱{{ number_format($p->price, 2) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="partsCartItems" class="space-y-2 mb-3 max-h-[150px] overflow-y-auto pr-1">
                        <!-- Items will be injected here -->
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-xs font-bold text-gray-500 uppercase">Parts Total:</span>
                        <span id="partsTotalDisplay" class="text-sm font-black text-gray-900">₱0.00</span>
                    </div>
                </div>

                <!-- Other Costs -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-tight">Additional Service / Other Costs</label>
                        <button type="button" onclick="addOtherCostRow('add')" class="text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-bold hover:bg-orange-200 transition">
                            + Add Service
                        </button>
                    </div>
                    
                    <div id="otherCostsItems" class="space-y-2">
                        <!-- Dynamic items here -->
                    </div>
                </div>

                <input type="hidden" name="parts_data" id="addPartsData">
                
                <div class="pt-4 mt-2 border-t-2 border-dashed border-gray-200">
                    <div class="flex justify-between items-center bg-green-50 p-4 rounded-xl border-2 border-green-200">
                        <div>
                            <span class="block text-[10px] font-black text-green-600 uppercase tracking-widest leading-none mb-1">Grand Total Cost</span>
                            <span id="addTotalCostDisplay" class="text-3xl font-black text-green-700 tabular-nums leading-none line-height-1">₱0.00</span>
                        </div>
                        <input type="hidden" name="cost" id="addTotalCostValue" value="0">
                        <div class="text-right">
                            <i data-lucide="calculator" class="w-8 h-8 text-green-200"></i>
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2 px-1 italic">Calculated sum of all parts and additional services above.</p>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 text-sm font-bold shadow-lg shadow-green-100 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Save Record
                </button>
                <button type="button" onclick="document.getElementById('addMaintenanceModal').classList.add('hidden')" class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 text-sm font-bold transition-all">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editMaintenanceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between mb-4">
            <h3 class="text-lg font-semibold">Edit Maintenance Record</h3>
            <button onclick="document.getElementById('editMaintenanceModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="editMaintForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Unit *</label>
                    <div class="relative">
                        <input type="text" id="editUnitDisplay" placeholder="Type to search unit..." required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
                        <input type="hidden" name="unit_id" id="editUnitId" required>
                        <input type="hidden" name="driver_id" id="editDriverId">
                        <div id="editUnitDropdown" class="search-dropdown hidden">
                            @foreach($units as $u)
                            <div class="search-option unit-option" 
                                data-id="{{ $u->id }}" 
                                data-name="{{ $u->plate_number }}"
                                data-driver-id="{{ $u->driver_id }}"
                                data-secondary-id="{{ $u->secondary_driver_id }}"
                                data-driver-name="{{ $drivers->where('id', $u->driver_id)->first()->name ?? '' }}">
                                <div class="font-medium text-xs text-gray-900">{{ $u->plate_number }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver Assigned</label>
                    <div class="relative">
                        <input type="text" id="editDriverDisplay" placeholder="Auto-fills on unit select..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
                        <div id="editDriverDropdown" class="search-dropdown hidden">
                            @foreach($drivers as $d)
                            <div class="search-option driver-option" data-id="{{ $d->id }}" data-name="{{ $d->name }}">
                                <div class="font-medium text-xs text-gray-900">{{ $d->name }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Type *</label>
                        <select name="maintenance_type" id="em_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="preventive">Preventive</option>
                            <option value="corrective">Corrective</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" id="em_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="em_description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-tight">Mechanic Name *</label>
                    <div class="space-y-2">
                        <div class="relative">
                            <input type="text" name="mechanic_name[]" id="editMechDisplay1" placeholder="Search primary mechanic..." required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                            <div id="editMechDropdown1" class="search-dropdown hidden">
                                @foreach($staff as $s)
                                <div class="search-option mech-option" data-name="{{ $s->name }}" onclick="selectMech('editMechDisplay1', '{{ $s->name }}', 'editMechDropdown1')">
                                    <div class="font-medium text-xs text-gray-900">{{ $s->name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $s->role }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div id="editSecondMechRow" class="hidden">
                            <div class="relative">
                                <input type="text" name="mechanic_name[]" id="editMechDisplay2" placeholder="Search secondary mechanic..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                                <div id="editMechDropdown2" class="search-dropdown hidden">
                                    @foreach($staff as $s)
                                    <div class="search-option mech-option" data-name="{{ $s->name }}" onclick="selectMech('editMechDisplay2', '{{ $s->name }}', 'editMechDropdown2')">
                                        <div class="font-medium text-xs text-gray-900">{{ $s->name }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $s->role }}</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="button" id="btnEditSecondMech" onclick="toggleSecondMech('edit')" class="text-[10px] text-blue-600 font-bold hover:underline flex items-center gap-1">
                            <i data-lucide="plus-circle" class="w-3 h-3"></i> Add Second Mechanic
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Started *</label>
                        <input type="date" name="date_started" id="em_date_started" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Completed</label>
                        <input type="date" name="date_completed" id="em_date_completed" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    </div>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-tight">Spare Parts Selection</label>
                        <button type="button" onclick="openQuickAddPart()" class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold hover:bg-blue-200 transition">
                            + New Part
                        </button>
                    </div>
                    
                    <div class="relative mb-3">
                        <input type="text" id="editPartDisplay" placeholder="Type to search parts..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <div id="editPartDropdown" class="search-dropdown hidden">
                            @foreach($spare_parts as $p)
                            <div class="search-option part-option" data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->price }}">
                                <div class="flex justify-between items-center">
                                    <div class="font-medium text-xs text-gray-900">{{ $p->name }}</div>
                                    <div class="text-[10px] font-bold text-blue-600">₱{{ number_format($p->price, 2) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="editPartsCartItems" class="space-y-2 mb-3 max-h-[150px] overflow-y-auto pr-1">
                        <!-- Items will be injected here -->
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-xs font-bold text-gray-500 uppercase">Parts Total:</span>
                        <span id="editPartsTotalDisplay" class="text-sm font-black text-gray-900">₱0.00</span>
                    </div>
                </div>

                <!-- Other Costs -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-tight">Additional Service / Other Costs</label>
                        <button type="button" onclick="addOtherCostRow('edit')" class="text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-bold hover:bg-orange-200 transition">
                            + Add Service
                        </button>
                    </div>
                    
                    <div id="editOtherCostsItems" class="space-y-2">
                        <!-- Dynamic items here -->
                    </div>
                </div>

                <input type="hidden" name="parts_data" id="editPartsData">

                <div class="pt-4 mt-2 border-t-2 border-dashed border-gray-200">
                    <div class="flex justify-between items-center bg-yellow-50 p-4 rounded-xl border-2 border-yellow-200">
                        <div>
                            <span class="block text-[10px] font-bold text-yellow-600 uppercase tracking-widest leading-none mb-1 text-xs">Final Maintenance Cost</span>
                            <span id="editTotalCostDisplay" class="text-3xl font-black text-yellow-700 tabular-nums leading-none">₱0.00</span>
                        </div>
                        <input type="hidden" name="cost" id="editTotalCostValue" value="0">
                        <div class="text-right">
                            <i data-lucide="calculator" class="w-8 h-8 text-yellow-200"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 text-sm font-bold shadow-lg shadow-yellow-100 transition-all">Update Record</button>
                <button type="button" onclick="document.getElementById('editMaintenanceModal').classList.add('hidden')" class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 text-sm font-bold transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- View Info Modal --}}
<div id="viewMaintenanceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-0 overflow-hidden max-h-[90vh] flex flex-col animate-fade-in transition-all">
        <div class="bg-yellow-600 p-6 text-white shrink-0">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span id="viewMaintType" class="px-2 py-0.5 bg-white/20 rounded text-[10px] font-black uppercase tracking-widest"></span>
                        <span id="viewMaintStatus" class="px-2 py-0.5 bg-black/20 rounded text-[10px] font-black uppercase tracking-widest"></span>
                    </div>
                    <h3 id="viewPlateNumber" class="text-3xl font-black tracking-tighter uppercase"></h3>
                    <p id="viewDriverName" class="text-yellow-100 font-bold text-sm uppercase"></p>
                </div>
                <button onclick="document.getElementById('viewMaintenanceModal').classList.add('hidden')" class="p-2 hover:bg-white/10 rounded-full transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        
        <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Left Column: Basics & Mechanics --}}
                <div class="space-y-6">
                    <div>
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <i data-lucide="info" class="w-3 h-3 text-yellow-600"></i> Service Description
                        </h4>
                        <div id="viewDescription" class="p-4 bg-gray-50 rounded-xl text-gray-700 text-sm italic border-l-4 border-yellow-200 leading-relaxed shadow-sm max-h-[100px] overflow-y-auto custom-scrollbar"></div>
                    </div>
                    
                    <div>
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <i data-lucide="wrench" class="w-3 h-3 text-yellow-600"></i> Assigned Mechanics
                        </h4>
                        <div id="viewMechanicsList" class="flex flex-wrap gap-2"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Date Started</h4>
                            <p id="viewDateStarted" class="text-sm font-bold text-gray-800"></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Date Completed</h4>
                            <p id="viewDateCompleted" class="text-sm font-bold text-gray-800"></p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Costing Breakdown --}}
                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 flex flex-col shadow-inner">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i data-lucide="receipt" class="w-3 h-3 text-yellow-600"></i> Costing Breakdown
                    </h4>
                    
                    <div id="viewBreakdownList" class="space-y-3 mb-6 flex-1 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                        {{-- Items injected here --}}
                    </div>

                    <div class="pt-4 border-t-2 border-dashed border-gray-200 mt-auto">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-black text-gray-400 uppercase">Grand Total Amount</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-yellow-700 tabular-nums tracking-tighter" id="viewGrandTotal"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 shrink-0 flex justify-end gap-3 border-t">
            <button onclick="document.getElementById('viewMaintenanceModal').classList.add('hidden')" 
                class="px-6 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-100 text-sm font-black uppercase tracking-tight transition shadow-sm font-bold">Close View</button>
        </div>
    </div>
</div>

    <!-- Inventory Management Modal -->
    <div id="partsModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-all">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-8 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Spare Parts Catalog</h3>
                    <p class="text-xs text-gray-500">Manage names and default prices for your inventory</p>
                </div>
                <button onclick="closePartsModal()" class="p-2 hover:bg-gray-100 rounded-full transition text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="flex gap-4 mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                <input type="text" id="newPartName" placeholder="Part Name (e.g., Oil Filter)" 
                    class="flex-1 px-4 py-2 border border-blue-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <input type="number" id="newPartPrice" placeholder="Price (₱)" 
                    class="w-32 px-4 py-2 border border-blue-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button onclick="saveNewPart()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold transition flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add
                </button>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Part Name</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Default Price</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="partsTableBody" class="divide-y divide-gray-50">
                        @foreach($spare_parts as $p)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-sm font-bold text-blue-600">₱{{ number_format($p->price, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button onclick="deletePart({{ $p->id }}, this)" class="p-2 text-red-100 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button onclick="closePartsModal()" class="px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-black text-sm font-bold transition">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Add Part Modal (Smaller) -->
    <div id="quickAddPartModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6">
            <h4 class="text-base font-bold text-gray-900 mb-4">Quick Add Spare Part</h4>
            <div class="space-y-4">
                <input type="hidden" id="quickPartId">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Part Name</label>
                    <input type="text" id="quickPartName" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Standard Price (₱)</label>
                    <input type="number" id="quickPartPrice" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:outline-none">
                </div>
                <div class="flex gap-2 pt-2">
                    <button onclick="saveQuickPart()" class="flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition">Save Part</button>
                    <button onclick="closeQuickAddPart()" class="flex-1 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-200 transition">Cancel</button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
// Global state for parts catalog and carts
let partsCatalog = @json($spare_parts);
let addPartsCart = [];
let editPartsCart = [];
let addOtherCosts = [];
let editOtherCosts = [];

// Record store - keyed by ID, no HTML attribute encoding issues
const maintRecords = @json($records->keyBy('id'));

function openAddMaint() {
    closeAllDropdowns();
    document.getElementById('addMaintenanceModal').classList.remove('hidden');
    // Reset mechanics
    document.getElementById('addMechDisplay1').value = '';
    document.getElementById('addMechDisplay2').value = '';
    document.getElementById('addSecondMechRow').classList.add('hidden');
    document.getElementById('btnAddSecondMech').classList.remove('hidden');
    lucide.createIcons();
}

function selectMech(inputId, name, dropdownId) {
    document.getElementById(inputId).value = name;
    document.getElementById(dropdownId).classList.add('hidden');
}

function toggleSecondMech(mode) {
    const row = document.getElementById(mode + 'SecondMechRow');
    const btn = document.getElementById('btn' + mode + 'SecondMech');
    row.classList.remove('hidden');
    btn.classList.add('hidden');
    lucide.createIcons();
}

async function openViewMaint(id) {
    closeAllDropdowns();
    const r = maintRecords[id];
    if (!r) { console.error('Record not found:', id); return; }
    const modal = document.getElementById('viewMaintenanceModal');
    
    // Header Info
    document.getElementById('viewPlateNumber').innerText = r.plate_number;
    document.getElementById('viewDriverName').innerText = r.driver_name || 'No Driver Assigned';
    document.getElementById('viewMaintType').innerText = r.maintenance_type;
    document.getElementById('viewMaintStatus').innerText = (r.status || '').replace('_', ' ');
    document.getElementById('viewDescription').innerText = r.description || 'No description provided.';
    
    const toReadable = v => v ? String(v).substring(0, 10) : '—';
    document.getElementById('viewDateStarted').innerText   = toReadable(r.date_started);
    document.getElementById('viewDateCompleted').innerText = r.date_completed ? toReadable(r.date_completed) : 'Ongoing';
    document.getElementById('viewGrandTotal').innerText = '₱' + parseFloat(r.cost || 0).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Mechanics badges
    const mechanics = (r.mechanic_name || '').split(',').map(m => m.trim()).filter(m => m);
    const mechList = document.getElementById('viewMechanicsList');
    mechList.innerHTML = mechanics.length > 0 
        ? mechanics.map(m => `<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold border border-yellow-200 flex items-center gap-1.5"><i data-lucide="user" class="w-3 h-3"></i> ${m}</span>`).join('')
        : '<span class="text-gray-400 italic text-xs">No mechanic specified</span>';

    // Show modal immediately, then fetch breakdown
    modal.classList.remove('hidden');
    lucide.createIcons();

    const breakdown = document.getElementById('viewBreakdownList');
    breakdown.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs">Loading breakdown...</div>';

    try {
        const res = await fetch(`{{ url('maintenance') }}/${id}/parts`);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const result = await res.json();
        if (result.success && result.data.length > 0) {
            breakdown.innerHTML = result.data.map(item => `
                <div class="flex justify-between items-center bg-white p-2.5 rounded-lg border border-gray-100 shadow-sm">
                    <div>
                        <div class="text-xs font-bold text-gray-800">${item.part_name}</div>
                        <div class="text-[10px] text-gray-400">${item.part_id ? `Qty: ${item.quantity} &times; &#8369;${parseFloat(item.price).toFixed(2)}` : 'Additional Service / Labor'}</div>
                    </div>
                    <div class="text-xs font-black text-gray-900 tabular-nums">&#8369;${parseFloat(item.total || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                </div>
            `).join('');
        } else {
            breakdown.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs italic">No detailed breakdown recorded for this entry.</div>';
        }
    } catch(e) {
        breakdown.innerHTML = '<div class="text-center py-4 text-red-400 text-xs">Could not load breakdown: ' + e.message + '</div>';
    }
}

async function openEditMaint(btn) {
    closeAllDropdowns();
    const id = btn.dataset.id;
    const r = maintRecords[id];
    if (!r) { console.error('Edit record not found:', id); return; }
    const modal = document.getElementById('editMaintenanceModal');
    
    // Open immediately for responsive feel
    modal.classList.remove('hidden');
    
    const base = "{{ url('maintenance') }}";
    document.getElementById('editMaintForm').action = base + '/' + id;
    document.getElementById('editUnitId').value         = r.unit_id;
    document.getElementById('editUnitDisplay').value    = r.plate_number;
    document.getElementById('editDriverId').value       = r.driver_id || '';
    document.getElementById('editDriverDisplay').value  = r.driver_name || '';
    
    // Set driver suggestion badges
    const unitOption = document.querySelector(`#editUnitDropdown .unit-option[data-id="${r.unit_id}"]`);
    if (unitOption) {
        const suggestions = [unitOption.dataset.driverId, unitOption.dataset.secondaryId].filter(id => id && id !== 'null').join(',');
        document.getElementById('editDriverDisplay').dataset.suggestedIds = suggestions;
    }

    document.getElementById('em_type').value   = r.maintenance_type || 'preventive';
    document.getElementById('em_status').value = r.status || 'pending';
    document.getElementById('em_description').value = r.description || '';
    
    // Handle Multiple Mechanics — filter empty strings before checking length
    const mechs = (r.mechanic_name || '').split(',').map(m => m.trim()).filter(m => m.length > 0);
    document.getElementById('editMechDisplay1').value = mechs[0] || '';
    if (mechs.length > 1 && mechs[1]) {
        document.getElementById('editSecondMechRow').classList.remove('hidden');
        document.getElementById('editMechDisplay2').value = mechs[1];
        document.getElementById('btnEditSecondMech').classList.add('hidden');
    } else {
        document.getElementById('editSecondMechRow').classList.add('hidden');
        document.getElementById('editMechDisplay2').value = '';
        document.getElementById('btnEditSecondMech').classList.remove('hidden');
    }
    
    // Date values: may come as "2026-04-13T00:00:00.000000Z" or "2026-04-13" — always slice to 10 chars
    const toDateInput = v => v ? String(v).substring(0, 10) : '';
    document.getElementById('em_date_started').value   = toDateInput(r.date_started);
    document.getElementById('em_date_completed').value = toDateInput(r.date_completed);
    
    // Clear carts and show loading placeholder
    editPartsCart = [];
    editOtherCosts = [];
    refreshCart('edit');
    refreshOtherCosts('edit');
    document.getElementById('editPartsCartItems').innerHTML = '<div class="text-center py-4 text-gray-400">Loading parts...</div>';
    
    // Fetch individual parts asynchronously — CRITICAL: use parseFloat to prevent string concat
    try {
        const res = await fetch(`${base}/${id}/parts`);
        if (!res.ok) throw new Error('Server error ' + res.status);
        const result = await res.json();
        editPartsCart = [];
        editOtherCosts = [];
        if (result.success && result.data.length > 0) {
            result.data.forEach(p => {
                if (p.part_id) {
                    editPartsCart.push({
                        id:    p.part_id,
                        name:  p.part_name,
                        price: parseFloat(p.price)    || 0,   // force number
                        qty:   parseInt(p.quantity)   || 1    // force integer
                    });
                } else {
                    editOtherCosts.push({
                        name:  p.part_name,
                        price: parseFloat(p.price) || 0       // force number
                    });
                }
            });
        }
        refreshCart('edit');
        refreshOtherCosts('edit');
    } catch(e) { 
        console.error('Edit parts fetch error:', e); 
        document.getElementById('editPartsCartItems').innerHTML =
            '<div class="text-center py-4 text-red-400 text-xs">Error loading parts: ' + e.message + '</div>';
    }
}

// --- Other Costs Management ---
function addOtherCostRow(type) {
    const list = type === 'add' ? addOtherCosts : editOtherCosts;
    list.push({ name: '', price: 0 });
    refreshOtherCosts(type);
}

function removeOtherCost(index, type) {
    const list = type === 'add' ? addOtherCosts : editOtherCosts;
    list.splice(index, 1);
    refreshOtherCosts(type);
    refreshCart(type);
}

function updateOtherCostValue(index, field, value, type) {
    const list = type === 'add' ? addOtherCosts : editOtherCosts;
    if (field === 'price') {
        list[index][field] = parseFloat(value) || 0;
    } else {
        list[index][field] = value;
    }
    refreshCart(type);
}

function refreshOtherCosts(type) {
    const list = type === 'add' ? addOtherCosts : editOtherCosts;
    const container = document.getElementById(type === 'add' ? 'otherCostsItems' : 'editOtherCostsItems');
    
    if (list.length === 0) {
        container.innerHTML = `<div class="text-center py-2 text-gray-400 italic text-[10px]">No additional services added.</div>`;
        return;
    }

    container.innerHTML = list.map((item, i) => `
        <div class="flex gap-2 items-center bg-white p-2 rounded border border-gray-100 shadow-sm">
            <input type="text" value="${item.name}" oninput="updateOtherCostValue(${i}, 'name', this.value, '${type}')" 
                placeholder="Service Description (e.g. Labor)" 
                class="flex-1 px-2 py-1 border rounded text-xs focus:ring-1 focus:ring-orange-500 focus:outline-none">
            <div class="relative w-24">
                <span class="absolute left-1 top-1.5 text-gray-400 text-[10px]">₱</span>
                <input type="number" value="${item.price}" oninput="updateOtherCostValue(${i}, 'price', this.value, '${type}')" 
                    class="w-full pl-3 pr-1 py-1 border rounded text-xs text-right focus:outline-none">
            </div>
            <button type="button" onclick="removeOtherCost(${i}, '${type}')" class="p-1 text-red-400 hover:text-red-600 transition">
                <i data-lucide="minus-circle" class="w-4 h-4"></i>
            </button>
        </div>
    `).join('');
    lucide.createIcons();
}

// --- Master Parts Catalog ---
function openPartsModal() {
    document.getElementById('partsModal').classList.remove('hidden');
    refreshPartsTable();
}
function closePartsModal() { document.getElementById('partsModal').classList.add('hidden'); }

async function saveNewPart() {
    const name = document.getElementById('newPartName').value;
    const price = document.getElementById('newPartPrice').value;
    if(!name || !price) return;

    try {
        const res = await fetch("{{ route('spare-parts.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name, price })
        });
        const result = await res.json();
        if(result.success) {
            partsCatalog.push(result.data);
            refreshPartsTable();
            refreshPartDropdowns();
            document.getElementById('newPartName').value = '';
            document.getElementById('newPartPrice').value = '';
        }
    } catch(e) { console.error(e); }
}

async function deletePart(id, btn) {
    if(!confirm('Are you sure?')) return;
    try {
        const res = await fetch(`{{ url('spare-parts') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const result = await res.json();
        if(result.success) {
            partsCatalog = partsCatalog.filter(p => p.id !== id);
            refreshPartsTable();
            refreshPartDropdowns();
        }
    } catch(e) { console.error(e); }
}

function refreshPartsTable() {
    const body = document.getElementById('partsTableBody');
    body.innerHTML = partsCatalog.map(p => `
        <tr class="hover:bg-gray-50/50 transition border-b border-gray-100">
            <td class="px-4 py-3 text-sm font-semibold text-gray-800">${p.name}</td>
            <td class="px-4 py-3 text-sm font-bold text-blue-600">₱${parseFloat(p.price).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
            <td class="px-4 py-3 text-right">
                <button onclick="deletePart(${p.id}, this)" class="p-2 text-red-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        </tr>
    `).join('');
    lucide.createIcons();
}

// --- Quick Add Part ---
function openQuickAddPart() { 
    if (!document.getElementById('quickPartId').value) {
        document.getElementById('quickPartName').value = '';
        document.getElementById('quickPartPrice').value = '';
        document.getElementById('quickAddPartModal').querySelector('h4').innerText = 'Quick Add Spare Part';
    }
    document.getElementById('quickAddPartModal').classList.remove('hidden'); 
}

function closeQuickAddPart() { 
    document.getElementById('quickAddPartModal').classList.add('hidden'); 
    document.getElementById('quickPartId').value = '';
}

async function saveQuickPart() {
    const id = document.getElementById('quickPartId').value;
    const name = document.getElementById('quickPartName').value;
    const price = document.getElementById('quickPartPrice').value;
    if(!name || !price) return;
    
    try {
        const res = await fetch("{{ route('spare-parts.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id, name, price })
        });
        const result = await res.json();
        if(result.success) {
            if (id) {
                const index = partsCatalog.findIndex(p => p.id == id);
                if (index !== -1) partsCatalog[index] = result.data;
            } else {
                partsCatalog.push(result.data);
                addPartToCart(result.data, 'add');
            }
            refreshPartDropdowns();
            refreshPartsTable();
            closeQuickAddPart();
        }
    } catch(e) { console.error(e); }
}

function editPartFromDropdown(id, name, price, event) {
    if(event) event.stopPropagation();
    document.getElementById('quickPartId').value = id;
    document.getElementById('quickPartName').value = name;
    document.getElementById('quickPartPrice').value = price;
    document.getElementById('quickAddPartModal').querySelector('h4').innerText = 'Edit Spare Part';
    openQuickAddPart();
}

// --- Cart Management ---
function addPartToCart(part, type) {
    const cart = type === 'add' ? addPartsCart : editPartsCart;
    const existing = cart.find(p => p.id === part.id);
    if(existing) {
        existing.qty++;
    } else {
        cart.push({ ...part, qty: 1 });
    }
    refreshCart(type);
}

function removeFromCart(index, type) {
    const cart = type === 'add' ? addPartsCart : editPartsCart;
    cart.splice(index, 1);
    refreshCart(type);
}

function updateQty(index, qty, type) {
    const cart = type === 'add' ? addPartsCart : editPartsCart;
    cart[index].qty = parseInt(qty) || 1;
    refreshCart(type);
}

function refreshCart(type) {
    const cart = type === 'add' ? addPartsCart : editPartsCart;
    const otherCosts = type === 'add' ? addOtherCosts : editOtherCosts;
    const container = document.getElementById(type === 'add' ? 'partsCartItems' : 'editPartsCartItems');
    const totalDisplay = document.getElementById(type === 'add' ? 'partsTotalDisplay' : 'editPartsTotalDisplay');
    const grandTotalDisplay = document.getElementById(type === 'add' ? 'addTotalCostDisplay' : 'editTotalCostDisplay');
    const hiddenInput = document.getElementById(type === 'add' ? 'addPartsData' : 'editPartsData');

    let partsTotal = 0;
    if(cart.length === 0) {
        container.innerHTML = `<div class="text-center py-4 text-gray-400 italic text-xs">No parts selected yet.</div>`;
        if (totalDisplay) totalDisplay.innerText = '\u20b10.00';
    } else {
        container.innerHTML = cart.map((p, i) => {
            // Always use parseFloat to prevent string concatenation
            const price = parseFloat(p.price) || 0;
            const qty = parseInt(p.qty) || 1;
            const subtotal = price * qty;
            partsTotal += subtotal;
            return `
                <div class="parts-cart-item flex justify-between items-center">
                    <div class="flex-1">
                        <div class="text-xs font-bold text-gray-800">${p.name}</div>
                        <div class="text-[10px] text-gray-400">\u20b1${price.toFixed(2)} / pc</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="number" value="${qty}" onchange="updateQty(${i}, this.value, '${type}')" 
                            class="w-12 px-1 py-0.5 border rounded text-xs text-center focus:outline-none">
                        <div class="text-xs font-black text-gray-900 w-20 text-right">\u20b1${subtotal.toFixed(2)}</div>
                        <button type="button" onclick="removeFromCart(${i}, '${type}')" class="text-red-400 hover:text-red-600 transition">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
        if (totalDisplay) totalDisplay.innerText = '\u20b1' + partsTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    }

    // Always parseFloat on other costs to prevent string concat
    let otherTotal = otherCosts.reduce((sum, item) => sum + (parseFloat(item.price) || 0), 0);
    const grandTotal = partsTotal + otherTotal;
    
    if (grandTotalDisplay) grandTotalDisplay.innerText = '\u20b1' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    const costInput = document.getElementById(type === 'add' ? 'addTotalCostValue' : 'editTotalCostValue');
    if (costInput) costInput.value = grandTotal.toFixed(2);
    
    // Bundle everything into parts_data
    const combinedData = {
        parts: cart.map(p => ({ ...p, price: parseFloat(p.price) || 0, qty: parseInt(p.qty) || 1 })),
        others: otherCosts.map(o => ({ ...o, price: parseFloat(o.price) || 0 }))
    };
    if (hiddenInput) hiddenInput.value = JSON.stringify(combinedData);
    
    lucide.createIcons();
}

function refreshPartDropdowns() {
    const addDropdown = document.getElementById('addPartDropdown');
    const editDropdown = document.getElementById('editPartDropdown');
    
    const html = partsCatalog.map(p => `
        <div class="search-option part-option group" data-id="${p.id}" data-name="${p.name}" data-price="${p.price}">
            <div class="flex justify-between items-center">
                <div class="font-medium text-xs text-gray-900">${p.name}</div>
                <div class="flex items-center gap-2">
                    <div class="text-[10px] font-bold text-blue-600">₱${parseFloat(p.price).toFixed(2)}</div>
                    <button onclick="editPartFromDropdown(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.price}, event)" 
                        class="p-1 opacity-10 sm:opacity-0 group-hover:opacity-100 hover:bg-yellow-100 rounded text-yellow-600 transition">
                        <i data-lucide="pencil" class="w-3 h-3"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    addDropdown.innerHTML = html;
    editDropdown.innerHTML = html;
    
    // Re-init listeners
    initPartSelectors();
    lucide.createIcons();
}

function initPartSelectors() {
    ['add', 'edit'].forEach(type => {
        const input = document.getElementById(`${type}PartDisplay`);
        const dropdown = document.getElementById(`${type}PartDropdown`);
        if(!input || !dropdown) return;
        const options = dropdown.querySelectorAll('.part-option');

        input.onfocus = () => { 
            dropdown.classList.remove('hidden'); 
            refreshPartDropdowns(); 
        };
        
        input.oninput = () => {
            const term = input.value.toLowerCase();
            options.forEach(opt => opt.style.display = opt.dataset.name.toLowerCase().includes(term) ? 'block' : 'none');
            dropdown.classList.remove('hidden');
        };

        options.forEach(opt => {
            opt.onclick = () => {
                const part = { id: parseInt(opt.dataset.id), name: opt.dataset.name, price: parseFloat(opt.dataset.price) };
                addPartToCart(part, type);
                input.value = '';
                dropdown.classList.add('hidden');
            };
        });
        
        // Hide on click outside
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });
}

function initSearchableDropdown(inputId, dropdownId, optionClass, onSelectCallback) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    if(!input || !dropdown) return;
    
    // We re-query options inside listeners to handle dynamic content if needed
    const getOptions = () => dropdown.querySelectorAll('.' + optionClass);

    input.addEventListener('mousedown', () => {
        closeAllDropdowns();
        dropdown.classList.remove('hidden');
    });

    input.addEventListener('input', () => {
        const searchTerm = input.value.toLowerCase();
        getOptions().forEach(opt => {
            const text = opt.dataset.name.toLowerCase();
            if (text.includes(searchTerm)) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
        dropdown.classList.remove('hidden');
    });

    // Delegated click listener is safer if content changes, but options are static here for now
    dropdown.addEventListener('click', (e) => {
        const opt = e.target.closest('.' + optionClass);
        if (opt) {
            input.value = opt.dataset.name;
            if (opt.dataset.id) {
                const hiddenInputId = inputId.replace('Display', 'Id');
                document.getElementById(hiddenInputId).value = opt.dataset.id;
            }
            dropdown.classList.add('hidden');
            if (onSelectCallback) onSelectCallback(opt.dataset);
        }
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Special logic for driver dropdown to highlight suggestions
    if (inputId.includes('Driver')) {
        const originalInputListener = input.oninput; // This might not work if added via addEventListener
        // Instead, we'll hook into the 'input' event we just added, but we need to run our suggestion logic
    }
}

function closeAllDropdowns() {
    document.querySelectorAll('.search-dropdown').forEach(d => d.classList.add('hidden'));
}

function filterDriverSuggestions(inputId, dropdownId, suggestedIds) {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;
    const options = dropdown.querySelectorAll('.driver-option');
    const searchTerm = document.getElementById(inputId).value.toLowerCase();
    
    // Split suggested IDs into an array
    const recommendations = suggestedIds ? suggestedIds.split(',').filter(id => id && id !== 'null') : [];

    options.forEach(opt => {
        const driverId = opt.dataset.id;
        const driverName = opt.dataset.name.toLowerCase();
        const isMatch = driverName.includes(searchTerm);
        
        opt.style.display = isMatch ? 'block' : 'none';
        
        // Match suggested IDs
        const isSuggested = recommendations.includes(driverId);
        
        if (isSuggested) {
            opt.classList.add('recommended-option');
            let nameDiv = opt.querySelector('.font-medium');
            if (nameDiv && !opt.querySelector('.suggested-badge')) {
                nameDiv.innerHTML += ' <span class="suggested-badge">Recommended</span>';
            }
        } else {
            opt.classList.remove('recommended-option');
            let badge = opt.querySelector('.suggested-badge');
            if (badge) badge.remove();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Add Unit Selection
    initSearchableDropdown('addUnitDisplay', 'addUnitDropdown', 'unit-option', (data) => {
        const driverId = data.driverId;
        const secondaryId = data.secondaryId;
        const driverDisplay = document.getElementById('addDriverDisplay');
        const driverHidden = document.getElementById('addDriverId');
        
        // Clear current driver (match Boundary)
        driverDisplay.value = '';
        driverHidden.value = '';
        
        // Store suggestions and trigger filter
        const suggestions = [driverId, secondaryId].filter(id => id && id !== 'null').join(',');
        driverDisplay.dataset.suggestedIds = suggestions;
        filterDriverSuggestions('addDriverDisplay', 'addDriverDropdown', suggestions);
    });

    // Add Driver Input - refine suggestion on type
    const addDriverInput = document.getElementById('addDriverDisplay');
    addDriverInput.addEventListener('input', () => {
        filterDriverSuggestions('addDriverDisplay', 'addDriverDropdown', addDriverInput.dataset.suggestedIds);
    });
    addDriverInput.addEventListener('focus', () => {
        filterDriverSuggestions('addDriverDisplay', 'addDriverDropdown', addDriverInput.dataset.suggestedIds);
    });
    
    initSearchableDropdown('addMechDisplay1', 'addMechDropdown1', 'mech-option');
    initSearchableDropdown('addMechDisplay2', 'addMechDropdown2', 'mech-option');
    initSearchableDropdown('editMechDisplay1', 'editMechDropdown1', 'mech-option');
    initSearchableDropdown('editMechDisplay2', 'editMechDropdown2', 'mech-option');
    
    // Edit Unit Selection
    initSearchableDropdown('editUnitDisplay', 'editUnitDropdown', 'unit-option', (data) => {
        const driverId = data.driverId;
        const secondaryId = data.secondaryId;
        const driverDisplay = document.getElementById('editDriverDisplay');
        const driverHidden = document.getElementById('editDriverId');
        
        // Clear current driver
        driverDisplay.value = '';
        driverHidden.value = '';
        
        // Store suggestions and trigger filter
        const suggestions = [driverId, secondaryId].filter(id => id && id !== 'null').join(',');
        driverDisplay.dataset.suggestedIds = suggestions;
        filterDriverSuggestions('editDriverDisplay', 'editDriverDropdown', suggestions);
    });

    const editDriverInput = document.getElementById('editDriverDisplay');
    editDriverInput.addEventListener('input', () => {
        filterDriverSuggestions('editDriverDisplay', 'editDriverDropdown', editDriverInput.dataset.suggestedIds);
    });
    editDriverInput.addEventListener('focus', () => {
        filterDriverSuggestions('editDriverDisplay', 'editDriverDropdown', editDriverInput.dataset.suggestedIds);
    });

    initSearchableDropdown('editDriverDisplay', 'editDriverDropdown', 'driver-option');
    initSearchableDropdown('editMechDisplay', 'editMechDropdown', 'mech-option');
    
    initPartSelectors();
});
</script>
@endpush
@endsection