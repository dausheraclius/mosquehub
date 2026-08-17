<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Simpan jejak aksi administrasi yang berhasil. Aksi baca biasa tidak
     * dicatat agar riwayat tetap mudah dibaca; unduhan/ekspor tetap dicatat.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $routeName = $request->route()?->getName() ?? '';

        if (! $request->user() || $response->getStatusCode() >= 400 || ! $this->shouldLog($request, $routeName)) {
            return $response;
        }

        activity('administrasi')
            ->causedBy($request->user())
            ->event($request->method())
            ->withProperties([
                'mosque_id' => $request->user()->mosque_id,
                'route' => $routeName,
                'method' => $request->method(),
                'path' => $request->path(),
            ])
            ->log($this->description($request, $routeName));

        return $response;
    }

    private function shouldLog(Request $request, string $routeName): bool
    {
        return ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)
            || str_starts_with($routeName, 'ekspor.')
            || in_array($routeName, ['laporan.excel-all', 'laporan.pdf-all', 'laporan.pdf', 'laporan.excel'], true);
    }

    private function description(Request $request, string $routeName): string
    {
        $action = match ($request->method()) {
            'POST' => 'Menambahkan atau memproses data',
            'PUT', 'PATCH' => 'Memperbarui data',
            'DELETE' => 'Menghapus data',
            default => 'Mengekspor data',
        };

        return $action.($routeName ? ' ('.$routeName.')' : '');
    }
}
