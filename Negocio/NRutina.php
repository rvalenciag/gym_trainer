<?php
// NRutina: reglas de programación de planes (CU06) y consulta del cliente (CU07).
require_once __DIR__ . '/../Datos/DRutina.php';
require_once __DIR__ . '/../Datos/DDetalleRutina.php';
require_once __DIR__ . '/../Datos/DCliente.php';
require_once __DIR__ . '/../Datos/DEjercicio.php';

class NRutina {
    public function listar() {
        return (new DRutina())->listarConCliente();
    }

    public function obtenerCabecera($id) {
        return (new DRutina())->obtenerCabecera((int)$id);
    }

    public function listarDetalles($idRutina) {
        return (new DDetalleRutina())->listarPorRutina((int)$idRutina);
    }

    // Valida cabecera + detalle (común a crear y actualizar). Devuelve [errores[], cab, dets].
    private function existeRutina($id) {
        return (new DRutina())->obtenerCabecera((int)$id) !== null;
    }

    private function tieneDetalle($dets) {
        return is_array($dets) && count($dets) > 0;
    }

    private function filaCompleta($d) {
        $dia = (int)($d['dia'] ?? 0);
        $ej = (int)($d['ejercicio'] ?? 0);
        $series = (int)($d['series'] ?? 0);
        $reps = trim((string)($d['repeticiones'] ?? ''));
        return $dia >= 1 && $dia <= 7 && $ej > 0 && $series > 0 && $reps !== '';
    }

    private function haySolape($idCliente, $ini, $fin, $excluirId = null) {
        return count((new DRutina())->buscarSolape((int)$idCliente, $ini, $fin, $excluirId)) > 0;
    }

    private function validar($idInstructor, $idCliente, $nombre, $ini, $fin, $dets, $excluirId = null) {
        $errores = [];
        $nombre = trim($nombre);
        if ((int)$idCliente <= 0 || $nombre === '' || $ini === '' || $fin === '') {
            $errores[] = 'Complete cliente, nombre del plan y fechas de vigencia';
        }
        if ($ini !== '' && $fin !== '' && $fin < $ini) {
            $errores[] = 'La fecha de finalización no puede ser menor a la fecha de inicio';
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
            $dia = (int)$d['dia'];
            $ej = (int)$d['ejercicio'];
            $series = (int)$d['series'];
            $reps = trim((string)$d['repeticiones']);
            $peso = trim((string)($d['peso'] ?? ''));
            $desc = trim((string)($d['descanso'] ?? ''));
            if ($dEj->obtenerPorId($ej) === null) {
                $errores[] = 'Uno de los ejercicios seleccionados no existe';
                break;
            }
            $limpios[] = [
                'id_ejercicio' => $ej,
                'dia_semana' => $dia,
                'series' => $series,
                'repeticiones' => $reps,
                'peso_sugerido' => $peso === '' ? null : $peso,
                'descanso' => $desc === '' ? null : (int)$desc,
            ];
        }
        // Regla no-solape A1 (bordes cuentan; al editar se excluye la propia).
        if (count($errores) === 0 && $ini !== '' && $fin !== '' && $this->haySolape($idCliente, $ini, $fin, $excluirId)) {
            $errores[] = 'Ya existe un plan asignado a este cliente en esas fechas';
        }
        $cab = ['id_instructor' => (int)$idInstructor, 'id_cliente' => (int)$idCliente,
            'nombre_plan' => $nombre, 'fecha_inicio' => $ini, 'fecha_fin' => $fin];
        return [$errores, $cab, $limpios];
    }

    // Composición fuerte sin D->D: N orquesta DRutina + DDetalleRutina dentro de UNA
    // transacción (wrappers de DRutina; N nunca ve PDO ni SQL). Todo o nada.
    public function crear($idInstructor, $idCliente, $nombre, $ini, $fin, $dets) {
        list($errores, $cab, $limpios) = $this->validar($idInstructor, $idCliente, $nombre, $ini, $fin, $dets);
        if (count($errores) > 0) {
            return ['ok' => false, 'msg' => $errores[0]];
        }
        $dr = new DRutina();
        $dd = new DDetalleRutina();
        $dr->iniciarTransaccion();
        try {
            $id = $dr->crearCabecera($cab);
            foreach ($limpios as $d) {
                $dd->insertar($id, $d);
            }
            $dr->confirmar();
        } catch (Exception $e) {
            $dr->revertir();
            return ['ok' => false, 'msg' => 'No se pudo guardar la rutina'];
        }
        return $id > 0
            ? ['ok' => true, 'msg' => 'Rutina creada y asignada con éxito al cliente']
            : ['ok' => false, 'msg' => 'No se pudo guardar la rutina'];
    }

    public function actualizar($id, $idCliente, $nombre, $ini, $fin, $dets, $idInstructor = 0) {
        if (!$this->existeRutina($id)) {
            return ['ok' => false, 'msg' => 'La rutina no existe'];
        }
        list($errores, $cab, $limpios) = $this->validar(
            $idInstructor, $idCliente, $nombre, $ini, $fin, $dets, (int)$id);
        if (count($errores) > 0) {
            return ['ok' => false, 'msg' => $errores[0]];
        }
        $dr = new DRutina();
        $dr->iniciarTransaccion();
        try {
            $dr->actualizarCabecera((int)$id, $cab);
            (new DDetalleRutina())->reemplazarDeRutina((int)$id, $limpios);
            $dr->confirmar();
            $ok = true;
        } catch (Exception $e) {
            $dr->revertir();
            $ok = false;
        }
        return ['ok' => $ok, 'msg' => $ok ? 'Rutina actualizada correctamente' : 'No se pudo actualizar'];
    }

    public function eliminar($id) {
        $ok = (new DRutina())->eliminar((int)$id);
        return ['ok' => $ok, 'msg' => $ok ? 'Rutina eliminada del sistema' : 'No se pudo eliminar'];
    }

    // CU07: plan vigente + ejercicios del día (solo lectura).
    public function vigenteDelDia($idCliente, $hoy, $dia) {
        $rut = (new DRutina())->obtenerVigente((int)$idCliente, $hoy);
        if ($rut === null) {
            return ['rutina' => null, 'detalles' => []];
        }
        $det = (new DDetalleRutina())->listarPorRutinaYDia((int)$rut['id'], (int)$dia);
        return ['rutina' => $rut, 'detalles' => $det];
    }
}
