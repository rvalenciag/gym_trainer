<?php
// DRutina: acceso a la cabecera de la tabla RUTINA.
require_once __DIR__ . '/Conexion.php';

class DRutina
{
    private $pdo;
    private $id;
    private $nombrePlan;
    private $idInstructor;
    private $idCliente;

    public function __construct($datos = [])
    {
        $this->pdo = Conexion::get();
        $this->id = $datos['id'] ?? null;
        $this->nombrePlan = $datos['nombre_plan'] ?? null;
        $this->idInstructor = $datos['id_instructor'] ?? null;
        $this->idCliente = $datos['id_cliente'] ?? null;
    }

    public function iniciarTransaccion()
    {
        $this->pdo->beginTransaction();
    }

    public function confirmar()
    {
        $this->pdo->commit();
    }

    public function revertir()
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function listarConCliente()
    {
        return $this->pdo->query(
            'SELECT R.*, U.nombre AS cliente
             FROM RUTINA R
             INNER JOIN CLIENTE C ON C.id_usuario = R.id_cliente
             INNER JOIN USUARIO U ON U.id = C.id_usuario
             ORDER BY R.id DESC'
        )->fetchAll();
    }

    public function listarPorCliente()
    {
        $st = $this->pdo->prepare(
            'SELECT id, nombre_plan, id_instructor, id_cliente
             FROM RUTINA
             WHERE id_cliente = ?
             ORDER BY id DESC'
        );
        $st->execute([(int) $this->idCliente]);

        return $st->fetchAll();
    }

    public function obtenerCabecera()
    {
        $st = $this->pdo->prepare(
            'SELECT id, nombre_plan, id_instructor, id_cliente
             FROM RUTINA
             WHERE id = ?'
        );
        $st->execute([(int) $this->id]);
        $fila = $st->fetch();

        if ($fila === false) {
            return null;
        }

        $this->cargarDatos($fila);
        return $this->aArray();
    }

    public function obtenerDeCliente()
    {
        $st = $this->pdo->prepare(
            'SELECT id, nombre_plan, id_instructor, id_cliente
             FROM RUTINA
             WHERE id = ? AND id_cliente = ?'
        );
        $st->execute([
            (int) $this->id,
            (int) $this->idCliente,
        ]);
        $fila = $st->fetch();

        if ($fila === false) {
            return null;
        }

        $this->cargarDatos($fila);
        return $this->aArray();
    }

    public function crearCabecera()
    {
        $st = $this->pdo->prepare(
            'INSERT INTO RUTINA (id_instructor, id_cliente, nombre_plan)
             VALUES (?, ?, ?)'
        );
        $st->execute([
            (int) $this->idInstructor,
            (int) $this->idCliente,
            $this->nombrePlan,
        ]);

        $this->id = (int) $this->pdo->lastInsertId();
        return $this->id;
    }

    public function actualizarCabecera()
    {
        $st = $this->pdo->prepare(
            'UPDATE RUTINA
             SET id_cliente = ?, nombre_plan = ?
             WHERE id = ?'
        );

        return $st->execute([
            (int) $this->idCliente,
            $this->nombrePlan,
            (int) $this->id,
        ]);
    }

    public function eliminar()
    {
        $st = $this->pdo->prepare('DELETE FROM RUTINA WHERE id = ?');
        return $st->execute([(int) $this->id]);
    }

    private function cargarDatos($fila)
    {
        $this->id = (int) $fila['id'];
        $this->nombrePlan = $fila['nombre_plan'];
        $this->idInstructor = (int) $fila['id_instructor'];
        $this->idCliente = (int) $fila['id_cliente'];
    }

    private function aArray()
    {
        return [
            'id' => $this->id,
            'nombre_plan' => $this->nombrePlan,
            'id_instructor' => $this->idInstructor,
            'id_cliente' => $this->idCliente,
        ];
    }
}
