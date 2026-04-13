<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SparePartController extends Controller
{
    /**
     * Get all spare parts (API)
     */
    public function index()
    {
        $parts = SparePart::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $parts
        ]);
    }

    /**
     * Store or Update a spare part
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer|exists:spare_parts,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if (isset($data['id'])) {
            $part = SparePart::find($data['id']);
            $part->update($data);
        } else {
            $part = SparePart::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Part saved successfully',
            'data' => $part
        ]);
    }

    /**
     * Delete a spare part
     */
    public function destroy($id)
    {
        $part = SparePart::findOrFail($id);
        $part->delete();

        return response()->json([
            'success' => true,
            'message' => 'Part deleted successfully'
        ]);
    }
}
