# Smoke Test Checklist - Día de Encendido

## 1) Preparación de entorno
1. Levantar servicios.
2. Ejecutar migraciones pendientes.
3. Ejecutar `AuthSeeder`.
4. Verificar que `JWT_SECRET` esté definido en `.env`.

## 2) Auth y seguridad
1. `POST /api/v1/auth/login` responde 200.
2. `GET /api/v1/auth/me` con token responde 200.
3. `POST /api/v1/auth/refresh` responde 200 y rota refresh token.
4. `POST /api/v1/auth/logout` responde 200.
5. Sin token en `/api/v1/productos` responde 401.
6. Con rol insuficiente en endpoint admin responde 403.
7. Flood de requests activa 429 (rate-limit).

## 3) Catálogos v1
1. `GET /api/v1/productos` responde 200 + `meta`.
2. `GET /api/v1/motocicletas` responde 200.
3. `GET /api/v1/piezas` responde 200.
4. `GET /api/v1/aliases` responde 200.
5. `GET /api/v1/compatibilidades` responde 200.

## 4) Búsqueda e import
1. `GET /api/v1/search?q=...` responde 200 con `items`.
2. `GET /api/v1/search-missed` (admin) responde 200.
3. `POST /api/v1/import/productos` (admin) responde 201 con `job_id`.

## 5) Auditoría
1. Confirmar inserciones en `audit_logs` por llamadas API.
2. Verificar `user_id` en logs para endpoints autenticados.

## 6) Documentación
1. `GET /docs/openapi.yaml` responde archivo.
2. `GET /docs/api` renderiza Swagger UI.
3. Operaciones críticas presentes en OpenAPI (`auth`, `productos`, `search`, `compatibilidades`).

## 7) Criterio de salida
- Todas las pruebas anteriores en verde sin errores 500.
- Contratos JSON (`status/success/data/message/errors`) consistentes.
