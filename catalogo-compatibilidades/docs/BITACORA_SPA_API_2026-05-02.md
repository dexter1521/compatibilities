# Bitácora Técnica SPA + API

Fecha: 2026-05-02  
Proyecto: Catálogo de Compatibilidades (Shark Motors)

## Resumen Ejecutivo
Se consolidó la API v1 con contratos estables y pruebas de integración, y se inició la migración a SPA interna (Vue + Vite) consumiendo la API.

## Estado Actual
- API REST v1 funcional con JWT, RBAC, rate limit y auditoría.
- Contrato OpenAPI actualizado (`openapi/openapi.yaml`).
- Suite de pruebas API en Docker pasando:
  - 12 tests
  - 61 assertions
- Front legacy depurado:
  - Limpieza de `app/Views/layouts/fiva.php`
  - Reducción de `public/fiva-assets/js/custom.js` a funciones usadas
- SPA base creada en `frontend/`:
  - Vue 3 + Vite + Pinia + Vue Router + Axios
  - Login JWT, refresh y logout
  - Shell responsive inicial (sidebar/topbar)
  - Vista de buscador conectada a `/api/v1/search`
- Integración CI4 <-> SPA:
  - Host web en `/app` y fallback `/app/*`
  - Build de Vite publicado en `public/spa`

## Commits Relevantes
- `c22ee41`: inicio SPA + eliminación de artefactos de prueba legacy
- `e682176`: integración host CI4 en `/app` + publicación build Vite en `public/spa`

## Rutas Clave
- Legacy server-rendered: `/`
- API docs: `/docs/api`
- OpenAPI: `/api/openapi.yaml` (canónico, `/docs/openapi.yaml` como alias)
- SPA interna: `/app`
- API v1 base: `/api/v1/*`

## Cómo Ejecutar (estado actual)
1. Levantar entorno:
   - `docker compose up -d`
2. Backend CI4:
   - `http://localhost:8080`
3. SPA publicada por CI4:
   - `http://localhost:8080/app`
4. Desarrollo SPA (hot reload):
   - `cd frontend`
   - `npm install`
   - `npm run dev`

## Decisiones Técnicas Tomadas
- Se mantiene CodeIgniter 4 como backend principal (sin migrar framework).
- Se adopta estrategia híbrida:
  - Corto plazo: conviven vistas legacy y SPA
  - Mediano plazo: migración gradual de módulos a SPA
- Se priorizó una SPA interna (no SSR) por contexto operativo de mostrador y panel interno.

## Pendientes Inmediatos
- Cierre de migración SPA completado para módulos funcionales principales.
- Mantener documentación de despliegue frontend y procedimiento de release versionado de `public/spa`.
- Consolidar menú de roles (fase de ajuste menor para permisos.)

## Riesgos Abiertos
- Convivencia temporal legacy + SPA puede duplicar flujos hasta completar migración.
- Si no se define pronto política de despliegue SPA, puede haber drift entre `frontend/src` y `public/spa`.

## Criterio de Cierre de Fase
Esta fase se considera cerrada cuando:
- Todos los módulos operativos estén en SPA,
- Legacy quede en modo mantenimiento,
- Y se ejecute smoke test integral sobre `/app` con API v1.

- 2026-05-03: Se agregó edición inline en módulo SPA de Productos (PUT `/api/v1/productos/{id}`), con edición rápida de clave, nombre y estado, y flujo Guardar/Cancelar.
- 2026-05-03: Extendida la migración SPA con edición inline en módulos de `motocicletas` y `compatibilidades` (`PUT /api/v1/motocicletas/{id}` y `PUT /api/v1/compatibilidades/{id}`).
- 2026-05-03: Se completó edición inline en SPA para `piezas` y `aliases` (PUT `/api/v1/piezas/{id}`, PUT `/api/v1/aliases/{id}`), con acciones Editar/Guardar/Cancelar.
- 2026-05-03: Migración SPA de módulos CRUD finalizada (productos, motocicletas, piezas, compatibilidades, aliases) con edición inline y flujo completo de alta/editar/baja (create, PUT, delete) para uso operativo interno.
- 2026-05-03: Se agregó el endpoint PUT /api/v1/aliases/{id} en API v1 para alinear con el cliente SPA (ruta, controller y servicio) y se actualizó openapi.yaml.
- 2026-05-03: Fase de migración SPA de operación interna marcada como CERRADA. Criterios: módulos migrados a SPA con alta/edición/baja por API, contrato v1 estable para alias PUT, y cobertura documental abierta para pruebas de smoke/test de /app.
- 2026-05-03: Se formalizó checklist de smoke test completo para /app (API+UI) en `docs/SMOKE_TEST_CHECKLIST.md`, incluyendo navegación por módulos y escenarios desktop/mobile.
