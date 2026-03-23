@extends('layouts.app')

@section('title', 'Office Expenses - Euro System')
@section('page-heading', 'Office Expenses')
@section('page-subheading', 'Track and manage all operational and administrative expenses')

@section('content')

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">This Month</p>
                        <p class="text-2xl font-bold text-red-600">{{ formatCurrency($stats['this_month'] ?? 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total expenses</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <i data-lucide="dollar-sign" class="h-8 w-8 text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Last Month</p>
                        <p class="text-2xl font-bold text-gray-700">{{ formatCurrency($stats['last_month'] ?? 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Previous period</p>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-full">
                        <i data-lucide="calendar" class="h-8 w-8 text-gray-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Monthly Change</p>
                        @php
                            $change = $stats['monthly_change'] ?? 0;
                        @endphp
                        <p class="text-2xl font-bold {{ $change < 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $change > 0 ? '+' : '' }}{{ $change }}%
                        </p>
                        <p class="text-xs text-gray-500 mt-1">vs last month</p>
                    </div>
                    <div class="p-3 {{ $change < 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-full">
                        <i data-lucide="{{ $change < 0 ? 'trending-down' : 'trending-up' }}" class="h-8 w-8 {{ $change < 0 ? 'text-green-600' : 'text-red-600' }}"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Records</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['total_records'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">All time</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i data-lucide="file-text" class="h-8 w-8 text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Breakdown --}}
    @if(isset($category_breakdown) && count($category_breakdown) > 0)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Expenses by Category (This Month)</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($category_breakdown as $cat)
                <div class="text-center p-4 border rounded-lg">
                    <div class="text-lg font-bold text-gray-800">{{ formatCurrency($cat->total) }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $cat->category }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Search / Filter + Add Button --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('office-expenses.index') }}">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                            placeholder="Search by description, category, or reference...">
                    </div>
                </div>

                <div class="lg:w-48">
                    <select name="category" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                        <option value="">All Categories</option>
                        <option value="Utilities" {{ ($category_filter ?? '') === 'Utilities' ? 'selected' : '' }}>Utilities</option>
                        <option value="Supplies" {{ ($category_filter ?? '') === 'Supplies' ? 'selected' : '' }}>Supplies</option>
                        <option value="Repairs" {{ ($category_filter ?? '') === 'Repairs' ? 'selected' : '' }}>Repairs</option>
                        <option value="Communications" {{ ($category_filter ?? '') === 'Communications' ? 'selected' : '' }}>Communications</option>
                        <option value="Transportation" {{ ($category_filter ?? '') === 'Transportation' ? 'selected' : '' }}>Transportation</option>
                        <option value="Other" {{ ($category_filter ?? '') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="lg:w-40">
                    <select name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="pending" {{ ($status_filter ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ ($status_filter ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ ($status_filter ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i> Filter
                    </button>
                    <button type="button" onclick="openAddExpenseModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Expense
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Expenses Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $expense->category }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div>{{ Str::limit($expense->description, 40) }}</div>
                                @if(strlen($expense->description) > 40)
                                    <span class="text-blue-600 cursor-pointer text-xs" onclick="showDescription('{{ addslashes($expense->description) }}')">more</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $expense->unit_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $expense->reference_number ?? '-' }}
                                <div class="text-[10px] text-gray-400 mt-1">
                                    <span title="Input by {{ $expense->creator_name ?? 'System' }}">In: {{ $expense->creator_name ?? 'System' }}</span>
                                    @if(isset($expense->editor_name) && $expense->editor_name)
                                        <span class="ml-1" title="Last edit by {{ $expense->editor_name }}">Ed: {{ $expense->editor_name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                {{ formatCurrency($expense->amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($expense->status === 'approved') bg-green-100 text-green-800
                                    @elseif($expense->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" onclick="openEditExpenseModal({{ $expense->id }})" class="text-blue-600 hover:text-blue-900 mr-2">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                @if($expense->status === 'pending')
                                    <form method="POST" action="{{ route('office-expenses.approve', $expense->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900 mr-2">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('office-expenses.reject', $expense->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900 mr-2">Reject</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('office-expenses.destroy', $expense->id) }}" class="inline"
                                    onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="dollar-sign" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p>No expenses recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($expenses) && method_exists($expenses, 'links'))
        <div class="px-6 py-4 border-t">
            {{ $expenses->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- Add/Edit Expense Modal --}}
    <div id="expenseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900" id="expenseModalTitle">Add Expense</h3>
                <button onclick="closeExpenseModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="expenseForm" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" id="expenseFormMethod" value="POST">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date *</label>
                    <input type="date" name="expense_date" id="expenseDate" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category" id="expenseCategory" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="">Select Category</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Supplies">Supplies</option>
                        <option value="Repairs">Repairs</option>
                        <option value="Communications">Communications</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" id="expenseDescription" required rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        placeholder="Describe the expense..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                        <input type="number" name="amount" id="expenseAmount" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference No.</label>
                        <input type="text" name="reference_number" id="expenseReference"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Related Unit</label>
                    <select name="unit_id" id="expenseUnit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="">No specific unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->unit_number }} - {{ $unit->plate_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-3 mt-4">
                    <button type="submit" class="flex-1 bg-yellow-600 text-white py-2 rounded-lg hover:bg-yellow-700">Save</button>
                    <button type="button" onclick="closeExpenseModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Description Modal --}}
    <div id="descModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Full Description</h3>
                <button onclick="document.getElementById('descModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6">
                <p id="fullDescText" class="text-gray-700"></p>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
function openAddExpenseModal() {
    document.getElementById('expenseModalTitle').textContent = 'Add Expense';
    document.getElementById('expenseFormMethod').value = 'POST';
    document.getElementById('expenseForm').action = '{{ route('office-expenses.store') }}';
    document.getElementById('expenseDate').value = '{{ date('Y-m-d') }}';
    document.getElementById('expenseCategory').value = '';
    document.getElementById('expenseDescription').value = '';
    document.getElementById('expenseAmount').value = '';
    document.getElementById('expenseReference').value = '';
    document.getElementById('expenseUnit').value = '';
    document.getElementById('expenseModal').classList.remove('hidden');
    lucide.createIcons();
}

function openEditExpenseModal(id) {
    fetch('{{ url('office-expenses') }}/' + id + '?format=json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('expenseModalTitle').textContent = 'Edit Expense';
        document.getElementById('expenseFormMethod').value = 'PUT';
        document.getElementById('expenseForm').action = '{{ url('office-expenses') }}/' + id;
        document.getElementById('expenseDate').value = data.expense_date || '';
        document.getElementById('expenseCategory').value = data.category || '';
        document.getElementById('expenseDescription').value = data.description || '';
        document.getElementById('expenseAmount').value = data.amount || '';
        document.getElementById('expenseReference').value = data.reference_number || '';
        document.getElementById('expenseUnit').value = data.unit_id || '';
        document.getElementById('expenseModal').classList.remove('hidden');
        lucide.createIcons();
    })
    .catch(() => {
        document.getElementById('expenseModalTitle').textContent = 'Edit Expense';
        document.getElementById('expenseFormMethod').value = 'PUT';
        document.getElementById('expenseForm').action = '{{ url('office-expenses') }}/' + id;
        document.getElementById('expenseModal').classList.remove('hidden');
        lucide.createIcons();
    });
}

function closeExpenseModal() {
    document.getElementById('expenseModal').classList.add('hidden');
}

function showDescription(text) {
    document.getElementById('fullDescText').textContent = text;
    document.getElementById('descModal').classList.remove('hidden');
    lucide.createIcons();
}
</script>
@endpush
