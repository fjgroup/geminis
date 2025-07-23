# Script para migrar TODOS los controladores de Products

Write-Host "🚀 Migrando controladores del dominio Products..." -ForegroundColor Green

# Lista de controladores relacionados con Products
$productControllers = @(
    @{Source="AdminProductController.php"; Dest="Admin\AdminProductController.php"},
    @{Source="AdminProductControllerRefactored.php"; Dest="Admin\AdminProductControllerRefactored.php"},
    @{Source="AdminProductTypeController.php"; Dest="Admin\AdminProductTypeController.php"},
    @{Source="ProductController.php"; Dest="Admin\ProductController.php"},
    @{Source="ConfigurableOptionController.php"; Dest="Admin\ConfigurableOptionController.php"},
    @{Source="ConfigurableOptionGroupController.php"; Dest="Admin\ConfigurableOptionGroupController.php"},
    @{Source="DiscountPercentageController.php"; Dest="Admin\DiscountPercentageController.php"}
)

$sourceDir = "app\Http\Controllers\Admin"
$destDir = "app\Domains\Products\Infrastructure\Http\Controllers"

# Crear directorios si no existen
if (!(Test-Path "$destDir\Admin")) {
    New-Item -ItemType Directory -Path "$destDir\Admin" -Force | Out-Null
}

foreach ($controller in $productControllers) {
    $sourcePath = "$sourceDir\$($controller.Source)"
    $destPath = "$destDir\$($controller.Dest)"
    
    if (Test-Path $sourcePath) {
        # Copiar archivo
        Copy-Item $sourcePath $destPath -Force
        
        # Leer contenido
        $content = Get-Content $destPath -Raw
        
        # Actualizar namespace
        $content = $content -replace "namespace App\\Http\\Controllers\\Admin;", "namespace App\\Domains\\Products\\Infrastructure\\Http\\Controllers\\Admin;"
        
        # Actualizar imports de modelos
        $content = $content -replace "use App\\Models\\Product;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\Product;"
        $content = $content -replace "use App\\Models\\ProductPricing;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\ProductPricing;"
        $content = $content -replace "use App\\Models\\ProductType;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\ProductType;"
        $content = $content -replace "use App\\Models\\ConfigurableOption;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\ConfigurableOption;"
        $content = $content -replace "use App\\Models\\ConfigurableOptionGroup;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\ConfigurableOptionGroup;"
        $content = $content -replace "use App\\Models\\DiscountPercentage;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\DiscountPercentage;"
        $content = $content -replace "use App\\Models\\BillingCycle;", "use App\\Domains\\Products\\Infrastructure\\Persistence\\Models\\BillingCycle;"
        
        # Actualizar imports de requests
        $content = $content -replace "use App\\Http\\Requests\\Admin\\", "use App\\Domains\\Products\\Infrastructure\\Http\\Requests\\"
        
        # Guardar cambios
        Set-Content $destPath $content -Encoding UTF8
        
        Write-Host "  ✅ Migrado: $($controller.Source)" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️ No encontrado: $($controller.Source)" -ForegroundColor Yellow
    }
}

Write-Host "🎉 ¡Migración de controladores completada!" -ForegroundColor Green
