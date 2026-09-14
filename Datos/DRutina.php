<?php
// DRutina: espejo de RUTINA (CU06/CU07). Solo cabecera: el detalle vive en DDetalleRutina
// (composición fuerte ◆) y lo orquesta NRutina. Prohibido D->D: esta clase jamás instancia
// otro D. Expone wrappers de transacción para que N coordine ambas tablas sin ver PDO.
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
    // El detalle vive en DDetalleRutina (composición fuerte, ver esa clase).

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

    // Wrappers de transacción para que N coordine RUTINA + DETALLE_RUTINA
    // (mismo PDO singleton: el rollback sigue atómico). N nunca ve PDO ni SQL.
    public function iniciarTransaccion() {
        $this->pdo->beginTransaction();
    }

    public function confirmar() {
        $this->pdo->commit();
    }

    public function revertir() {
        $this->pdo->rollBack();
    }

    // Alta de cabecera. El detalle lo inserta N vía DDetalleRutina dentro de la transacción.
    // $cab: [id_instructor, id_cliente, nombre_plan, fecha_inicio, fecha_fin]
    public function crearCabecera($cab) {
        $st = $this->pdo->prepare(
            'INSERT INTO RUTINA(id_instructor, id_cliente, nombre_plan, fecha_inicio, fecha_fin) ' .
            'VALUES(?, ?, ?, ?, ?)'
        );
        $st->execute([$cab['id_instructor'], $cab['id_cliente'], $cab['nombre_plan'],
            $cab['fecha_inicio'], $cab['fecha_fin']]);
        return (int)$this->pdo->lastInsertId();
    }

    // Actualizar solo cabecera. El reemplazo del detalle lo hace N vía DDetalleRutina.
    public function actualizarCabecera($id, $cab) {
        $st = $this->pdo->prepare(
            'UPDATE RUTINA SET id_cliente = ?, nombre_plan = ?, fecha_inicio = ?, fecha_fin = ? ' .
            'WHERE id = ?'
        );
        return $st->execute([$cab['id_cliente'], $cab['nombre_plan'], $cab['fecha_inicio'],
            $cab['fecha_fin'], $id]);
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
}
