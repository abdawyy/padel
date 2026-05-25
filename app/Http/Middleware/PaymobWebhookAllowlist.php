<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymobWebhookAllowlist
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('services.paymob.webhook_allowed_ips', []);

        if ($allowed === [] || $allowed === null) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if (! in_array($clientIp, $allowed, true)) {
            abort(403, 'Webhook source IP is not allowed.');
        }

        return $next($request);
    }
}
