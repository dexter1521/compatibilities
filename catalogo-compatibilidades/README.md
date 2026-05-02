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

> Tras cambios en el `Dockerfile` o dependencias ejecutar:
> ```bash
> docker compose up -d --build app
> ```

---

## Primera vez (migraciones + datos de demo)

```bash
# 1. Ejecutar migraciones
docker compose exec app php spark migrate

# 2. Seed de demostración (motos, piezas, compatibilidades, productos)
docker compose exec app php spark db:seed DemoSeeder
```

Para poblar el catálogo real de motocicletas y aliases ver la sección [Catálogo de datos](#catálogo-de-datos).

---

## Módulos

| Ruta | Controlador | Descripción |
|---|---|---|
| `/` | `Home` | Dashboard con KPIs y buscador rápido |
| `/buscador` | `Search` | Buscador principal por mostrador |
| `/motos` | `Motos` | CRUD de marcas y motocicletas |
| `/piezas` | `Piezas` | CRUD de piezas maestras |
| `/compatibilidades` | `Compatibilidades` | CRUD de relaciones pieza ↔ moto |
| `/import` | `Import` | Importador de Excel/CSV desde MyBusiness |

---

## Importar productos desde Excel

El archivo debe tener columnas (el orden no importa, se detectan por nombre):

| Columna | Descripción |
|---|---|
| `proveedor` | Nombre del proveedor (ej. REMSA) |
| `clave_proveedor` | Clave del POS (ej. BD-HFT150) |
| `nombre` | Descripción del producto (ej. "Bujía FT150") |

Formatos soportados: `.xlsx`, `.xls`, `.csv`. Tamaño máximo: 20 MB.

Tras importar, el servicio ejecuta automáticamente el pipeline de enriquecimiento — ver la siguiente sección.

---

## Pipeline de enriquecimiento automático (`ImportService`)

Después de hacer upsert de cada producto, `ImportService::enrichProducto()` analiza la descripción y:

1. **Detecta motocicletas** — `detectarMotos()` normaliza la descripción (mayúsculas, quita guiones) y busca coincidencias contra todos los registros de la tabla `alias_motos` usando `str_contains`. Si encuentra una o más motos, continúa.

2. **Detecta tipo de pieza** — `detectarTipo()` itera la propiedad `$mapTipos` (tipo → keywords[]) de más específico a más genérico. Ejemplo: `'FILTRO DE ACEITE'` se evalúa antes que `'ACEITE'`.

3. **Crea o reutiliza una `pieza_maestra`** — `getOrCreatePiezaMaestra()` hace upsert por `nombre` en la tabla `piezas_maestras`.

4. **Asigna `pieza_maestra_id`** al producto en la tabla `productos`.

5. **Crea registros en `compatibilidades`** — uno por cada motocicleta detectada (si el par pieza–moto no existe ya).

Si no se detecta tipo **o** no se detecta ninguna moto, el producto se importa sin enriquecer (no falla el job).

### Ampliar el mapa de tipos

Editar la propiedad `$mapTipos` en `app/Services/ImportService.php`:

```php
private array $mapTipos = [
    'Mi Nuevo Tipo' => ['KEYWORD1', 'KEYWORD DOS'],
    // ...
];
```

Las keywords se normalizan automáticamente antes de comparar, por lo que no es necesario manejar variaciones de mayúsculas/minúsculas ni guiones.

---

## Catálogo de datos

### Marcas y motocicletas

El catálogo de motocicletas se pobla directamente en DB. Para replicar el entorno ejecutar los INSERTs del directorio `docs/seeds/` (en construcción) o correr el DemoSeeder y completar manualmente.

Marcas actuales: **Honda, Yamaha, Italika, TVS, Vento, Suzuki, Bajaj**.

### Agregar aliases a una motocicleta

Los aliases son las cadenas que `detectarMotos()` busca en las descripciones de productos importados.

```sql
INSERT INTO alias_motos (motocicleta_id, alias, slug) VALUES
( (SELECT id FROM motocicletas WHERE slug='italika-ft150'), 'FT150',  'ft150'),
( (SELECT id FROM motocicletas WHERE slug='italika-ft150'), 'FT 150', 'ft-150');
```

Conviene agregar variantes: con espacio, con guión, con nombre de marca prefijado.

### Tipos de pieza (`tipos_pieza`)

Catálogo de referencia de 33 tipos de pieza (bujía, filtros, balatas, etc.). Usado actualmente como referencia; la detección de tipo en el pipeline usa `$mapTipos` en `ImportService`, no esta tabla directamente.

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

# Acceso directo a MariaDB
docker exec compat_db mariadb -u compat -pcompat123 compatibilidades

# Reconstruir contenedor tras cambios en Dockerfile
docker compose up -d --build app
```

---

## Estado SPA Interna

Se inició la migración a SPA para uso interno.

- Entrada SPA servida por CI4 en: `http://localhost:8080/app`
- Código fuente SPA: `frontend/`
- Build publicado por Vite: `public/spa/`
- API consumida por SPA: `http://localhost:8080/api/v1`

Para detalle técnico de avances y decisiones, ver:

- `docs/BITACORA_SPA_API_2026-05-02.md`

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
