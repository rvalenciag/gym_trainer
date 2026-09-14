<?php
// DCliente: espejo de CLIENTE + JOIN con USUARIO (CU05 crea/lee ambas juntas).
require_once __DIR__ . '/Conexion.php';

class DCliente {
    private $pdo;
    // Atributos espejo de CLIENTE (+ datos de USUARIO que viajan juntos):
    private $id_usuario;
    private $observacion;

    public function __construct() {
        $this->pdo = Conexion::get();
    }

    public function listarConUsuario() {
        return $this->pdo->query(
            'SELECT U.id, U.nombre, U.correo, U.telefono, C.observacion FROM CLIENTE C ' .
            'JOIN USUARIO U ON U.id = C.id_usuario ORDER BY U.id'
        )->fetchAll();
    }

    public function obtenerConUsuario($id) {
        $st = $this->pdo->prepare(
            'SELECT U.id, U.nombre, U.correo, U.telefono, C.observacion FROM CLIENTE C ' .
            'JOIN USUARIO U ON U.id = C.id_usuario WHERE U.id = ?'
        );
        $st->execute([$id]);
        $f = $st->fetch();
        return $f === false ? null : $f;
    }

    // Alta en transacción: USUARIO (rol CLIENTE) + perfil CLIENTE. Todo o nada.
    public function crearConUsuario($nombre, $correo, $telefono, $hash, $observacion) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "INSERT INTO USUARIO(nombre, correo, telefono, contrasena, rol) VALUES(?, ?, ?, ?, 'CLIENTE')"
            );
            $st->execute([$nombre, $correo, $telefono, $hash]);
            $id = (int)$this->pdo->lastInsertId();
            $st = $this->pdo->prepare('INSERT INTO CLIENTE(id_usuario, observacion) VALUES(?, ?)');
            $st->execute([$id, $observacion]);
            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return 0;
        }
    }

    // Edición en transacción: datos de USUARIO + observaciones de CLIENTE.
    public function actualizarCompleto($id, $nombre, $correo, $telefono, $observacion) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                'UPDATE USUARIO SET nombre = ?, correo = ?, telefono = ? WHERE id = ?'
            );
            $st->execute([$nombre, $correo, $telefono, $id]);
            $st = $this->pdo->prepare('UPDATE CLIENTE SET observacion = ? WHERE id_usuario = ?');
            $st->execute([$observacion, $id]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    // Borrado físico en cascada: basta borrar USUARIO (CASCADE arrastra CLIENTE→RUTINA→DETALLE).
    public function eliminarTodo($id) {
        $st = $this->pdo->prepare('DELETE FROM USUARIO WHERE id = ?');
        return $st->execute([$id]);
    }
}
