<?php
// PLogin: presentación de inicio de sesión (CU01, pantalla pública, sin menú).
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NAuth.php';

class PLogin {
    public function ingresar($correo, $clave) {
        $res = (new NAuth())->verificar($correo, $clave);
        if ($res['ok']) {
            session_regenerate_id(true);
            $_SESSION['uid'] = $res['user']['id'];
            $_SESSION['nombre'] = $res['user']['nombre'];
            $_SESSION['rol'] = $res['user']['rol'];
            $_SESSION['last'] = time();
            header('Location: index.php');
            exit;
        }
        return $res['msg'];
    }

    public function mostrar() {
        if (isset($_SESSION['uid'])) {
            header('Location: index.php');
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->ingresar($_POST['correo'] ?? '', $_POST['contrasena'] ?? '');
        }
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Iniciar Sesión - Gym Instructor</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="Presentacion/assets/css/app.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-md-5">
                        <div class="card shadow">
                            <div class="card-header bg-dark text-white text-center">
                                <h4 class="mb-0"><span class="text-success">Gym</span> Instructor</h4>
                            </div>
                            <div class="card-body">
                                <?php if (isset($_GET['fin'])): ?>
                                    <div class="alert alert-info">Sesión finalizada correctamente</div>
                                <?php endif; ?>
                                <?php if ($error !== ''): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                                <form method="post" action="index.php?r=login">
                                    <div class="mb-3">
                                        <label class="form-label">Correo Electrónico</label>
                                        <input type="email" name="correo" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" name="contrasena" class="form-control" required>
                                    </div>
                                    <button class="btn btn-success w-100" type="submit">Ingresar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
