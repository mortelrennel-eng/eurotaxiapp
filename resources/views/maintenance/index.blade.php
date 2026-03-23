@extends('layouts.app')

@section('title', 'Maintenance - Euro System')
@section('page-heading', 'Maintenance Management')
@section('page-subheading', 'Track unit maintenance records')

@section('content')
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
        <input type="text" name="search" value="{{ $search }}" placeholder="Search unit or mechanic..."
            class="flex-1 min-w-[150px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
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
        <button type="button" onclick="document.getElementById('addMaintenanceModal').classList.remove('hidden')"
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
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
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $r->unit_number }}</p>
                        <p class="text-xs text-gray-500">{{ $r->plate_number }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $r->maintenance_type === 'emergency' ? 'bg-red-100 text-red-800' : ($r->maintenance_type === 'corrective' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($r->maintenance_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-700 max-w-xs truncate" title="{{ $r->description }}">{{ $r->description }}</td>
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
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button onclick='openEditMaint(@json($r))' class="text-blue-600 hover:text-blue-900"><i data-lucide="edit" class="w-4 h-4"></i></button>
                            <form method="POST" action="{{ route('maintenance.destroy', $r->id) }}" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-400">
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
                    <select name="unit_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                        <option value="">Select unit...</option>
                        @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->unit_number }} — {{ $u->plate_number }}</option>
                        @endforeach
                    </select>
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
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cost (₱) *</label>
                        <input type="number" name="cost" step="0.01" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Mechanic Name</label>
                        <input type="text" name="mechanic_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    </div>
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
                    <label class="block text-xs font-medium text-gray-700 mb-1">Parts Used</label>
                    <textarea name="parts_used" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-4">
                <button type="submit" class="flex-1 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">Save</button>
                <button type="button" onclick="document.getElementById('addMaintenanceModal').classList.add('hidden')" class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">Cancel</button>
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
                    <select name="unit_id" id="em_unit_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                        @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->unit_number }} — {{ $u->plate_number }}</option>
                        @endforeach
                    </select>
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
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" id="em_description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cost (₱) *</label>
                        <input type="number" name="cost" id="em_cost" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Mechanic Name</label>
                        <input type="text" name="mechanic_name" id="em_mechanic_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Started *</label>
                        <input type="date" name="date_started" id="em_date_started" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Completed</label>
                        <input type="date" name="date_completed" id="em_date_completed" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Parts Used</label>
                    <textarea name="parts_used" id="em_parts_used" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-4">
                <button type="submit" class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Update</button>
                <button type="button" onclick="document.getElementById('editMaintenanceModal').classList.add('hidden')" class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditMaint(r) {
    const base = "{{ url('maintenance') }}";
    document.getElementById('editMaintForm').action = base + '/' + r.id;
    document.getElementById('em_unit_id').value         = r.unit_id;
    document.getElementById('em_type').value            = r.maintenance_type;
    document.getElementById('em_status').value          = r.status || 'pending';
    document.getElementById('em_description').value     = r.description;
    document.getElementById('em_cost').value            = r.cost;
    document.getElementById('em_mechanic_name').value   = r.mechanic_name || '';
    document.getElementById('em_date_started').value    = r.date_started;
    document.getElementById('em_date_completed').value  = r.date_completed || '';
    document.getElementById('em_parts_used').value      = r.parts_used || '';
    document.getElementById('editMaintenanceModal').classList.remove('hidden');
}
</script>
@endpush
@endsection