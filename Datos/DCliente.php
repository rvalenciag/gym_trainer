<?php
// DCliente: acceso a la tabla CLIENTE y consultas con su usuario asociado.
require_once __DIR__ . '/Conexion.php';

class DCliente
{
    private $pdo;
    private $idUsuario;
    private $observacion;

    public function __construct($datos = [])
    {
        $this->pdo = Conexion::get();
        $this->idUsuario = $datos['id_usuario'] ?? null;
        $this->observacion = $datos['observacion'] ?? null;
    }

    public function listarConUsuario()
    {
        return $this->pdo->query(
            'SELECT U.id, U.nombre, U.correo, U.telefono, C.observacion
             FROM CLIENTE C
             INNER JOIN USUARIO U ON U.id = C.id_usuario
             ORDER BY U.id'
        )->fetchAll();
    }

    public function obtenerConUsuario()
    {
        $st = $this->pdo->prepare(
            'SELECT U.id, U.nombre, U.correo, U.telefono, C.observacion
             FROM CLIENTE C
             INNER JOIN USUARIO U ON U.id = C.id_usuario
             WHERE U.id = ?'
        );
        $st->execute([(int) $this->idUsuario]);
        $fila = $st->fetch();

        if ($fila === false) {
            return null;
        }

        $this->cargarDatos($fila);
        return $this->aArray($fila);
    }

    public function crearPerfil()
    {
        $st = $this->pdo->prepare(
            'INSERT INTO CLIENTE (id_usuario, observacion) VALUES (?, ?)'
        );

        return $st->execute([
            (int) $this->idUsuario,
            $this->observacion,
        ]);
    }

    public function actualizarPerfil()
    {
        $st = $this->pdo->prepare(
            'UPDATE CLIENTE SET observacion = ? WHERE id_usuario = ?'
        );

        return $st->execute([
            $this->observacion,
            (int) $this->idUsuario,
        ]);
    }

    private function cargarDatos($fila)
    {
        $this->idUsuario = (int) $fila['id'];
        $this->observacion = $fila['observacion'];
    }

    private function aArray($fila)
    {
        return [
            'id' => $this->idUsuario,
            'nombre' => $fila['nombre'],
            'correo' => $fila['correo'],
            'telefono' => $fila['telefono'],
            'observacion' => $this->observacion,
        ];
    }
}
