<?php
// DDetalleRutina: espejo de DETALLE_RUTINA (composición fuerte de RUTINA: rombo negro).
// Sin cabecera no existe: nace y muere con su rutina (CASCADE en BD, transacción en DRutina).
require_once __DIR__ . '/Conexion.php';

class DDetalleRutina {
    private $pdo;
    // Atributos espejo de DETALLE_RUTINA:
    private $id;
    private $id_rutina; // FK → RUTINA (composición)
    private $id_ejercicio; // FK → EJERCICIO
    private $dia_semana;
    private $series;
    private $repeticiones;
    private $peso_sugerido;
    private $descanso;

    public function __construct() {
        $this->pdo = Conexion::get();
    }

    public function insertar($idRutina, $fila) {
        $st = $this->pdo->prepare(
            'INSERT INTO DETALLE_RUTINA(id_rutina, id_ejercicio, dia_semana, series, repeticiones, ' .
            'peso_sugerido, descanso) VALUES(?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([(int)$idRutina, $fila['id_ejercicio'], $fila['dia_semana'], $fila['series'],
            $fila['repeticiones'], $fila['peso_sugerido'], $fila['descanso']]);
        return (int)$this->pdo->lastInsertId();
    }

    // Reemplazo total del detalle (lo usa Opción B de CU06): DELETE + INSERT en lote.
    public function reemplazarDeRutina($idRutina, $dets) {
        $st = $this->pdo->prepare('DELETE FROM DETALLE_RUTINA WHERE id_rutina = ?');
        $st->execute([(int)$idRutina]);
        foreach ((array)$dets as $d) {
            $this->insertar($idRutina, $d);
        }
        return true;
    }

    public function eliminarDeRutina($idRutina) {
        $st = $this->pdo->prepare('DELETE FROM DETALLE_RUTINA WHERE id_rutina = ?');
        return $st->execute([(int)$idRutina]);
    }

    public function listarPorRutina($idRutina) {
        $st = $this->pdo->prepare(
            'SELECT D.*, E.nombre AS ejercicio FROM DETALLE_RUTINA D ' .
            'JOIN EJERCICIO E ON E.id = D.id_ejercicio WHERE D.id_rutina = ? ORDER BY D.dia_semana, D.id'
        );
        $st->execute([(int)$idRutina]);
        return $st->fetchAll();
    }

    public function listarPorRutinaYDia($idRutina, $dia) {
        $st = $this->pdo->prepare(
            'SELECT D.*, E.nombre AS ejercicio, E.descripcion, E.tipo_multimedia, E.url ' .
            'FROM DETALLE_RUTINA D JOIN EJERCICIO E ON E.id = D.id_ejercicio ' .
            'WHERE D.id_rutina = ? AND D.dia_semana = ? ORDER BY D.id'
        );
        $st->execute([(int)$idRutina, (int)$dia]);
        return $st->fetchAll();
    }

    // Para el mensaje amigable antes de que la BD frene con 1451 (RESTRICT en id_ejercicio).
    public function contarPorEjercicio($idEjercicio) {
        $st = $this->pdo->prepare('SELECT COUNT(*) AS n FROM DETALLE_RUTINA WHERE id_ejercicio = ?');
        $st->execute([(int)$idEjercicio]);
        $f = $st->fetch();
        return (int)$f['n'];
    }
}
