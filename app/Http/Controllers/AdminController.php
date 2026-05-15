<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Helpers\BaseResponse;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    /**
     * Display a listing of guru users.
     */
    public function index(Request $request)
    {
        $gurus = User::where('role', 'guru')
            ->select('id', 'name', 'email', 'is_active', 'created_at')
            ->orderBy('name')
            ->paginate($request->query('per_page', 10));

        return BaseResponse::OK($gurus, 'Guru list retrieved successfully');
    }

    /**
     * Get subscription history with optional status filter.
     */
    public function history(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $status = $request->query('status');

        $query = Subscription::with('user')
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status); // FIX: was 'status' .$status (concatenation bug)
        }

        $histories = $query->paginate($perPage);

        return BaseResponse::OK($histories, 'Subscription history retrieved successfully');
    }

    /**
     * Display the specified guru detail.
     */
    public function show($id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);

        return BaseResponse::OK([
            'id' => $guru->id,
            'name' => $guru->name,
            'email' => $guru->email,
            'phone_number' => $guru->phone_number,
            'role' => $guru->role,
            'is_active' => $guru->is_active,
            'created_at' => $guru->created_at,
            'teacher_id' => 'Guru' . $guru->id,
        ], 'Guru detail retrieved successfully');
    }

    /**
     * Get financial summary (income, expenses, net profit).
     */
    public function keuangan(Request $request)
    {
        $year = $request->query('year', Carbon::now()->year);

        $totalIncome = Subscription::where('status', 'paid')
            ->whereYear('created_at', $year)
            ->sum('amount');

        $totalExpenses = Expense::whereYear('date', $year)
            ->sum('amount');

        $netProfit = $totalIncome - $totalExpenses;

        $bulanan = [];
        for ($m = 1; $m <= 12; $m++) { // FIX: was $b = 1 (wrong variable)
            $income = Subscription::where('status', 'paid')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->sum('amount');

            $expense = Expense::whereYear('date', $year)
                ->whereMonth('date', $m)
                ->sum('amount');

            $bulanan[] = [
                'month' => Carbon::create($year, $m)->format('M'),
                'income' => $income,
                'expense' => $expense,
            ];
        }

        return BaseResponse::OK([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpenses,
            'net_profit' => $netProfit,
            'chart_data' => $bulanan,
        ], 'Financial summary retrieved successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return BaseResponse::OK(null, 'User deleted successfully');
    }
}
