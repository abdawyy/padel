<?php

namespace App\Livewire;

use App\Support\AdminClubContext;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AdminClubSwitcher extends Component
{
    public ?int $selectedClubId = null;

    public function mount(): void
    {
        $this->selectedClubId = AdminClubContext::id();
    }

    public function updatedSelectedClubId($value): void
    {
        $user = auth()->user();
        $clubId = $value !== null && $value !== '' ? (int) $value : null;

        if ($clubId !== null && $user && ! in_array($clubId, $user->accessibleClubIds(), true)) {
            $this->selectedClubId = AdminClubContext::id();

            return;
        }

        AdminClubContext::set($clubId);
        $this->redirect(request()->header('Referer', '/admin'), navigate: true);
    }

    public function clearClub(): void
    {
        AdminClubContext::set(null);
        $this->selectedClubId = null;
        $this->redirect(request()->header('Referer', '/admin'), navigate: true);
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.admin-club-switcher', [
            'clubs' => $user
                ? $user->clubs()->orderBy('name')->get(['clubs.id', 'clubs.name'])
                : collect(),
        ]);
    }
}
