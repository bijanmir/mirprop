<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasOrganization
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Adjust to your relationships. Example assumes: $user->organizations()
        if (! $user || ! $user->organizations()->exists()) {
            return redirect()->route('orgs.create')
                ->with('warning', 'Create or join an organization to continue.');
            // or: abort(403);
        }

        return $next($request);
    }
}
