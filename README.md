# Gym Trainer

Aplicación web en PHP para gestionar categorías musculares, ejercicios, clientes y rutinas de entrenamiento.

## Requisitos

- PHP 8 o superior
- Extensión PHP `pdo_mysql`
- MariaDB o MySQL
- Un servidor web con PHP, o el servidor integrado de PHP para desarrollo

Verifica la extensión requerida:

```bash
php -m | grep pdo_mysql
```

Debe mostrar:

```text
pdo_mysql
```

## Instalación local

### 1. Clonar el repositorio

```bash
git clone https://github.com/rvalenciag/gym_trainer.git
cd gym_trainer
```

### 2. Crear la base de datos y sus tablas

El script `database/schema.sql` crea la base de datos `gym_db` y todas las tablas necesarias.

```bash
sudo mariadb < database/schema.sql
```

### 3. Crear el usuario de la aplicación

Ingresa a MariaDB como administrador:

```bash
sudo mariadb
```

Ejecuta lo siguiente:

```sql
CREATE USER IF NOT EXISTS 'gym_user'@'localhost'
    IDENTIFIED BY 'gym_pass';

CREATE USER IF NOT EXISTS 'gym_user'@'127.0.0.1'
    IDENTIFIED BY 'gym_pass';

GRANT SELECT, INSERT, UPDATE, DELETE
    ON gym_db.* TO 'gym_user'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE
    ON gym_db.* TO 'gym_user'@'127.0.0.1';
```

> Estas credenciales son solo para desarrollo local

### 4. Cargar los datos iniciales

El archivo `database/seed.sql` crea el entrenador inicial.

```bash
mariadb -h 127.0.0.1 -u gym_user -p gym_db < database/seed.sql
```

MariaDB solicitará la contraseña configurada para `gym_user`.

### 5. Verificar la conexión

```bash
mariadb -h 127.0.0.1 -u gym_user -p gym_db -e "SHOW TABLES;"
```

Deben aparecer las tablas:

```text
USUARIO
INSTRUCTOR
CLIENTE
CATEGORIA
EJERCICIO
RUTINA
DETALLE_RUTINA
```

## Configuración de la aplicación

Las credenciales de conexión están en:

```text
config/database.php
```

Por defecto, deben coincidir con la instalación descrita:

```php
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = gym_db
DB_USER = gym_user
DB_PASS = gym_pass
```

## Ejecutar en desarrollo

Desde la raíz del proyecto:

```bash
php -d upload_max_filesize=20M -d post_max_size=25M -S localhost:8000
```

Abre en el navegador:

```text
http://localhost:8000
```

## Usuario inicial

Las credenciales del instructor inicial son las definidas en `database/seed.sql`.

El usuario debe tener:

- Correo: `entrenador@gym.local`
- Contraseña: `Instructor123!`
- Rol: `INSTRUCTOR`
- Una fila asociada en la tabla `INSTRUCTOR`


## Estructura del proyecto

```text
config/          Configuración de conexión a la base de datos
Datos/           Acceso a datos y consultas SQL
Negocio/         Reglas de negocio
Presentacion/    Vistas, controladores y recursos estáticos
database/        Esquema y datos iniciales de MariaDB
uploads/         Archivos multimedia subidos por usuarios
```

## Permisos para archivos subidos

El proceso PHP debe poder escribir en:

```text
uploads/ejercicios/
```

Con el servidor integrado de PHP normalmente basta ejecutarlo desde el usuario propietario del proyecto. En Apache o PHP-FPM, asigna permisos al usuario que ejecuta PHP.

## Problemas frecuentes

### `could not find driver`

Falta habilitar `pdo_mysql` en PHP.

```bash
php -m | grep pdo_mysql
```

### `Access denied for user 'gym_user'`

Verifica que el usuario exista, que la contraseña coincida con `config/database.php` y que tenga permisos sobre `gym_db`.

### No aparecen tablas

Vuelve a ejecutar el esquema:

```bash
sudo mariadb < database/schema.sql
```

Luego ejecuta el seed:

```bash
mariadb -h 127.0.0.1 -u gym_user -p gym_db < database/seed.sql
