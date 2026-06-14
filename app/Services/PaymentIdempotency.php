<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PaymentIdempotency
{
    public function resolve(User $user, ?string $key, callable $callback): array
    {
        if ($key === null || $key === '') {
            return $callback();
        }

        $cacheKey = sprintf('payment_idempotency:%d:%s', $user->id, hash('sha256', $key));

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $result = $callback();

        Cache::put($cacheKey, $result, now()->addHour());

        return $result;
    }
}
