---
type: "always_apply"
description: "Example description"
---
# 🎯 PLAN MAESTRO DE MIGRACIÓN DDD + SOLID + HEXAGONAL

## ⚠️ REGLAS FUNDAMENTALES - LEER SIEMPRE ANTES DE TRABAJAR

### 🔒 CARPETAS Y PERMISOS
- **fjgroupca_NO_SOLID_NO_HEXAGONAL_NO_DDD/**: SOLO LECTURA - Es la referencia funcional
- **app/**: MOVER archivos (nunca eliminar) - Es el trabajo en progreso hexagonal
- **NO COMENTAR CÓDIGO** - Si algo no existe, CREARLO basándose en la referencia

### 🎯 OBJETIVO FINAL
- Sistema 100% DDD + SOLID + HEXAGONAL
- NO compatibilidad con sistema antiguo
- TODO debe funcionar perfectamente

### 🧠 LIMITACIONES DE MEMORIA
- Contexto: ~2 horas máximo
- SIEMPRE leer este archivo antes de continuar
- Actualizar el archivo con cada progreso importante  MIGRATION_MASTER_PLAN.md
- NO confiar en comentarios "MIGRADO" o "SOLUCIONADO" en código

---

## 📋 METODOLOGÍA NUEVA: "VERIFICACIÓN Y MIGRACIÓN COMPLETA"

### FASE 1: AUDITORÍA COMPLETA
1. **Inventario de Referencia**: Listar TODOS los archivos en fjgroupca_NO_SOLID_NO_HEXAGONAL_NO_DDD/app/
2. **Inventario Hexagonal**: Listar TODOS los archivos en app/Domains/
3. **Comparación**: Identificar qué falta, qué está duplicado, qué está mal ubicado

### FASE 2: MIGRACIÓN POR DOMINIO COMPLETO
1. **Un dominio a la vez** - Completar 100% antes de pasar al siguiente
2. **Para cada archivo faltante**:
   - Copiar desde referencia
   - Refactorizar para SOLID + DDD
   - Actualizar namespaces
   - Crear dependencias necesarias
3. **Verificar funcionamiento** antes de continuar

### FASE 3: INTEGRACIÓN Y PRUEBAS
1. Actualizar rutas
2. Actualizar service providers
3. Probar cada funcionalidad
4. Corregir errores inmediatamente

---