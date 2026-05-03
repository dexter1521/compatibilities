# Bit√°cora T√©cnica SPA + API

Fecha: 2026-05-02  
Proyecto: Cat√°logo de Compatibilidades (Shark Motors)

## Resumen Ejecutivo
Se consolid√≥ la API v1 con contratos estables y pruebas de integraci√≥n, y se inici√≥ la migraci√≥n a SPA interna (Vue + Vite) consumiendo la API.

## Estado Actual
- API REST v1 funcional con JWT, RBAC, rate limit y auditor√≠a.
- Contrato OpenAPI actualizado (`docs/openapi.yaml`).
- Suite de pruebas API en Docker pasando:
  - 12 tests
  - 61 assertions
- Front legacy depurado:
  - limpieza de `app/Views/layouts/fiva.php`
  - reducci√≥n de `public/fiva-assets/js/custom.js` a funciones usadas
- SPA base creada en `frontend/`:
  - Vue 3 + Vite + Pinia + Vue Router + Axios
  - login JWT, refresh y logout
  - shell responsive inicial (sidebar/topbar)
  - vista de buscador conectada a `/api/v1/search`
- Integraci√≥n CI4 ‚Üî SPA:
  - host web en `/app` y fallback `/app/*`
  - build de Vite publicado en `public/spa`

## Commits Relevantes
- `c22ee41`: inicio SPA + eliminaci√≥n de artefactos de prueba legacy
- `e682176`: integraci√≥n host CI4 en `/app` + publicaci√≥n build Vite en `public/spa`

## Rutas Clave
- Legacy server-rendered: `/`
- API docs: `/docs/api`
- OpenAPI: `/docs/openapi.yaml`
- SPA interna: `/app`
- API v1 base: `/api/v1/*`

## C√≥mo Ejecutar (estado actual)
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

## Decisiones T√©cnicas Tomadas
- Se mantiene CodeIgniter 4 como backend principal (sin migrar framework).
- Se adopta estrategia h√≠brida:
  - corto plazo: conviven vistas legacy y SPA
  - mediano plazo: migraci√≥n gradual de m√≥dulos a SPA
- Se prioriz√≥ una SPA interna (no SSR) por contexto operativo de mostrador y panel interno.

## Pendientes Inmediatos
- Cierre de migraciÛn SPA completado para mÛdulos funcionales principales.
- Mantener documentaciÛn de despliegue frontend y procedimiento de release versionado de `public/spa`.
- Consolidar men˙ de roles (fase de ajuste menor para permisos.)

## Riesgos Abiertos
- Convivencia temporal legacy + SPA puede duplicar flujos hasta completar migraci√≥n.
- Si no se define pronto pol√≠tica de despliegue SPA, puede haber drift entre `frontend/src` y `public/spa`.

## Criterio de Cierre de Fase
Esta fase se considera cerrada cuando:
- todos los m√≥dulos operativos est√©n en SPA,
- legacy quede en modo mantenimiento,
- y se ejecute smoke test integral sobre `/app` con API v1.


- 2026-05-03: Se agregÛ ediciÛn inline en mÛdulo SPA de Productos (PUT `/api/v1/productos/{id}`), con ediciÛn r·pida de clave, nombre y estado, y flujo Guardar/Cancelar.

- 2026-05-03: Extendida la migraciÛn SPA con ediciÛn inline en mÛdulos de `motocicletas` y `compatibilidades` (`PUT /api/v1/motocicletas/{id}` y `PUT /api/v1/compatibilidades/{id}`).
- 2026-05-03: Se completÛ ediciÛn inline en SPA para `piezas` y `aliases` (PUT `/api/v1/piezas/{id}`, PUT `/api/v1/aliases/{id}`), con acciones Editar/Guardar/Cancelar.

- 2026-05-03: MigraciÛn SPA de mÛdulos CRUD finalizada (productos, motocicletas, piezas, compatibilidades, aliases) con ediciÛn inline y flujo completo de alta/editar/baja (create, PUT, delete) para uso operativo interno.



- 2026-05-03: Se agrego el endpoint PUT /api/v1/aliases/{id} en API v1 para alinear con el cliente SPA (ruta, controller y servicio) y se actualizo openapi.yaml.

- 2026-05-03: Fase de migracion SPA de operacion interna marcada como CERRADA. Criterios: modulos migrados a SPA con alta/edicion/baja por API, contrato v1 estable para alias PUT, y cobertura documental abierta para pruebas de smoke/test de /app.
- 2026-05-03: Se formalizo checklist de smoke test completo para /app (API+UI) en docs/SMOKE_TEST_CHECKLIST.md, incluyendo navegacion por m√É¬≥dulos y escenarios desktop/mobile.
