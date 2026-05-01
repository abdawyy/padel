@extends('web.layout')

@section('title', 'Register Your Academy — ' . config('app.name'))

@section('content')

<div class="min-h-screen bg-gray-50 py-14 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Register Your Academy</h1>
            <p class="text-gray-500">Create your account and submit your academy for approval.<br>
                You'll get a <strong>14-day free trial</strong> once approved.</p>
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 mb-6 text-sm">
                <p class="font-semibold mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.academy.submit') }}" class="space-y-8">
            @csrf

            {{-- Section: Your Account --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-7">
                <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 bg-brand-600 text-white rounded-full text-sm flex items-center justify-center font-bold">1</span>
                    Your Account
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="Ahmed Hassan" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="ahmed@example.com" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="+20 100 000 0000" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="Minimum 8 characters" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="Re-enter your password" />
                    </div>
                </div>
            </div>

            {{-- Section: Academy Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-7">
                <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 bg-brand-600 text-white rounded-full text-sm flex items-center justify-center font-bold">2</span>
                    Academy Details
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Academy Name <span class="text-red-500">*</span></label>
                        <input type="text" name="academy_name" value="{{ old('academy_name') }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="My Padel Club" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                        <input type="text" name="academy_address" value="{{ old('academy_address') }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                               placeholder="123 Main Street, Cairo, Egypt" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Primary Sport <span class="text-red-500">*</span></label>
                        <select name="academy_sport" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition bg-white">
                            @foreach(['padel' => '🎾 Padel', 'tennis' => '🎾 Tennis', 'squash' => '🏸 Squash', 'pickleball' => '🏓 Pickleball'] as $val => $label)
                                <option value="{{ $val }}" {{ old('academy_sport') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section: Choose Plan --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-7">
                <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 bg-brand-600 text-white rounded-full text-sm flex items-center justify-center font-bold">3</span>
                    Choose a Plan
                </h2>

                {{-- Billing toggle --}}
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-sm text-gray-600 font-medium">Billing:</span>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="billing_cycle" value="monthly" class="accent-brand-600"
                               {{ old('billing_cycle', 'monthly') === 'monthly' ? 'checked' : '' }} /> Monthly
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="billing_cycle" value="yearly" class="accent-brand-600"
                               {{ old('billing_cycle') === 'yearly' ? 'checked' : '' }} /> Yearly
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">Save ~17%</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($plans as $index => $plan)
                        <label class="cursor-pointer">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" class="sr-only peer"
                                   {{ (old('plan_id', $selected) == $plan->id || (!old('plan_id') && !$selected && $index === 0)) ? 'checked' : '' }} />
                            <div class="border-2 border-gray-200 rounded-xl p-5 peer-checked:border-brand-500 peer-checked:bg-brand-50 transition">
                                <p class="font-bold text-gray-900">{{ $plan->name }}</p>
                                <p class="text-2xl font-extrabold text-brand-600 mt-1">${{ number_format($plan->monthly_price, 0) }}<span class="text-sm text-gray-400 font-normal">/mo</span></p>
                                @if($plan->features)
                                    <ul class="mt-3 space-y-1">
                                        @foreach(array_slice((array) $plan->features, 0, 3) as $feature)
                                            <li class="text-xs text-gray-500 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 rounded-xl text-lg transition shadow-lg">
                Submit Academy Registration →
            </button>

            <p class="text-center text-sm text-gray-400">
                Already registered?
                <a href="{{ url('/admin/login') }}" class="text-brand-600 hover:underline font-medium">Login to your portal</a>
            </p>
        </form>
    </div>
</div>

@endsection
