<x-filament-panels::page>
@include('filament.player.partials.theme')

<div class="player-panel max-w-xl space-y-4">
    <form wire:submit="save" class="player-card p-6 space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input type="text" wire:model="name" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
            @error('name') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
            <input type="tel" wire:model="phone" class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
            @error('phone') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Skill level (WPT 1–7)</label>
            <select wire:model="skill_level" class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                <option value="">Not set</option>
                @foreach(range(1, 7) as $level)
                    <option value="{{ $level }}">Level {{ $level }}</option>
                @endforeach
            </select>
            @error('skill_level') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date of birth</label>
            <input type="date" wire:model="date_of_birth" class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
            @error('date_of_birth') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Preferred sport</label>
            <select wire:model="preferred_sport" class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                <option value="padel">Padel</option>
                <option value="tennis">Tennis</option>
                <option value="pickleball">Pickleball</option>
                <option value="squash">Squash</option>
            </select>
            @error('preferred_sport') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400">Email changes are managed by your club administrator.</p>

        <button type="submit" class="rounded-lg bg-primary-600 px-5 py-2 text-sm font-semibold text-white hover:opacity-90">
            Save profile
        </button>
    </form>
</div>
</x-filament-panels::page>
