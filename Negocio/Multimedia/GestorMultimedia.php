<?php
require_once __DIR__ . '/ProcesadorMultimedia.php';

class GestorMultimedia {
    private ProcesadorMultimedia $estrategia;

    public function setEstrategia(ProcesadorMultimedia $estrategia) {
        $this->estrategia = $estrategia;
    }

    public function procesar(array $datos = []) : array {
        return $this->estrategia->procesar($datos);
    }
}
