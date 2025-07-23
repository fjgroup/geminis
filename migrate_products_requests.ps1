# Script para migrar TODOS los Form Requests de Products

Write-Host "🚀 Migrando Form Requests del dominio Products..." -ForegroundColor Green

# Lista de requests relacionados con Products
$productRequests = @(
    "UpdateProductRequest",
    "StoreProductTypeRequest", 
    "UpdateProductTypeRequest",
    "UpdateProductPricingRequest",
    "StoreConfigurableOptionRequest",
    "UpdateConfigurableOptionRequest", 
    "StoreConfigurableOptionGroupRequest",
    "UpdateConfigurableOptionGroupRequest"
)

$destDir = "app\Domains\Products\Infrastructure\Http\Requests"

foreach ($request in $productRequests) {
    $sourcePath = "app\Http\Requests\Admin\$request.php"
    $destPath = "$destDir\$request.php"
    
    if (Test-Path $sourcePath) {
        # Copiar archivo
        Copy-Item $sourcePath $destPath -Force
        
        # Leer contenido
        $content = Get-Content $destPath -Raw
        
        # Actualizar namespace
        $content = $content -replace "namespace App\\Http\\Requests\\Admin;", "namespace App\\Domains\\Products\\Infrastructure\\Http\\Requests;"
        
        # Actualizar imports de modelos
        $content = $content -replace "use App\\Models\\Product;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\Product;"
        $content = $content -replace "use App\\Models\\ProductPricing;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\ProductPricing;"
        $content = $content -replace "use App\\Models\\ProductType;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\ProductType;"
        $content = $content -replace "use App\\Models\\ConfigurableOption;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\ConfigurableOption;"
        $content = $content -replace "use App\\Models\\ConfigurableOptionGroup;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\ConfigurableOptionGroup;"
        
        # Guardar cambios
        Set-Content $destPath $content -Encoding UTF8
        
        Write-Host "  ✅ Migrado: $request" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️ No encontrado: $request" -ForegroundColor Yellow
    }
}

Write-Host "🎉 ¡Migración de Form Requests completada!" -ForegroundColor Green
