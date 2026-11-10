<?php
// NEjercicio: reglas del banco de ejercicios, recursos multimedia y archivos.
require_once __DIR__ . '/../Datos/DEjercicio.php';
require_once __DIR__ . '/../Datos/DDetalleRutina.php';

require_once __DIR__ . '/Multimedia/GestorMultimedia.php';
require_once __DIR__ . '/Multimedia/ProcesadorImagen.php';
require_once __DIR__ . '/Multimedia/ProcesadorVideoURL.php';
require_once __DIR__ . '/Multimedia/ProcesadorIframe.php';

class NEjercicio
{
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

        list($ruta, $error) = $this->resolverMedia($tipo, $file, $texto, $actual['url'], $actual['tipo_multimedia']);

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

    private function resolverMedia($tipo, $file, $texto, $actual = null, $tipoAnterior = null)
    {
        $gestor = new GestorMultimedia();

        switch ($tipo) {
            case 'IMAGEN':
                $gestor->setEstrategia(new ProcesadorImagen());
                break;
            case 'VIDEO_URL':
                $gestor->setEstrategia(new ProcesadorVideoURL());
                break;
            case 'IFRAME_HTML':
                $gestor->setEstrategia(new ProcesadorIframe());
                break;
            default:
                return [null, 'El recurso multimedia ingresado no es válido.'];
        }

        return $gestor->procesar([
            'archivo' => $file,
            'texto' => $texto,
            'actual' => $actual,
            'tipo_anterior' => $tipoAnterior,
        ]);
    }
}
