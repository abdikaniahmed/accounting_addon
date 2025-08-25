<?php

namespace App\Support\Audit;
//for Addon
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\UserResolver;

// Optional import — only if Sentinel is installed (it is in Yoori)
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class MultiGuardUserResolver implements UserResolver
{
    public static function resolve()
    {
        // Preferred guards from config; we'll filter to only those that exist
        $preferred = config('audit.user.guards', ['web', 'api']);

        // Guards actually defined in config/auth.php
        $defined = array_keys(config('auth.guards', []));
        $guards  = array_values(array_intersect($preferred, $defined));

        if (empty($guards)) {
            $guards = ['web', 'api']; // safe fallbacks
        }

        // 1) Try Laravel guards first
        foreach ($guards as $guard) {
            try {
                if (Auth::guard($guard)->check()) {
                    if (function_exists('request') && request()) {
                        request()->attributes->set('_audit_guard', $guard);
                    }
                    return Auth::guard($guard)->user();
                }
            } catch (\Throwable $e) {
                // Guard not configured or misconfigured — ignore and continue
            }
        }

        // 2) Try Sentinel (this is what Yoori uses)
        try {
            if (class_exists(Sentinel::class) && Sentinel::check()) {
                $user = Sentinel::getUser();
                if (function_exists('request') && request()) {
                    request()->attributes->set('_audit_guard', 'sentinel');
                }
                return $user; // This will be Cartalyst\Sentinel\Users\EloquentUser
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // 3) Final fallback (rarely hit)
        if (Auth::check()) {
            if (function_exists('request') && request()) {
                request()->attributes->set('_audit_guard', 'web');
            }
            return Auth::user();
        }

        return null; // -> stored as "System"
    }
}