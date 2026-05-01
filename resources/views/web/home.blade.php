@extends('web.layout')

@section('title', config('app.name') . ' — Sports Academy Management')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white py-24 px-4">
    <div class="max-w-4xl mx-auto text-center">
        <span class="inline-block bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full mb-4 uppercase tracking-widest">
            All-in-one Platform
        </span>
        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">
            Run your sports academy<br class="hidden md:block"/> like a pro
        </h1>
        <p class="text-lg md:text-xl text-white/80 mb-10 max-w-2xl mx-auto">
            {{ config('app.name') }} gives padel, tennis and squash academies everything they need —
            court bookings, coach management, player matchmaking and payments in one place.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register.academy') }}"
               class="bg-white text-brand-700 font-bold px-8 py-4 rounded-xl shadow hover:shadow-lg hover:bg-gray-50 transition text-lg">
                Start Free 14-Day Trial
            </a>
            <a href="#plans"
               class="border border-white/50 text-white font-medium px-8 py-4 rounded-xl hover:bg-white/10 transition text-lg">
                View Pricing
            </a>
        </div>
        <p class="mt-6 text-white/60 text-sm">No credit card required · Cancel anytime</p>
    </div>
</section>

{{-- Features --}}
<section class="py-20 px-4 bg-white">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-900">Everything your academy needs</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['🎾', 'Court Bookings',       'Manage court slots, accept bookings and handle open-match matchmaking automatically.'],
                ['👨‍🏫', 'Coach Management',      'Coaches apply to sessions, track their assignments and view player skill levels.'],
                ['💳', 'Integrated Payments',   'Paymob-powered payments for bookings, session enrollments and subscriptions.'],
                ['📊', 'Analytics Dashboard',   'Track revenue, occupancy rates and player retention from a single panel.'],
                ['🏅', 'Skill Matchmaking',     'Filter open matches by skill bracket so players always find the right game.'],
                ['🔔', 'Smart Notifications',   'Automated emails for bookings, approvals, subscription renewals and more.'],
            ] as [$icon, $title, $desc])
            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                <div class="text-4xl mb-3">{{ $icon }}</div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $title }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Pricing / Plans --}}
<section id="plans" class="py-20 px-4 bg-gray-50">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-3">Simple, transparent pricing</h2>
            <p class="text-gray-500">Start with a 14-day free trial on any plan. No credit card needed.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-{{ $plans->count() }} gap-8 items-stretch">
            @foreach($plans as $index => $plan)
                @php $popular = $index === 1; @endphp
                <div class="relative bg-white rounded-2xl border {{ $popular ? 'border-brand-500 shadow-xl ring-2 ring-brand-500' : 'border-gray-200 shadow' }} flex flex-col overflow-hidden">
                    @if($popular)
                        <div class="bg-brand-600 text-white text-xs font-bold text-center py-1.5 uppercase tracking-widest">
                            Most Popular
                        </div>
                    @endif
                    <div class="p-8 flex flex-col flex-1">
                        <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                        <p class="text-gray-400 text-sm mt-1 mb-6">{{ $plan->description }}</p>

                        <div class="mb-2">
                            <span class="text-4xl font-extrabold text-gray-900">${{ number_format($plan->monthly_price, 0) }}</span>
                            <span class="text-gray-400 text-sm">/month</span>
                        </div>
                        <p class="text-gray-400 text-xs mb-6">
                            or ${{ number_format($plan->yearly_price, 0) }}/year
                            <span class="text-green-600 font-semibold">
                                (save ${{ number_format(($plan->monthly_price * 12) - $plan->yearly_price, 0) }})
                            </span>
                        </p>

                        @if($plan->features)
                            <ul class="space-y-2 mb-8 flex-1">
                                @foreach((array) $plan->features as $feature)
                                    <li class="flex items-center gap-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <a href="{{ route('register.academy', ['plan' => $plan->id]) }}"
                           class="mt-auto block text-center {{ $popular ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-gray-900 text-white hover:bg-gray-700' }} font-semibold py-3 px-6 rounded-xl transition">
                            Get Started
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 px-4 bg-brand-600 text-white text-center">
    <h2 class="text-3xl font-bold mb-4">Ready to grow your academy?</h2>
    <p class="text-white/80 mb-8">Join hundreds of clubs already using {{ config('app.name') }}.</p>
    <a href="{{ route('register.academy') }}"
       class="bg-white text-brand-700 font-bold px-8 py-4 rounded-xl hover:bg-gray-100 transition text-lg">
        Start Your Free Trial →
    </a>
</section>

@endsection
