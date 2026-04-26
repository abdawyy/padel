<x-filament-panels::page>
@php
    $matches = $this->getMatches();
    $userId  = auth()->id();
@endphp

<style>
.mx-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 16px;
}
.mx-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: transform .15s, box-shadow .15s;
}
.mx-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.10); }
.dark .mx-card { background: #111827; border-color: #374151; }
.mx-banner { height: 6px; }
.mx-body { padding: 18px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
.mx-title { font-size: 17px; font-weight: 800; color: #111827; line-height: 1.3; }
.dark .mx-title { color: #f9fafb; }
.mx-sub { font-size: 12px; color: #6b7280; }
.mx-row { display: flex; align-items: center; gap: 7px; font-size: 13px; color: #374151; }
.dark .mx-row { color: #d1d5db; }
.mx-chip {
    display: inline-flex; align-items: center; border-radius: 999px;
    padding: 3px 10px; font-size: 11px; font-weight: 700;
}
.mx-status-pending    { background:#fef3c7; color:#92400e; }
.mx-status-confirmed  { background:#d1fae5; color:#065f46; }
.mx-status-completed  { background:#dbeafe; color:#1e40af; }
.mx-status-cancelled  { background:#ffe4e6; color:#9f1239; }
.mx-owner-badge { background:#ede9fe; color:#5b21b6; }
.mx-footer { border-top: 1px solid #f3f4f6; padding: 12px 18px; display: flex; gap: 8px; flex-wrap: wrap; }
.dark .mx-footer { border-color: #1f2937; }
.mx-btn {
    flex: 1; min-width: 100px; padding: 8px 14px; border-radius: 10px;
    font-size: 13px; font-weight: 700; cursor: pointer; border: none;
    text-align: center; transition: opacity .15s;
}
.mx-btn:hover { opacity: .85; }
.mx-btn-outline { background: #f9fafb; color: #374151; border: 1px solid #e5e7eb; }
.mx-btn-danger  { background: #ffe4e6; color: #be123c; }
.mx-empty {
    padding: 48px 20px; text-align: center;
    background: #fff; border: 2px dashed #d1d5db;
    border-radius: 18px; color: #6b7280;
}
.dark .mx-empty { background: #111827; border-color: #374151; }
/* Modal */
.mx-modal-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 9999;
    align-items: center; justify-content: center;
}
.mx-modal-backdrop.open { display: flex; }
.mx-modal {
    background: #fff; border-radius: 20px; padding: 28px;
    width: 100%; max-width: 460px; box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: mxSlideUp .2s ease;
}
.dark .mx-modal { background: #1f2937; }
@keyframes mxSlideUp { from { transform: translateY(16px); opacity:0; } to { transform: translateY(0); opacity:1; } }
.mx-modal-title { font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 14px; }
.dark .mx-modal-title { color: #f9fafb; }
.mx-modal-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
.dark .mx-modal-row { border-color: #374151; }
.mx-modal-label { color: #6b7280; }
.mx-modal-value { font-weight: 600; color: #111827; text-align: right; max-width: 260px; }
.dark .mx-modal-value { color: #f9fafb; }
.mx-modal-close {
    margin-top: 18px; width: 100%; padding: 10px;
    background: #f3f4f6; border: none; border-radius: 10px;
    font-weight: 700; cursor: pointer; font-size: 14px; color: #374151;
}
</style>

<!-- Detail Modal -->
<div class="mx-modal-backdrop" id="mxModal" onclick="if(event.target===this)closeMxModal()">
    <div class="mx-modal">
        <div class="mx-modal-title" id="mxModalTitle"></div>
        <div id="mxModalBody"></div>
        <button class="mx-modal-close" onclick="closeMxModal()">Close</button>
    </div>
</div>

@if($matches->isEmpty())
    <div class="mx-empty">
        <div style="font-size:40px;">🏓</div>
        <div style="font-weight:700; font-size:16px; margin-top:8px;">No matches yet</div>
        <div style="font-size:13px; margin-top:4px;">Your bookings and matches will appear here.</div>
    </div>
@else
    <div class="mx-grid">
        @foreach($matches as $match)
        @php
            $isOwner    = $match->owner_user_id === $userId;
            $canAct     = in_array($match->status, ['pending','confirmed']);
            $typeLabel  = ucwords(str_replace('_', ' ', $match->match_type ?? ''));
            $levelLabel = $match->skill_level ? 'Level ' . $match->skill_level : 'All levels';
            $bClr       = $match->match_type === 'private' ? '#8b5cf6' : '#0ea5e9';
            $dtFmt      = \Carbon\Carbon::parse($match->start_time)->format('D d M Y · H:i');
            $price      = number_format($match->total_price ?? 0, 2);
        @endphp

        <article class="mx-card">
            <div class="mx-banner" style="background:{{ $bClr }};"></div>
            <div class="mx-body">

                <div>
                    <div class="mx-title">{{ $match->court->name ?? '—' }}</div>
                    <div class="mx-sub">{{ $match->court->club->name ?? '—' }}
                        @if($match->coach) · 👨‍🏫 {{ $match->coach->name }}@endif
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:5px;">
                    <span class="mx-chip" style="background:{{ $bClr }}1a; color:{{ $bClr }};">{{ $typeLabel }}</span>
                    <span class="mx-chip mx-status-{{ $match->status }}">{{ ucfirst($match->status) }}</span>
                    @if($isOwner)
                        <span class="mx-chip mx-owner-badge">Organiser</span>
                    @endif
                    <span class="mx-chip" style="background:#f3f4f6; color:#6b7280;">{{ $levelLabel }}</span>
                </div>

                <div class="mx-row">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $dtFmt }}</span>
                </div>

                <div class="mx-row">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>${{ $price }}</span>
                </div>

                <div class="mx-row">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>{{ $match->owner->name ?? '—' }}</span>
                </div>

            </div>

            <div class="mx-footer">
                <button class="mx-btn mx-btn-outline" onclick="openMxModal('{{ addslashes($match->court->name ?? '') }}', '{{ addslashes($match->court->club->name ?? '') }}', '{{ addslashes($match->owner->name ?? '') }}', '{{ addslashes($match->coach->name ?? 'No coach') }}', '{{ $dtFmt }}', '{{ $typeLabel }}', '{{ $levelLabel }}', '{{ ucfirst($match->status) }}', '${{ $price }}')">
                    View Details
                </button>
                @if($canAct)
                    @if($isOwner)
                        <button class="mx-btn mx-btn-danger"
                            onclick="confirmCancel({{ $match->id }}, '{{ addslashes($match->court->name ?? 'court') }}')">
                            Cancel
                        </button>
                    @else
                        <button class="mx-btn mx-btn-danger"
                            onclick="confirmLeave({{ $match->id }}, '{{ addslashes($match->court->name ?? 'match') }}')">
                            Leave
                        </button>
                    @endif
                @endif
            </div>
        </article>
        @endforeach
    </div>
@endif

<script>
function openMxModal(court, club, owner, coach, dt, type, level, status, price) {
    document.getElementById('mxModalTitle').textContent = court;
    document.getElementById('mxModalBody').innerHTML = [
        mrow('Club', club), mrow('Organiser', owner),
        mrow('Coach', coach || 'No coach'), mrow('Date & Time', dt),
        mrow('Match Type', type), mrow('Level', level),
        mrow('Status', status), mrow('Price', price)
    ].join('');
    document.getElementById('mxModal').classList.add('open');
}
function mrow(label, value) {
    return `<div class="mx-modal-row"><span class="mx-modal-label">${label}</span><span class="mx-modal-value">${value}</span></div>`;
}
function closeMxModal() {
    document.getElementById('mxModal').classList.remove('open');
}
function confirmCancel(id, court) {
    if (!confirm('Cancel this booking at "' + court + '"? This cannot be undone.')) return;
    @this.cancelBooking(id);
}
function confirmLeave(id, court) {
    if (!confirm('Leave the match at "' + court + '"? This cannot be undone.')) return;
    @this.leaveMatch(id);
}
</script>

</x-filament-panels::page>
