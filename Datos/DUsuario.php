<?php
// DUsuario: espejo de la tabla USUARIO. La usan NAuth (login) y NCliente (altas).
require_once __DIR__ . '/Conexion.php';

class DUsuario
{
    private $pdo;
    // Atributos espejo de USUARIO (documental: diagrama de clases = código):
    private $id;
    private $nombre;
    private $correo;
    private $telefono;
    private $contrasena;
    private $rol;
    private $fecha;

    public function __construct()
    {
        $this->pdo = Conexion::get();
    }

    public function buscarPorCorreo($correo)
    {
        $st = $this->pdo->prepare('SELECT * FROM USUARIO WHERE correo = ?');
        $st->execute([$correo]);
        $f = $st->fetch();
        return $f === false ? null : $f;
    }

    public function obtenerPorId($id)
    {
        $st = $this->pdo->prepare('SELECT * FROM USUARIO WHERE id = ?');
        $st->execute([$id]);
        $f = $st->fetch();
        return $f === false ? null : $f;
    }

    public function crear($nombre, $correo, $telefono, $hash, $rol)
    {
        $st = $this->pdo->prepare(
            'INSERT INTO USUARIO(nombre, correo, telefono, contrasena, rol) VALUES(?, ?, ?, ?, ?)'
        );
        $st->execute([$nombre, $correo, $telefono, $hash, $rol]);
        return (int)$this->pdo->lastInsertId();
    }

    // $excluirId: al editar, el propio correo no cuenta como duplicado.
    public function existeCorreo($correo, $excluirId = null)
    {
        if ($excluirId === null) {
            $st = $this->pdo->prepare('SELECT COUNT(*) AS n FROM USUARIO WHERE correo = ?');
            $st->execute([$correo]);
        } else {
            $st = $this->pdo->prepare('SELECT COUNT(*) AS n FROM USUARIO WHERE correo = ? AND id <> ?');
            $st->execute([$correo, $excluirId]);
        }
        $f = $st->fetch();
        return ((int)$f['n']) > 0;
    }
}
