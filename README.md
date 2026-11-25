# HALCON — Sistema de gestión de pedidos y empleados

Aplicación escolar desarrollada con Laravel 12, Vue 3 y SQLite. Conserva el rastreador público de pedidos y agrega un espacio privado para empleados con acceso basado en roles.

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js y npm
- SQLite habilitado en PHP

## Instalación local

1. Instala las dependencias de PHP:

   ```bash
   composer install
   ```

2. Crea tu archivo de entorno y la base SQLite:

   ```bash
   cp .env.example .env
   ```

   En Windows PowerShell puedes usar `Copy-Item .env.example .env`.

3. Crea la clave y el archivo de base de datos:

   ```bash
   php artisan key:generate
   ```

   Crea `database/database.sqlite` si todavía no existe.

4. Ejecuta las migraciones y los datos de demostración:

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. Instala y compila los recursos del frontend:

   ```bash
   npm install
   npm run build
   ```

6. En desarrollo, abre dos terminales:

   ```bash
   php artisan serve
   ```

   ```bash
   npm run dev
   ```

   Visita `http://127.0.0.1:8000`.

## Cuenta inicial de demostración

El seeder crea el administrador solo si todavía no existe una cuenta con ese correo:

- Correo: `admin@halcon.local`
- Contraseña inicial: `HalconAdmin2026!`
- Rol: Administrador

Estas credenciales son exclusivamente para la demostración local. Cámbialas antes de cualquier despliegue real. También puedes configurar `HALCON_ADMIN_EMAIL` y `HALCON_ADMIN_PASSWORD` en `.env` antes de ejecutar `php artisan db:seed`.

## Roles

| Rol | Acceso |
|---|---|
| Administrator | Resumen, gestión de empleados y todos los pedidos |
| Sales | Registrar y consultar pedidos |
| Purchasing | Consultar pedidos con compra pendiente y marcar la compra completada |
| Warehouse | Consultar pedidos en proceso y marcarlos listos para ruta |
| Route | Gestionar pedidos listos para ruta, marcar envíos y registrar evidencia de entrega |

Solo el administrador puede consultar, registrar, editar, activar o desactivar empleados. No existe registro público de clientes ni de empleados.

## Flujo de demostración

1. Inicia sesión con la cuenta de administrador.
2. Entra a **Empleados** y registra una cuenta de Ventas.
3. Cierra sesión e inicia con la cuenta de Ventas.
4. Registra un pedido y marca si requiere compra de materiales.
5. Crea o usa cuentas de Compras, Almacén y Rutas para demostrar el flujo por áreas.
6. El rastreador público continúa disponible en `/` y usa número de cliente y factura.

## Rutas principales

- `/` — Rastreador público de pedidos.
- `/login` — Inicio de sesión de empleados.
- `/dashboard` — Panel privado protegido.
- `/api/employees` — API de administración de empleados (solo Administrator).
- `/api/workspace/orders` — Bandeja de pedidos filtrada por rol.
- `/api/orders` — Consulta pública de pedidos.

## Seguridad implementada

- Autenticación de sesión de Laravel.
- Regeneración de sesión al iniciar sesión.
- Cierre de sesión e invalidación del token CSRF.
- Acceso al panel protegido con middleware `auth`.
- Autorización de endpoints mediante middleware `role`.
- Las cuentas inactivas no pueden iniciar sesión.
- Validación de correo único y contraseñas confirmadas.
- Protección para conservar al menos un administrador activo.
- No se permite que el administrador desactive su propia cuenta desde el panel.
