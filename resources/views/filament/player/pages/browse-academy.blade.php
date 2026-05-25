<x-filament-panels::page>
@php
    $clubs = $this->getClubs();
    $sessions = $this->getSessions();
@endphp

<style>
.ba-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; }
.ba-card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:16px; }
.ba-pay-overlay { position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:9999; display:flex; align-items:center; justify-content:center; padding:20px; }
.ba-pay-box { background:#fff; border-radius:16px; padding:16px; width:100%; max-width:720px; }
.ba-pay-box iframe { width:100%; min-height:480px; border:0; }
</style>

@if($paymentIframeUrl)
    <div class="ba-pay-overlay">
        <div class="ba-pay-box">
            <div class="flex justify-between mb-3">
                <h3 class="text-lg font-bold">{{ __('player.complete_payment') }}</h3>
                <button wire:click="closePayment" type="button" class="text-sm text-gray-500">{{ __('player.close') }}</button>
            </div>
            <iframe src="{{ $paymentIframeUrl }}" title="{{ __('player.complete_payment') }}"></iframe>
        </div>
    </div>
@endif

<div class="flex flex-wrap gap-4 mb-6">
    <div>
        <label class="text-sm font-medium">{{ __('player.club') }}</label>
        <select wire:model.live="clubId" class="mt-1 block w-48 rounded-lg border-gray-300 text-sm">
            <option value="">{{ __('player.all_clubs') }}</option>
            @foreach($clubs as $club)
                <option value="{{ $club->id }}">{{ $club->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">{{ __('player.date') }}</label>
        <input type="date" wire:model.live="sessionDate" class="mt-1 block rounded-lg border-gray-300 text-sm" />
    </div>
</div>

@if($sessions->isEmpty())
    <div class="rounded-xl border border-dashed p-10 text-center text-gray-500">{{ __('player.no_sessions') }}</div>
@else
    <div class="ba-grid">
        @foreach($sessions as $session)
            <div class="ba-card">
                <div class="text-xs text-gray-500">{{ $session->club?->name }}</div>
                <div class="text-lg font-bold mt-1">{{ $session->title }}</div>
                <div class="text-sm mt-2">{{ $session->start_time?->format('D, M j · H:i') }}</div>
                <div class="text-sm text-gray-600">{{ $session->players_count }}/{{ $session->max_players }} enrolled · {{ number_format($session->price_per_player, 0) }} EGP</div>
                @if($session->coach)
                    <div class="text-xs mt-1">Coach: {{ $session->coach->name }}</div>
                @endif
                <button
                    wire:click="enroll({{ $session->id }})"
                    type="button"
                    class="mt-4 w-full rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white"
                    @if($session->players_count >= $session->max_players) disabled @endif
                >
                    {{ (float) $session->price_per_player > 0 ? __('player.enroll_pay') : __('player.enroll_free') }}
                </button>
            </div>
        @endforeach
    </div>
@endif
</x-filament-panels::page>
