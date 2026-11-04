<?php
// NRutina: reglas para planes semanales y consulta de planes asignados.
require_once __DIR__ . '/../Datos/DRutina.php';
require_once __DIR__ . '/../Datos/DDetalleRutina.php';
require_once __DIR__ . '/../Datos/DCliente.php';
require_once __DIR__ . '/../Datos/DEjercicio.php';

class NRutina
{
    public function listar()
    {
        return (new DRutina())->listarConCliente();
    }

    public function listarPorCliente($idCliente)
    {
        return (new DRutina())->listarPorCliente((int)$idCliente);
    }

    public function obtenerCabecera($id)
    {
        return (new DRutina())->obtenerCabecera((int)$id);
    }

    public function listarDetalles($idRutina)
    {
        return (new DDetalleRutina())->listarPorRutina((int)$idRutina);
    }

    private function existeRutina($id)
    {
        return (new DRutina())->obtenerCabecera((int)$id) !== null;
    }

    private function tieneDetalle($dets)
    {
        return is_array($dets) && count($dets) > 0;
    }

    private function filaCompleta($d)
    {
        return (int)($d['dia'] ?? 0) >= 1 && (int)($d['dia'] ?? 0) <= 7
            && (int)($d['ejercicio'] ?? 0) > 0 && (int)($d['series'] ?? 0) > 0
            && trim((string)($d['repeticiones'] ?? '')) !== '';
    }

    private function validar($idInstructor, $idCliente, $nombre, $dets)
    {
        $errores = [];
        $nombre = trim($nombre);
        if ((int)$idCliente <= 0 || $nombre === '') {
            $errores[] = 'Complete cliente y nombre del plan';
        }
        if ((new DCliente())->obtenerConUsuario((int)$idCliente) === null) {
            $errores[] = 'El cliente seleccionado no existe';
        }
        if (!$this->tieneDetalle($dets)) {
            $errores[] = 'Debe agregar al menos un ejercicio a la lista antes de guardar la rutina';
        }
        $limpios = [];
        $dEj = new DEjercicio();
        foreach ((array)$dets as $d) {
            if (!$this->filaCompleta($d)) {
                $errores[] = 'Cada ejercicio debe tener día, ejercicio, series y repeticiones (peso y descanso son opcionales)';
                break;
            }
            $ej = (int)$d['ejercicio'];
            if ($dEj->obtenerPorId($ej) === null) {
                $errores[] = 'Uno de los ejercicios seleccionados no existe';
                break;
            }
            $peso = trim((string)($d['peso'] ?? ''));
            $desc = trim((string)($d['descanso'] ?? ''));
            $limpios[] = [
                'id_ejercicio' => $ej,
                'dia_semana' => (int)$d['dia'],
                'series' => (int)$d['series'],
                'repeticiones' => trim((string)$d['repeticiones']),
                'peso_sugerido' => $peso === '' ? null : $peso,
                'descanso' => $desc === '' ? null : (int)$desc,
            ];
        }
        $cabecera = [
            'id_instructor' => (int)$idInstructor,
            'id_cliente' => (int)$idCliente,
            'nombre_plan' => $nombre,
        ];
        return [$errores, $cabecera, $limpios];
    }

    public function crear($idInstructor, $idCliente, $nombre, $dets)
    {
        list($errores, $cab, $limpios) = $this->validar($idInstructor, $idCliente, $nombre, $dets);
        if (count($errores) > 0) {
            return ['ok' => false, 'msg' => $errores[0]];
        }
        $dr = new DRutina();
        try {
            $dr->iniciarTransaccion();
            $id = $dr->crearCabecera($cab);
            $dd = new DDetalleRutina();
            foreach ($limpios as $d) {
                $dd->insertar($id, $d);
            }
            $dr->confirmar();
            return ['ok' => true, 'msg' => 'Rutina creada y asignada con éxito al cliente'];
        } catch (Exception $e) {
            $dr->revertir();
            return ['ok' => false, 'msg' => 'No se pudo guardar la rutina'];
        }
    }

    public function actualizar($id, $idCliente, $nombre, $dets, $idInstructor = 0)
    {
        if (!$this->existeRutina($id)) {
            return ['ok' => false, 'msg' => 'La rutina no existe'];
        }
        list($errores, $cab, $limpios) = $this->validar($idInstructor, $idCliente, $nombre, $dets);
        if (count($errores) > 0) {
            return ['ok' => false, 'msg' => $errores[0]];
        }
        $dr = new DRutina();
        try {
            $dr->iniciarTransaccion();
            $dr->actualizarCabecera((int)$id, $cab);
            (new DDetalleRutina())->reemplazarDeRutina((int)$id, $limpios);
            $dr->confirmar();
            return ['ok' => true, 'msg' => 'Rutina actualizada correctamente'];
        } catch (Exception $e) {
            $dr->revertir();
            return ['ok' => false, 'msg' => 'No se pudo actualizar'];
        }
    }

    public function eliminar($id)
    {
        $ok = (new DRutina())->eliminar((int)$id);
        return ['ok' => $ok, 'msg' => $ok ? 'Rutina eliminada del sistema' : 'No se pudo eliminar'];
    }

    public function rutinaDelDia($idCliente, $idRutina, $dia)
    {
        $rutina = (new DRutina())->obtenerDeCliente((int)$idRutina, (int)$idCliente);
        if ($rutina === null) {
            return ['rutina' => null, 'detalles' => []];
        }
        return [
            'rutina' => $rutina,
            'detalles' => (new DDetalleRutina())->listarPorRutinaYDia((int)$rutina['id'], (int)$dia),
        ];
    }
}
