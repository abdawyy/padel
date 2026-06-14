<?php

namespace App\Providers;

use App\Models\AcademySession;
use App\Models\Club;
use App\Models\CoachApplication;
use App\Models\Court;
use App\Models\CourtSlot;
use App\Policies\AcademySessionPolicy;
use App\Policies\BookingPolicy;
use App\Policies\ClubPolicy;
use App\Policies\CoachApplicationPolicy;
use App\Policies\CourtPolicy;
use App\Policies\CourtSlotPolicy;
use App\Models\Booking;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected array $policies = [
        Club::class => ClubPolicy::class,
        Court::class => CourtPolicy::class,
        AcademySession::class => AcademySessionPolicy::class,
        CourtSlot::class => CourtSlotPolicy::class,
        CoachApplication::class => CoachApplicationPolicy::class,
        Booking::class => BookingPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
