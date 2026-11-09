<?php
// NRutina: reglas para planes semanales y consulta de planes asignados.
require_once __DIR__ . '/../Datos/DRutina.php';
require_once __DIR__ . '/../Datos/DDetalleRutina.php';

class NRutina
{
    public function listar()
    {
        return (new DRutina())->listarConCliente();
    }

    public function listarPorCliente($idCliente)
    {
        return (new DRutina([
            'id_cliente' => (int) $idCliente,
        ]))->listarPorCliente();
    }

    public function obtenerCabecera($id)
    {
        return (new DRutina([
            'id' => (int) $id,
        ]))->obtenerCabecera();
    }

    public function listarDetalles($idRutina)
    {
        return (new DDetalleRutina([
            'id_rutina' => (int) $idRutina,
        ]))->listarPorRutina();
    }

    public function crear($idInstructor, $idCliente, $nombre, $dets)
    {
        list($errores, $cabecera, $detallesLimpios) = $this->validar(
            $idInstructor,
            $idCliente,
            $nombre,
            $dets
        );

        if (count($errores) > 0) {
            return ['ok' => false, 'msg' => $errores[0]];
        }

        $rutina = new DRutina($cabecera);

        try {
            $rutina->iniciarTransaccion();
            $idRutina = $rutina->crearCabecera();

            foreach ($detallesLimpios as $detalle) {
                $detalle['id_rutina'] = $idRutina;
                (new DDetalleRutina($detalle))->insertar();
            }

            $rutina->confirmar();

            return [
                'ok' => true,
                'msg' => 'Rutina creada y asignada con éxito al cliente',
            ];
        } catch (Exception $e) {
            $rutina->revertir();

            return [
                'ok' => false,
                'msg' => 'No se pudo guardar la rutina',
            ];
        }
    }

    public function actualizar($id, $idCliente, $nombre, $dets, $idInstructor = 0)
    {
        $id = (int) $id;

        if (!$this->existeRutina($id)) {
            return ['ok' => false, 'msg' => 'La rutina no existe'];
        }

        list($errores, $cabecera, $detallesLimpios) = $this->validar(
            $idInstructor,
            $idCliente,
            $nombre,
            $dets
        );

        if (count($errores) > 0) {
            return ['ok' => false, 'msg' => $errores[0]];
        }

        $cabecera['id'] = $id;
        $rutina = new DRutina($cabecera);

        try {
            $rutina->iniciarTransaccion();
            $rutina->actualizarCabecera();

            $detalleAnterior = new DDetalleRutina([
                'id_rutina' => $id,
            ]);
            $detalleAnterior->eliminarDeRutina();

            foreach ($detallesLimpios as $detalle) {
                $detalle['id_rutina'] = $id;
                (new DDetalleRutina($detalle))->insertar();
            }

            $rutina->confirmar();

            return [
                'ok' => true,
                'msg' => 'Rutina actualizada correctamente',
            ];
        } catch (Exception $e) {
            $rutina->revertir();

            return [
                'ok' => false,
                'msg' => 'No se pudo actualizar',
            ];
        }
    }

    public function eliminar($id)
    {
        $rutina = new DRutina([
            'id' => (int) $id,
        ]);
        $ok = $rutina->eliminar();

        return [
            'ok' => $ok,
            'msg' => $ok ? 'Rutina eliminada del sistema' : 'No se pudo eliminar',
        ];
    }

    public function rutinaDelDia($idCliente, $idRutina, $dia)
    {
        $rutina = new DRutina([
            'id' => (int) $idRutina,
            'id_cliente' => (int) $idCliente,
        ]);
        $cabecera = $rutina->obtenerDeCliente();

        if ($cabecera === null) {
            return [
                'rutina' => null,
                'detalles' => [],
            ];
        }

        $detalles = (new DDetalleRutina([
            'id_rutina' => (int) $cabecera['id'],
            'dia_semana' => (int) $dia,
        ]))->listarPorRutinaYDia();

        return [
            'rutina' => $cabecera,
            'detalles' => $detalles,
        ];
    }

    private function existeRutina($id)
    {
        return (new DRutina([
            'id' => (int) $id,
        ]))->obtenerCabecera() !== null;
    }

    private function tieneDetalle($dets)
    {
        return is_array($dets) && count($dets) > 0;
    }

    private function filaCompleta($detalle)
    {
        return (int) ($detalle['dia'] ?? 0) >= 1
            && (int) ($detalle['dia'] ?? 0) <= 7
            && (int) ($detalle['ejercicio'] ?? 0) > 0
            && (int) ($detalle['series'] ?? 0) > 0
            && trim((string) ($detalle['repeticiones'] ?? '')) !== '';
    }

    private function validar($idInstructor, $idCliente, $nombre, $dets)
    {
        $errores = [];
        $nombre = trim($nombre);

        if ((int) $idCliente <= 0 || $nombre === '') {
            $errores[] = 'Complete cliente y nombre del plan';
        }

        if (!$this->tieneDetalle($dets)) {
            $errores[] = 'Debe agregar al menos un ejercicio a la lista antes de guardar la rutina';
        }

        $detallesLimpios = [];

        foreach ((array) $dets as $detalle) {
            if (!$this->filaCompleta($detalle)) {
                $errores[] = 'Cada ejercicio debe tener día, ejercicio, series y repeticiones (peso y descanso son opcionales)';
                break;
            }

            $peso = trim((string) ($detalle['peso'] ?? ''));
            $descanso = trim((string) ($detalle['descanso'] ?? ''));

            $detallesLimpios[] = [
                'id_ejercicio' => (int) $detalle['ejercicio'],
                'dia_semana' => (int) $detalle['dia'],
                'series' => (int) $detalle['series'],
                'repeticiones' => trim((string) $detalle['repeticiones']),
                'peso_sugerido' => $peso === '' ? null : $peso,
                'descanso' => $descanso === '' ? null : (int) $descanso,
            ];
        }

        $cabecera = [
            'id_instructor' => (int) $idInstructor,
            'id_cliente' => (int) $idCliente,
            'nombre_plan' => $nombre,
        ];

        return [$errores, $cabecera, $detallesLimpios];
    }
}
