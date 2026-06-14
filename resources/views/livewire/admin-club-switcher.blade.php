@if($clubs->isNotEmpty())
    <div class="flex items-center gap-2 me-4">
        <label for="admin-club-switcher" class="text-sm font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">
            Club
        </label>
        <select
            id="admin-club-switcher"
            wire:model.live="selectedClubId"
            class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 py-1.5"
        >
            <option value="">All clubs</option>
            @foreach($clubs as $club)
                <option value="{{ $club->id }}">{{ $club->name }}</option>
            @endforeach
        </select>
        @if($selectedClubId)
            <button type="button" wire:click="clearClub" class="text-xs text-gray-500 hover:text-gray-700">Clear</button>
        @endif
    </div>
@endif
