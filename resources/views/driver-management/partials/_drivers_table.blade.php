<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Driver Name</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Assigned Unit</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">License</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Daily Target</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                <th class="px-6 py-1 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($drivers as $driver)
                @php $has_shortage = isset($driver->net_shortage) && $driver->net_shortage > 0; @endphp
                <tr class="cursor-pointer {{ $has_shortage ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50' }}" onclick="openEditDriverModal({{ $driver->id }})">
                    <td class="px-6 py-1 whitespace-nowrap">
                        <div class="flex items-center gap-1.5">
                            <div class="text-xs font-medium {{ $has_shortage ? 'text-red-700 shortage-text-blink' : 'text-gray-900' }}">{{ $driver->full_name }}</div>
                            @if($has_shortage)
                                <span class="shortage-blink inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-red-600 text-white text-[9px] font-bold rounded uppercase tracking-tight shadow-sm"
                                      title="Net unpaid shortage: ₱{{ number_format($driver->net_shortage, 2) }}. Driver needs to submit excess to cover this.">
                                    <i data-lucide="alert-triangle" class="w-2.5 h-2.5"></i> SHORTAGE ₱{{ number_format($driver->net_shortage, 2) }}
                                </span>
                            @endif
                        </div>
                        <div class="text-[9px] text-gray-400">
                            <span title="Input by {{ $driver->creator_name ?? 'System' }}">In: {{ $driver->creator_name ?? 'System' }}</span>
                            @if(isset($driver->editor_name) && $driver->editor_name)
                                <span class="ml-1" title="Last edit by {{ $driver->editor_name }}">Ed: {{ $driver->editor_name }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-1 whitespace-nowrap text-xs text-gray-900">
                        @if(!empty($driver->assigned_unit))
                            {{ $driver->assigned_unit }}
                        @else
                            <span class="text-green-600 font-semibold text-[10px] bg-green-50 px-2 py-0.5 rounded border border-green-200">No Unit</span>
                        @endif
                    </td>
                    <td class="px-6 py-1 whitespace-nowrap text-xs text-gray-900">
                        {{ $driver->license_number ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-1 whitespace-nowrap">
                        <span class="px-2 inline-flex text-[10px] leading-4 font-semibold rounded-full {{ $driver->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $driver->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-1 whitespace-nowrap">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1">
                                @if(!empty($driver->assigned_unit))
                                    <span class="text-xs font-bold text-gray-900">₱{{ number_format($driver->current_target ?? $driver->daily_boundary_target, 2) }}</span>
                                    <span class="text-[8px] bg-gray-100 text-gray-400 border border-gray-200 px-1 rounded uppercase tracking-tighter" title="Target inherited from Unit {{ $driver->assigned_unit }}">Unit-Based</span>
                                @else
                                    <span class="text-[10px] font-bold text-gray-400 italic">Pending Unit</span>
                                @endif
                            </div>
                            @if(isset($driver->target_label) && $driver->target_type !== 'regular')
                                <span class="text-[9px] font-black uppercase tracking-tighter px-1 rounded-sm mt-0.5 w-fit
                                    @if($driver->target_type === 'coding') bg-red-100 text-red-600 border border-red-200
                                    @elseif($driver->target_type === 'discount') bg-blue-100 text-blue-600 border border-blue-200
                                    @else bg-gray-100 text-gray-500 @endif">
                                    {{ $driver->target_label }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-1 whitespace-nowrap text-xs text-gray-900">
                        {{ $driver->performance_rating ?? 'Good' }}
                    </td>
                    <td class="px-6 py-1 whitespace-nowrap text-xs font-medium">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="text-blue-600 hover:text-blue-900"
                                onclick="event.stopPropagation(); openDriverDetails({{ $driver->id }})"
                                title="View Details"
                            >
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            </button>
                            <button
                                type="button"
                                class="text-indigo-600 hover:text-indigo-900"
                                onclick="event.stopPropagation(); openEditDriverModal({{ $driver->id }})"
                                title="Edit Driver"
                            >
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </button>
                            <button
                                type="button"
                                class="text-red-600 hover:text-red-900"
                                onclick="event.stopPropagation(); deleteDriver({{ $driver->id }}, '{{ $driver->full_name }}')"
                                title="Delete Driver"
                            >
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                        <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
                        <p>No drivers found.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($pagination['total_pages'] > 1)
    <div class="px-6 py-2 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing {{ $pagination['total_items'] }} results / Page {{ $pagination['page'] }} of {{ $pagination['total_pages'] }}
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
