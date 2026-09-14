<?php
// NEjercicio: reglas del banco de ejercicios (CU04). Uploads e iframe se validan aquí.
require_once __DIR__ . '/../Datos/DEjercicio.php';
require_once __DIR__ . '/../Datos/DCategoria.php';
require_once __DIR__ . '/../Datos/DDetalleRutina.php';

class NEjercicio {
    const TIPOS = ['IMAGEN', 'VIDEO_URL', 'IFRAME_HTML'];
    const EXT_PERMITIDAS = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png', 'webp' => 'image/webp', 'mp4' => 'video/mp4', 'webm' => 'video/webm'];
    const MAX_BYTES = 16 * 1024 * 1024; // 16 MB (Regla 3)
    const DIR = 'uploads/ejercicios/';

    public function listar() {
        return (new DEjercicio())->listarConCategoria();
    }

    private function datosBasicosCompletos($nombre, $idCategoria) {
        return trim($nombre) !== '' && (int)$idCategoria > 0;
    }

    private function existeCategoria($id) {
        return (new DCategoria())->obtenerPorId((int)$id) !== null;
    }

    private function existeEjercicio($id) {
        return (new DEjercicio())->obtenerPorId((int)$id) !== null;
    }

    private function esArchivoLocal($ruta) {
        return strpos((string)$ruta, self::DIR) === 0 && is_file((string)$ruta);
    }

    // Archivo recién subido: sin error PHP, ≤16 MB, extensión y MIME reales coinciden.
    private function imagenValida($file) {
        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            return false;
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            return false;
        }
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!isset(self::EXT_PERMITIDAS[$ext])) {
            return false;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($file['tmp_name']) === self::EXT_PERMITIDAS[$ext];
    }

    private function urlValida($texto) {
        $url = trim((string)$texto);
        return $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== false
            && preg_match('#^https?://#i', $url) === 1;
    }

    // Solo UN iframe, sin scripts ni eventos, src https (anti-XSS, Regla 3).
    private function iframeSeguro($html) {
        $html = trim((string)$html);
        if ($html === '' || stripos($html, '<script') !== false
                || preg_match('#\son\w+\s*=#i', $html) === 1
                || stripos($html, 'javascript:') !== false) {
            return false;
        }
        if (preg_match('#<iframe\b[^>]*>(\s*</iframe>)?#i', $html) !== 1) {
            return false;
        }
        return preg_match('#src\s*=\s*["\']https://[^"\']+#i', $html) === 1;
    }

    // Resuelve el recurso según el tipo. Devuelve [ruta|null, error|null].
    // $file: entrada de $_FILES (o null). $texto: URL o código iframe.
    private function resolverMedia($tipo, $file, $texto, $actual = null) {
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
                $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
                $nuevo = self::DIR . 'ej_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($file['tmp_name'], $nuevo)) {
                    return [null, 'No se pudo guardar el archivo'];
                }
                if ($actual !== null && $this->esArchivoLocal($actual)) {
                    @unlink($actual); // reemplazo: se borra el archivo viejo
                }
                return [$nuevo, null];
            }
            // Sin archivo nuevo: se conserva el actual (edición) o es error (alta).
            return $actual !== null ? [$actual, null]
                : [null, 'El recurso multimedia ingresado no es válido'];
        }
        if ($tipo === 'VIDEO_URL') {
            $url = trim((string)$texto);
            if (!$this->urlValida($url)) {
                return [null, 'El recurso multimedia ingresado no es válido'];
            }
            return [$url, null];
        }
        // IFRAME_HTML.
        $html = trim((string)$texto);
        if (!$this->iframeSeguro($html)) {
            return [null, 'El recurso multimedia ingresado no es válido'];
        }
        return [$html, null];
    }

    public function guardar($nombre, $idCategoria, $descripcion, $tipo, $file, $texto) {
        $nombre = trim($nombre);
        if (!$this->datosBasicosCompletos($nombre, $idCategoria)) {
            return ['ok' => false, 'msg' => 'Complete el nombre y la categoría'];
        }
        if (!$this->existeCategoria($idCategoria)) {
            return ['ok' => false, 'msg' => 'La categoría seleccionada no existe'];
        }
        list($ruta, $err) = $this->resolverMedia($tipo, $file, $texto);
        if ($err !== null) {
            return ['ok' => false, 'msg' => $err];
        }
        $id = (new DEjercicio())->crear($nombre, trim($descripcion), $tipo, $ruta, (int)$idCategoria);
        if ($id <= 0 && $this->esArchivoLocal($ruta)) {
            @unlink($ruta); // la BD falló: no dejar archivo huérfano
        }
        return $id > 0
            ? ['ok' => true, 'msg' => 'Ejercicio registrado exitosamente']
            : ['ok' => false, 'msg' => 'No se pudo registrar el ejercicio'];
    }

    public function actualizar($id, $nombre, $idCategoria, $descripcion, $tipo, $file, $texto) {
        $nombre = trim($nombre);
        if (!$this->datosBasicosCompletos($nombre, $idCategoria)) {
            return ['ok' => false, 'msg' => 'Complete el nombre y la categoría'];
        }
        if (!$this->existeEjercicio($id)) {
            return ['ok' => false, 'msg' => 'El ejercicio no existe'];
        }
        $d = new DEjercicio();
        $act = $d->obtenerPorId((int)$id);
        list($ruta, $err) = $this->resolverMedia($tipo, $file, $texto, $act['url']);
        if ($err !== null) {
            return ['ok' => false, 'msg' => $err];
        }
        $ok = $d->actualizar((int)$id, $nombre, trim($descripcion), $tipo, $ruta, (int)$idCategoria);
        return ['ok' => $ok, 'msg' => $ok ? 'Ejercicio actualizado correctamente' : 'No se pudo actualizar'];
    }

    public function eliminar($id) {
        $d = new DEjercicio();
        if (!$this->existeEjercicio($id)) {
            return ['ok' => false, 'msg' => 'El ejercicio no existe'];
        }
        $act = $d->obtenerPorId((int)$id);
        if ((new DDetalleRutina())->contarPorEjercicio((int)$id) > 0) {
            return ['ok' => false, 'msg' => 'No se puede eliminar el ejercicio porque tiene rutinas vinculadas'];
        }
        $ok = $d->eliminar((int)$id);
        if ($ok && $this->esArchivoLocal($act['url'])) {
            @unlink((string)$act['url']); // se borra el archivo local
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Ejercicio eliminado del catálogo' : 'No se pudo eliminar'];
    }
}
