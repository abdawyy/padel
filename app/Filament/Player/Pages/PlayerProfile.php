<?php

namespace App\Filament\Player\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PlayerProfile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?string $title = 'Edit Profile';

    protected static ?int $navigationSort = 10;

    public ?string $name = null;

    public ?string $phone = null;

    public ?int $skill_level = null;

    public ?string $date_of_birth = null;

    public ?string $preferred_sport = 'padel';

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->phone = $user->phone;
        $this->skill_level = $user->skill_level;
        $this->date_of_birth = $user->date_of_birth?->toDateString();
        $this->preferred_sport = $user->preferred_sport ?? 'padel';
    }

    public function getView(): string
    {
        return 'filament.player.pages.player-profile';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'skill_level' => ['nullable', 'integer', 'between:1,7'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'preferred_sport' => ['required', 'in:padel,tennis,pickleball,squash'],
        ]);

        auth()->user()->update($validated);

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();
    }
}
