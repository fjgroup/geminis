# Plan de Organización del Frontend (Vue.js con Inertia)

Este documento detalla una estructura de directorios y principios de organización para el código frontend de Vue.js en el proyecto, buscando mejorar la mantenibilidad, escalabilidad y facilitar la depuración. La organización propuesta sigue la separación por roles (Admin, Cliente, etc.) ya implementada en el backend.

## Estructura de Directorios Propuesta (`resources/js/`)

```
resources/js/
├── Pages/          # Vistas principales (páginas de Inertia)
│   ├── Admin/      # Páginas específicas del panel de administración
│   │   ├── Dashboard.vue
│   │   ├── Invoices/
│   │   │   ├── Index.vue
│   │   │   ├── Show.vue
│   │   │   └── ...
│   │   ├── Orders/
│   │   │   └── ...
│   │   └── ...
│   ├── Client/     # Páginas específicas del área de cliente
│   │   ├── Dashboard.vue
│   │   ├── Invoices/
│   │   │   ├── Index.vue
│   │   │   └── ...
│   │   └── ...
│   ├── Reseller/   # Páginas específicas del panel de revendedor (si aplica)
│   │   └── ...
│   └── Welcome.vue   # Ejemplo de página pública (landing page)
├── Components/     # Componentes reutilizables
│   ├── Shared/     # Componentes usados en múltiples roles/áreas (globales)
│   │   ├── Button.vue
│   │   ├── Modal.vue
│   │   └── ...
│   ├── Admin/      # Componentes específicos de la interfaz de administración
│   │   ├── AdminLayout.vue # (Puede ir aquí o en Layouts)
│   │   ├── InvoiceListTable.vue
│   │   └── ...
│   ├── Client/     # Componentes específicos de la interfaz de cliente
│   │   ├── ClientLayout.vue # (Puede ir aquí o en Layouts)
│   │   ├── ClientDashboardStats.vue
│   │   └── ...
│   ├── Forms/      # Componentes de formulario especializados (si no son específicos de rol)
│   │   ├── DatePicker.vue
│   │   └── ...
│   └── UI/         # Elementos básicos de interfaz de usuario (iconos, loaders)
│       ├── Icon.vue
│       ├── LoadingSpinner.vue
│       └── ...
├── Layouts/        # Layouts principales de Inertia
│   ├── AdminLayout.vue
│   ├── AuthenticatedLayout.vue
│   ├── GuestLayout.vue
│   └── ...
├── Composables/    # Lógica Vue reutilizable (Composition API)
│   ├── useForm.js
│   ├── usePagination.js
│   └── ...
├── Utilities/      # Funciones JavaScript auxiliares (no dependientes de Vue)
│   ├── formatDate.js
│   ├── validationHelpers.js
│   └── ...
└── app.js          # Punto de entrada principal
```

## Principios Clave de Organización

1.  **Separación por Rol/Área:** Los directorios `Pages/`, `Components/`, etc., deben reflejar la estructura de roles del backend (`Admin`, `Client`, `Reseller`) para mantener la consistencia.
2.  **Granularidad de Componentes:** Dividir las interfaces en componentes pequeños y reutilizables, ubicándolos en el directorio `Components/` apropiado (`Shared/`, `Admin/`, `Client/`).
3.  **Lógica Reutilizable:** Utilizar `Composables/` para lógica Vue compartida y `Utilities/` para funciones JavaScript puras.
4.  **Alineación con Rutas y Controladores:** Asegurarse de que la estructura en `Pages/` coincida con las vistas renderizadas por los controladores de Inertia. Los nombres de archivo en `Pages/` deben corresponder directamente a la primera parte del parámetro en `Inertia::render('Directorio/NombrePagina', ...)`.

## Implementación del Plan

Una vez aprobado este plan, la implementación implicaría:

1.  Crear los subdirectorios necesarios dentro de `resources/js/Components/` (ej: `Admin/`, `Client/`, `Shared/`).
2.  Mover los componentes existentes a los directorios apropiados según su ámbito de uso.
3.  Al crear nuevos componentes, ubicarlos en el directorio correcto desde el principio.
4.  Identificar y extraer lógica reutilizable en `Composables/` o `Utilities/`.
5.  Refactorizar las páginas (`Pages/`) para utilizar los componentes y composables organizados.

Este plan servirá como una guía clara para estructurar el código frontend, lo que facilitará el desarrollo futuro y la depuración al tener una organización lógica y predecible.
