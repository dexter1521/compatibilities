# Contratos Front-Back API v1

Documento normativo para consumo frontend (SPA/PWA) del backend `Shark Compatibility Engine API`.

## 1) Convención global de respuesta

Todas las respuestas usan este shape:

```json
{
  "status": 200,
  "success": true,
  "data": {},
  "message": "Consulta exitosa",
  "errors": null
}
```

Errores de validación:

```json
{
  "status": 422,
  "success": false,
  "data": null,
  "message": "Validación fallida",
  "errors": {
    "campo": ["motivo"]
  }
}
```

## 2) Formato de fechas y zona horaria

- Todos los campos `*_at` en respuesta salen en ISO-8601 (`RFC3339`), por ejemplo:
  - `2026-04-26T13:30:00+00:00`
- Zona horaria de API: `config('App')->appTimezone` (actualmente `UTC`).
- Cada listado incluye `meta.timezone`.

## 3) Contratos de listado (paginación/orden/filtros)

## 3.1 Productos
Endpoint: `GET /api/v1/productos`

### Query params soportados
- `page` (int >= 1, default `1`)
- `per_page` (int 1..100, default `20`)
- `sort_by` (`id|nombre|clave_proveedor|created_at|updated_at|proveedor_nombre`, default `id`)
- `sort_dir` (`asc|desc`, default `desc`)
- `q` (string, opcional; busca en nombre/clave/proveedor)
- `proveedor_id` (int, opcional)
- `pieza_maestra_id` (int, opcional)
- `activo` (`0|1`, opcional)
- `enrich_estado` (`ok|sin_tipo|sin_moto|sin_ambos`, opcional)

### Data payload exacto
```json
{
  "items": [
    {
      "id": 1,
      "clave_proveedor": "BD-HFT150",
      "nombre": "Balata delantera FT150",
      "activo": 1,
      "enrich_estado": "ok",
      "created_at": "2026-04-26T12:00:00+00:00",
      "updated_at": "2026-04-26T12:05:00+00:00",
      "proveedor_id": 2,
      "pieza_maestra_id": 33,
      "proveedor_nombre": "REMSA",
      "pieza_nombre": "Balata Delantera|ITALIKA-FT150"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 120,
    "last_page": 6,
    "sort_by": "id",
    "sort_dir": "desc",
    "filters": {
      "q": "ft150",
      "proveedor_id": null,
      "pieza_maestra_id": null,
      "activo": null,
      "enrich_estado": null
    },
    "timezone": "UTC"
  }
}
```

## 3.2 Búsquedas no encontradas
Endpoint: `GET /api/v1/search-missed`

### Query params soportados
- `page` (int >= 1, default `1`)
- `per_page` (int 1..200, default `50`)
- `sort_by` (`contador|ultima_busqueda_at|created_at`, default `contador`)
- `sort_dir` (`asc|desc`, default `desc`)
- `q` (string, opcional)

### Data payload exacto
```json
{
  "items": [
    {
      "id": 15,
      "termino": "balata dm200",
      "termino_normalizado": "balata dm200",
      "contador": 12,
      "ultima_busqueda_at": "2026-04-26T10:00:00+00:00",
      "created_at": "2026-04-22T09:00:00+00:00",
      "updated_at": "2026-04-26T10:00:00+00:00"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 50,
    "total": 30,
    "last_page": 1,
    "sort_by": "contador",
    "sort_dir": "desc",
    "filters": {
      "q": null
    },
    "timezone": "UTC"
  }
}
```

## 4) Estructura exacta de payloads (módulos actuales)

## 4.1 Productos

### `GET /api/v1/productos/{id}`
`data` = objeto producto (mismo shape de item de listado).

### `POST /api/v1/productos`
Body:
```json
{
  "proveedor_id": 2,
  "clave_proveedor": "BD-HFT150",
  "nombre": "Balata delantera FT150",
  "pieza_maestra_id": 33,
  "activo": 1,
  "enrich_estado": "ok"
}
```

### `PUT /api/v1/productos/{id}`
Body parcial permitido con mismos campos de `POST`.

### `DELETE /api/v1/productos/{id}`
`data = null`.

## 4.2 Search inteligente

### `GET /api/v1/search?q=balata+ft150&limit=20`
- `q` obligatorio, mínimo 2 chars.
- `limit` opcional, rango 1..50.

Data:
```json
{
  "items": [
    {
      "pieza_maestra_id": 33,
      "pieza_nombre": "Balata Delantera|ITALIKA-FT150",
      "productos": [
        {
          "id": 1,
          "clave_proveedor": "BD-HFT150",
          "nombre": "Balata delantera FT150",
          "proveedor": "REMSA"
        }
      ],
      "compatibilidades": [
        {
          "id": 4,
          "confirmada": 1,
          "contador_confirmaciones": 7,
          "score_relevancia": 7,
          "marca_nombre": "ITALIKA",
          "moto_modelo": "FT150",
          "anio_desde": 2019,
          "anio_hasta": 2024,
          "cilindrada": "150"
        }
      ]
    }
  ],
  "meta": {
    "q": "balata ft150",
    "limit": 20,
    "total": 1,
    "timezone": "UTC"
  }
}
```

## 4.3 Compatibilidades (actual en v1)

### `PATCH /api/v1/compatibilidades/{id}/confirmar`
Data:
```json
{
  "id": 4,
  "confirmada": 1,
  "contador_confirmaciones": 8,
  "score_relevancia": 8,
  "updated_at": "2026-04-26T13:00:00+00:00"
}
```

## 5) Códigos HTTP definitivos por caso

- `200 OK`: lecturas, actualizaciones, confirmaciones, borrado exitoso con body.
- `201 Created`: creación e importación exitosas (`POST productos`, `POST import/productos`).
- `404 Not Found`: recurso inexistente.
- `409 Conflict`: duplicidad de clave lógica (`proveedor_id + clave_proveedor`).
- `422 Unprocessable Entity`: validación de entrada.
- `500 Internal Server Error`: error no controlado.

## 6) Versionado sin breaking changes

Reglas obligatorias para mantener `v1` estable:

1. Nunca eliminar ni renombrar campos existentes en `v1`.
2. Solo cambios aditivos en `v1` (nuevos campos opcionales, nuevos endpoints).
3. No cambiar semántica de campos existentes.
4. Si hay cambio incompatible:
   - crear `v2` (`/api/v2/...`),
   - mantener `v1` operando durante ventana de migración.
5. Si un endpoint se depreca en `v1`, anunciar en documentación antes de retiro.

## 7) Estado actual de cobertura v1

Cubierto:
- `auth` (`login`, `refresh`, `me`, `logout`)
- `productos` CRUD
- `motocicletas` CRUD
- `piezas` CRUD
- `aliases` (listado, alta, baja)
- `compatibilidades` CRUD + confirmar
- `search`
- `search-missed`
- `import/productos`

Pendiente para cerrar PDR completo:
- política fina por rol en endpoints sensibles (RBAC detallado),
- OpenAPI/Swagger completo,
- pruebas de integración API + hardening seguridad (rotación de secretos y revocación de access token por blacklist si se requiere).

## 8) RBAC (v1)

La matriz detallada de permisos por endpoint está en:

- `docs/RBAC_MATRIX_V1.md`
