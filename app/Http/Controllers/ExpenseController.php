<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expense = Expense::latest()->paginate(10);
        return response() -> json($expense);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate= $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255'
        ]);

        $validate['user_id'] = Auth::id();

        $expense = Expense::create($validate);

        return response() -> json([
            'message' => 'pengeluaran berhasil ditambahkan',
            'expense'=>$expense]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $expense = Expense::findOrFail($id);
        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);

        $validate= $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255'
        ]);

        $expense->update($validate);

        return response() -> json([
            'message' => 'Pengeluaran berhasil diperbarui',
            'expense' => $expense
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return response() -> json(['message' => 'Pengeluaran berhasil dihapus']);
    }

    public function indexView(){
        $expenses = Expense::latest()->paginate(10);
        
    }
}
