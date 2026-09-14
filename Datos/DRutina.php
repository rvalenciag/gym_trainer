<?php
// DRutina: espejo de RUTINA + DETALLE_RUTINA (composición, CU06/CU07).
require_once __DIR__ . '/Conexion.php';

class DRutina {
    private $pdo;
    // Atributos espejo de RUTINA:
    private $id;
    private $nombre_plan;
    private $fecha_inicio;
    private $fecha_fin;
    private $id_instructor;
    private $id_cliente;
    // Atributos espejo de DETALLE_RUTINA (viajan en arrays, ver crearConDetalle):
    // id_rutina, id_ejercicio, dia_semana, series, repeticiones, peso_sugerido, descanso

    public function __construct() {
        $this->pdo = Conexion::get();
    }

    public function listarConCliente() {
        return $this->pdo->query(
            'SELECT R.*, U.nombre AS cliente FROM RUTINA R ' .
            'JOIN CLIENTE C ON C.id_usuario = R.id_cliente ' .
            'JOIN USUARIO U ON U.id = C.id_usuario ORDER BY R.id'
        )->fetchAll();
    }

    public function obtenerCabecera($id) {
        $st = $this->pdo->prepare('SELECT * FROM RUTINA WHERE id = ?');
        $st->execute([$id]);
        $f = $st->fetch();
        return $f === false ? null : $f;
    }

    public function listarDetalles($idRutina) {
        $st = $this->pdo->prepare(
            'SELECT D.*, E.nombre AS ejercicio FROM DETALLE_RUTINA D ' .
            'JOIN EJERCICIO E ON E.id = D.id_ejercicio WHERE D.id_rutina = ? ORDER BY D.dia_semana, D.id'
        );
        $st->execute([$idRutina]);
        return $st->fetchAll();
    }

    // Alta en transacción: cabecera + N detalles. Se llama UNA vez al Guardar. Todo o nada.
    // $cab: [id_instructor, id_cliente, nombre_plan, fecha_inicio, fecha_fin]
    // $dets[]: [id_ejercicio, dia_semana, series, repeticiones, peso_sugerido, descanso]
    public function crearConDetalle($cab, $dets) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                'INSERT INTO RUTINA(id_instructor, id_cliente, nombre_plan, fecha_inicio, fecha_fin) ' .
                'VALUES(?, ?, ?, ?, ?)'
            );
            $st->execute([$cab['id_instructor'], $cab['id_cliente'], $cab['nombre_plan'],
                $cab['fecha_inicio'], $cab['fecha_fin']]);
            $id = (int)$this->pdo->lastInsertId();
            $st = $this->pdo->prepare(
                'INSERT INTO DETALLE_RUTINA(id_rutina, id_ejercicio, dia_semana, series, repeticiones, ' .
                'peso_sugerido, descanso) VALUES(?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($dets as $d) {
                $st->execute([$id, $d['id_ejercicio'], $d['dia_semana'], $d['series'],
                    $d['repeticiones'], $d['peso_sugerido'], $d['descanso']]);
            }
            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return 0;
        }
    }

    // Actualizar: UPDATE cabecera + reemplazo total del detalle (DELETE + INSERT). Todo o nada.
    public function actualizarConDetalle($id, $cab, $dets) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                'UPDATE RUTINA SET id_cliente = ?, nombre_plan = ?, fecha_inicio = ?, fecha_fin = ? ' .
                'WHERE id = ?'
            );
            $st->execute([$cab['id_cliente'], $cab['nombre_plan'], $cab['fecha_inicio'],
                $cab['fecha_fin'], $id]);
            $st = $this->pdo->prepare('DELETE FROM DETALLE_RUTINA WHERE id_rutina = ?');
            $st->execute([$id]);
            $st = $this->pdo->prepare(
                'INSERT INTO DETALLE_RUTINA(id_rutina, id_ejercicio, dia_semana, series, repeticiones, ' .
                'peso_sugerido, descanso) VALUES(?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($dets as $d) {
                $st->execute([$id, $d['id_ejercicio'], $d['dia_semana'], $d['series'],
                    $d['repeticiones'], $d['peso_sugerido'], $d['descanso']]);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    // Eliminar: basta borrar RUTINA (CASCADE arrastra el DETALLE).
    public function eliminar($id) {
        $st = $this->pdo->prepare('DELETE FROM RUTINA WHERE id = ?');
        return $st->execute([$id]);
    }

    // Regla no-solape A1: planes del cliente que cruzan [ini, fin] (bordes cuentan).
    public function buscarSolape($idCliente, $ini, $fin, $excluirId = null) {
        if ($excluirId === null) {
            $st = $this->pdo->prepare(
                'SELECT id, nombre_plan, fecha_inicio, fecha_fin FROM RUTINA ' .
                'WHERE id_cliente = ? AND fecha_inicio <= ? AND fecha_fin >= ?'
            );
            $st->execute([$idCliente, $fin, $ini]);
        } else {
            $st = $this->pdo->prepare(
                'SELECT id, nombre_plan, fecha_inicio, fecha_fin FROM RUTINA ' .
                'WHERE id_cliente = ? AND fecha_inicio <= ? AND fecha_fin >= ? AND id <> ?'
            );
            $st->execute([$idCliente, $fin, $ini, $excluirId]);
        }
        return $st->fetchAll();
    }

    // CU07: plan vigente a la fecha dada (gracias al no-solape, es 0 o 1).
    public function obtenerVigente($idCliente, $hoy) {
        $st = $this->pdo->prepare(
            'SELECT * FROM RUTINA WHERE id_cliente = ? AND fecha_inicio <= ? AND fecha_fin >= ? ' .
            'ORDER BY fecha_inicio LIMIT 1'
        );
        $st->execute([$idCliente, $hoy, $hoy]);
        $f = $st->fetch();
        return $f === false ? null : $f;
    }

    // CU07: ejercicios de un día con su guía (JOIN ejercicio).
    public function listarDetallesPorDia($idRutina, $dia) {
        $st = $this->pdo->prepare(
            'SELECT D.*, E.nombre AS ejercicio, E.descripcion, E.tipo_multimedia, E.url ' .
            'FROM DETALLE_RUTINA D JOIN EJERCICIO E ON E.id = D.id_ejercicio ' .
            'WHERE D.id_rutina = ? AND D.dia_semana = ? ORDER BY D.id'
        );
        $st->execute([$idRutina, $dia]);
        return $st->fetchAll();
    }
}
