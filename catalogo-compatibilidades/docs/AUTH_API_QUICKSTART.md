# Auth API Quickstart (v1)

## Variables opcionales para seeder

```bash
ADMIN_EMAIL=admin@sharkmotors.local
ADMIN_PASSWORD=Admin123!
ADMIN_NAME="Administrador API"
```

## Preparación de BD

```bash
php spark migrate
php spark db:seed AuthSeeder
```

## Flujo con cURL

Base URL ejemplo: `http://localhost:8080`

### 1) Login

```bash
curl -X POST "http://localhost:8080/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@sharkmotors.local",
    "password": "Admin123!"
  }'
```

Respuesta esperada: `data.access_token`, `data.refresh_token`.

### 2) Me

```bash
curl "http://localhost:8080/api/v1/auth/me" \
  -H "Authorization: Bearer ACCESS_TOKEN"
```

### 3) Refresh

```bash
curl -X POST "http://localhost:8080/api/v1/auth/refresh" \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "REFRESH_TOKEN"
  }'
```

### 4) Logout

```bash
curl -X POST "http://localhost:8080/api/v1/auth/logout" \
  -H "Authorization: Bearer ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "REFRESH_TOKEN"
  }'
```

## Notas

- `login` y `refresh` son públicos en `v1`.
- El resto de `/api/v1/*` requiere `Authorization: Bearer ...`.
- Rate limit activo por ruta/IP.
- Auditoría activa en tabla `audit_logs`.
