📄 PDR — Catálogo Inteligente de Compatibilidades

Proyecto: Shark Motors
Versión: 1.0
Estado: Aprobación para desarrollo MVP

1. 🎯 Objetivo del producto

Desarrollar un sistema que permita:

Identificar rápidamente qué refacción es compatible con una motocicleta.

Reduciendo:

tiempo de atención en mostrador
errores de recomendación
dependencia del conocimiento del vendedor

2. 🧠 Problema

Actualmente:

MyBusiness POS gestiona ventas e inventario
pero no resuelve compatibilidades

Ejemplo real:

Cliente: “Balatas para FT150”

Problemas actuales:

búsqueda manual
conocimiento empírico
inconsistencias
pérdida de ventas
3. 💡 Solución

Un sistema web que:

centraliza compatibilidades pieza ↔ motocicleta
permite búsqueda rápida
muestra equivalencias entre proveedores
aprende con el uso (confirmaciones)
4. 🧩 Principio clave

Separación de responsabilidades:

Sistema	Función
MyBusiness POS	Operación (ventas, inventario)
Catálogo	Inteligencia (compatibilidades)
5. ⚙️ Alcance del MVP
Incluye
Importación de productos desde Excel
Búsqueda por motocicleta
Búsqueda por texto
Registro de compatibilidades
Equivalencias entre proveedores
Confirmación desde mostrador
Registro de búsquedas no encontradas
Alias de motocicletas
No incluye
sincronización en tiempo real con POS
inventario o precios
ecommerce
roles avanzados
multi-tenant
6. 👤 Usuarios
Usuario principal
Vendedor en mostrador
Usuario secundario
Administrador (captura de datos)
7. 🔄 Flujo principal
Flujo operativo
Se registra producto en POS
Se importa al catálogo
Se vincula con pieza y moto
Cliente solicita pieza
Vendedor busca en sistema
Sistema devuelve opciones
Venta se realiza en POS
8. 🔍 Funcionalidades
8.1 Buscador

Entrada:

balata ft150

Salida:

pieza identificada
claves proveedor
motos compatibles
8.2 Compatibilidades

Relación:

pieza ↔ motocicleta

Incluye:

nivel de confianza
notas
confirmaciones
8.3 Equivalencias

Permite:

múltiples productos para una misma pieza

Ejemplo:

XAX
P102
BTA150
8.4 Confirmación

Acción:

Funcionó

Resultado:

incrementa confianza
valida compatibilidad
8.5 Búsquedas no encontradas

Captura automática de:

términos buscados
frecuencia

Uso:

priorizar captura
8.6 Importador Excel

Permite:

cargar catálogo inicial
acelerar arranque
9. 🗄️ Modelo de datos (alto nivel)

Entidades principales:

Motocicletas
Piezas
Productos
Compatibilidades
Equivalencias
Búsquedas
10. 📊 Métricas de éxito
% de productos catalogados
búsquedas exitosas
búsquedas no encontradas
confirmaciones registradas
tiempo de atención
11. ⚠️ Riesgos
Riesgo	Impacto	Mitigación
Captura lenta	Alto	Importador + priorización
Datos inconsistentes	Medio	Alias + normalización
Falta de uso	Alto	UI simple
12. 🧱 Stack tecnológico

Backend:

CodeIgniter 4

Base de datos:

MariaDB

Frontend:

Template-FivaAdmin
Bootstrap
HTMX

Infra:

Docker
13. ⏱️ Estimación MVP

Tiempo:

8 – 16 horas (con IA)
14. 🚀 Entregables MVP
API funcional
base de datos
importador Excel
buscador operativo
UI básica
sistema usable en mostrador
15. 🧠 Valor estratégico

Este sistema construye:

una base de conocimiento propia
una ventaja competitiva
un activo difícil de replicar
16. 📌 Conclusión

El proyecto:

es viable
tiene impacto directo en ventas
requiere disciplina en captura

El valor no está en el código, está en los datos.
