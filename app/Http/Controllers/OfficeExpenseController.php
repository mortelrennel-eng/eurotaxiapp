<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficeExpenseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to = $request->input('date_to', date('Y-m-d'));
        $page = max(1, (int) $request->input('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $query = DB::table('expenses as e')
            ->whereNull('e.deleted_at')
            ->leftJoin('users as u', 'e.recorded_by', '=', 'u.id')
            ->leftJoin('units as un', 'e.unit_id', '=', 'un.id')
            ->leftJoin('users as creator', 'e.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'e.updated_by', '=', 'editor.id')
            ->select('e.*', 'u.full_name as recorded_by_name', 'un.plate_number', 'creator.full_name as creator_name', 'editor.full_name as editor_name')
            ->whereBetween('e.date', [$date_from, $date_to]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('e.description', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('e.category', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search]);
            });
        }
        if (!empty($category)) {
            $query->where('e.category', $category);
        }

        $total = $query->count();
        $expenses = $query->orderByDesc('e.date')->offset($offset)->limit($limit)->get();

        $totals = DB::table('expenses')
            ->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])
            ->selectRaw('SUM(amount) as total_amount, COUNT(*) as total_count')
            ->first();

        $categories = DB::table('expenses')->whereNull('deleted_at')->distinct()->pluck('category');

        $thisMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        $thisMonthAmount = DB::table('expenses')
            ->whereNull('deleted_at')
            ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$thisMonth])
            ->sum('amount') ?? 0;
            
        $lastMonthAmount = DB::table('expenses')
            ->whereNull('deleted_at')
            ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$lastMonth])
            ->sum('amount') ?? 0;

        $changePercent = 0;
        if ($lastMonthAmount > 0) {
            $changePercent = round((($thisMonthAmount - $lastMonthAmount) / $lastMonthAmount) * 100, 1);
        }

        $stats = [
            'this_month' => $thisMonthAmount,
            'last_month' => $lastMonthAmount,
            'monthly_change' => $changePercent,
            'total_records' => DB::table('expenses')->whereNull('deleted_at')->count(),
            'by_category' => DB::table('expenses')
                ->selectRaw('category, COUNT(*) as count, SUM(amount) as total')
                ->whereNull('deleted_at')
                ->whereBetween('date', [$date_from, $date_to])
                ->groupBy('category')
                ->get(),
        ];

        if ($request->wantsJson() || $request->input('format') === 'json') {
            if ($request->route('id')) {
                $expense = DB::table('expenses')->where('id', $request->route('id'))->first();
                return response()->json($expense);
            }
            return response()->json(['expenses' => $expenses, 'stats' => $stats]);
        }

        $pagination = [
            'page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => $total,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($total / $limit),
            'prev_page' => $page - 1,
            'next_page' => $page + 1,
        ];

        // Get units for dropdown
        $units = DB::table('units')
            ->where('status', 'active')
            ->select('id', 'plate_number')
            ->orderBy('plate_number')
            ->get();

        return view('office-expenses.index', compact('expenses', 'pagination', 'search', 'category', 'date_from', 'date_to', 'totals', 'categories', 'stats', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'reference_number' => 'nullable|string',
            'unit_id' => 'nullable|integer',
        ]);

        // Use Eloquent to trigger TrackChanges trait
        Expense::create([
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'date' => $request->date,
            'reference_number' => $request->reference_number,
            'unit_id' => $request->unit_id ?: null,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('office-expenses.index')->with('success', 'Expense added successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'reference_number' => 'nullable|string',
            'unit_id' => 'nullable|integer',
        ]);

        // Use Eloquent to trigger TrackChanges trait
        $expense = Expense::findOrFail($id);
        $expense->update([
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'date' => $request->date,
            'reference_number' => $request->reference_number,
            'unit_id' => $request->unit_id ?: null,
        ]);

        return redirect()->route('office-expenses.index')->with('success', 'Expense updated successfully');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return redirect()->route('office-expenses.index')->with('success', 'Expense archived successfully');
    }

    public function approve(Request $request, $id)
    {
        // Use Eloquent to trigger TrackChanges trait
        $expense = Expense::findOrFail($id);
        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('office-expenses.index')->with('success', 'Expense approved successfully');
    }

    public function reject(Request $request, $id)
    {
        // Use Eloquent to trigger TrackChanges trait
        $expense = Expense::findOrFail($id);
        $expense->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('office-expenses.index')->with('success', 'Expense rejected successfully');
    }
}
