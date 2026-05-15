<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubcriptionRequest;
use App\Http\Requests\UpdateSubcriptionRequest;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Plan;
use App\Helpers\BaseResponse;

class SubscriptionController extends Controller
{
    /**
     * Get all available plans.
     */
    public function plan()
    {
        $plans = Plan::all(['type', 'duration_months', 'price']);
        return BaseResponse::OK($plans, 'Plans retrieved successfully');
    }

    /**
     * List all subscriptions (admin only).
     */
    public function index(Request $request)
    {
        if (Gate::denies('viewAny', Subscription::class)) {
            return BaseResponse::Forbidden('You are not authorized to view subscriptions');
        }

        $subscriptions = Subscription::with('user')
            ->latest()
            ->paginate($request->query('per_page', 10));

        return BaseResponse::OK($subscriptions, 'Subscriptions retrieved successfully');
    }

    /**
     * Get current user's subscription history.
     */
    public function subscription(Request $request)
    {
        $user = Auth::user();
        $subs = Subscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 10));

        return BaseResponse::OK($subs, 'User subscriptions retrieved successfully'); // FIX: was missing return
    }

    /**
     * Create a new subscription.
     */
    public function store(StoreSubcriptionRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $subscription = Subscription::create($data);

        return BaseResponse::OK($subscription, 'Subscription created successfully');
    }

    /**
     * Show a specific subscription.
     */
    public function show(Subscription $subscription)
    {
        if (Gate::denies('view', $subscription)) {
            return BaseResponse::Forbidden('You are not authorized to view this subscription');
        }

        $subscription->load('user');
        return BaseResponse::OK($subscription, 'Subscription retrieved successfully');
    }

    /**
     * Update a subscription.
     */
    public function update(UpdateSubcriptionRequest $request, Subscription $subscription)
    {
        if (Gate::denies('update', $subscription)) {
            return BaseResponse::Forbidden('You are not authorized to update this subscription');
        }

        $subscription->update($request->validated());
        return BaseResponse::OK($subscription, 'Subscription updated successfully');
    }

    /**
     * Delete a subscription.
     */
    public function destroy(Subscription $subscription)
    {
        Gate::authorize('delete', $subscription);
        $subscription->delete();

        return BaseResponse::OK(null, 'Subscription deleted successfully');
    }
}
