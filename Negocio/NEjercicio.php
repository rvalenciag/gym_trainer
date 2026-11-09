<?php
// NEjercicio: reglas del banco de ejercicios, recursos multimedia y archivos.
require_once __DIR__ . '/../Datos/DEjercicio.php';
require_once __DIR__ . '/../Datos/DDetalleRutina.php';

class NEjercicio
{
    const TIPOS = ['IMAGEN', 'VIDEO_URL', 'IFRAME_HTML'];
    const EXT_PERMITIDAS = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
    ];
    const MAX_BYTES = 16 * 1024 * 1024;
    const DIR = 'uploads/ejercicios/';

    public function listar()
    {
        return (new DEjercicio())->listarConCategoria();
    }

    public function guardar($nombre, $idCategoria, $descripcion, $tipo, $file, $texto)
    {
        $nombre = trim($nombre);

        if (!$this->datosBasicosCompletos($nombre, $idCategoria)) {
            return ['ok' => false, 'msg' => 'Complete el nombre y la categoría'];
        }

        list($ruta, $error) = $this->resolverMedia($tipo, $file, $texto);

        if ($error !== null) {
            return ['ok' => false, 'msg' => $error];
        }

        $ejercicio = new DEjercicio([
            'nombre' => $nombre,
            'descripcion' => trim($descripcion),
            'tipo_multimedia' => $tipo,
            'url' => $ruta,
            'id_categoria' => (int) $idCategoria,
        ]);
        $id = $ejercicio->crear();

        if ($id <= 0 && $this->esArchivoLocal($ruta)) {
            @unlink($ruta);
        }

        return $id > 0
            ? ['ok' => true, 'msg' => 'Ejercicio registrado exitosamente']
            : ['ok' => false, 'msg' => 'No se pudo registrar el ejercicio'];
    }

    public function actualizar($id, $nombre, $idCategoria, $descripcion, $tipo, $file, $texto)
    {
        $id = (int) $id;
        $nombre = trim($nombre);

        if (!$this->datosBasicosCompletos($nombre, $idCategoria)) {
            return ['ok' => false, 'msg' => 'Complete el nombre y la categoría'];
        }

        $consulta = new DEjercicio([
            'id' => $id,
        ]);
        $actual = $consulta->obtenerPorId();

        if ($actual === null) {
            return ['ok' => false, 'msg' => 'El ejercicio no existe'];
        }

        list($ruta, $error) = $this->resolverMedia($tipo, $file, $texto, $actual['url']);

        if ($error !== null) {
            return ['ok' => false, 'msg' => $error];
        }

        $ejercicio = new DEjercicio([
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => trim($descripcion),
            'tipo_multimedia' => $tipo,
            'url' => $ruta,
            'id_categoria' => (int) $idCategoria,
        ]);
        $ok = $ejercicio->actualizar();

        return [
            'ok' => $ok,
            'msg' => $ok ? 'Ejercicio actualizado correctamente' : 'No se pudo actualizar',
        ];
    }

    public function eliminar($id)
    {
        $id = (int) $id;
        $ejercicio = new DEjercicio([
            'id' => $id,
        ]);
        $actual = $ejercicio->obtenerPorId();

        if ($actual === null) {
            return ['ok' => false, 'msg' => 'El ejercicio no existe'];
        }

        $detalle = new DDetalleRutina([
            'id_ejercicio' => $id,
        ]);

        if ($detalle->contarPorEjercicio() > 0) {
            return [
                'ok' => false,
                'msg' => 'No se puede eliminar el ejercicio porque tiene rutinas vinculadas',
            ];
        }

        $ok = $ejercicio->eliminar();

        if ($ok && $this->esArchivoLocal($actual['url'])) {
            @unlink((string) $actual['url']);
        }

        return [
            'ok' => $ok,
            'msg' => $ok ? 'Ejercicio eliminado del catálogo' : 'No se pudo eliminar',
        ];
    }

    private function datosBasicosCompletos($nombre, $idCategoria)
    {
        return trim($nombre) !== '' && (int) $idCategoria > 0;
    }

    private function esArchivoLocal($ruta)
    {
        return strpos((string) $ruta, self::DIR) === 0 && is_file((string) $ruta);
    }

    private function imagenValida($file)
    {
        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            return false;
        }

        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            return false;
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

        if (!isset(self::EXT_PERMITIDAS[$extension])) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($file['tmp_name']) === self::EXT_PERMITIDAS[$extension];
    }

    private function urlValida($texto)
    {
        $url = trim((string) $texto);

        return $url !== ''
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && preg_match('#^https?://#i', $url) === 1;
    }

    private function iframeSeguro($html)
    {
        $html = trim((string) $html);

        if (
            $html === ''
            || stripos($html, '<script') !== false
            || preg_match('#\son\w+\s*=#i', $html) === 1
            || stripos($html, 'javascript:') !== false
        ) {
            return false;
        }

        if (preg_match('#<iframe\b[^>]*>(\s*</iframe>)?#i', $html) !== 1) {
            return false;
        }

        return preg_match('#src\s*=\s*["\']https://[^"\']+#i', $html) === 1;
    }

    private function resolverMedia($tipo, $file, $texto, $actual = null)
    {
        if (!in_array($tipo, self::TIPOS, true)) {
            return [null, 'El recurso multimedia ingresado no es válido'];
        }

        if ($tipo === 'IMAGEN') {
            if ($file !== null && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (($file['size'] ?? 0) > self::MAX_BYTES) {
                    return [null, 'El archivo supera el tamaño máximo de 16 MB'];
                }

                if (!$this->imagenValida($file)) {
                    return [null, 'El recurso multimedia ingresado no es válido'];
                }

                $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
                $nuevo = self::DIR . 'ej_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

                if (!move_uploaded_file($file['tmp_name'], $nuevo)) {
                    return [null, 'No se pudo guardar el archivo'];
                }

                if ($actual !== null && $this->esArchivoLocal($actual)) {
                    @unlink($actual);
                }

                return [$nuevo, null];
            }

            return $actual !== null
                ? [$actual, null]
                : [null, 'El recurso multimedia ingresado no es válido'];
        }

        if ($tipo === 'VIDEO_URL') {
            $url = trim((string) $texto);

            if (!$this->urlValida($url)) {
                return [null, 'El recurso multimedia ingresado no es válido'];
            }

            return [$url, null];
        }

        $html = trim((string) $texto);

        if (!$this->iframeSeguro($html)) {
            return [null, 'El recurso multimedia ingresado no es válido'];
        }

        return [$html, null];
    }
}
