<?php
defined('APP') || exit('Acceso no permitido');

require_once __DIR__ . '/../includes/header.php';

abstract class PGestionBase {
    protected int $idRegistro = 0;
    protected string $titulo = '';
    protected string $ruta = '';

    final public function mostrar(): void {
        $this->procesarFormulario();
        $this->procesarEliminacion();
        $this->prepararVista();

        mostrarHeader($this->titulo);
        $this->renderizarVista();
        mostrarFooter();
    }

    protected function procesarFormulario(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST')
            return;

        $this->idRegistro = (int)($_POST['id'] ?? 0);
        $this->leerFormulario();

        $resultado = $this->idRegistro === 0
            ? $this->guardar()
            : $this->actualizar();

        $this->finalizar($resultado);
    }

    protected function procesarEliminacion(): void {
        if (!isset($_GET['del']))
            return;

        $this->idRegistro = (int) $_GET['del'];

        $resultado = $this->eliminar();
        $this->finalizar($resultado);
    }

    private function finalizar(array $resultado): void {
        flash(
            $resultado['ok'] ? 'success' : 'danger',
            $resultado['msg']
        );

        header('Location: index.php?r=' . $this->ruta);
        exit;
    }

    // Deben implementar las clases hijas
    abstract protected function leerFormulario(): void;
    abstract protected function guardar(): array;
    abstract protected function actualizar(): array;
    abstract protected function eliminar(): array;
    abstract protected function prepararVista(): void;
    abstract protected function renderizarVista(): void;
}
