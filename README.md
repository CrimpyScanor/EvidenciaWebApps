# Evidence 2 — Backend API

## 1. Descripción del proyecto

Este proyecto consiste en una API REST desarrollada con Laravel para administrar usuarios, pedidos y facturas. Permite realizar operaciones CRUD, actualizar estados y consultar pedidos archivados.

## 2. Tecnologías utilizadas

- PHP
- Laravel
- MySQL
- Composer
- Postman para las pruebas de los endpoints

## 3. Requisitos previos

Antes de ejecutar el proyecto, es necesario contar con:

- PHP compatible con la versión de Laravel del proyecto.
- Composer.
- MySQL o MariaDB.
- XAMPP u otro entorno que proporcione PHP y MySQL.

## 4. Instalación y configuración

1. Clonar o descargar el repositorio.
2. Abrir una terminal en la carpeta raíz del proyecto.
3. Instalar las dependencias:

   ```bash
   composer install
   ```

4. Crear el archivo de configuración a partir del ejemplo:

   ```bash
   copy .env.example .env
   ```

   Si el archivo `.env` ya existe, no es necesario crearlo de nuevo.

5. Generar la clave de la aplicación:

   ```bash
   php artisan key:generate
   ```

6. Configurar las variables de conexión a la base de datos en el archivo `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=evidencia
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Estos valores corresponden a la configuración local utilizada durante las pruebas. Deben ajustarse si el entorno de ejecución utiliza otras credenciales.

7. Crear la base de datos `evidencia` en MySQL si todavía no existe.

8. Ejecutar las migraciones:

   ```bash
   php artisan migrate
   ```

9. Para cargar los datos de prueba en una instalación nueva, ejecutar:

   ```bash
   php artisan db:seed
   ```

10. Iniciar el servidor de desarrollo:

    ```bash
    php artisan serve
    ```

La API estará disponible normalmente en `http://127.0.0.1:8000`.

**Importante:** ejecutar los seeders puede agregar registros de prueba. No es necesario volver a ejecutarlos si los datos ya están cargados.

## 5. Endpoints de la API

La URL base utilizada durante las pruebas es:

`http://127.0.0.1:8000/api`

### Usuarios

| Método HTTP | Endpoint | Descripción |
|---|---|---|
| GET | `/users` | Listar usuarios |
| POST | `/users` | Crear un usuario |
| GET | `/users/{user}` | Consultar un usuario por ID |
| PUT/PATCH | `/users/{user}` | Actualizar información del usuario |
| PATCH | `/users/{user}/status` | Actualizar el estado del usuario |

### Pedidos

| Método HTTP | Endpoint | Descripción |
|---|---|---|
| GET | `/orders` | Listar pedidos |
| POST | `/orders` | Crear un pedido |
| GET | `/orders/{order}` | Consultar un pedido por ID |
| GET | `/orders/invoice/{invoice}` | Buscar un pedido por número de factura |
| PUT/PATCH | `/orders/{order}` | Actualizar un pedido |
| PATCH | `/orders/{order}/status` | Cambiar el estado del pedido |
| DELETE | `/orders/{order}` | Archivar un pedido |
| GET | `/orders/archived` | Consultar pedidos archivados |

## 6. Ejemplos de solicitudes

### Crear un usuario

**Método:** POST

**Endpoint:** `/users`

Cuerpo JSON de ejemplo:

```json
{
  "name": "Peter Parker",
  "email": "peter.parker@example.test",
  "password": "Password123!",
  "is_active": true
}
```

### Crear un pedido

**Método:** POST

**Endpoint:** `/orders`

Cuerpo JSON de ejemplo:

```json
{
  "user_id": 26,
  "total_value": 1500,
  "tax": 240,
  "shipping_charges": 100
}
```

El `user_id` debe corresponder a un usuario existente. El pedido creado genera una factura asociada.

### Cambiar el estado de un pedido

**Método:** PATCH

**Endpoint:** `/orders/{order}/status`

Cuerpo JSON de ejemplo:

```json
{
  "status": "processing"
}
```

### Archivar un pedido

**Método:** DELETE

**Endpoint:** `/orders/{order}`

Esta operación archiva el pedido mediante eliminación lógica, de modo que puede consultarse posteriormente en `/orders/archived`.

## 7. Pruebas con Postman

Los endpoints se probaron utilizando Postman. Para las solicitudes JSON se utiliza el encabezado:

`Accept: application/json`

Para enviar cuerpos JSON también se utiliza:

`Content-Type: application/json`

Las pruebas incluyen la creación y consulta de usuarios, la creación y actualización de pedidos, la búsqueda por factura y la verificación de pedidos archivados.

## 8. Ejecución

Con MySQL activo, las dependencias instaladas y el archivo `.env` configurado, ejecutar:

```bash
php artisan serve
```

Después, enviar las solicitudes desde Postman utilizando la URL base indicada anteriormente.
