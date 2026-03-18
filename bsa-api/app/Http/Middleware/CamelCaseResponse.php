<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CamelCaseResponse
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->setData($this->toCamel($response->getData(true)));
        }

        return $response;
    }

    private function toCamel(mixed $data): mixed
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[is_string($key) ? Str::camel($key) : $key] = $this->toCamel($value);
            }
            return $result;
        }

        return $data;
    }
}
