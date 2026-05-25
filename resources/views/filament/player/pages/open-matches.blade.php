<x-filament-panels::page>
@php
    $matches = $this->getOpenMatches();
    $clubs = $this->getClubs();
@endphp

<style>
.om-filters { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:20px; }
.om-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.om-card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:16px; }
.dark .om-card { background:#111827; border-color:#374151; }
.om-pay-overlay {
    position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:9999;
    display:flex; align-items:center; justify-content:center; padding:20px;
}
.om-pay-box { background:#fff; border-radius:16px; padding:16px; width:100%; max-width:720px; }
.om-pay-box iframe { width:100%; min-height:480px; border:0; border-radius:12px; }
</style>

<div class="om-filters">
    <div>
        <label class="text-sm font-medium">Club</label>
        <select wire:model.live="clubId" class="mt-1 block w-48 rounded-lg border-gray-300 text-sm">
            <option value="">All clubs</option>
            @foreach($clubs as $club)
                <option value="{{ $club->id }}">{{ $club->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">Sport</label>
        <select wire:model.live="sportType" class="mt-1 block w-40 rounded-lg border-gray-300 text-sm">
            <option value="">Any</option>
            <option value="padel">Padel</option>
            <option value="tennis">Tennis</option>
            <option value="squash">Squash</option>
            <option value="pickleball">Pickleball</option>
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">Your skill</label>
        <select wire:model.live="skillLevel" class="mt-1 block w-32 rounded-lg border-gray-300 text-sm">
            <option value="">Any</option>
            @for($i = 1; $i <= 7; $i++)
                <option value="{{ $i }}">Level {{ $i }}</option>
            @endfor
        </select>
    </div>
</div>

@if($paymentIframeUrl)
    <div class="om-pay-overlay">
        <div class="om-pay-box">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-bold">Complete payment</h3>
                <button wire:click="closePayment" type="button" class="text-sm text-gray-500">Close</button>
            </div>
            <iframe src="{{ $paymentIframeUrl }}" title="Paymob checkout"></iframe>
        </div>
    </div>
@endif

@if($matches->isEmpty())
    <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
        No open matches match your filters right now.
    </div>
@else
    <div class="om-grid">
        @foreach($matches as $match)
            <div class="om-card">
                <div class="text-xs text-gray-500">{{ $match->court?->club?->name }}</div>
                <div class="text-lg font-bold mt-1">{{ $match->court?->name ?? 'Court' }}</div>
                <div class="text-sm mt-2">{{ $match->start_time?->format('D, M j · H:i') }}</div>
                <div class="text-sm text-gray-600">{{ ucfirst($match->sport_type) }} · {{ $match->capacity_slots_used ?? 0 }}/{{ $match->max_players }} players</div>
                @if($match->skill_min || $match->skill_max)
                    <div class="text-xs mt-1">Skill {{ $match->skill_min ?? '?' }}–{{ $match->skill_max ?? '?' }}</div>
                @endif
                <button
                    wire:click="joinMatch({{ $match->id }})"
                    type="button"
                    class="mt-4 w-full rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    Join match
                </button>
            </div>
        @endforeach
    </div>
@endif
</x-filament-panels::page>
