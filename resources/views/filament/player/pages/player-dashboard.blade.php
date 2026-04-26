<x-filament-panels::page>
    @php
        $user = $this->getUser();
        $skill = $this->getSkillLabel();
        $activePackages = $this->getActivePackages();
        $upcomingTraining = $this->getUpcomingTraining();
        $upcomingMatches = $this->getUpcomingMatches();
    @endphp

    <style>
        .pd-shell {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .pd-hero {
            border-radius: 18px;
            background: linear-gradient(120deg, #0ea5e9, #14b8a6);
            color: #fff;
            padding: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .pd-avatar {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.24);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
        }
        .pd-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        .pd-metric,
        .pd-panel,
        .pd-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
        }
        .dark .pd-metric,
        .dark .pd-panel,
        .dark .pd-card {
            background: #111827;
            border-color: #374151;
        }
        .pd-metric {
            padding: 14px;
        }
        .pd-metric-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6b7280;
            font-weight: 700;
        }
        .pd-metric-value {
            margin-top: 6px;
            font-size: 28px;
            font-weight: 800;
            color: #111827;
        }
        .dark .pd-metric-value {
            color: #f9fafb;
        }
        .pd-panel {
            padding: 14px;
        }
        .pd-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .pd-panel-title {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #111827;
        }
        .dark .pd-panel-title {
            color: #f9fafb;
        }
        .pd-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 10px;
        }
        .pd-card {
            padding: 12px;
        }
        .pd-muted {
            color: #6b7280;
            font-size: 13px;
        }
        .pd-chip {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            background: #e0f2fe;
            color: #0369a1;
            margin-top: 8px;
            margin-right: 6px;
        }
        .pd-chip-danger {
            background: #ffe4e6;
            color: #be123c;
        }
        .pd-empty {
            padding: 14px;
            border: 1px dashed #9ca3af;
            border-radius: 12px;
            color: #6b7280;
            font-size: 13px;
        }
    </style>

    <div class="pd-shell">
        <section class="pd-hero">
            <div style="display:flex; gap:12px; align-items:center;">
                <div class="pd-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                <div>
                    <div style="font-size:24px; font-weight:800; line-height:1.1;">{{ $user->name }}</div>
                    <div style="font-size:14px; opacity:.95;">{{ $user->email }}</div>
                    @if($user->phone)
                        <div style="font-size:13px; opacity:.9;">{{ $user->phone }}</div>
                    @endif
                </div>
            </div>
            <div style="text-align:right; font-size:13px;">
                <div style="font-weight:700;">{{ $skill }}</div>
                <div>Sport: {{ ucfirst($user->preferred_sport ?? 'padel') }}</div>
                @if($user->date_of_birth)
                    <div>Born: {{ $user->date_of_birth->format('d M Y') }}</div>
                @endif
            </div>
        </section>

        <section class="pd-metrics">
            <article class="pd-metric">
                <div class="pd-metric-label">Active Packages</div>
                <div class="pd-metric-value">{{ $this->getActivePackagesCount() }}</div>
            </article>
            <article class="pd-metric">
                <div class="pd-metric-label">Upcoming Training</div>
                <div class="pd-metric-value">{{ $this->getUpcomingSessionsCount() }}</div>
            </article>
            <article class="pd-metric">
                <div class="pd-metric-label">Total Matches</div>
                <div class="pd-metric-value">{{ $this->getTotalMatchesCount() }}</div>
            </article>
        </section>

        <section class="pd-panel">
            <div class="pd-panel-head">
                <h3 class="pd-panel-title">Active Packages</h3>
                <a href="{{ route('filament.player.pages.my-packages') }}">View all</a>
            </div>

            @if($activePackages->isNotEmpty())
                <div class="pd-grid">
                    @foreach($activePackages as $pkg)
                        @php $pivot = $pkg->pivot; $expired = \Carbon\Carbon::parse($pivot->expires_at)->isPast(); @endphp
                        <article class="pd-card">
                            <div style="font-weight:700; color:#111827;" class="dark:text-gray-100">{{ $pkg->name }}</div>
                            <div class="pd-muted">{{ $pkg->club->name }} - {{ ucfirst($pkg->type) }}</div>
                            <div>
                                <span class="pd-chip {{ $expired ? 'pd-chip-danger' : '' }}">Expires {{ \Carbon\Carbon::parse($pivot->expires_at)->format('d M Y') }}</span>
                                @if($pivot->sessions_remaining !== null)
                                    <span class="pd-chip">{{ $pivot->sessions_remaining }} sessions left</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="pd-empty">No active packages found.</div>
            @endif
        </section>

        <section class="pd-panel">
            <div class="pd-panel-head">
                <h3 class="pd-panel-title">Upcoming Training</h3>
                <a href="{{ route('filament.player.pages.my-training') }}">View all</a>
            </div>

            @if($upcomingTraining->isNotEmpty())
                <div class="pd-grid">
                    @foreach($upcomingTraining as $session)
                        <article class="pd-card">
                            <div style="font-weight:700; color:#111827;" class="dark:text-gray-100">{{ $session->title }}</div>
                            <div class="pd-muted">{{ $session->club->name ?? '-' }}</div>
                            @if($session->coach)
                                <div class="pd-muted">Coach: {{ $session->coach->name }}</div>
                            @endif
                            <span class="pd-chip">{{ $session->start_time->format('D, d M Y H:i') }}</span>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="pd-empty">No upcoming training sessions.</div>
            @endif
        </section>

        <section class="pd-panel">
            <div class="pd-panel-head">
                <h3 class="pd-panel-title">Upcoming Matches</h3>
                <a href="{{ route('filament.player.pages.my-matches') }}">View all</a>
            </div>

            @if($upcomingMatches->isNotEmpty())
                <div class="pd-grid">
                    @foreach($upcomingMatches as $match)
                        <article class="pd-card">
                            <div style="font-weight:700; color:#111827;" class="dark:text-gray-100">{{ $match->court->club->name ?? '-' }} - {{ $match->court->name ?? '-' }}</div>
                            <div>
                                <span class="pd-chip">{{ ucwords(str_replace('_', ' ', $match->match_type)) }}</span>
                                @if($match->skill_level)
                                    <span class="pd-chip">Level: {{ ucfirst($match->skill_level) }}</span>
                                @endif
                            </div>
                            <span class="pd-chip">{{ $match->start_time->format('D, d M Y H:i') }}</span>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="pd-empty">No upcoming matches.</div>
            @endif
        </section>
    </div>

</x-filament-panels::page>
