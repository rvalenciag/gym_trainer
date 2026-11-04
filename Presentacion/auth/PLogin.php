<?php
// PLogin: presentación de inicio de sesión (CU01, pantalla pública, sin menú).
defined('APP') || exit('Acceso no permitido');
require_once __DIR__ . '/../../Negocio/NAuth.php';

class PLogin
{
    private string $correo = '';
    private ?string $contrasena = null;
    private string $mensajeError = '';

    public function ingresar($correo, $contrasena)
    {
        $this->correo = trim($correo);
        $this->contrasena = $contrasena;

        $res = (new NAuth())->verificar($this->correo, $this->contrasena);
        $this->contrasena = null;

        if ($res['ok']) {
            session_regenerate_id(true);
            $_SESSION['uid'] = $res['user']['id'];
            $_SESSION['nombre'] = $res['user']['nombre'];
            $_SESSION['rol'] = $res['user']['rol'];
            $_SESSION['last'] = time();
            header('Location: index.php');
            exit;
        }
        $this->mensajeError = $res['msg'];
        return $this->mensajeError;
    }

    public function mostrar()
    {
        if (isset($_SESSION['uid'])) {
            header('Location: index.php');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->ingresar($_POST['correo'] ?? '', $_POST['contrasena'] ?? '');
        }
?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Iniciar Sesión - Gym Trainer</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="Presentacion/assets/css/app.css" rel="stylesheet">
        </head>

        <body class="gym-login-body">
            <main class="gym-login-layout">
                <section class="gym-login-hero" aria-label="Gym Trainer">
                    <div class="gym-login-hero-content">
                        <div class="gym-login-brand-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <path d="M4 9v6m3-9v12m3-15v18m3-12v6m3-9v12m3-9v6" />
                                <path d="M2 11h2m12 0h2M2 13h2m12 0h2" />
                            </svg>
                        </div>
                        <h1>Gym <strong>Trainer</strong></h1>
                        <h2>Sistema para<br>Instructor de Gimnasio</h2>
                        <span class="gym-login-accent"></span>
                        <p>Organiza y consulta tus entrenamientos<br>en un solo lugar.</p>
                    </div>
                </section>
                <section class="gym-login-panel">
                    <div class="gym-login-card">
                        <div class="gym-login-user-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="3.5" />
                                <path d="M5 20c.7-3.3 3.1-5 7-5s6.3 1.7 7 5" />
                            </svg>
                        </div>
                        <h2>Iniciar Sesión</h2>
                        <p>Accede a tu cuenta para continuar</p>
                        <?php if (isset($_GET['fin'])): ?>
                            <div class="alert alert-info">Sesión finalizada correctamente</div>
                        <?php endif; ?>
                        <?php if ($this->mensajeError !== ''): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($this->mensajeError); ?></div>
                        <?php endif; ?>
                        <form method="post" action="index.php?r=login" class="gym-login-form">
                            <label class="visually-hidden" for="login-correo">Correo electrónico</label>
                            <div class="gym-input-wrap">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                                <input id="login-correo" type="email" name="correo" class="form-control" placeholder="Correo electrónico" autocomplete="email" value="<?php echo htmlspecialchars($this->correo, ENT_QUOTES); ?>" required>
                            </div>
                            <label class="visually-hidden" for="login-contrasena">Contraseña</label>
                            <div class="gym-input-wrap">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="5" y="10" width="14" height="10" rx="2" />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg>
                                <input id="login-contrasena" type="password" name="contrasena" class="form-control" placeholder="Contraseña" autocomplete="current-password" required>
                                <button type="button" class="gym-password-toggle" data-password-toggle="login-contrasena" aria-label="Mostrar contraseña">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                                        <circle cx="12" cy="12" r="2.5" />
                                        <path d="M4 4 20 20" />
                                    </svg>
                                </button>
                            </div>
                            <button class="gym-primary-btn gym-login-submit" type="submit">
                                <span aria-hidden="true">→</span> Ingresar
                            </button>
                        </form>
                    </div>
                </section>
            </main>
            <script src="Presentacion/assets/js/app.js"></script>
        </body>

        </html>
<?php
    }
}
