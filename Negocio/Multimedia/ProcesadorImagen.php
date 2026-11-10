<?php
require_once __DIR__ . '/ProcesadorMultimedia.php';

class ProcesadorImagen implements ProcesadorMultimedia
{
    private const MAX_BYTES = 16 * 1024 * 1024;
    private const DIR = 'uploads/ejercicios/';

    private const EXT_PERMITIDAS = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
    ];

    public function procesar(array $datos = []): array
    {
        $file = $datos['archivo'] ?? null;
        $actual = $datos['actual'] ?? null;
        $tipoAnterior = $datos['tipo_anterior'] ?? null;

        // Si no seleccionó otro archivo, conservar el anterior.
        if (
            $file === null ||
            ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
        ) {
            if ($tipoAnterior === 'IMAGEN') {
                return [$actual, null];
            }

            return [null, 'El recurso multimedia ingresado no es válido'];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [null, 'No se pudo recibir el archivo'];
        }

        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            return [null, 'El archivo supera el tamaño máximo de 16 MB'];
        }

        $extension = strtolower(
            pathinfo($file['name'] ?? '', PATHINFO_EXTENSION)
        );

        if (!isset(self::EXT_PERMITIDAS[$extension])) {
            return [null, 'El recurso multimedia ingresado no es válido'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if ($mime !== self::EXT_PERMITIDAS[$extension]) {
            return [null, 'El recurso multimedia ingresado no es válido'];
        }

        $nombre = 'ej_' . time() . '_'
            . bin2hex(random_bytes(4)) . '.' . $extension;

        $rutaRelativa = self::DIR . $nombre;

        $directorio = dirname(__DIR__, 2) . '/' . self::DIR;

        if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
            return [null, 'No se pudo crear el directorio de archivos'];
        }

        if (!move_uploaded_file($file['tmp_name'], $directorio . $nombre)) {
            return [null, 'No se pudo guardar el archivo'];
        }

        return [$rutaRelativa, null];
    }
}
