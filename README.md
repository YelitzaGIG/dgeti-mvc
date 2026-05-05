
## Estructura del proyecto

```
dgeti-mvc/
├── app/
│   ├── controllers/
│   │   ├── BaseController.php          ← Controlador base
│   │   ├── AuthController.php          ← Login, registro, recuperación
│   │   ├── DashboardController.php     ← Panel principal
│   │   └── JustificantesController.php ← CRUD justificantes
│   ├── models/
│   │   ├── UserModel.php               ← Modelo de usuarios (sesión)
│   │   └── JustificanteModel.php       ← Modelo BD justificantes
│   └── views/
│       ├── layouts/
│       │   ├── auth.php                ← Layout pantallas auth
│       │   └── main.php                ← Layout con sidebar
│       ├── auth/
│       │   ├── welcome.php             ← Bienvenida
│       │   ├── login.php               ← Inicio de sesión
│       │   ├── register.php            ← Registro
│       │   ├── forgot.php              ← Recuperar contraseña
│       │   └── reset.php               ← Nueva contraseña
│       ├── dashboard/
│       │   ├── index.php               ← Panel principal
│       │   └── perfil.php              ← Perfil de usuario
│       └── justificantes/
│           ├── index.php               ← Listado con filtros
│           ├── create.php              ← Nuevo justificante
│           ├── show.php                ← Detalle
│           └── edit.php                ← Editar
├── config/
│   ├── app.php                         ← Constantes globales
│   └── database.php                    ← Conexión PDO Singleton
├── database/
│   └── justificantes_db.sql            ← Script SQL completo
├── public/
│   ├── css/
│   │   ├── variables.css               ← Paleta Pantone + tokens
│   │   ├── base.css                    ← Reset, botones, formularios
│   │   ├── auth.css                    ← Estilos pantallas auth
│   │   └── dashboard.css               ← Layout sidebar/topbar
│   ├── js/
│   │   └── app.js                      ← Interacciones, sidebar, ripple
│   ├── .htaccess                       ← Rewrite rules
│   └── index.php                       ← Front controller
├── .htaccess                           ← Redirige a /public
└── README.md
```

---

### Pasos
4. **Verificar la URL base** en `config/app.php`:
   ```php
   define('APP_URL', 'http://localhost/dgeti-mvc');
   ```


6. Acceder en el navegador:
   ```
   http://localhost/dgeti-mvc/public/auth
   ```

---

## Credenciales de prueba

| Rol          | Correo                   | Contraseña |
|--------------|--------------------------|------------|
| Alumno       | alumno@cetis.edu.mx      | password   |
| Docente      | docente@cetis.edu.mx     | password   |
| Administrador| admin@cetis.edu.mx       | password   |


## Paleta de colores (Pantone)

| Variable CSS             | Pantone | Hex       | Uso                    |
|--------------------------|---------|-----------|------------------------|
| `--pantone-7421`         | 7421    | `#621132` | Primario principal     |
| `--pantone-7420`         | 7420    | `#9D2449` | Primario hover/light   |
| `--pantone-504`          | 504     | `#4E232E` | Primario oscuro        |
| `--pantone-490`          | 490     | `#56242A` | Variante vino          |
| `--pantone-465`          | 465     | `#B38E5D` | Acento dorado          |
| `--pantone-468`          | 468     | `#D4C19C` | Acento dorado claro    |

---

## Seguridad implementada

- CSRF tokens en todos los formularios POST
- Contraseñas con `password_hash()` (bcrypt)
- Sesiones con `session_regenerate_id()` al hacer login
- Sanitización de entradas con `htmlspecialchars()`
- Prepared statements con PDO para todas las consultas
- Control de acceso por rol (alumno / docente / admin)
- Método de petición verificado en todos los POST

---

## Rutas disponibles

| Ruta                           | Controlador            | Descripción              |
|--------------------------------|------------------------|--------------------------|
| `/public/auth`                 | AuthController@index   | Bienvenida               |
| `/public/auth/login`           | AuthController@login   | Login                    |
| `/public/auth/loginpost`       | AuthController@loginpost | Procesar login (POST)   |
| `/public/auth/logout`          | AuthController@logout  | Cerrar sesión            |
| `/public/auth/register`        | AuthController@register| Formulario registro      |
| `/public/auth/forgotpassword`  | AuthController@forgotpassword | Recuperar contraseña|
| `/public/auth/resetpassword`   | AuthController@resetpassword  | Nueva contraseña    |
| `/public/dashboard`            | DashboardController@index | Panel principal       |
| `/public/dashboard/perfil`     | DashboardController@perfil | Mi perfil            |
| `/public/justificantes`        | JustificantesController@index | Listado           |
| `/public/justificantes/create` | JustificantesController@create | Nuevo            |
| `/public/justificantes/store`  | JustificantesController@store  | Guardar (POST)   |
| `/public/justificantes/show/ID`| JustificantesController@show   | Ver detalle      |
| `/public/justificantes/edit/ID`| JustificantesController@edit   | Editar           |
| `/public/justificantes/update/ID` | JustificantesController@update | Actualizar (POST)|
| `/public/justificantes/delete/ID` | JustificantesController@delete | Eliminar (POST) |

---

## Notas de desarrollo

- Patrón MVC sin framework externo (PHP puro)
- Front controller en `public/index.php`
- Autoloader manual para controllers y models
- Layouts con output buffering (`ob_start` / `ob_get_clean`)
- Conexión PDO con patrón Singleton
- Flash messages con `$_SESSION['flash']`
- Todos los assets en `/public/` (separación root/web)
