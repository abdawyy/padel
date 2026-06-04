<x-filament-panels::page>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-gray-700 p-5">
            <div class="text-sm text-gray-500 dark:text-gray-400">Upcoming Sessions</div>
            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $this->getUpcomingSessionsCount() }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-gray-700 p-5">
            <div class="text-sm text-gray-500 dark:text-gray-400">Upcoming Matches</div>
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $this->getUpcomingMatchesCount() }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-gray-700 p-5">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Sessions</div>
            <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $this->getTotalCoachedSessionsCount() }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-gray-700 p-5">
            <div class="text-sm text-gray-500 dark:text-gray-400">Unique Players</div>
            <div class="text-3xl font-bold text-violet-600 dark:text-violet-400">{{ $this->getUniquePlayersCount() }}</div>
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('filament.coach.pages.coach-sessions') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                Open My Sessions
            </a>
            <a href="{{ route('filament.coach.pages.coach-matches') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                Open My Matches
            </a>
            @if(auth()->user()?->hasAdminAccess())
                <a href="{{ route('filament.admin.resources.academy-sessions.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700">
                    Manage Sessions (Admin)
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-gray-700 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Upcoming sessions</h3>
            @forelse($this->getUpcomingSessions() as $session)
                <div class="py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="font-semibold text-gray-900 dark:text-white">{{ $session->title }}</div>
                    <div class="text-sm text-gray-500">{{ $session->club?->name }} · {{ $session->start_time?->format('D d M · H:i') }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $session->players_count }} / {{ $session->max_players }} players</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No upcoming sessions scheduled.</p>
            @endforelse
        </div>

        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow ring-1 ring-gray-200 dark:ring-gray-700 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Upcoming matches</h3>
            @forelse($this->getUpcomingMatches() as $match)
                <div class="py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="font-semibold text-gray-900 dark:text-white">{{ $match->court?->name ?? 'Court' }}</div>
                    <div class="text-sm text-gray-500">{{ $match->court?->club?->name }} · {{ $match->start_time?->format('D d M · H:i') }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ ucfirst($match->status) }} · {{ ucfirst(str_replace('_', ' ', $match->match_type ?? '')) }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No upcoming coached matches.</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
