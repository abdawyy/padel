<x-filament-panels::page>
@php
    $clubs = $this->getClubs();
    $courts = $this->getCourts();
    $slots = $this->getAvailableSlots();
@endphp

<style>
.bc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:8px; margin-top:12px; }
.bc-slot { padding:10px; border:1px solid #e5e7eb; border-radius:10px; text-align:center; cursor:pointer; font-size:13px; }
.bc-slot.selected { border-color:#2563eb; background:#eff6ff; }
.bc-pay-overlay { position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:9999; display:flex; align-items:center; justify-content:center; padding:20px; }
.bc-pay-box { background:#fff; border-radius:16px; padding:16px; width:100%; max-width:720px; }
.bc-pay-box iframe { width:100%; min-height:480px; border:0; border-radius:12px; }
</style>

@if($paymentIframeUrl)
    <div class="bc-pay-overlay">
        <div class="bc-pay-box">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-bold">Pay your share</h3>
                <button wire:click="closePayment" type="button" class="text-sm text-gray-500">Close</button>
            </div>
            <iframe src="{{ $paymentIframeUrl }}" title="Paymob checkout"></iframe>
        </div>
    </div>
@endif

<div class="space-y-4 max-w-2xl">
    <div>
        <label class="text-sm font-medium">Club</label>
        <select wire:model.live="clubId" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
            <option value="">Select club</option>
            @foreach($clubs as $club)
                <option value="{{ $club->id }}">{{ $club->name }}</option>
            @endforeach
        </select>
    </div>

    @if($clubId)
    <div>
        <label class="text-sm font-medium">Court</label>
        <select wire:model.live="courtId" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
            <option value="">Select court</option>
            @foreach($courts as $court)
                <option value="{{ $court->id }}">{{ $court->name }} ({{ ucfirst($court->sport_type) }}) — {{ number_format($court->price_per_hour, 0) }} EGP/hr</option>
            @endforeach
        </select>
    </div>
    @endif

    @if($courtId)
    <div>
        <label class="text-sm font-medium">Date</label>
        <input type="date" wire:model.live="bookingDate" min="{{ now()->toDateString() }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm" />
    </div>

    <div>
        <label class="text-sm font-medium">Match type</label>
        <select wire:model="matchType" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
            <option value="private_match">Private match</option>
            <option value="open_match">Open match (others can join)</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium">Available times</label>
        @if($slots->isEmpty())
            <p class="text-sm text-gray-500 mt-2">No slots available for this date.</p>
        @else
            <div class="bc-grid">
                @foreach($slots as $slot)
                    <button
                        type="button"
                        wire:click="selectSlot('{{ $slot['start'] }}', '{{ $slot['end'] }}')"
                        class="bc-slot {{ $slotStart === $slot['start'] ? 'selected' : '' }}"
                    >
                        {{ $slot['label'] }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <div>
        <label class="text-sm font-medium">Notes (optional)</label>
        <textarea wire:model="notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 text-sm"></textarea>
    </div>

    <button
        wire:click="createBooking"
        type="button"
        @disabled(! $slotStart)
        class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white disabled:opacity-50"
    >
        Book &amp; pay
    </button>
    @endif
</div>
</x-filament-panels::page>
