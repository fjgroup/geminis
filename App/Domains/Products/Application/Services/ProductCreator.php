<?php

namespace App\Domains\Products\Application\Services;

use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductType;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use App\Domains\Products\Infrastructure\Persistence\Models\BillingCycle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Servicio especializado para creación de productos
 * 
 * Aplica Single Responsibility Principle - solo se encarga de crear productos
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ProductCreator
{
    /**
     * Crear un nuevo producto
     */
    public function create(array $productData): array
    {
        try {
            DB::beginTransaction();

            // Validar que el slug no exista
            if (isset($productData['slug']) && Product::where('slug', $productData['slug'])->exists()) {
                return [
                    'success' => false,
                    'message' => 'El slug ya está en uso',
                    'product' => null
                ];
            }

            // Generar slug si no se proporciona
            if (!isset($productData['slug']) || empty($productData['slug'])) {
                $productData['slug'] = Str::slug($productData['name']);
                
                // Asegurar que el slug sea único
                $originalSlug = $productData['slug'];
                $counter = 1;
                while (Product::where('slug', $productData['slug'])->exists()) {
                    $productData['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Validar product_type_id si se proporciona
            if (isset($productData['product_type_id'])) {
                $productType = ProductType::find($productData['product_type_id']);
                if (!$productType) {
                    return [
                        'success' => false,
                        'message' => 'Tipo de producto no encontrado',
                        'product' => null
                    ];
                }
            }

            // Crear el producto
            $product = Product::create([
                'name' => $productData['name'],
                'slug' => $productData['slug'],
                'description' => $productData['description'] ?? null,
                'module_name' => $productData['module_name'] ?? null,
                'owner_id' => $productData['owner_id'] ?? null,
                'status' => $productData['status'] ?? 'active',
                'is_publicly_available' => $productData['is_publicly_available'] ?? true,
                'is_resellable_by_default' => $productData['is_resellable_by_default'] ?? true,
                'display_order' => $productData['display_order'] ?? 0,
                'product_type_id' => $productData['product_type_id'] ?? null,
                'welcome_email_template_id' => $productData['welcome_email_template_id'] ?? null,
            ]);

            // Crear precios si se proporcionan
            if (isset($productData['pricings']) && is_array($productData['pricings'])) {
                foreach ($productData['pricings'] as $pricingData) {
                    $this->createProductPricing($product, $pricingData);
                }
            }

            DB::commit();

            Log::info('Producto creado exitosamente', [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'created_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'product' => $product->load(['productType', 'pricings.billingCycle'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creando producto', [
                'error' => $e->getMessage(),
                'product_data' => $productData,
                'created_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear el producto: ' . $e->getMessage(),
                'product' => null
            ];
        }
    }

    /**
     * Crear producto con tipo específico
     */
    public function createWithType(array $productData, int $productTypeId): array
    {
        $productData['product_type_id'] = $productTypeId;
        return $this->create($productData);
    }

    /**
     * Crear producto de dominio
     */
    public function createDomainProduct(array $productData): array
    {
        // Buscar o crear tipo de producto para dominios
        $domainType = ProductType::firstOrCreate(
            ['name' => 'Domain'],
            [
                'slug' => 'domain',
                'description' => 'Registro y gestión de dominios',
                'is_active' => true
            ]
        );

        $productData['product_type_id'] = $domainType->id;
        $productData['module_name'] = 'domain';
        $productData['is_publicly_available'] = true;

        return $this->create($productData);
    }

    /**
     * Crear producto de hosting
     */
    public function createHostingProduct(array $productData): array
    {
        // Buscar o crear tipo de producto para hosting
        $hostingType = ProductType::firstOrCreate(
            ['name' => 'Hosting'],
            [
                'slug' => 'hosting',
                'description' => 'Servicios de hosting web',
                'is_active' => true
            ]
        );

        $productData['product_type_id'] = $hostingType->id;
        $productData['module_name'] = $productData['module_name'] ?? 'cpanel';
        $productData['is_publicly_available'] = true;

        return $this->create($productData);
    }

    /**
     * Crear pricing para un producto
     */
    private function createProductPricing(Product $product, array $pricingData): ProductPricing
    {
        // Validar billing_cycle_id
        $billingCycle = BillingCycle::find($pricingData['billing_cycle_id']);
        if (!$billingCycle) {
            throw new \Exception('Ciclo de facturación no encontrado');
        }

        return ProductPricing::create([
            'product_id' => $product->id,
            'billing_cycle_id' => $pricingData['billing_cycle_id'],
            'price' => $pricingData['price'],
            'setup_fee' => $pricingData['setup_fee'] ?? 0,
            'currency_code' => $pricingData['currency_code'] ?? 'USD',
            'is_active' => $pricingData['is_active'] ?? true,
        ]);
    }

    /**
     * Validar datos de producto antes de crear
     */
    public function validateProductData(array $productData): array
    {
        $errors = [];

        // Validar nombre requerido
        if (empty($productData['name'])) {
            $errors[] = 'El nombre del producto es requerido';
        }

        // Validar slug único si se proporciona
        if (isset($productData['slug']) && !empty($productData['slug'])) {
            if (Product::where('slug', $productData['slug'])->exists()) {
                $errors[] = 'El slug ya está en uso';
            }
        }

        // Validar estado válido
        if (isset($productData['status'])) {
            $validStatuses = ['active', 'inactive', 'draft'];
            if (!in_array($productData['status'], $validStatuses)) {
                $errors[] = 'Estado no válido';
            }
        }

        // Validar product_type_id si se proporciona
        if (isset($productData['product_type_id'])) {
            if (!ProductType::find($productData['product_type_id'])) {
                $errors[] = 'Tipo de producto no encontrado';
            }
        }

        // Validar precios si se proporcionan
        if (isset($productData['pricings']) && is_array($productData['pricings'])) {
            foreach ($productData['pricings'] as $index => $pricingData) {
                if (empty($pricingData['billing_cycle_id'])) {
                    $errors[] = "Precio #{$index}: Ciclo de facturación requerido";
                }
                if (!isset($pricingData['price']) || $pricingData['price'] < 0) {
                    $errors[] = "Precio #{$index}: Precio debe ser mayor o igual a 0";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Crear producto desde plantilla
     */
    public function createFromTemplate(int $templateProductId, array $overrides = []): array
    {
        try {
            $template = Product::with(['pricings', 'configurableOptionGroups'])->find($templateProductId);
            
            if (!$template) {
                return [
                    'success' => false,
                    'message' => 'Producto plantilla no encontrado',
                    'product' => null
                ];
            }

            // Preparar datos del nuevo producto basado en la plantilla
            $productData = [
                'name' => $overrides['name'] ?? $template->name . ' (Copia)',
                'description' => $overrides['description'] ?? $template->description,
                'module_name' => $overrides['module_name'] ?? $template->module_name,
                'product_type_id' => $overrides['product_type_id'] ?? $template->product_type_id,
                'status' => $overrides['status'] ?? 'draft',
                'is_publicly_available' => $overrides['is_publicly_available'] ?? false,
                'is_resellable_by_default' => $overrides['is_resellable_by_default'] ?? $template->is_resellable_by_default,
                'owner_id' => $overrides['owner_id'] ?? $template->owner_id,
            ];

            // Copiar precios si no se especifican otros
            if (!isset($overrides['pricings']) && $template->pricings->isNotEmpty()) {
                $productData['pricings'] = $template->pricings->map(function ($pricing) {
                    return [
                        'billing_cycle_id' => $pricing->billing_cycle_id,
                        'price' => $pricing->price,
                        'setup_fee' => $pricing->setup_fee,
                        'currency_code' => $pricing->currency_code,
                        'is_active' => $pricing->is_active,
                    ];
                })->toArray();
            }

            return $this->create($productData);

        } catch (\Exception $e) {
            Log::error('Error creando producto desde plantilla', [
                'template_id' => $templateProductId,
                'error' => $e->getMessage(),
                'created_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear producto desde plantilla',
                'product' => null
            ];
        }
    }

    /**
     * Obtener estadísticas de creación de productos
     */
    public function getCreationStats(): array
    {
        try {
            $totalProducts = Product::count();
            $activeProducts = Product::where('status', 'active')->count();
            $publicProducts = Product::where('is_publicly_available', true)->count();
            $recentProducts = Product::where('created_at', '>=', now()->subDays(7))->count();

            return [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'public_products' => $publicProducts,
                'recent_products' => $recentProducts,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de productos', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_products' => 0,
                'active_products' => 0,
                'public_products' => 0,
                'recent_products' => 0,
            ];
        }
    }
}
