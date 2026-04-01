# Integracion FivaAdmin en CodeIgniter 4

## Tipo de documento
How-to guide (guia operativa para continuar el desarrollo del MVP).

## Objetivo
Tener el panel base de FivaAdmin funcionando dentro de CodeIgniter 4, reutilizando assets del template y dejando lista la base para Buscador, CRUD e Importador.

## Estado actual
- Layout base FivaAdmin montado en `app/Views/layouts/fiva.php`.
- Dashboard principal activo en `app/Views/dashboard/index.php`.
- Ruta `/` conectada al dashboard.
- Ruta `GET /search` creada como placeholder HTTP 501.
- HTMX y AlpineJS cargados desde CDN para acelerar el siguiente sprint.

## Archivos clave
- `app/Controllers/Home.php`
- `app/Config/Routes.php`
- `app/Views/layouts/fiva.php`
- `app/Views/dashboard/index.php`
- `public/fiva-assets/*`
- `public/favicon.png`

## Como levantar el proyecto
1. Desde la raiz del workspace, levantar contenedores:
   - `docker compose up -d --build`
2. Ejecutar migraciones:
   - `docker compose exec app php spark migrate`
3. Abrir navegador:
   - `http://localhost:8080`

## Criterios de validacion rapida
- `GET /` responde 200 y muestra el dashboard con estilo Fiva.
- `GET /search` responde 501 mostrando alerta de endpoint pendiente.
- El menu lateral y navbar renderizan correctamente en desktop y mobile.

## Siguiente bloque recomendado
1. Implementar pantalla Buscador con respuesta real desde base de datos.
2. Crear CRUD para motos, piezas maestras y compatibilidades.
3. Integrar importador de Excel con tabla `import_jobs` + `import_items`.
4. Conectar boton "Funciono" a confirmacion de compatibilidades.
