<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Expense;
use App\Models\User;
use App\Models\TeacherCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Helpers\BaseResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
     * Display a listing of all users (Guru & Siswa) with search and filters.
     */
    public function listUsers(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $statusPremium = $request->query('status_premium');
        $perPage = $request->query('per_page', 10);

        $query = User::query();

        // Exclude Admin from standard user list
        $query->where('role', '!=', 'admin');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($role) {
            $dbRole = $role === 'siswa' ? 'user' : $role;
            $query->where('role', $dbRole);
        }

        if ($statusPremium) {
            if ($statusPremium === 'premium') {
                $query->whereHas('subscriptions', function ($q) {
                    $q->where('status', 'active')
                      ->where('end_date', '>=', now());
                });
            } elseif ($statusPremium === 'gratis') {
                $query->whereDoesntHave('subscriptions', function ($q) {
                    $q->where('status', 'active')
                      ->where('end_date', '>=', now());
                });
            }
        }

        $users = $query->latest()->paginate($perPage);

        $formattedUsers = $users->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role === 'guru' ? 'Guru' : 'Siswa',
                'status_premium' => $user->is_premium ? 'Premium' : 'Gratis',
                'is_active' => (bool) $user->is_active,
                'created_at' => $user->created_at ? $user->created_at->translatedFormat('d F Y') : null,
            ];
        });

        return BaseResponse::OK($formattedUsers, 'Users list retrieved successfully');
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
        $user = User::with('teacherCredential')->findOrFail($id);

        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number ?? 'Belum mengisi',
            'role' => $user->role === 'guru' ? 'Guru' : 'Siswa',
            'is_active' => (bool) $user->is_active,
            'created_at' => $user->created_at ? $user->created_at->translatedFormat('d F Y') : null,
            'status_akun' => $user->is_premium ? 'Premium' : 'Gratis',
        ];

        if ($user->role === 'guru') {
            $credential = $user->teacherCredential;
            
            // Auto generate if teacher doesn't have credential yet (for consistency)
            if (!$credential) {
                $credential = TeacherCredential::create([
                    'user_id' => $user->id,
                    'teacher_id' => 'Exam' . $user->id,
                    'teacher_key' => Str::random(16),
                ]);
            }

            $response['kredensial'] = [
                'status_key' => 'Aktif',
                'tanggal_generate' => $credential->created_at ? $credential->created_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
                'terakhir_digunakan' => $user->currentAccessToken() && $user->currentAccessToken()->last_used_at
                    ? Carbon::parse($user->currentAccessToken()->last_used_at)->translatedFormat('d F Y, H:i') . ' WIB'
                    : 'Belum pernah digunakan',
                'teacher_id' => $credential->teacher_id,
                'teacher_key' => $credential->teacher_key,
            ];
        }

        return BaseResponse::OK($response, 'User detail retrieved successfully');
    }

    public function keuangan(Request $request)
    {
        $year = $request->query('year', Carbon::now()->year);

        // JOIN with plans table to sum the price where subscriptions payment status is 'paid'
        $totalIncome = (float) Subscription::join('plans', 'subscriptions.plan_type', '=', 'plans.type')
            ->where('subscriptions.payment_status', 'paid')
            ->whereYear('subscriptions.created_at', $year)
            ->sum('plans.price');

        $totalExpenses = (float) Expense::whereYear('date', $year)
            ->sum('amount');

        $netProfit = $totalIncome - $totalExpenses;

        $bulanan = [];
        for ($m = 1; $m <= 12; $m++) {
            $income = (float) Subscription::join('plans', 'subscriptions.plan_type', '=', 'plans.type')
                ->where('subscriptions.payment_status', 'paid')
                ->whereYear('subscriptions.created_at', $year)
                ->whereMonth('subscriptions.created_at', $m)
                ->sum('plans.price');

            $expense = (float) Expense::whereYear('date', $year)
                ->whereMonth('date', $m)
                ->sum('amount');

            $bulanan[] = [
                'month' => Carbon::create($year, $m)->translatedFormat('M'),
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
     * Get details of a single transaction (subscription).
     * For web: shows detailed transaction information
     */
    public function transactionDetail($id)
    {
        $subscription = Subscription::with('user', 'plan')->findOrFail($id);

        $response = [
            'id' => $subscription->id,
            'user' => [
                'id' => $subscription->user->id,
                'name' => $subscription->user->name,
                'email' => $subscription->user->email,
                'role' => $subscription->user->role === 'guru' ? 'Guru' : 'Siswa',
            ],
            'package' => [
                'type' => $subscription->plan_type,
                'name' => $subscription->plan->name ?? $subscription->plan_type,
                'price' => (float) $subscription->plan->price ?? 0,
                'duration_days' => $subscription->plan->duration_days ?? 30,
            ],
            'payment' => [
                'amount' => (float) $subscription->amount,
                'status' => $subscription->payment_status,
                'method' => $subscription->payment_method ?? 'N/A',
                'transaction_id' => $subscription->transaction_id ?? '-',
            ],
            'status' => $subscription->status,
            'started_at' => $subscription->start_date ? Carbon::parse($subscription->start_date)->translatedFormat('d F Y H:i') : null,
            'ended_at' => $subscription->end_date ? Carbon::parse($subscription->end_date)->translatedFormat('d F Y H:i') : null,
            'created_at' => $subscription->created_at ? $subscription->created_at->translatedFormat('d F Y H:i') : null,
            'updated_at' => $subscription->updated_at ? $subscription->updated_at->translatedFormat('d F Y H:i') : null,
        ];

        return BaseResponse::OK($response, 'Transaction detail retrieved successfully');
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
