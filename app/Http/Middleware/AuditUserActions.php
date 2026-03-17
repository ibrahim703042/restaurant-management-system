<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditUserActions
{
    private const SKIP_PREFIXES = ['_ignition', 'telescope', 'horizon', 'sanctum'];

    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'current_password', '_token', 'image', 'photo',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! auth()->check()) {
            return $response;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        $path = $request->path();
        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $response;
            }
        }

        if ($request->routeIs('audit.*')) {
            return $response;
        }

        // Skip JSON list endpoints (read-only catalog for UI)
        if ($request->routeIs('*.list') || str_ends_with($path, '/list')) {
            return $response;
        }

        if ($request->routeIs('*.showJson') || str_contains($path, '/json') && $request->isMethod('GET')) {
            return $response;
        }

        $route = $request->route();
        $name = $route?->getName();
        $action = $name ?: $request->method().' '.$path;

        $summary = $this->summarizePayload($request);

        try {
            AuditLog::query()->create([
                'user_id' => auth()->id(),
                'action' => mb_substr($action, 0, 128),
                'method' => $request->method(),
                'path' => mb_substr('/'.$path, 0, 512),
                'route_name' => $name ? mb_substr($name, 0, 128) : null,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
                'payload_summary' => $summary,
                'response_status' => $response->getStatusCode(),
            ]);
        } catch (\Throwable) {
            // Never break the app if audit insert fails
        }

        return $response;
    }

    private function summarizePayload(Request $request): ?array
    {
        $all = $request->except(self::SENSITIVE_KEYS);
        $out = [];

        $i = 0;
        foreach ($all as $key => $value) {
            if (++$i > 30) {
                $out['_truncated'] = 'More fields omitted';
                break;
            }
            if (is_array($value)) {
                if (count($value) > 20) {
                    $out[$key] = '['.count($value).' items]';
                } else {
                    $out[$key] = array_map(fn ($v) => is_scalar($v) ? $v : (is_array($v) ? '[…]' : '[obj]'), $value);
                }
            } elseif (is_scalar($value)) {
                $s = (string) $value;
                $out[$key] = mb_strlen($s) > 200 ? mb_substr($s, 0, 200).'…' : $s;
            }
        }

        return $out === [] ? null : $out;
    }
}
