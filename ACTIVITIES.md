PROMPT — Lead Engineer

Proyecto: Catálogo de Compatibilidades (MVP rápido)

🎯 Objetivo

Construir un MVP funcional en el menor tiempo posible (8–16 horas).

El sistema debe permitir responder en mostrador:

¿Qué pieza le queda a esta motocicleta?

📌 Contexto
Sistema actual: MyBusiness POS (ventas/inventario)
Problema: no existe lógica de compatibilidad pieza ↔ moto
Este sistema:
NO reemplaza el POS
solo agrega inteligencia de búsqueda

La clave de integración es:

clave_proveedor

⚙️ Stack (obligatorio)

Backend

PHP 8.2
CodeIgniter 4

Base de datos

MariaDB

Frontend

Template: Template-FivaAdmin-main
Bootstrap (incluido en template)
HTMX + AlpineJS

Infra

Docker Compose (app + nginx + db)
🏗️ Arquitectura
Excel (MyBusiness)
      ↓
Importador
      ↓
CodeIgniter 4 (API + Views)
      ↓
MariaDB
📁 Estructura esperada
app/
 ├ Controllers
 ├ Models
 ├ Services
 ├ Views
 │   ├ layouts
 │   ├ motos
 │   ├ piezas
 │   ├ compatibilidades
 │   └ import
 └ Database
     ├ Migrations
     └ Seeds
🧱 Alcance del MVP
1. Importador Excel
Subir archivo
Leer productos
Guardar:
proveedor
clave_proveedor
nombre
2. Catálogo

CRUD para:

motos
piezas maestras
compatibilidades
3. Buscador

Endpoint:

GET /search?q=

Debe buscar en:

productos
piezas
alias de motos

Debe devolver:

pieza
claves proveedor
compatibilidades

Si no hay resultados:

→ guardar búsqueda en busquedas_no_encontradas

4. Compatibilidades

Permitir:

crear relación pieza ↔ moto
confirmar desde UI

Endpoint:

POST /compatibilidades/{id}/confirm

Acción:

incrementar contador
marcar como confirmada
🖥️ UI (mínima)

Usar Template-FivaAdmin-main, NO diseñar desde cero.

Pantallas:
Buscador
input principal
resultados
botón "Funcionó"
Admin
CRUD motos
CRUD piezas
CRUD compatibilidades
Importador
subir Excel
procesar
🗄️ Base de datos (mínimo)

Tablas:

marcas
motocicletas
alias_motos
piezas_maestras
proveedores
productos
compatibilidades
equivalencias
busquedas_no_encontradas
import_jobs
import_items

Requisitos:

índices
evitar duplicados
normalización básica (slug/alias)
🚫 No incluir en MVP
ecommerce
sync con POS
roles avanzados
SPA
microservicios
✅ Criterio de éxito

El MVP está listo cuando:

Se puede importar Excel
Se pueden crear piezas
Se pueden registrar compatibilidades
Buscar "balata ft150" devuelve resultados
Se pueden confirmar resultados
Se registran búsquedas sin resultado
⏱️ Tiempo esperado

8–16 horas

🚀 Instrucción final

Empieza por:

docker-compose
migraciones
layout con template
CRUD básico
importador
buscador
⚠️ Regla

No optimizar.
No sobre diseñar.
Primero que funcione.