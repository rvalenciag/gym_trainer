<?php
// NCliente: reglas de gestión de alumnos (CU05). Hashea con BCRYPT al registrar.
require_once __DIR__ . '/../Datos/DCliente.php';
require_once __DIR__ . '/../Datos/DUsuario.php';

class NCliente {
    public function listar() {
        return (new DCliente())->listarConUsuario();
    }

    public function obtener($id) {
        return (new DCliente())->obtenerConUsuario((int)$id);
    }

    private function correoValido($correo) {
        return filter_var(trim($correo), FILTER_VALIDATE_EMAIL) !== false;
    }

    private function correoDuplicado($correo, $excluirId = null) {
        return (new DUsuario())->existeCorreo(trim($correo), $excluirId === null ? null : (int)$excluirId);
    }

    public function registrar($nombre, $correo, $telefono, $clave, $observacion) {
        $nombre = trim($nombre);
        $correo = trim($correo);
        if ($nombre === '' || $correo === '' || $clave === '') {
            return ['ok' => false, 'msg' => 'Complete nombre, correo y contraseña inicial'];
        }
        if (!$this->correoValido($correo)) {
            return ['ok' => false, 'msg' => 'El correo electrónico no es válido'];
        }
        if ($this->correoDuplicado($correo)) {
            return ['ok' => false, 'msg' => 'El correo electrónico ya se encuentra registrado en el sistema'];
        }
        $tel = trim($telefono) === '' ? null : trim($telefono);
        $obs = trim($observacion) === '' ? null : trim($observacion);
        $id = (new DCliente())->crearConUsuario(
            $nombre, $correo, $tel, password_hash($clave, PASSWORD_BCRYPT), $obs
        );
        return $id > 0
            ? ['ok' => true, 'msg' => 'Cliente registrado exitosamente']
            : ['ok' => false, 'msg' => 'No se pudo registrar el cliente'];
    }

    public function actualizar($id, $nombre, $correo, $telefono, $observacion) {
        $nombre = trim($nombre);
        $correo = trim($correo);
        if ($nombre === '' || $correo === '') {
            return ['ok' => false, 'msg' => 'Complete nombre y correo'];
        }
        if (!$this->correoValido($correo)) {
            return ['ok' => false, 'msg' => 'El correo electrónico no es válido'];
        }
        if ($this->correoDuplicado($correo, (int)$id)) {
            return ['ok' => false, 'msg' => 'El correo electrónico ya se encuentra registrado en el sistema'];
        }
        $tel = trim($telefono) === '' ? null : trim($telefono);
        $obs = trim($observacion) === '' ? null : trim($observacion);
        $ok = (new DCliente())->actualizarCompleto((int)$id, $nombre, $correo, $tel, $obs);
        return ['ok' => $ok, 'msg' => $ok ? 'Datos del cliente actualizados' : 'No se pudo actualizar'];
    }

    public function eliminar($id) {
        // Borrado físico en cascada (USUARIO arrastra perfil + rutinas + detalles).
        $ok = (new DCliente())->eliminarTodo((int)$id);
        return ['ok' => $ok,
            'msg' => $ok ? 'Cliente y sus registros eliminados permanentemente del sistema'
                                     : 'No se pudo eliminar'];
    }
}
