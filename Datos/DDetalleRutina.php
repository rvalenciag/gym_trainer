<?php
// DDetalleRutina: acceso a la tabla DETALLE_RUTINA.
require_once __DIR__ . '/Conexion.php';

class DDetalleRutina
{
    private $pdo;
    private $id;
    private $idRutina;
    private $idEjercicio;
    private $diaSemana;
    private $series;
    private $repeticiones;
    private $pesoSugerido;
    private $descanso;

    public function __construct($datos = [])
    {
        $this->pdo = Conexion::get();
        $this->id = $datos['id'] ?? null;
        $this->idRutina = $datos['id_rutina'] ?? null;
        $this->idEjercicio = $datos['id_ejercicio'] ?? null;
        $this->diaSemana = $datos['dia_semana'] ?? null;
        $this->series = $datos['series'] ?? null;
        $this->repeticiones = $datos['repeticiones'] ?? null;
        $this->pesoSugerido = $datos['peso_sugerido'] ?? null;
        $this->descanso = $datos['descanso'] ?? null;
    }

    public function insertar()
    {
        $st = $this->pdo->prepare(
            'INSERT INTO DETALLE_RUTINA
                (
                    id_rutina,
                    id_ejercicio,
                    dia_semana,
                    series,
                    repeticiones,
                    peso_sugerido,
                    descanso
                )
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            (int) $this->idRutina,
            (int) $this->idEjercicio,
            (int) $this->diaSemana,
            (int) $this->series,
            $this->repeticiones,
            $this->pesoSugerido,
            $this->descanso,
        ]);

        $this->id = (int) $this->pdo->lastInsertId();
        return $this->id;
    }

    public function eliminarDeRutina()
    {
        $st = $this->pdo->prepare(
            'DELETE FROM DETALLE_RUTINA WHERE id_rutina = ?'
        );

        return $st->execute([(int) $this->idRutina]);
    }

    public function listarPorRutina()
    {
        $st = $this->pdo->prepare(
            'SELECT D.*, E.nombre AS ejercicio
             FROM DETALLE_RUTINA D
             INNER JOIN EJERCICIO E ON E.id = D.id_ejercicio
             WHERE D.id_rutina = ?
             ORDER BY D.dia_semana, D.id'
        );
        $st->execute([(int) $this->idRutina]);

        return $st->fetchAll();
    }

    public function listarPorRutinaYDia()
    {
        $st = $this->pdo->prepare(
            'SELECT
                D.*,
                E.nombre AS ejercicio,
                E.descripcion,
                E.tipo_multimedia,
                E.url
             FROM DETALLE_RUTINA D
             INNER JOIN EJERCICIO E ON E.id = D.id_ejercicio
             WHERE D.id_rutina = ? AND D.dia_semana = ?
             ORDER BY D.id'
        );
        $st->execute([
            (int) $this->idRutina,
            (int) $this->diaSemana,
        ]);

        return $st->fetchAll();
    }

    public function contarPorEjercicio()
    {
        $st = $this->pdo->prepare(
            'SELECT COUNT(*) AS n
             FROM DETALLE_RUTINA
             WHERE id_ejercicio = ?'
        );
        $st->execute([(int) $this->idEjercicio]);
        $fila = $st->fetch();

        return (int) $fila['n'];
    }
}
