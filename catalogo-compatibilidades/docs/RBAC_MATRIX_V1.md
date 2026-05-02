# Matriz de Permisos API v1

## Roles
- `admin`
- `vendedor`

## Reglas globales
- Todos los endpoints `/api/v1/*` requieren JWT, excepto:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/refresh`
- Rate limit y auditoría aplican a todo `/api/v1/*`.

## Permisos por endpoint

### Auth
- `POST /api/v1/auth/login`: público
- `POST /api/v1/auth/refresh`: público
- `GET /api/v1/auth/me`: `admin`, `vendedor`
- `POST /api/v1/auth/logout`: `admin`, `vendedor`

### Search
- `GET /api/v1/search`: `admin`, `vendedor`
- `GET /api/v1/search-missed`: `admin`

### Productos
- `GET /api/v1/productos`: `admin`, `vendedor`
- `GET /api/v1/productos/{id}`: `admin`, `vendedor`
- `POST /api/v1/productos`: `admin`
- `PUT /api/v1/productos/{id}`: `admin`
- `DELETE /api/v1/productos/{id}`: `admin`

### Motocicletas
- `GET /api/v1/motocicletas`: `admin`, `vendedor`
- `GET /api/v1/motocicletas/{id}`: `admin`, `vendedor`
- `POST /api/v1/motocicletas`: `admin`
- `PUT /api/v1/motocicletas/{id}`: `admin`
- `DELETE /api/v1/motocicletas/{id}`: `admin`

### Piezas
- `GET /api/v1/piezas`: `admin`, `vendedor`
- `GET /api/v1/piezas/{id}`: `admin`, `vendedor`
- `POST /api/v1/piezas`: `admin`
- `PUT /api/v1/piezas/{id}`: `admin`
- `DELETE /api/v1/piezas/{id}`: `admin`

### Aliases
- `GET /api/v1/aliases`: `admin`, `vendedor`
- `POST /api/v1/aliases`: `admin`
- `DELETE /api/v1/aliases/{id}`: `admin`

### Compatibilidades
- `GET /api/v1/compatibilidades`: `admin`, `vendedor`
- `GET /api/v1/compatibilidades/{id}`: `admin`, `vendedor`
- `POST /api/v1/compatibilidades`: `admin`
- `PUT /api/v1/compatibilidades/{id}`: `admin`
- `DELETE /api/v1/compatibilidades/{id}`: `admin`
- `PATCH /api/v1/compatibilidades/{id}/confirmar`: `admin`, `vendedor`

### Importador
- `POST /api/v1/import/productos`: `admin`

## Códigos esperados
- Sin token: `401`
- Token inválido/expirado: `401`
- Rol insuficiente: `403`
