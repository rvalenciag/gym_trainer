<?php
require_once __DIR__ . '/ProcesadorMultimedia.php';

class ProcesadorIframe implements ProcesadorMultimedia
{
    public function procesar(array $datos = []): array
    {
        $html = trim((string) ($datos['texto'] ?? ''));
        $actual = $datos['actual'] ?? null;
        $tipoAnterior = $datos['tipo_anterior'] ?? null;

        if ($html === '' && $tipoAnterior === 'IFRAME_HTML') {
            return [$actual, null];
        }

        if (
            $html === '' ||
            stripos($html, '<script') !== false ||
            preg_match('#\son\w+\s*=#i', $html) === 1 ||
            stripos($html, 'javascript:') !== false
        ) {
            return [
                null,
                'El recurso multimedia ingresado no es válido'
            ];
        }

        if (
            preg_match('#<iframe\b[^>]*>(\s*</iframe>)?#i', $html) !== 1 ||
            preg_match('#src\s*=\s*["\']https://[^"\']+#i', $html) !== 1
        ) {
            return [
                null,
                'El recurso multimedia ingresado no es válido'
            ];
        }

        return [$html, null];
    }
}
