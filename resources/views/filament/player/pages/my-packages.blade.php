<x-filament-panels::page>
@php
    $subs = $this->getSubscriptions();
@endphp

<style>
.pkg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}
.pkg-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: transform .15s, box-shadow .15s;
}
.pkg-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,.10);
}
.dark .pkg-card {
    background: #111827;
    border-color: #374151;
}
.pkg-card-banner {
    height: 8px;
}
.pkg-card-body {
    padding: 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.pkg-card-title {
    font-size: 18px;
    font-weight: 800;
    color: #111827;
    line-height: 1.2;
}
.dark .pkg-card-title { color: #f9fafb; }
.pkg-card-club {
    font-size: 13px;
    color: #6b7280;
}
.pkg-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 3px 10px;
    font-size: 12px;
    font-weight: 700;
    background: #f3f4f6;
    color: #374151;
    margin-right: 4px;
}
.pkg-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #374151;
}
.dark .pkg-row { color: #d1d5db; }
.pkg-progress-wrap {
    background: #e5e7eb;
    border-radius: 999px;
    height: 6px;
    overflow: hidden;
}
.pkg-progress-bar {
    height: 100%;
    border-radius: 999px;
}
.pkg-status-active    { background: #d1fae5; color: #065f46; }
.pkg-status-expired   { background: #ffe4e6; color: #9f1239; }
.pkg-status-suspended { background: #fef3c7; color: #92400e; }
.pkg-status-cancelled { background: #f3f4f6; color: #6b7280; }
.pkg-empty {
    padding: 40px 20px;
    text-align: center;
    background: #fff;
    border: 2px dashed #d1d5db;
    border-radius: 18px;
    color: #6b7280;
}
.dark .pkg-empty { background: #111827; border-color: #374151; }
</style>

@if($subs->isEmpty())
    <div class="pkg-empty">
        <div style="font-size:40px;">📦</div>
        <div style="font-weight:700; font-size:16px; margin-top:8px;">No packages yet</div>
        <div style="font-size:13px; margin-top:4px;">Contact your club to get a package assigned to your account.</div>
    </div>
@else
    <div class="pkg-grid">
        @foreach($subs as $sub)
        @php
            $expired   = $sub->expires_at && \Carbon\Carbon::parse($sub->expires_at)->isPast();
            $bannerClr = $this->typeColor($sub->package->type ?? 'custom');
            $daysLeft  = $sub->expires_at ? max(0, (int) now()->diffInDays(\Carbon\Carbon::parse($sub->expires_at), false)) : null;
            $totalDays = ($sub->starts_at && $sub->expires_at)
                ? max(1, (int) \Carbon\Carbon::parse($sub->starts_at)->diffInDays(\Carbon\Carbon::parse($sub->expires_at)))
                : 1;
            $pct = $daysLeft !== null ? min(100, round(($daysLeft / $totalDays) * 100)) : 100;
        @endphp

        <article class="pkg-card">
            <div class="pkg-card-banner" style="background:{{ $bannerClr }};"></div>
            <div class="pkg-card-body">

                <div>
                    <div class="pkg-card-title">{{ $sub->package->name ?? '—' }}</div>
                    <div class="pkg-card-club">{{ $sub->package->club->name ?? '—' }}</div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    <span class="pkg-chip" style="background:{{ $bannerClr }}1a; color:{{ $bannerClr }};">{{ ucfirst($sub->package->type ?? '—') }}</span>
                    <span class="pkg-chip pkg-status-{{ $sub->status }}">{{ ucfirst($sub->status) }}</span>
                </div>

                <div class="pkg-row">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ \Carbon\Carbon::parse($sub->starts_at)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($sub->expires_at)->format('d M Y') }}</span>
                </div>

                @if(!$expired && $daysLeft !== null)
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:11px; color:#6b7280; margin-bottom:4px;">
                            <span>Time remaining</span>
                            <span>{{ $daysLeft }} day{{ $daysLeft !== 1 ? 's' : '' }} left</span>
                        </div>
                        <div class="pkg-progress-wrap">
                            <div class="pkg-progress-bar" style="width:{{ $pct }}%; background:{{ $bannerClr }};"></div>
                        </div>
                    </div>
                @elseif($expired)
                    <div style="font-size:12px; font-weight:600; color:#be123c;">⚠ Expired</div>
                @endif

                @if($sub->sessions_remaining !== null)
                    <div class="pkg-row">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span><strong>{{ $sub->sessions_remaining }}</strong> session{{ $sub->sessions_remaining !== 1 ? 's' : '' }} remaining</span>
                    </div>
                @else
                    <div class="pkg-row">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Unlimited sessions</span>
                    </div>
                @endif

                @if($sub->notes)
                    <div style="font-size:12px; color:#6b7280; border-top:1px solid #f3f4f6; padding-top:8px;">{{ $sub->notes }}</div>
                @endif

            </div>
        </article>
        @endforeach
    </div>
@endif

</x-filament-panels::page>
