<?php

namespace App\Http\Controllers\Web;

use App\Models\Club;
use App\Models\ClubSaasSubscription;
use App\Models\SaasPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AcademyRegistrationController extends Controller
{
    /** Landing page — show SaaS plans */
    public function home()
    {
        $plans = SaasPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('web.home', compact('plans'));
    }

    /** Step 1 – show registration form, optionally pre-select a plan */
    public function showRegister(Request $request)
    {
        $plans     = SaasPlan::where('is_active', true)->orderBy('sort_order')->get();
        $selected  = $request->query('plan');

        return view('web.register', compact('plans', 'selected'));
    }

    /** Step 2 – handle form submission */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Account
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            // Academy
            'academy_name'          => ['required', 'string', 'max:255'],
            'academy_address'       => ['required', 'string'],
            'academy_sport'         => ['required', 'in:padel,tennis,squash,pickleball'],
            // Plan
            'plan_id'               => ['required', 'exists:saas_plans,id'],
            'billing_cycle'         => ['required', 'in:monthly,yearly'],
        ]);

        $plan  = SaasPlan::findOrFail($validated['plan_id']);
        $cycle = $validated['billing_cycle'];
        $price = $plan->priceFor($cycle);

        DB::transaction(function () use ($validated, $plan, $cycle, $price) {
            // Create the owner user
            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'phone'     => $validated['phone'] ?? null,
                'password'  => Hash::make($validated['password']),
                'role'      => 'academy_admin',
                'is_active' => true,
            ]);

            // Create the club
            $club = Club::create([
                'name'                => $validated['academy_name'],
                'sport_type'          => $validated['academy_sport'],
                'address'             => $validated['academy_address'],
                'subscription_status' => 'inactive',
                'registration_status' => 'pending',
            ]);

            // Attach owner
            $user->clubs()->attach($club->id, ['role' => 'owner']);

            // SaaS subscription (pending approval)
            $endsAt = $cycle === 'yearly' ? now()->addYear() : now()->addMonth();
            ClubSaasSubscription::create([
                'club_id'       => $club->id,
                'saas_plan_id'  => $plan->id,
                'billing_cycle' => $cycle,
                'amount_paid'   => $price,
                'starts_at'     => now()->toDateString(),
                'ends_at'       => $endsAt->toDateString(),
                'status'        => 'pending',
            ]);

            // Log the user in
            Auth::guard('web')->login($user);
        });

        return redirect()->route('register.pending')
            ->with('success', 'Registration submitted! Your academy is under review. You will be notified once approved.');
    }

    /** Pending approval page */
    public function pending()
    {
        return view('web.pending');
    }
}
