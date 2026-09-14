<?php
// Guía multimedia de un ejercicio (parcial P, lo usan mis_rutinas y detalle).
// $e: fila con descripcion, tipo_multimedia, url.
defined('APP') || exit('Acceso no permitido');

function mostrarGuia($e) {
    echo '<div class="guia-box">';
    echo '    <p class="mb-2"><strong>Instrucciones:</strong><br>' .
        nl2br(htmlspecialchars($e['descripcion'] ?? 'Sin instrucciones')) . '</p>';

    $tipo = $e['tipo_multimedia'] ?? '';
    $url = (string)($e['url'] ?? '');
    if ($tipo === 'IMAGEN' && $url !== '') {
        echo '    <img src="' . htmlspecialchars($url) . '" class="img-fluid guia-img" alt="Guía">';
    } elseif ($tipo === 'VIDEO_URL' && $url !== '') {
        echo '    <a class="btn btn-sm btn-info" target="_blank" href="' . htmlspecialchars($url) . '">Ver video instructivo</a>';
    } elseif ($tipo === 'IFRAME_HTML' && $url !== '') {
        echo '    ' . $url; // ya saneado por NEjercicio al registrar
    } else {
        echo '    <span class="text-muted">Sin recurso multimedia</span>';
    }
    echo '</div>';
}
