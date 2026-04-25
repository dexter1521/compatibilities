# Auditoría Fase 1 - Migración a API REST (PDR v2.0)

## Alcance
Revisión del estado real del backend en `catalogo-compatibilidades` para definir migración gradual a arquitectura API-first sin romper operación actual.

## Clasificación por componente

### Reutilizable
- `app/Services/ImportService.php`
  - Lógica de importación y enriquecimiento de negocio ya funcional.
  - Reutilizada en endpoint `POST /api/v1/import/productos`.
- `app/Models/SearchModel.php`
  - Lógica de búsqueda por término y registro de búsquedas no encontradas.
  - Reutilizada en endpoint `GET /api/v1/search`.
- Migración base de dominio:
  - `app/Database/Migrations/2026-04-01-000001_CreateMvpCatalogSchema.php`
  - Mantiene entidades clave: productos, motos, alias, compatibilidades, equivalencias, logs de búsqueda.

### Refactorizable
- Controladores web CRUD (`Motos`, `Piezas`, `Marcas`, `Compatibilidades`, `Search`, `Import`)
  - Mezclan respuestas HTML/HTMX con lógica de negocio y consultas directas.
  - Deben quedar como capa web legacy mientras API toma responsabilidades.
- `app/Config/Routes.php`
  - Predominantemente orientado a vistas.
  - Ya se inició separación con grupo `/api/v1`.

### Riesgoso
- Confirmación de compatibilidad embebida en controlador web (`Search::confirm`).
  - Riesgo de inconsistencia al escalar canales (web, móvil, integraciones).
  - Mitigado parcialmente al mover a servicio API transaccional.
- Falta de autenticación/autorización API (JWT + roles).
  - Riesgo de exposición de endpoints al publicarlos sin filtro.

### Obsoleto o fuera de enfoque API
- Endpoints con respuesta parcial HTML para HTMX como único contrato.
  - No cumplen formato JSON estándar del PDR.

## Brecha contra PDR

### Cubierto en esta iteración
- Base de arquitectura API versionada (`/api/v1`).
- Estandarización de respuesta JSON (`status/success/data/message/errors`).
- Recursos iniciales:
  - `GET/POST/PUT/DELETE /api/v1/productos`
  - `GET /api/v1/search`
  - `GET /api/v1/search-missed`
  - `PATCH /api/v1/compatibilidades/{id}/confirmar`
  - `POST /api/v1/import/productos`

### Pendiente prioritario
- JWT login y refresh token.
- Roles (`admin`, `vendedor`) y filtros de autorización.
- Rate limit y auditoría de acciones API.
- Recursos faltantes del PDR: motocicletas, piezas, aliases, compatibilidades CRUD completo.
- Swagger/OpenAPI y suite de pruebas API con cobertura mínima.

## Recomendación de ejecución
1. Implementar módulo Auth JWT + filtros y tablas `users/roles` con migraciones controladas.
2. Completar recursos REST faltantes con servicios dedicados.
3. Integrar documentación OpenAPI y pruebas de integración por endpoint.
4. Mantener rutas HTMX actuales durante transición para evitar ruptura operativa.
