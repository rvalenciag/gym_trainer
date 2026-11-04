<?php
// Layout común (capa P): header + menú por rol + flash. Acceso solo vía index.php.
defined('APP') || exit('Acceso no permitido');

function flash($tipo, $texto)
{
    $_SESSION['flash'] = ['t' => $tipo, 'm' => $texto];
}

function iconoGym($nombre, $clase = '')
{
    $iconos = [
        'logo' => '<path d="M4 9v6m3-9v12m3-15v18m3-12v6m3-9v12m3-9v6"/><path d="M2 11h2m12 0h2M2 13h2m12 0h2"/>',
        'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c.7-3.3 3.1-5 7-5s6.3 1.7 7 5"/>',
        'category' => '<path d="m12 3 8 4.5-8 4.5-8-4.5L12 3Z"/><path d="m4 12 8 4.5 8-4.5M4 16.5l8 4.5 8-4.5"/>',
        'exercise' => '<path d="M4 9v6m3-9v12m3-15v18m3-12v6m3-9v12m3-9v6"/><path d="M2 11h2m12 0h2M2 13h2m12 0h2"/>',
        'clients' => '<circle cx="9" cy="8" r="3"/><path d="M3.5 19c.5-3 2.3-4.8 5.5-4.8s5 1.8 5.5 4.8"/><path d="M16 5.5a2.7 2.7 0 0 1 0 5.3m1.8 3.8c1.5.7 2.4 2.1 2.7 4"/>',
        'routine' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4m8-4v4M4 10h16m-9 4h.01m4 0h.01m-8 3h.01m4 0h.01"/>',
        'logout' => '<path d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/><path d="m14 8 4 4-4 4m4-4H9"/>',
        'save' => '<path d="M5 4h11l3 3v13H5V4Z"/><path d="M8 4v6h7V4M8 20v-6h8v6"/>',
        'clear' => '<path d="m4 16 7-8 9 9-3 3H8L4 16Z"/><path d="m13 7 2 2"/>',
        'edit' => '<path d="m4 20 4.2-1 9.4-9.4a2.1 2.1 0 0 0-3-3L5.2 16 4 20Z"/><path d="m13 8 3 3"/>',
        'delete' => '<path d="M5 7h14m-9 4v5m4-5v5M8 7l1-3h6l1 3m-9 0 1 13h8l1-13"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8" cy="9" r="1.5"/><path d="m4 18 5-5 3 3 2-2 6 4"/>',
    ];
    $trazo = $iconos[$nombre] ?? '';
    return '<svg class="gym-icon ' . htmlspecialchars($clase) . '" viewBox="0 0 24 24" fill="none" '
        . 'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" '
        . 'aria-hidden="true">' . $trazo . '</svg>';
}

function mostrarHeader($titulo)
{
    $rol = $_SESSION['rol'] ?? '';
    $nombre = htmlspecialchars($_SESSION['nombre'] ?? '');
    $r = $_GET['r'] ?? '';

    $rutasInstructor = ['categorias', 'ejercicios', 'clientes', 'rutinas'];
    $layoutModerno = ($rol === 'INSTRUCTOR' && in_array($r, $rutasInstructor, true))
        || ($rol === 'CLIENTE' && $r === 'mi-rutina');
    $GLOBALS['gym_layout_moderno'] = $layoutModerno;

    if ($layoutModerno) {
        $items = $rol === 'INSTRUCTOR'
            ? [
                'categorias' => ['Categorías', 'category'],
                'ejercicios' => ['Ejercicios', 'exercise'],
                'clientes' => ['Clientes', 'clients'],
                'rutinas' => ['Rutinas', 'routine'],
            ]
            : ['mi-rutina' => ['Mis Rutinas', 'routine']];
        echo '<!DOCTYPE html><html lang="es"><head>';
        echo '<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . htmlspecialchars($titulo) . ' - Gym Trainer</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<link href="Presentacion/assets/css/app.css" rel="stylesheet"></head>';
        echo '<body class="gym-app-body"><div class="gym-app-shell">';
        echo '<aside class="gym-sidebar">';
        echo '<a class="gym-brand" href="index.php?r=categorias">' . iconoGym('logo')
            . '<span>Gym <strong>Trainer</strong></span></a>';
        echo '<div class="gym-profile">' . iconoGym('user', 'gym-profile-icon')
            . '<div><strong>' . ($rol === 'INSTRUCTOR' ? 'Instructor' : 'Cliente') . '</strong><span>'
            . $nombre . '</span></div></div>';
        echo '<nav class="gym-side-nav" aria-label="Navegación principal">';
        foreach ($items as $ruta => $item) {
            $activo = $r === $ruta ? ' active' : '';
            echo '<a class="gym-nav-item' . $activo . '" href="index.php?r=' . $ruta . '">'
                . iconoGym($item[1]) . '<span>' . $item[0] . '</span></a>';
        }
        echo '</nav>';
        echo '<a class="gym-logout" href="index.php?r=salir" '
            . 'onclick="return confirm(\'¿Está seguro de que desea salir del sistema?\')">'
            . iconoGym('logout') . '<span>Cerrar sesión</span></a>';
        echo '</aside><main class="gym-content">';

        if (isset($_SESSION['flash'])) {
            $f = $_SESSION['flash'];
            unset($_SESSION['flash']);
            echo '<div class="alert alert-' . htmlspecialchars($f['t']) . ' alert-dismissible fade show" role="alert">'
                . htmlspecialchars($f['m'])
                . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }
        return;
    }

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
            $act = $r === $k ? ' active' : '';
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

function mostrarFooter()
{
    if (!empty($GLOBALS['gym_layout_moderno'])) {
        echo '</main></div>';
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
        echo '<script src="Presentacion/assets/js/app.js"></script>';
        echo '</body></html>';
        return;
    }
    echo '    </div>';
    echo '    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
    echo '    <script src="Presentacion/assets/js/app.js"></script>';
    echo '</body>';
    echo '</html>';
}
