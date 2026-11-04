<?php
// PSalir: cierre de sesión (CU02). Acción pura, sin vista: destruye y vuelve al login.
defined('APP') || exit('Acceso no permitido');

class PSalir
{
    public function cerrar()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: index.php?r=login&fin=1');
        exit;
    }
}
