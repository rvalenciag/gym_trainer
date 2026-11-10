<?php
// NCliente: reglas para cuentas y perfiles de clientes.
require_once __DIR__ . '/../Datos/DCliente.php';
require_once __DIR__ . '/../Datos/DUsuario.php';

class NCliente
{
    public function listar()
    {
        return (new DCliente())->listarConUsuario();
    }

    public function guardar($nombre, $correo, $telefono, $clave, $observacion)
    {
        $nombre = trim($nombre);
        $correo = trim($correo);

        if ($nombre === '' || $correo === '' || $clave === '') {
            return [
                'ok' => false,
                'msg' => 'Complete nombre, correo y contraseña inicial',
            ];
        }

        if (!$this->correoValido($correo)) {
            return [
                'ok' => false,
                'msg' => 'El correo electrónico no es válido',
            ];
        }

        $telefono = trim($telefono) === '' ? null : trim($telefono);
        $observacion = trim($observacion) === '' ? null : trim($observacion);
        $usuario = new DUsuario([
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'contrasena' => password_hash($clave, PASSWORD_BCRYPT),
            'rol' => 'CLIENTE',
        ]);

        if ($usuario->existeCorreo()) {
            return [
                'ok' => false,
                'msg' => 'El correo electrónico ya se encuentra registrado en el sistema',
            ];
        }

        try {
            $usuario->iniciarTransaccion();
            $idCliente = $usuario->crear();

            $perfil = new DCliente([
                'id_usuario' => $idCliente,
                'observacion' => $observacion,
            ]);
            $perfil->crearPerfil();

            $usuario->confirmar();
        } catch (Exception $e) {
            $usuario->revertir();

            return [
                'ok' => false,
                'msg' => 'No se pudo registrar el cliente',
            ];
        }

        return [
            'ok' => true,
            'msg' => 'Cliente registrado exitosamente',
        ];
    }

    public function actualizar($id, $nombre, $correo, $telefono, $observacion)
    {
        $id = (int) $id;
        $nombre = trim($nombre);
        $correo = trim($correo);

        if ($id <= 0 || $nombre === '' || $correo === '') {
            return [
                'ok' => false,
                'msg' => 'Complete nombre y correo',
            ];
        }

        if (!$this->correoValido($correo)) {
            return [
                'ok' => false,
                'msg' => 'El correo electrónico no es válido',
            ];
        }

        $cliente = new DCliente([
            'id_usuario' => $id,
        ]);

        if ($cliente->obtenerConUsuario() === null) {
            return [
                'ok' => false,
                'msg' => 'El cliente seleccionado no existe',
            ];
        }

        $telefono = trim($telefono) === '' ? null : trim($telefono);
        $observacion = trim($observacion) === '' ? null : trim($observacion);
        $usuario = new DUsuario([
            'id' => $id,
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
        ]);

        if ($usuario->existeCorreo()) {
            return [
                'ok' => false,
                'msg' => 'El correo electrónico ya se encuentra registrado en el sistema',
            ];
        }

        try {
            $usuario->iniciarTransaccion();
            $usuario->actualizar();

            $perfil = new DCliente([
                'id_usuario' => $id,
                'observacion' => $observacion,
            ]);
            $perfil->actualizarPerfil();

            $usuario->confirmar();
        } catch (Exception $e) {
            $usuario->revertir();

            return [
                'ok' => false,
                'msg' => 'No se pudo actualizar',
            ];
        }

        return [
            'ok' => true,
            'msg' => 'Datos del cliente actualizados',
        ];
    }

    public function eliminar($id)
    {
        $id = (int) $id;
        $cliente = new DCliente([
            'id_usuario' => $id,
        ]);

        if ($cliente->obtenerConUsuario() === null) {
            return [
                'ok' => false,
                'msg' => 'El cliente seleccionado no existe',
            ];
        }

        $ok = (new DUsuario([
            'id' => $id,
        ]))->eliminar();

        return [
            'ok' => $ok,
            'msg' => $ok
                ? 'Cliente y sus registros eliminados permanentemente del sistema'
                : 'No se pudo eliminar',
        ];
    }

    private function correoValido($correo)
    {
        return filter_var(trim($correo), FILTER_VALIDATE_EMAIL) !== false;
    }
}
