<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait RespondsWithJsonOrRedirect
{
    protected function wantsApiResponse(Request $request): bool
    {
        return $request->wantsJson() || $request->expectsJson();
    }
}
