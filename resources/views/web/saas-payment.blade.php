@extends('web.layout')

@section('title', 'Complete Payment — ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-gray-50 py-14 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">
            <h1 class="text-2xl font-extrabold text-gray-900 mb-2">Complete your subscription payment</h1>
            <p class="text-gray-500 mb-6">Secure checkout powered by Paymob. After payment, your academy stays pending admin approval.</p>

            @if(!empty($iframeUrl))
                <iframe src="{{ $iframeUrl }}" class="w-full min-h-[520px] rounded-xl border border-gray-200" title="Paymob checkout"></iframe>
            @else
                <p class="text-red-600">Payment session could not be started. Please contact support.</p>
            @endif

            <a href="{{ route('register.pending') }}" class="inline-block mt-6 text-sm text-brand-600 font-semibold">Back to registration status</a>
        </div>
    </div>
</div>
@endsection
