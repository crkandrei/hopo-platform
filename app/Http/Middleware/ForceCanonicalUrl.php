<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCanonicalUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction()) {
            return $next($request);
        }

        $host = $request->getHost();
        $scheme = $request->getScheme();

        $needsWwwStrip = str_starts_with($host, 'www.');
        $needsHttpsUpgrade = $scheme === 'http';

        if ($needsWwwStrip || $needsHttpsUpgrade) {
            $canonicalHost = $needsWwwStrip ? substr($host, 4) : $host;
            $url = 'https://' . $canonicalHost . $request->getRequestUri();

            return redirect($url, 301);
        }

        return $next($request);
    }
}
