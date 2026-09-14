<?php
// PMiRutina: presentación de Mi Rutina, cabecera + barra Día 1-7 + lista del día (CU07, solo lectura).
// Los 7 días se pintan desde PHP; JS solo alterna cuál se ve ( + ?dia= sin JS).
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NRutina.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/media.php';

class PMiRutina {
    public function verRutina($idCliente, $hoy, $dia) {
        return (new NRutina())->vigenteDelDia($idCliente, $hoy, $dia);
    }

    public function mostrar() {
        $hoy = date('Y-m-d');
        $diaActual = (int)date('N'); // 1=Lun ... 7=Dom (Regla 4)
        $dia = isset($_GET['dia']) ? (int)$_GET['dia'] : $diaActual;
        if ($dia < 1 || $dia > 7) {
            $dia = $diaActual;
        }
        $res = $this->verRutina($_SESSION['uid'], $hoy, $dia);
        $rutina = $res['rutina'];
        mostrarHeader('Mi Rutina');

        if ($rutina === null) {
            echo '<div class="card"><div class="card-body text-center">';
            echo '    <p class="mb-0">Aún no tienes una rutina activa asignada. ' .
                'Ponte en contacto con tu instructor para que te diseñe un plan</p>';
            echo '</div></div>';
            mostrarFooter();
            return;
        }

        // Cabecera del plan vigente.
        echo '<div class="card mb-3"><div class="card-body">';
        echo '    <h5 class="card-title mb-1">' . htmlspecialchars($rutina['nombre_plan']) . '</h5>';
        echo '    <span class="text-muted">Vigencia: ' . $rutina['fecha_inicio'] . ' al ' . $rutina['fecha_fin'] . '</span>';
        echo '</div></div>';

        // Barra de días (el punto ● marca hoy).
        echo '<div class="mb-3 d-flex gap-2 flex-wrap">';
        $nombres = [1 => 'Día 1', 2 => 'Día 2', 3 => 'Día 3', 4 => 'Día 4', 5 => 'Día 5', 6 => 'Día 6', 7 => 'Día 7'];
        foreach ($nombres as $k => $v) {
            $act = $k === $dia ? ' active' : '';
            $hoyM = $k === $diaActual ? ' ●' : '';
            echo '    <a href="index.php?r=mi-rutina&dia=' . $k . '" data-dia-btn="' . $k . '" ' .
                'class="btn btn-outline-dark dia-btn' . $act . '">' . $v . $hoyM . '</a>';
        }
        echo '</div>';

        // Secciones por día (solo la seleccionada visible).
        for ($d = 1; $d <= 7; $d++) {
            $lista = $d === $dia
                ? $res['detalles']
                : $this->verRutina($_SESSION['uid'], $hoy, $d)['detalles'];
            echo '<div data-dia-sec="' . $d . '"' . ($d === $dia ? '' : ' style="display:none"') . '>';
            if (count($lista) === 0) {
                echo '    <div class="alert alert-info">Descanso: sin ejercicios este día.</div>';
            } else {
                echo '    <table class="table table-striped table-bordered bg-white">';
                echo '        <thead class="table-dark">';
                echo '            <tr><th>Ejercicio</th><th>Series</th><th>Reps</th><th>Peso</th><th>Descanso</th><th></th></tr>';
                echo '        </thead>';
                echo '        <tbody>';
                foreach ($lista as $e) {
                    $gid = 'g' . $d . '_' . $e['id'];
                    echo '            <tr>';
                    echo '                <td>' . htmlspecialchars($e['ejercicio']) . '</td>';
                    echo '                <td>' . $e['series'] . '</td>';
                    echo '                <td>' . htmlspecialchars($e['repeticiones']) . '</td>';
                    echo '                <td>' . htmlspecialchars($e['peso_sugerido'] ?? '-') . '</td>';
                    echo '                <td>' . htmlspecialchars($e['descanso'] ?? '-') . ' seg</td>';
                    echo '                <td><button class="btn btn-sm btn-info" data-guia-btn="' . $gid . '">Ver Guía Multimedia</button></td>';
                    echo '            </tr>';
                    echo '            <tr id="' . $gid . '" style="display:none"><td colspan="6">';
                    mostrarGuia($e);
                    echo '            </td></tr>';
                }
                echo '        </tbody>';
                echo '    </table>';
            }
            echo '</div>';
        }
        mostrarFooter();
    }
}
