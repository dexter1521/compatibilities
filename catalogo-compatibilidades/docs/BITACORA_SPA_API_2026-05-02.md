# Bitácora Técnica SPA + API

Fecha: 2026-05-02  
Proyecto: Catálogo de Compatibilidades (Shark Motors)

## Resumen Ejecutivo
Se consolidó la API v1 con contratos estables y pruebas de integración, y se inició la migración a SPA interna (Vue + Vite) consumiendo la API.

## Estado Actual
- API REST v1 funcional con JWT, RBAC, rate limit y auditoría.
- Contrato OpenAPI actualizado (`docs/openapi.yaml`).
- Suite de pruebas API en Docker pasando:
  - 12 tests
  - 61 assertions
- Front legacy depurado:
  - limpieza de `app/Views/layouts/fiva.php`
  - reducción de `public/fiva-assets/js/custom.js` a funciones usadas
- SPA base creada en `frontend/`:
  - Vue 3 + Vite + Pinia + Vue Router + Axios
  - login JWT, refresh y logout
  - shell responsive inicial (sidebar/topbar)
  - vista de buscador conectada a `/api/v1/search`
- Integración CI4 ↔ SPA:
  - host web en `/app` y fallback `/app/*`
  - build de Vite publicado en `public/spa`

## Commits Relevantes
- `c22ee41`: inicio SPA + eliminación de artefactos de prueba legacy
- `e682176`: integración host CI4 en `/app` + publicación build Vite en `public/spa`

## Rutas Clave
- Legacy server-rendered: `/`
- API docs: `/docs/api`
- OpenAPI: `/docs/openapi.yaml`
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
  - corto plazo: conviven vistas legacy y SPA
  - mediano plazo: migración gradual de módulos a SPA
- Se priorizó una SPA interna (no SSR) por contexto operativo de mostrador y panel interno.

## Pendientes Inmediatos
1. Migrar módulos SPA restantes:
   - productos
   - motocicletas
   - piezas
   - compatibilidades
   - importador
2. Integrar menú SPA completo por rol.
3. Reducir/retirar Debug Toolbar en `/app` para entorno de desarrollo SPA limpio.
4. Cerrar documentación de despliegue frontend (build/release/versionado de assets).

## Riesgos Abiertos
- Convivencia temporal legacy + SPA puede duplicar flujos hasta completar migración.
- Si no se define pronto política de despliegue SPA, puede haber drift entre `frontend/src` y `public/spa`.

## Criterio de Cierre de Fase
Esta fase se considera cerrada cuando:
- todos los módulos operativos estén en SPA,
- legacy quede en modo mantenimiento,
- y se ejecute smoke test integral sobre `/app` con API v1.
