<?php
// DUsuario: acceso a la tabla USUARIO.
require_once __DIR__ . '/Conexion.php';

class DUsuario
{
    private $pdo;
    private $id;
    private $nombre;
    private $correo;
    private $telefono;
    private $contrasena;
    private $rol;
    private $fecha;

    public function __construct($datos = [])
    {
        $this->pdo = Conexion::get();
        $this->id = $datos['id'] ?? null;
        $this->nombre = $datos['nombre'] ?? null;
        $this->correo = $datos['correo'] ?? null;
        $this->telefono = $datos['telefono'] ?? null;
        $this->contrasena = $datos['contrasena'] ?? null;
        $this->rol = $datos['rol'] ?? null;
        $this->fecha = $datos['fecha'] ?? null;
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

    public function buscarPorCorreo()
    {
        $st = $this->pdo->prepare(
            'SELECT id, nombre, correo, telefono, contrasena, rol, fecha
             FROM USUARIO
             WHERE correo = ?'
        );
        $st->execute([$this->correo]);
        $fila = $st->fetch();

        if ($fila === false) {
            return null;
        }

        $this->cargarDatos($fila);
        return $this->aArray();
    }

    public function obtenerPorId()
    {
        $st = $this->pdo->prepare(
            'SELECT id, nombre, correo, telefono, contrasena, rol, fecha
             FROM USUARIO
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

    public function crear()
    {
        $st = $this->pdo->prepare(
            'INSERT INTO USUARIO (nombre, correo, telefono, contrasena, rol)
             VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([
            $this->nombre,
            $this->correo,
            $this->telefono,
            $this->contrasena,
            $this->rol,
        ]);

        $this->id = (int) $this->pdo->lastInsertId();
        return $this->id;
    }

    public function actualizar()
    {
        $st = $this->pdo->prepare(
            'UPDATE USUARIO
             SET nombre = ?, correo = ?, telefono = ?
             WHERE id = ?'
        );

        return $st->execute([
            $this->nombre,
            $this->correo,
            $this->telefono,
            (int) $this->id,
        ]);
    }

    public function eliminar()
    {
        $st = $this->pdo->prepare('DELETE FROM USUARIO WHERE id = ?');
        return $st->execute([(int) $this->id]);
    }

    public function existeCorreo()
    {
        if ($this->id === null) {
            $st = $this->pdo->prepare(
                'SELECT COUNT(*) AS n FROM USUARIO WHERE correo = ?'
            );
            $st->execute([$this->correo]);
        } else {
            $st = $this->pdo->prepare(
                'SELECT COUNT(*) AS n FROM USUARIO WHERE correo = ? AND id <> ?'
            );
            $st->execute([$this->correo, (int) $this->id]);
        }

        $fila = $st->fetch();
        return (int) $fila['n'] > 0;
    }

    private function cargarDatos($fila)
    {
        $this->id = (int) $fila['id'];
        $this->nombre = $fila['nombre'];
        $this->correo = $fila['correo'];
        $this->telefono = $fila['telefono'];
        $this->contrasena = $fila['contrasena'];
        $this->rol = $fila['rol'];
        $this->fecha = $fila['fecha'];
    }

    private function aArray()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'contrasena' => $this->contrasena,
            'rol' => $this->rol,
            'fecha' => $this->fecha,
        ];
    }
}
