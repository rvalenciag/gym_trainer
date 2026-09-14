<?php
// NAuth: reglas de autenticación (CU01/CU02). Sin atributos, sin SQL, sin $_POST.
// Devuelve ['ok'=>bool, 'user'=>array|'msg'=>string] y P maneja la sesión.
require_once __DIR__ . '/../Datos/DUsuario.php';

class NAuth {
    public function verificar($correo, $clave) {
        $correo = trim($correo);
        if ($correo === '' || $clave === '') {
            return ['ok' => false, 'msg' => 'Por favor, ingrese su correo y contraseña'];
        }
        $u = (new DUsuario())->buscarPorCorreo($correo);
        if ($u === null || !password_verify($clave, $u['contrasena'])) {
            return ['ok' => false, 'msg' => 'Correo electrónico o contraseña incorrectos'];
        }
        unset($u['contrasena']);
        return ['ok' => true, 'user' => $u];
    }
}
