@extends('web.layout')

@section('title', 'Registration Submitted — ' . config('app.name'))

@section('content')

<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-20">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-lg border border-gray-200 p-10 text-center">

        <div class="w-20 h-20 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-extrabold text-gray-900 mb-3">Registration Submitted!</h1>
        <p class="text-gray-500 mb-6 leading-relaxed">
            Your academy has been submitted for review. Our team will verify your details and
            <strong>approve your account within 24 hours</strong>. You'll receive an email notification once approved.
        </p>

        <div class="bg-brand-50 border border-brand-200 rounded-xl p-4 mb-8 text-left text-sm text-brand-800">
            <p class="font-semibold mb-1">What happens next?</p>
            <ol class="list-decimal list-inside space-y-1 text-brand-700">
                <li>Our team reviews your academy details</li>
                <li>You receive an approval email</li>
                <li>A <strong>14-day free trial</strong> starts automatically</li>
                <li>Log in to your academy portal and get started</li>
            </ol>
        </div>

        <a href="{{ url('/admin/login') }}"
           class="block w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl transition mb-3">
            Go to Academy Portal Login
        </a>
        <a href="{{ route('home') }}" class="text-sm text-gray-400 hover:text-brand-600 transition">
            ← Back to home
        </a>
    </div>
</div>

@endsection
