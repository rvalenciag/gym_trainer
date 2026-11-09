<?php
// NAuth: reglas de autenticación. No recibe directamente datos HTTP.
require_once __DIR__ . '/../Datos/DUsuario.php';

class NAuth
{
    public function verificar($correo, $clave)
    {
        $correo = trim($correo);

        if ($correo === '' || $clave === '') {
            return [
                'ok' => false,
                'msg' => 'Por favor, ingrese su correo y contraseña',
            ];
        }

        $usuario = new DUsuario([
            'correo' => $correo,
        ]);
        $datosUsuario = $usuario->buscarPorCorreo();

        if ($datosUsuario === null || !password_verify($clave, $datosUsuario['contrasena'])) {
            return [
                'ok' => false,
                'msg' => 'Correo electrónico o contraseña incorrectos',
            ];
        }

        unset($datosUsuario['contrasena']);

        return [
            'ok' => true,
            'user' => $datosUsuario,
        ];
    }
}
