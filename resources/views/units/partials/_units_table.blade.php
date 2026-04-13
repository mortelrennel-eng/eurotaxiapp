<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Plate Number Info</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Vehicle Details</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Assigned Drivers</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Boundary Rate</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($units as $unit)
                @php
                    $is_available = (!$unit->driver_id && !$unit->secondary_driver_id) && $unit->status === 'active';
                    $primary_driver = $unit->primary_driver ?? null;
                    $secondary_driver = $unit->secondary_driver ?? null;
                    $total_collected = $unit->total_collected ?? 0;
                    $purchase_cost = $unit->purchase_cost ?? 0;
                    $roi_achieved = $unit->roi_achieved ?? false;
                @endphp
                <tr class="hover:bg-gray-50 cursor-pointer text-[13px]" onclick="viewUnitDetails({{ $unit->id }})">
                    <td class="px-6 py-1 whitespace-nowrap">
                        <div class="space-y-0.5">
                            <div class="font-bold text-gray-900">{{ $unit->plate_number }}</div>
                            @if($unit->motor_no)
                                <div class="text-[10px] text-gray-500 font-mono tracking-tight cursor-default" title="Motor Number">M: {{ $unit->motor_no }}</div>
                            @endif
                            @if($unit->chassis_no)
                                <div class="text-[10px] text-gray-500 font-mono tracking-tight cursor-default" title="Chassis Number">C: {{ $unit->chassis_no }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-1 whitespace-nowrap">
                        <div class="space-y-0.5">
                            <div class="font-medium text-gray-900">{{ $unit->make }} {{ $unit->model }}</div>
                            <div class="text-xs text-gray-500">{{ $unit->year }}</div>
                            <div class="flex items-center gap-2 text-[10px]">
                                <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded-full">{{ ucfirst($unit->unit_type ?? 'new') }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-1 whitespace-nowrap">
                        <div class="space-y-0.5">
                            @if($unit->driver_id && $primary_driver)
                                @php $d1 = explode('|', $primary_driver); @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-medium text-gray-900">D1:</span>
                                    <span class="text-[11px] text-gray-700">{{ $d1[0] ?? '' }}</span>
                                </div>
                            @else
                                <div class="text-[11px] text-gray-400">No D1</div>
                            @endif
                            @if($unit->secondary_driver_id && $secondary_driver)
                                @php $d2 = explode('|', $secondary_driver); @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-medium text-gray-900">D2:</span>
                                    <span class="text-[11px] text-gray-700">{{ $d2[0] ?? '' }}</span>
                                </div>
                            @else
                                <div class="text-[11px] text-gray-400">No D2</div>
                            @endif
                        </div>
                    </td>

                    <td class="px-6 py-1 whitespace-nowrap">
                        <span class="px-1.5 py-0.5 text-[10px] rounded-full
                                @if($unit->status === 'active') bg-green-100 text-green-800
                                @elseif($unit->status === 'maintenance') bg-yellow-100 text-yellow-800
                                @elseif($unit->status === 'coding') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                            {{ ucfirst($unit->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-1 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-900">{{ formatCurrency($unit->current_rate ?? $unit->boundary_rate) }}</span>
                            @if(isset($unit->rate_label) && $unit->rate_type !== 'regular')
                                <span class="text-[9px] font-black uppercase tracking-tighter px-1 rounded-sm mt-0.5 w-fit
                                    @if($unit->rate_type === 'coding') bg-red-100 text-red-600 border border-red-200
                                    @elseif($unit->rate_type === 'discount') bg-blue-100 text-blue-600 border border-blue-200
                                    @else bg-gray-100 text-gray-500 @endif">
                                    {{ $unit->rate_label }}
                                </span>
                            @else
                                <span class="text-[9px] text-gray-400 font-medium uppercase tracking-tighter">Standard Rate</span>
                            @endif
                        </div>
                    </td>

                    <td class="px-6 py-1 whitespace-nowrap">
                        <div class="flex gap-1">
                            <button onclick="event.stopPropagation(); editUnit({{ $unit->id }})"
                                class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="Edit Unit">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </button>
                            <form method="POST" action="{{ route('units.destroy', $unit->id) }}"
                                onsubmit="return confirm('Delete unit {{ $unit->plate_number }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="event.stopPropagation()"
                                    class="p-1 text-red-600 hover:bg-red-50 rounded" title="Delete Unit">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i data-lucide="car" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p>No units found</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($pagination['total_pages'] > 1)
    <div class="px-6 py-2 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing {{ $pagination['total_items'] }} results / Page {{ $pagination['page'] }} of
                {{ $pagination['total_pages'] }}
            </div>
            <div class="flex items-center gap-2">
                @if($pagination['has_prev'])
                    <a href="javascript:void(0)" onclick="changePage({{ $pagination['prev_page'] }})"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                @endif
                @for($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++)
                    <a href="javascript:void(0)" onclick="changePage({{ $i }})"
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                                    {{ $i === $pagination['page'] ? 'z-10 bg-yellow-50 border-yellow-500 text-yellow-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                        {{ $i }}
                    </a>
                @endfor
                @if($pagination['has_next'])
                    <a href="javascript:void(0)" onclick="changePage({{ $pagination['next_page'] }})"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
