<?php
require_once __DIR__ . '/ProcesadorMultimedia.php';

class ProcesadorVideoURL implements ProcesadorMultimedia
{
    public function procesar(array $datos = []): array
    {
        $url = trim((string) ($datos['texto'] ?? ''));
        $actual = $datos['actual'] ?? null;
        $tipoAnterior = $datos['tipo_anterior'] ?? null;

        if ($url === '' && $tipoAnterior === 'VIDEO_URL') {
            return [$actual, null];
        }

        if (
            $url === ''
            || filter_var($url, FILTER_VALIDATE_URL) === false
            || preg_match('#^https?://#i', $url) !== 1
        ) {
            return [null, 'El recurso multimedia ingresado no es válido'];
        }

        return [$url, null];
    }
}
