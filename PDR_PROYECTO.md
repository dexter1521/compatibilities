# 📄 PRD Técnico — Migración a API REST

# Proyecto: Shark Compatibility Engine

# Cliente: Shark Motors

# Versión: 2.0

# Estado: Listo para ejecución con agente IA

---

# 1. 🎯 Objetivo General

Evolucionar el backend actual del sistema de compatibilidades de refacciones para motocicletas hacia una arquitectura **API RESTful profesional**, manteniendo la lógica funcional existente y reutilizando lo que ya sirve.

El objetivo NO es rehacer todo desde cero.

El objetivo SÍ es:

- conservar reglas de negocio actuales
- refactorizar estructura
- exponer endpoints REST limpios
- separar frontend del backend
- preparar escalabilidad futura
- permitir app móvil / ecommerce / integraciones futuras

---

# 2. 🧠 Contexto Actual

Existe un backend funcional parcial desarrollado en PHP.

Características actuales:

- resuelve parte del flujo operativo
- lógica mezclada entre vistas/controladores/modelos
- respuestas no estandarizadas
- no sigue estándar REST
- posible acoplamiento fuerte con frontend
- útil como base de negocio

Esto significa:

👉 ya existe conocimiento codificado  
👉 no se debe desechar sin analizar

---

# 3. 🎯 Objetivo de la Migración

Convertir el backend actual en una plataforma API-first.

Debe quedar preparado para:

- Frontend web Bootstrap / HTMX
- futura app móvil
- futura app mostrador táctil
- integración con Mercado Libre
- integración WooCommerce
- sincronización con POS
- IA futura para búsqueda semántica

---

# 4. 🧱 Estrategia Técnica Obligatoria

## No reescribir todo desde cero.

Aplicar estrategia:

### Fase 1 — Auditoría

El agente debe revisar:

- controladores existentes
- modelos existentes
- helpers
- consultas SQL
- reglas de negocio reutilizables
- dependencias innecesarias

Clasificar:

- reutilizable
- refactorizable
- obsoleto
- duplicado
- riesgoso

---

### Fase 2 — API REST

Construir nueva capa API manteniendo lógica útil.

---

### Fase 3 — Limpieza gradual

Migrar módulos viejos sin romper operación.

---

# 5. ⚙️ Stack Deseado

Backend:

- PHP 8.2+
- Laravel 11 preferente

o mantener:

- CodeIgniter 4 (si acelera entrega)

Base de datos:

- MariaDB

Infra:

- Docker
- Nginx o Caddy
- Redis opcional

Auth:

- JWT

---

# 6. 📦 Recursos REST Requeridos

## Productos

```http
GET    /api/v1/productos
GET    /api/v1/productos/{id}
POST   /api/v1/productos
PUT    /api/v1/productos/{id}
DELETE /api/v1/productos/{id}
Motocicletas
GET /api/v1/motocicletas
POST /api/v1/motocicletas
PUT /api/v1/motocicletas/{id}
Piezas
GET /api/v1/piezas
POST /api/v1/piezas
Compatibilidades
GET /api/v1/compatibilidades
POST /api/v1/compatibilidades
PUT /api/v1/compatibilidades/{id}
DELETE /api/v1/compatibilidades/{id}
PATCH /api/v1/compatibilidades/{id}/confirmar
Alias
GET /api/v1/aliases
POST /api/v1/aliases
Buscador Inteligente
GET /api/v1/search?q=balata ft150

Respuesta esperada:

productos encontrados
motos compatibles
equivalencias
score relevancia
Importador Excel
POST /api/v1/import/productos
Búsquedas no encontradas
GET /api/v1/search-missed
7. 📄 Estándar de Respuesta JSON

Todas las respuestas deben usar:

{
  "success": true,
  "message": "Consulta exitosa",
  "data": [],
  "errors": null
}

Errores:

{
  "success": false,
  "message": "Validación fallida",
  "data": null,
  "errors": {
    "campo": ["requerido"]
  }
}
8. 🔐 Seguridad

Implementar:

JWT login
refresh token opcional
roles:
admin
vendedor
rate limit
logs auditoría
9. 🗄️ Modelo de Datos Recomendado

Tablas mínimas:

productos
piezas
motocicletas
marcas_moto
modelos_moto
compatibilidades
aliases
search_logs
users
roles
10. 🧠 Reglas de Negocio Clave
Confirmación positiva

Cuando vendedor confirma:

“sí funcionó”

Incrementar score de compatibilidad.

Búsqueda fallida

Guardar término buscado.

Alias

FT150 = FT 150 = Italika FT150

Equivalencias

Varias marcas pueden cubrir misma pieza.

11. 🚫 Lo que NO debe hacer el agente
rehacer todo sin revisar código actual
romper base de datos actual sin migración
mezclar vistas con API
usar respuestas HTML
hardcodear lógica
eliminar reglas existentes sin documentar
12. 📈 Entregables Esperados
Primera entrega:
estructura nueva backend
rutas REST
auth JWT
módulo productos
módulo búsqueda
Segunda entrega:
compatibilidades
alias
confirmaciones
importador Excel
Tercera entrega:
métricas
documentación Swagger
tests básicos
13. 📊 KPI Técnicos
tiempo respuesta < 400ms
búsqueda < 1 seg
endpoints documentados
código modular
cobertura mínima tests 40%
14. 🧠 Instrucción Directa al Agente IA

Analiza el proyecto existente y reutiliza lo valioso.

No destruyas lógica ya funcional.

Refactoriza hacia arquitectura REST profesional.

Prioriza velocidad + mantenibilidad + escalabilidad.

Cada cambio relevante documentarlo.

15. 🎯 Resultado Final Esperado

Backend moderno que conserve experiencia operativa actual pero quede listo para crecer como producto comercial.

16. Nombre Interno del Sistema

Shark Compatibility Engine API
```
