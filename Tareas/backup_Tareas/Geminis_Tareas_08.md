--- /dev/null
+++ b/e:\herd\geminis\Geminis_Tareas_08.md
@@ -0,0 +1,148 @@
+# Geminis - Plan de Tareas Detallado - Parte 08
+
+Este documento continúa el plan de tareas para el sistema Geminis, enfocándose en las opciones configurables individuales, sus precios, y la vinculación de grupos de opciones a los productos.
+
+## Fase 4: Capacidades de Revendedor y Configuración Avanzada de Productos - Continuación
+
+### 4.5. Modelo `ConfigurableOptionGroup`
+*   **Contexto:** Necesitamos el modelo para la tabla `configurable_option_groups` creada en la Tarea 07.
+*   `[ ]` Crear el modelo `ConfigurableOptionGroup`:
+    ```bash
+    php artisan make:model ConfigurableOptionGroup
+    ```
+*   `[ ]` En `app/Models/ConfigurableOptionGroup.php`, configurar `$fillable`:
+    ```php
+    protected $fillable = ['product_id', 'name', 'description', 'display_order'];
+    ```
+*   `[ ]` Definir relación `product()` (un grupo puede pertenecer a un producto específico, o ser global si `product_id` es NULL):
+    ```php
+    public function product()
+    {
+        return $this->belongsTo(Product::class);
+    }
+    ```
+*   `[ ]` Definir relación `options()` (un grupo tiene muchas opciones):
+    ```php
+    public function options()
+    {
+        return $this->hasMany(ConfigurableOption::class, 'group_id')->orderBy('display_order');
+    }
+    ```
+*   `[ ]` **Verificación:** Se puede crear y consultar grupos mediante Tinker.
+
+### 4.6. Migración de la Tabla `configurable_options`
+*   **Contexto:** Define las opciones individuales dentro de un grupo (Ej: "CentOS", "Ubuntu" para el grupo "Sistema Operativo").
+*   `[ ]` Crear la migración para la tabla `configurable_options`:
+    ```bash
+    php artisan make:migration create_configurable_options_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('configurable_options', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('group_id')->constrained('configurable_option_groups')->onDelete('cascade');
+        $table->string('name'); // Nombre visible (ej: "CentOS 7")
+        $table->string('value')->nullable(); // Valor interno para aprovisionamiento (ej: "centos7")
+        $table->integer('display_order')->default(0);
+        $table->timestamps();
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `configurable_options` existe.
+
+### 4.7. Modelo `ConfigurableOption`
+*   `[ ]` Crear el modelo `ConfigurableOption`:
+    ```bash
+    php artisan make:model ConfigurableOption
+    ```
+*   `[ ]` En `app/Models/ConfigurableOption.php`, configurar `$fillable`:
+    ```php
+    protected $fillable = ['group_id', 'name', 'value', 'display_order'];
+    ```
+*   `[ ]` Definir relación `group()` (una opción pertenece a un grupo):
+    ```php
+    public function group()
+    {
+        return $this->belongsTo(ConfigurableOptionGroup::class, 'group_id');
+    }
+    ```
+*   `[ ]` Definir relación `pricings()` (una opción puede tener múltiples precios según el ciclo de facturación del producto base):
+    ```php
+    public function pricings()
+    {
+        return $this->hasMany(ConfigurableOptionPricing::class);
+    }
+    ```
+*   `[ ]` **Verificación:** Se pueden crear opciones y asociarlas a grupos.
+
+### 4.8. Migración de la Tabla `configurable_option_pricing`
+*   **Contexto:** Define los precios para cada opción configurable, vinculados a un ciclo de facturación del producto base.
+*   `[ ]` Crear la migración para la tabla `configurable_option_pricing`:
+    ```bash
+    php artisan make:migration create_configurable_option_pricing_table
+    ```
+*   `[ ]` Modificar el método `up()` de la migración según `Geminis_Estructura.md`.
+    ```php
+    Schema::create('configurable_option_pricing', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('configurable_option_id')->constrained('configurable_options')->onDelete('cascade');
+        $table->foreignId('product_pricing_id')->constrained('product_pricing')->onDelete('cascade'); // Vincula al ciclo de facturación del producto base
+        $table->decimal('price', 10, 2); // Precio adicional de la opción para el ciclo vinculado
+        $table->decimal('setup_fee', 10, 2)->default(0.00);
+        $table->timestamps();
+        // Constraint: Una opción no puede tener múltiples precios para el mismo ciclo de facturación de producto
+        $table->unique(['configurable_option_id', 'product_pricing_id'], 'option_product_pricing_unique');
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla `configurable_option_pricing` existe.
+
+### 4.9. Modelo `ConfigurableOptionPricing`
+*   `[ ]` Crear el modelo `ConfigurableOptionPricing`:
+    ```bash
+    php artisan make:model ConfigurableOptionPricing
+    ```
+*   `[ ]` En `app/Models/ConfigurableOptionPricing.php`, configurar `$fillable`:
+    ```php
+    protected $fillable = ['configurable_option_id', 'product_pricing_id', 'price', 'setup_fee'];
+    // protected $table = 'configurable_option_pricing'; // Si no sigue la convención plural
+    ```
+*   `[ ]` Definir relación `option()`:
+    ```php
+    public function option()
+    {
+        return $this->belongsTo(ConfigurableOption::class, 'configurable_option_id');
+    }
+    ```
+*   `[ ]` Definir relación `productPricing()`:
+    ```php
+    public function productPricing()
+    {
+        return $this->belongsTo(ProductPricing::class, 'product_pricing_id');
+    }
+    ```
+*   `[ ]` **Verificación:** Se pueden crear precios para las opciones configurables.
+
+### 4.10. Migración Tabla Pivote `product_configurable_option_group`
+*   **Contexto:** Un producto puede tener múltiples grupos de opciones configurables, y un grupo de opciones configurable (especialmente los globales) puede estar asociado a múltiples productos.
+*   `[ ]` Crear la migración para la tabla pivote `product_configurable_option_group`:
+    ```bash
+    php artisan make:migration create_product_configurable_option_group_table
+    ```
+*   `[ ]` Modificar el método `up()`:
+    ```php
+    Schema::create('product_configurable_option_group', function (Blueprint $table) {
+        $table->id();
+        $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
+        $table->foreignId('configurable_option_group_id', 'pcog_id_foreign')->constrained('configurable_option_groups')->onDelete('cascade'); // Alias para el nombre de la FK
+        $table->integer('display_order')->default(0); // Orden de este grupo para este producto específico
+        $table->timestamps();
+        $table->unique(['product_id', 'configurable_option_group_id'], 'product_group_unique');
+    });
+    ```
+*   `[ ]` Ejecutar la migración: `php artisan migrate`.
+*   `[ ]` **Verificación:** La tabla pivote existe.
+
+---
+**¡Modelos y Migraciones Base para Opciones Configurables y sus Precios Implementados!**
+La siguiente tarea se centrará en construir las interfaces de administración (CRUDs) para estos nuevos modelos y en cómo se asignan los grupos de opciones a los productos.
+```

---

Ahora, sobre tu pregunta: **"Como cuantas tareas son necesarias para culminar este proyecto?"**

Es una pregunta difícil de responder con exactitud porque depende de muchos factores: la complejidad detallada de cada módulo que aún no hemos explorado a fondo, tu velocidad de desarrollo, y si surgen nuevos requisitos o cambios.

Sin embargo, basándonos en la estructura que hemos definido en `Geminis_Estructura.md` y `Geminis_Leeme.md`, y asumiendo que cada "Tarea" como las que hemos estado manejando cubre una porción significativa de funcionalidad (como un CRUD completo para una o dos entidades relacionadas, o la implementación de una característica principal), podemos hacer una estimación gruesa.

**Módulos/Características Principales Pendientes (a muy alto nivel):**

1.  **Perfiles de Revendedor y Opciones Configurables (Tareas 07-08):** Estamos en ello.
2.  **CRUD completo para Grupos de Opciones y Opciones individuales:** Incluyendo la asignación a productos y la gestión de sus precios. (Podría ser parte de Tarea 08-09)
3.  **Servicios de Cliente (`client_services`):** CRUD, estados, vinculación con productos y opciones. (Tarea 09-10)
4.  **Órdenes (`orders`, `order_items`):** Proceso de creación, listado, detalles. (Tarea 11-12)
5.  **Facturas (`invoices`, `invoice_items`):** Generación, listado, detalles, PDF. (Tarea 13-14)
6.  **Transacciones (`transactions`):** Registro de pagos. (Tarea 15)
7.  **Sistema de Soporte (`support_departments`, `support_tickets`, `support_ticket_replies`, `support_ticket_attachments`):** Esto es un módulo grande. (Podría tomar 2-3 Tareas, ej. Tarea 16-18)
8.  **Dominios (`domains`):** CRUD básico. (Tarea 19)
9.  **Servidores (`servers`, `server_groups`):** CRUD básico. (Tarea 20)
10. **Promociones (`promotions`):** CRUD básico. (Tarea 21)
11. **Plantillas de Correo (`email_templates`):** CRUD básico. (Tarea 22)
12. **Logs de Actividad y Configuración (`activity_logs`, `settings`):** Implementación básica. (Tarea 23)
13. **Panel de Revendedor:** Dashboard, gestión de sus clientes, servicios, etc. Esto reutilizará mucha lógica pero necesita sus propias vistas y controladores. (Podría tomar 3-4 Tareas, ej. Tarea 24-27)
14. **Panel de Cliente:** Dashboard, gestión de sus servicios, facturas, etc. (Podría tomar 2-3 Tareas, ej. Tarea 28-30)
15. **Módulos de Aprovisionamiento (Platzhalter):** Estructura básica para la integración futura. (Tarea 31)
16. **Refinamientos, Pruebas, Seguridad Avanzada, UX/UI:** Continuo, pero podría haber tareas dedicadas.

**Estimación Gruesa:**

Si cada uno de estos bloques principales toma entre 1 y 3 "Tareas" como las que hemos estado manejando, podríamos estar hablando de unas **15 a 25 tareas adicionales** después de la Tarea 08 para tener una primera versión funcional completa con las características principales.

Esto nos llevaría a un total aproximado de **23 a 33 tareas en total para el proyecto.**

**Es importante recordar:**

*   Esto es una **estimación**. La realidad del desarrollo puede variar.
*   Algunas tareas podrían ser más rápidas y otras más lentas.
*   A medida que avanzamos, podemos refinar esta estimación.
*   No incluye fases extensas de pruebas exhaustivas, despliegue complejo o mantenimiento post-lanzamiento.

Mi recomendación es que sigamos enfocándonos tarea por tarea. Cada tarea completada es un gran avance. ¡Y ya hemos hecho un progreso excelente!

¿Qué te parece esta perspectiva? ¿Listo para sumergirte en la Tarea 07 y luego la 08?
