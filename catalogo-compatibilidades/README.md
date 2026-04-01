# Catálogo de Compatibilidades — Shark Motors

Sistema para responder en mostrador: **¿qué pieza le queda a esta moto?**

Integra claves de proveedor del POS (MyBusiness) con lógica de compatibilidad pieza ↔ motocicleta.

---

## Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2 + CodeIgniter 4.4.8 |
| Base de datos | MariaDB 11.4 |
| Frontend | FivaAdmin (Bootstrap 4) + HTMX 1.9 + AlpineJS 3.14 |
| Infra | Docker Compose (app + nginx + db) |
| Importador | PhpSpreadsheet 5.5 |

---

## Levantar el entorno

```bash
# Desde la raíz del repo (d:/htdocs/sharkmotors/)
docker compose up -d

# Verificar contenedores
docker compose ps
```

La app queda disponible en **http://localhost:8080**

---

## Primera vez (migraciones + datos de demo)

```bash
# Migraciones
docker compose exec app php spark migrate

# Seed de demostración (motos, piezas, compatibilidades, productos)
docker compose exec app php spark db:seed DemoSeeder
```

---

## Módulos

| Ruta | Descripción |
|---|---|
| `/` | Dashboard con KPIs y buscador rápido |
| `/buscador` | Buscador principal por mostrador |
| `/motos` | CRUD de marcas y motocicletas |
| `/piezas` | CRUD de piezas maestras |
| `/compatibilidades` | CRUD de relaciones pieza ↔ moto |
| `/import` | Importador de Excel/CSV desde MyBusiness |

---

## Importar productos desde Excel

El archivo debe tener columnas (el orden no importa, se detectan por nombre):

| Columna | Descripción |
|---|---|
| `proveedor` | Nombre del proveedor (ej. REMSA) |
| `clave_proveedor` | Clave del POS (ej. BD-HFT150) |
| `nombre` | Descripción del producto |

Formatos soportados: `.xlsx`, `.xls`, `.csv`. Tamaño máximo: 20 MB.

---

## Variables de entorno

Copiar `env` a `.env` y ajustar:

```ini
app.baseURL = 'http://localhost:8080/'

database.default.hostname = db
database.default.database = compatibilidades
database.default.username = compat
database.default.password = compat123
database.default.DBDriver = MySQLi
```

---

## Comandos útiles

```bash
# Ver rutas registradas
docker compose exec app php spark route:list

# Limpiar caché de vistas
docker compose exec app php spark cache:clear

# Reconstruir contenedor tras cambios en Dockerfile
docker compose up -d --build app
```

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 7.4 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> The end of life date for PHP 7.4 was November 28, 2022.
> The end of life date for PHP 8.0 was November 26, 2023.
> If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> The end of life date for PHP 8.1 will be November 25, 2024.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
