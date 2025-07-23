# Script para limpiar el sistema híbrido y completar la migración a arquitectura hexagonal
# Ejecutar desde la raíz del proyecto

Write-Host "🧹 Iniciando limpieza del sistema híbrido..." -ForegroundColor Yellow

# 1. Eliminar modelos antiguos en /app/Models (si existen)
$modelsPath = "app\Models"
if (Test-Path $modelsPath) {
    Write-Host "📁 Limpiando modelos antiguos en $modelsPath..." -ForegroundColor Cyan
    $oldModels = @(
        "User.php", "Product.php", "Invoice.php", "ClientService.php", 
        "BillingCycle.php", "Transaction.php", "ConfigurableOption.php",
        "ConfigurableOptionGroup.php", "ProductPricing.php", "InvoiceItem.php"
    )
    
    foreach ($model in $oldModels) {
        $modelPath = Join-Path $modelsPath $model
        if (Test-Path $modelPath) {
            Remove-Item $modelPath -Force
            Write-Host "  ✓ Eliminado: $model" -ForegroundColor Green
        }
    }
}

# 2. Limpiar directorios vacíos
$emptyDirs = @(
    "app\Actions\Admin",
    "app\Actions\Client", 
    "app\Actions",
    "app\Policies",
    "app\Repositories",
    "app\Http\Requests\Admin",
    "app\Http\Requests\Auth",
    "app\Http\Requests\Client",
    "app\Http\Requests\Reseller"
)

foreach ($dir in $emptyDirs) {
    if (Test-Path $dir) {
        $items = Get-ChildItem $dir -Force
        if ($items.Count -eq 0) {
            Remove-Item $dir -Force -Recurse
            Write-Host "  ✓ Directorio vacío eliminado: $dir" -ForegroundColor Green
        }
    }
}

# 3. Verificar archivos problemáticos restantes
Write-Host "🔍 Verificando archivos con namespaces antiguos..." -ForegroundColor Cyan

$problematicFiles = @()

# Buscar archivos que usen App\Models\
$appFiles = Get-ChildItem -Path "app" -Recurse -Include "*.php" | Where-Object { $_.FullName -notlike "*\Domains\*" }
foreach ($file in $appFiles) {
    $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
    if ($content -and $content -match "use App\\Models\\") {
        $problematicFiles += $file.FullName
    }
}

if ($problematicFiles.Count -gt 0) {
    Write-Host "⚠️  Archivos con namespaces antiguos encontrados:" -ForegroundColor Yellow
    foreach ($file in $problematicFiles) {
        Write-Host "  - $file" -ForegroundColor Red
    }
} else {
    Write-Host "✅ No se encontraron archivos con namespaces antiguos" -ForegroundColor Green
}

# 4. Limpiar cache de Laravel
Write-Host "🧹 Limpiando cache de Laravel..." -ForegroundColor Cyan
try {
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    Write-Host "✅ Cache de Laravel limpiado" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Error limpiando cache: $($_.Exception.Message)" -ForegroundColor Yellow
}

Write-Host "🎉 Limpieza completada!" -ForegroundColor Green
Write-Host "📝 Próximos pasos:" -ForegroundColor Yellow
Write-Host "  1. Ejecutar: composer dump-autoload" -ForegroundColor White
Write-Host "  2. Actualizar rutas para usar controladores migrados" -ForegroundColor White
Write-Host "  3. Probar la aplicación" -ForegroundColor White
