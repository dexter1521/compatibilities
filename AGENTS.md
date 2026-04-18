# Reglas para Agentes en el Repositorio

Este documento establece las reglas y directrices que deben seguir todos los agentes (como subagentes de IA, scripts automatizados o herramientas de desarrollo) que interactúen con este repositorio.

## Proyecto
Este repositorio contiene el proyecto **Catálogo de Compatibilidades** desarrollado con **CodeIgniter 4**, parte del sitio web **Shark Motors**.

## Reglas Generales
1. **No modificar archivos críticos sin aprobación**: Antes de editar archivos como `composer.json`, `Dockerfile`, archivos de configuración en `app/Config/`, o bases de datos, obtener confirmación del desarrollador jefe.

2. **Seguir estándares de CodeIgniter 4**: Todas las modificaciones deben adherirse a las mejores prácticas de CodeIgniter 4, incluyendo estructura de directorios, convenciones de nomenclatura y patrones de diseño.

3. **Validar cambios**: Después de cualquier modificación, ejecutar pruebas unitarias (`phpunit`), verificar sintaxis y asegurar que el proyecto compile sin errores.

4. **Documentar cambios**: Proporcionar comentarios claros en el código y actualizar documentación relevante (como `README.md` o archivos en `docs/`).

5. **Seguridad primero**: No exponer información sensible como claves API, contraseñas o datos de configuración en commits o registros.

6. **Usar control de versiones**: Todos los cambios deben hacerse a través de branches separados y pull requests para revisión.

7. **No hacer commit de archivos temporales o basura**: Evitar incluir archivos de debug, cookies, archivos HTML generados temporalmente, scripts de prueba o cualquier archivo no relacionado con el código fuente principal del proyecto.

## Reglas Específicas para Agentes
- **Acceso limitado**: Los agentes solo pueden acceder a archivos dentro del workspace definido. No intentar acceder a directorios externos o sistemas no autorizados.
- **Ejecución controlada**: No ejecutar comandos destructivos (como `rm -rf`) sin verificación explícita.
- **Monitoreo**: Registrar todas las acciones realizadas para auditoría.
- **Compatibilidad**: Asegurar que cualquier herramienta o script sea compatible con el entorno (Windows, Docker, etc.).

## Contacto
Para preguntas o modificaciones a estas reglas, contactar al administrador del repositorio.