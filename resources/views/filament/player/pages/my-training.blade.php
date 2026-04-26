<x-filament-panels::page>
@php
    $sessions = $this->getSessions();

    $youtubeMeta = static function (?string $url): ?array {
        if (!$url) {
            return null;
        }

        preg_match('/(?:youtube\.com\/watch\?(?:.*&)?v=|youtu\.be\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{11})/', $url, $matches);

        $videoId = $matches[1] ?? null;

        if (!$videoId) {
            return null;
        }

        return [
            'url' => $url,
            'embed_url' => 'https://www.youtube.com/embed/' . $videoId . '?rel=0&modestbranding=1',
            'thumbnail_url' => 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg',
        ];
    };

    $sessionCards = $sessions->map(function ($session) use ($youtubeMeta) {
        $videos = collect($session->training_video_urls)
            ->map(fn (string $url) => $youtubeMeta($url))
            ->filter()
            ->values();

        return [
            'id' => $session->id,
            'title' => $session->title,
            'club' => $session->club->name ?? '-',
            'court' => $session->court->name ?? '-',
            'coach' => $session->coach->name ?? 'No coach assigned',
            'type' => ucwords(str_replace('_', ' ', $session->session_type ?? '')),
            'sport' => ucfirst($session->sport_type ?? 'padel'),
            'level' => $session->skill_level ? 'Level ' . $session->skill_level : 'All levels',
            'status' => ucfirst($session->status),
            'status_key' => $session->status,
            'start' => $session->start_time?->format('D d M Y · H:i'),
            'end' => $session->end_time?->format('H:i'),
            'max_players' => $session->max_players,
            'enrolled' => $session->players_count ?? 0,
            'price' => number_format((float) $session->price_per_player, 2),
            'session_plan' => $session->session_plan,
            'notes' => $session->notes,
            'can_withdraw' => $session->status === 'scheduled',
            'is_today' => $session->start_time?->isToday() ?? false,
            'is_this_week' => ($session->start_time?->isFuture() ?? false) && (($session->start_time?->diffInDays(now()) ?? 99) <= 7) && !($session->start_time?->isToday() ?? false),
            'videos' => $videos->all(),
            'thumbnail_url' => $videos->first()['thumbnail_url'] ?? null,
            'banner_color' => match ($session->session_type) {
                'group_training' => '#6366f1',
                'private_training' => '#0ea5e9',
                'academy_class' => '#f59e0b',
                'coached_match' => '#10b981',
                'open_match' => '#8b5cf6',
                default => '#6b7280',
            },
        ];
    })->values();
@endphp

<style>
.tr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 18px; }
.tr-card { background: #fff; border-radius: 18px; border: 1px solid #e5e7eb; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 6px rgba(0,0,0,.06); transition: transform .15s, box-shadow .15s; }
.tr-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.10); }
.dark .tr-card { background: #111827; border-color: #374151; }
.tr-banner { height: 6px; }
.tr-body { padding: 18px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
.tr-title { font-size: 17px; font-weight: 800; color: #111827; line-height: 1.3; }
.dark .tr-title { color: #f9fafb; }
.tr-sub { font-size: 12px; color: #6b7280; }
.tr-row { display: flex; align-items: flex-start; gap: 7px; font-size: 13px; color: #374151; line-height: 1.5; }
.dark .tr-row { color: #d1d5db; }
.tr-chip { display: inline-flex; align-items: center; border-radius: 999px; padding: 3px 10px; font-size: 11px; font-weight: 700; }
.tr-status-scheduled { background: #dbeafe; color: #1e40af; }
.tr-status-ongoing { background: #fef3c7; color: #92400e; }
.tr-status-completed { background: #d1fae5; color: #065f46; }
.tr-status-cancelled { background: #ffe4e6; color: #9f1239; }
.tr-plan-snippet { padding: 12px 14px; background: #f8fafc; border-radius: 12px; font-size: 12px; color: #334155; line-height: 1.6; }
.dark .tr-plan-snippet { background: #0f172a; color: #cbd5e1; }
.tr-thumb { position: relative; cursor: pointer; border-radius: 10px; overflow: hidden; background: #000; aspect-ratio: 16 / 9; margin-top: 4px; }
.tr-thumb img { width: 100%; height: 100%; object-fit: cover; opacity: .86; display: block; }
.tr-play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
.tr-play span { width: 48px; height: 48px; background: rgba(255,255,255,.92); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,.3); }
.tr-footer { border-top: 1px solid #f3f4f6; padding: 12px 18px; display: flex; gap: 8px; flex-wrap: wrap; }
.dark .tr-footer { border-color: #1f2937; }
.tr-btn { flex: 1; min-width: 100px; padding: 8px 14px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; border: none; text-align: center; }
.tr-btn-outline { background: #f9fafb; color: #374151; border: 1px solid #e5e7eb; }
.tr-btn-danger { background: #ffe4e6; color: #be123c; }
.tr-empty { padding: 56px 20px; text-align: center; background: #fff; border: 2px dashed #d1d5db; border-radius: 18px; color: #6b7280; }
.dark .tr-empty { background: #111827; border-color: #374151; }
.tr-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 9998; align-items: center; justify-content: center; padding: 16px; }
.tr-modal-backdrop.open { display: flex; }
.tr-modal { background: #fff; border-radius: 24px; width: 100%; max-width: 720px; max-height: 92vh; overflow-y: auto; box-shadow: 0 24px 80px rgba(0,0,0,.25); }
.dark .tr-modal { background: #1f2937; }
.tr-mhead { padding: 22px 22px 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.tr-mtitle { font-size: 20px; font-weight: 900; color: #111827; line-height: 1.3; }
.dark .tr-mtitle { color: #f9fafb; }
.tr-mclose { width: 32px; height: 32px; border-radius: 50%; background: #f3f4f6; border: none; cursor: pointer; font-size: 18px; color: #6b7280; }
.tr-mbody { padding: 16px 22px 24px; display: flex; flex-direction: column; gap: 14px; }
.tr-mrow { display: flex; justify-content: space-between; align-items: flex-start; padding: 9px 0; border-bottom: 1px solid #f3f4f6; gap: 12px; }
.dark .tr-mrow { border-color: #374151; }
.tr-mlabel { font-size: 13px; color: #6b7280; }
.tr-mval { font-size: 13px; font-weight: 600; color: #111827; text-align: right; }
.dark .tr-mval { color: #f9fafb; }
.tr-mnotes { background: #f9fafb; border-radius: 10px; padding: 12px 14px; font-size: 13px; color: #374151; line-height: 1.7; }
.dark .tr-mnotes { background: #111827; color: #d1d5db; }
.tr-mvideo { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; background: #000; }
.tr-mvideo iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: none; border-radius: 12px; }
.tr-mno-video { background: #f3f4f6; border-radius: 12px; padding: 20px; text-align: center; color: #9ca3af; font-size: 13px; }
.tr-video-strip { display: grid; grid-template-columns: repeat(auto-fit, minmax(112px, 1fr)); gap: 10px; }
.tr-video-thumb-btn { border: 1px solid #e5e7eb; background: #fff; border-radius: 12px; overflow: hidden; cursor: pointer; padding: 0; text-align: left; }
.dark .tr-video-thumb-btn { background: #111827; border-color: #374151; }
.tr-video-thumb-btn.active { border-color: #0ea5e9; box-shadow: 0 0 0 2px rgba(14,165,233,.18); }
.tr-video-thumb-btn img { width: 100%; aspect-ratio: 16 / 9; object-fit: cover; display: block; }
.tr-video-thumb-btn span { display: block; padding: 8px 10px; font-size: 12px; font-weight: 700; color: #374151; }
.dark .tr-video-thumb-btn span { color: #e5e7eb; }
</style>

<div class="tr-modal-backdrop" id="trModal" onclick="if (event.target === this) closeTrModal()">
    <div class="tr-modal">
        <div class="tr-mhead">
            <div class="tr-mtitle" id="trModalTitle"></div>
            <button class="tr-mclose" type="button" onclick="closeTrModal()">&#x2715;</button>
        </div>
        <div class="tr-mbody">
            <div id="trVideoWrap"></div>
            <div id="trVideoStrip"></div>
            <div id="trInfoRows"></div>
            <div id="trPlanWrap"></div>
            <div id="trNotesWrap"></div>
        </div>
    </div>
</div>

@if ($sessionCards->isEmpty())
    <div class="tr-empty">
        <div style="font-size: 44px;">🎓</div>
        <div style="font-weight: 800; font-size: 17px; margin-top: 10px;">No training sessions yet</div>
        <div style="font-size: 13px; margin-top: 4px;">You have not been enrolled in any training sessions.</div>
    </div>
@else
    <div class="tr-grid">
        @foreach ($sessionCards as $card)
            <article class="tr-card">
                <div class="tr-banner" style="background: {{ $card['banner_color'] }};"></div>
                <div class="tr-body">
                    <div>
                        <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 5px;">
                            @if ($card['is_today'])
                                <span style="background: #fef9c3; color: #854d0e; border-radius: 999px; padding: 2px 9px; font-size: 11px; font-weight: 800;">TODAY</span>
                            @elseif ($card['is_this_week'])
                                <span style="background: #dbeafe; color: #1e40af; border-radius: 999px; padding: 2px 9px; font-size: 11px; font-weight: 800;">THIS WEEK</span>
                            @endif
                        </div>

                        <div class="tr-title">{{ $card['title'] }}</div>
                        <div class="tr-sub">{{ $card['club'] }} @if ($card['coach'] !== 'No coach assigned') · 👨‍🏫 {{ $card['coach'] }} @endif</div>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                        <span class="tr-chip" style="background: {{ $card['banner_color'] }}1a; color: {{ $card['banner_color'] }};">{{ $card['type'] }}</span>
                        <span class="tr-chip tr-status-{{ $card['status_key'] }}">{{ $card['status'] }}</span>
                        <span class="tr-chip" style="background: #f3f4f6; color: #6b7280;">{{ $card['level'] }}</span>
                    </div>

                    <div class="tr-row">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/></svg>
                        <span>{{ $card['start'] }} - {{ $card['end'] }}</span>
                    </div>

                    <div class="tr-row">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                        <span>{{ $card['court'] }}</span>
                    </div>

                    @if ($card['session_plan'])
                        <div class="tr-plan-snippet">
                            {{ \Illuminate\Support\Str::limit(str_replace(["\r\n", "\n", "\r"], ' ', $card['session_plan']), 120) }}
                        </div>
                    @endif

                    @if ($card['thumbnail_url'])
                        <div class="tr-thumb" onclick="openTrModal({{ $card['id'] }})">
                            <img src="{{ $card['thumbnail_url'] }}" alt="Training video thumbnail" loading="lazy">
                            <div class="tr-play">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#ef4444" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="tr-footer">
                    <button class="tr-btn tr-btn-outline" type="button" onclick="openTrModal({{ $card['id'] }})">View Session</button>
                    @if ($card['can_withdraw'])
                        <button class="tr-btn tr-btn-danger" type="button" onclick="confirmWithdraw({{ $card['id'] }}, @js($card['title']))">Withdraw</button>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endif

<script>
const trData = @js($sessionCards);
let trCurrentSessionId = null;
let trCurrentVideoIndex = 0;

function renderTrVideo(session, index) {
    const current = session.videos[index] ?? null;
    const videoWrap = document.getElementById('trVideoWrap');
    const videoStrip = document.getElementById('trVideoStrip');

    if (!current) {
        videoWrap.innerHTML = '<div class="tr-mno-video">No video linked for this session yet.</div>';
        videoStrip.innerHTML = '';
        return;
    }

    videoWrap.innerHTML = `<div class="tr-mvideo"><iframe src="${current.embed_url}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe></div>`;

    videoStrip.innerHTML = session.videos.length > 1
        ? `<div class="tr-video-strip">${session.videos.map((video, videoIndex) => `
            <button type="button" class="tr-video-thumb-btn ${videoIndex === index ? 'active' : ''}" onclick="switchTrVideo(${session.id}, ${videoIndex})">
                <img src="${video.thumbnail_url}" alt="Training video ${videoIndex + 1}">
                <span>Video ${videoIndex + 1}</span>
            </button>
        `).join('')}</div>`
        : '';
}

function openTrModal(id) {
    const session = trData.find((item) => item.id === id);
    if (!session) {
        return;
    }

    trCurrentSessionId = id;
    trCurrentVideoIndex = 0;

    document.getElementById('trModalTitle').textContent = session.title;
    renderTrVideo(session, trCurrentVideoIndex);

    const rows = [
        ['Club', session.club],
        ['Court', session.court],
        ['Coach', session.coach],
        ['Date', `${session.start} - ${session.end}`],
        ['Sport', session.sport],
        ['Type', session.type],
        ['Level', session.level],
        ['Status', session.status],
        ['Players', `${session.enrolled} / ${session.max_players} enrolled`],
        ['Price', `EGP ${session.price} / player`],
    ];

    document.getElementById('trInfoRows').innerHTML = rows
        .map(([label, value]) => `<div class="tr-mrow"><span class="tr-mlabel">${label}</span><span class="tr-mval">${value}</span></div>`)
        .join('');

    document.getElementById('trPlanWrap').innerHTML = session.session_plan
        ? `<div style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;">Session Plan</div><div class="tr-mnotes">${String(session.session_plan).replace(/\n/g, '<br>')}</div>`
        : '<div style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;">Session Plan</div><div class="tr-mnotes">No session plan added yet.</div>';

    document.getElementById('trNotesWrap').innerHTML = session.notes
        ? `<div style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;">Coach Notes</div><div class="tr-mnotes">${String(session.notes).replace(/\n/g, '<br>')}</div>`
        : '';

    document.getElementById('trModal').classList.add('open');
}

function switchTrVideo(sessionId, videoIndex) {
    const session = trData.find((item) => item.id === sessionId);
    if (!session) {
        return;
    }

    trCurrentSessionId = sessionId;
    trCurrentVideoIndex = videoIndex;
    renderTrVideo(session, videoIndex);
}

function closeTrModal() {
    document.getElementById('trModal').classList.remove('open');
    document.getElementById('trVideoWrap').innerHTML = '';
    document.getElementById('trVideoStrip').innerHTML = '';
    trCurrentSessionId = null;
    trCurrentVideoIndex = 0;
}

function confirmWithdraw(id, title) {
    if (!confirm(`Withdraw from "${title}"?\nThis cannot be undone.`)) {
        return;
    }

    @this.withdraw(id);
}
</script>
</x-filament-panels::page>
