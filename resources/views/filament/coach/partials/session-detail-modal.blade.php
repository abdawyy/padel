@php
    $videos = $session->training_video_urls ?? [];
@endphp

<div class="space-y-4 text-sm text-gray-700 dark:text-gray-300">
    <div><span class="font-semibold">Club:</span> {{ $session->club?->name ?? '—' }}</div>
    <div><span class="font-semibold">Court:</span> {{ $session->court?->name ?? '—' }}</div>
    <div><span class="font-semibold">When:</span> {{ $session->start_time?->format('D, d M Y H:i') }} – {{ $session->end_time?->format('H:i') }}</div>

    <div>
        <span class="font-semibold">Enrolled players ({{ $session->players->count() }}/{{ $session->max_players }})</span>
        @if($session->players->isEmpty())
            <p class="mt-1 text-gray-500">No players enrolled yet.</p>
        @else
            <ul class="mt-2 list-disc list-inside">
                @foreach($session->players as $player)
                    <li>{{ $player->name }} <span class="text-gray-500">({{ $player->email }})</span></li>
                @endforeach
            </ul>
        @endif
    </div>

    <div>
        <span class="font-semibold">Session plan</span>
        <p class="mt-1 whitespace-pre-wrap rounded-lg bg-gray-50 dark:bg-gray-900 p-3">{{ $session->session_plan ?: 'No plan added.' }}</p>
    </div>

    @if($session->notes)
        <div>
            <span class="font-semibold">Notes</span>
            <p class="mt-1 whitespace-pre-wrap rounded-lg bg-gray-50 dark:bg-gray-900 p-3">{{ $session->notes }}</p>
        </div>
    @endif

    <div>
        <span class="font-semibold">Training videos</span>
        @if(empty($videos))
            <p class="mt-1 text-gray-500">No videos linked.</p>
        @else
            <ul class="mt-2 space-y-1">
                @foreach($videos as $url)
                    <li><a href="{{ $url }}" target="_blank" rel="noopener" class="text-primary-600 hover:underline">{{ $url }}</a></li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
