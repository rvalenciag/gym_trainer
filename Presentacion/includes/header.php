<?php
// Layout común (capa P): header + menú por rol + flash. Acceso solo vía index.php.
defined('APP') || exit('Acceso no permitido');

function flash($tipo, $texto) {
    $_SESSION['flash'] = ['t' => $tipo, 'm' => $texto];
}

function mostrarHeader($titulo) {
    $rol = $_SESSION['rol'] ?? '';
    $nombre = htmlspecialchars($_SESSION['nombre'] ?? '');
    $r = $_GET['r'] ?? '';

    // Menú según rol (CU01 redirige a cada interfaz).
    $menu = '';
    if ($rol === 'INSTRUCTOR') {
        $items = [
            'categorias' => 'Categorías',
            'ejercicios' => 'Ejercicios',
            'clientes' => 'Clientes',
            'rutinas' => 'Rutinas',
        ];
        foreach ($items as $k => $v) {
            $act = ($r === $k || ($k === 'rutinas' && $r === 'crear_rutina')) ? ' active' : '';
            $menu .= '<a class="nav-link' . $act . '" href="index.php?r=' . $k . '">' . $v . '</a>';
        }
    } elseif ($rol === 'CLIENTE') {
        $act = $r === 'mi-rutina' ? ' active' : '';
        $menu = '<a class="nav-link' . $act . '" href="index.php?r=mi-rutina">Mi Rutina</a>';
    }

    echo '<!DOCTYPE html>';
    echo '<html lang="es">';
    echo '<head>';
    echo '    <meta charset="utf-8">';
    echo '    <meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '    <title>' . htmlspecialchars($titulo) . ' - Gym Instructor</title>';
    echo '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '    <link href="Presentacion/assets/css/app.css" rel="stylesheet">';
    echo '</head>';
    echo '<body class="bg-light">';
    echo '    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">';
    echo '        <div class="container-fluid">';
    echo '            <a class="navbar-brand fw-bold" href="index.php"><span class="text-success">Gym</span> Instructor</a>';
    echo '            <div class="navbar-nav me-auto">' . $menu . '</div>';
    echo '            <span class="navbar-text me-3">' . $nombre . ' (' . $rol . ')</span>';
    echo '            <a class="btn btn-sm btn-outline-warning" href="index.php?r=salir" ' .
        'onclick="return confirm(\'¿Está seguro de que desea salir del sistema?\')">Cerrar Sesión</a>';
    echo '        </div>';
    echo '    </nav>';
    echo '    <div class="container py-4">';

    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '        <div class="alert alert-' . htmlspecialchars($f['t']) . ' alert-dismissible fade show" role="alert">';
        echo '            ' . htmlspecialchars($f['m']);
        echo '            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '        </div>';
    }
    echo '        <h2 class="mb-4">' . htmlspecialchars($titulo) . '</h2>';
}

function mostrarFooter() {
    echo '    </div>';
    echo '    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
    echo '    <script src="Presentacion/assets/js/app.js"></script>';
    echo '</body>';
    echo '</html>';
}
