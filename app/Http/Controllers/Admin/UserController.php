<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display all users with their credit balances.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');

        $query = User::query()
            ->withSum('creditTransactions', 'amount')
            ->withCount('requests')
            ->withCount(['requests as rated_count' => fn ($q) => $q->whereNotNull('rating')])
            ->withCount(['requests as up_count' => fn ($q) => $q->where('rating', 'up')])
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->get()->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'patron_status' => $user->patron_status,
            'patron_tier_cents' => $user->patron_tier_cents,
            'is_active_patron' => $user->isActivePatron(),
            'monthly_limit' => $user->getMonthlyRequestLimit(),
            'credit_balance' => (int) ($user->credit_transactions_sum_amount ?? 0),
            'request_count' => (int) $user->requests_count,
            'rated_count' => (int) $user->rated_count,
            'up_percentage' => $user->rated_count > 0
                ? round($user->up_count * 100.0 / $user->rated_count, 1)
                : null,
        ]);

        return Inertia::render('admin/Users', [
            'users' => $users,
            'search' => $search ?? '',
        ]);
    }

    /**
     * Adjust credits for a user.
     */
    public function adjustCredits(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $amount = (int) $validated['amount'];
        $type = $amount > 0 ? CreditTransaction::TYPE_BONUS : CreditTransaction::TYPE_ADJUSTMENT;

        $user->creditTransactions()->create([
            'amount' => $amount,
            'type' => $type,
            'description' => $validated['reason'],
        ]);

        $action = $amount > 0 ? 'Added' : 'Removed';
        $absAmount = abs($amount);

        return back()->with('success', "{$action} {$absAmount} credit(s) for {$user->name}.");
    }
}
