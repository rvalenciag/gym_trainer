<?php
// Punto de entrada único (front-controller, capa P).
// Lista blanca ruta → [archivo, clase, método] + guardia por rol.
define('APP', true);
date_default_timezone_set('America/La_Paz');
session_start();

$rutas_instructor = [
    'categorias' => 'PCategorias',      // CU03
    'ejercicios' => 'PEjercicios',      // CU04
    'clientes' => 'PClientes',          // CU05
    'rutinas' => 'PRutinas',            // CU06 lista
    'crear_rutina' => 'PCrearRutina',   // CU06 diseño
];
$rutas_cliente = [
    'mi-rutina' => 'PMiRutina',         // CU07
    'detalle' => 'PDetalleEjercicio',   // CU07 guía
];
$archivos = [
    'PLogin' => 'Presentacion/auth/PLogin.php',
    'PSalir' => 'Presentacion/auth/PSalir.php',
    'PCategorias' => 'Presentacion/entrenador/PCategorias.php',
    'PEjercicios' => 'Presentacion/entrenador/PEjercicios.php',
    'PClientes' => 'Presentacion/entrenador/PClientes.php',
    'PRutinas' => 'Presentacion/entrenador/PRutinas.php',
    'PCrearRutina' => 'Presentacion/entrenador/PCrearRutina.php',
    'PMiRutina' => 'Presentacion/cliente/PMiRutina.php',
    'PDetalleEjercicio' => 'Presentacion/cliente/PDetalleEjercicio.php',
];
$inicio_instructor = 'index.php?r=categorias';
$inicio_cliente = 'index.php?r=mi-rutina';

$r = $_GET['r'] ?? '';

// CU02: salir destruye la sesión.
if ($r === 'salir') {
    require $archivos['PSalir'];
    (new PSalir())->cerrar();
}

// Sin sesión: solo login (CU01).
if (!isset($_SESSION['uid'])) {
    require $archivos['PLogin'];
    (new PLogin())->mostrar();
    exit;
}

// Con sesión: cada rol solo ve sus pantallas.
$rol = $_SESSION['rol'];
if ($rol === 'INSTRUCTOR') {
    if ($r === '' || $r === 'login' || !isset($rutas_instructor[$r])) {
        header('Location: ' . $inicio_instructor);
        exit;
    }
    $clase = $rutas_instructor[$r];
    require $archivos[$clase];
    (new $clase())->mostrar();
    exit;
}
if ($rol === 'CLIENTE') {
    if ($r === '' || $r === 'login' || !isset($rutas_cliente[$r])) {
        header('Location: ' . $inicio_cliente);
        exit;
    }
    $clase = $rutas_cliente[$r];
    require $archivos[$clase];
    (new $clase())->mostrar();
    exit;
}
// Rol desconocido: fuera.
header('Location: index.php?r=salir');
exit;
