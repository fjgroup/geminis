# Script para corregir TODOS los namespaces a arquitectura hexagonal

Write-Host "🔧 CORRECCIÓN MASIVA DE NAMESPACES - ARQUITECTURA HEXAGONAL" -ForegroundColor Green
Write-Host "=============================================================" -ForegroundColor Green

# Mapeo de modelos a sus dominios hexagonales
$namespaceMap = @{
    # Products Domain
    'App\Models\Product' = 'App\Domains\Products\Infrastructure\Persistence\Models\Product'
    'App\Models\ProductType' = 'App\Domains\Products\Infrastructure\Persistence\Models\ProductType'
    'App\Models\ProductPricing' = 'App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing'
    'App\Models\ConfigurableOption' = 'App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOption'
    'App\Models\ConfigurableOptionGroup' = 'App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOptionGroup'
    'App\Models\ConfigurableOptionPricing' = 'App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOptionPricing'
    'App\Models\BillingCycle' = 'App\Domains\Products\Infrastructure\Persistence\Models\BillingCycle'
    'App\Models\DiscountPercentage' = 'App\Domains\Products\Infrastructure\Persistence\Models\DiscountPercentage'
    
    # Users Domain
    'App\Models\User' = 'App\Domains\Users\Infrastructure\Persistence\Models\User'
    'App\Models\ResellerProfile' = 'App\Domains\Users\Infrastructure\Persistence\Models\ResellerProfile'
    
    # ClientServices Domain
    'App\Models\ClientService' = 'App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService'
    
    # BillingAndPayments Domain
    'App\Models\Transaction' = 'App\Domains\BillingAndPayments\Infrastructure\Persistence\Models\Transaction'
    'App\Models\PaymentMethod' = 'App\Domains\BillingAndPayments\Infrastructure\Persistence\Models\PaymentMethod'
    
    # Orders Domain
    'App\Models\OrderConfigurableOption' = 'App\Domains\Orders\Infrastructure\Persistence\Models\OrderConfigurableOption'
    
    # Corregir referencias de dominios antiguos
    'App\Domains\Products\Models\' = 'App\Domains\Products\Infrastructure\Persistence\Models\'
    'App\Domains\Users\Models\' = 'App\Domains\Users\Infrastructure\Persistence\Models\'
    'App\Domains\ClientServices\Models\' = 'App\Domains\ClientServices\Infrastructure\Persistence\Models\'
    'App\Domains\BillingAndPayments\Models\' = 'App\Domains\BillingAndPayments\Infrastructure\Persistence\Models\'
    'App\Domains\Invoices\Models\' = 'App\Domains\Invoices\Infrastructure\Persistence\Models\'
    'App\Domains\Orders\Models\' = 'App\Domains\Orders\Infrastructure\Persistence\Models\'
}

# Buscar todos los archivos PHP
$phpFiles = Get-ChildItem -Path "app" -Filter "*.php" -Recurse

$totalFiles = $phpFiles.Count
$processedFiles = 0
$modifiedFiles = 0

Write-Host "📁 Procesando $totalFiles archivos PHP..." -ForegroundColor Cyan

foreach ($file in $phpFiles) {
    $processedFiles++
    $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
    
    if (-not $content) {
        continue
    }
    
    $originalContent = $content
    $fileModified = $false
    
    # Aplicar todas las correcciones de namespace
    foreach ($oldNamespace in $namespaceMap.Keys) {
        $newNamespace = $namespaceMap[$oldNamespace]
        
        # Corregir use statements
        $pattern = "use\s+$([regex]::Escape($oldNamespace));"
        $replacement = "use $newNamespace;"
        if ($content -match $pattern) {
            $content = $content -replace $pattern, $replacement
            $fileModified = $true
        }
        
        # Corregir use statements con alias
        $pattern = "use\s+$([regex]::Escape($oldNamespace))\s+as\s+"
        $replacement = "use $newNamespace as "
        if ($content -match $pattern) {
            $content = $content -replace $pattern, $replacement
            $fileModified = $true
        }
    }
    
    # Corregir dobles barras invertidas
    if ($content -match '\\\\') {
        $content = $content -replace '\\\\', '\'
        $fileModified = $true
    }
    
    # Guardar solo si hubo cambios
    if ($fileModified -and ($content -ne $originalContent)) {
        try {
            Set-Content $file.FullName $content -Encoding UTF8
            $modifiedFiles++
            Write-Host "  ✅ $($file.Name)" -ForegroundColor Green
        } catch {
            Write-Host "  ❌ Error en $($file.Name): $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
    # Mostrar progreso cada 50 archivos
    if ($processedFiles % 50 -eq 0) {
        $percentage = [math]::Round(($processedFiles / $totalFiles) * 100, 1)
        Write-Host "📊 Progreso: $percentage% ($processedFiles/$totalFiles)" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "🎉 CORRECCIÓN COMPLETADA" -ForegroundColor Green
Write-Host "========================" -ForegroundColor Green
Write-Host "📁 Archivos procesados: $processedFiles" -ForegroundColor Cyan
Write-Host "✅ Archivos modificados: $modifiedFiles" -ForegroundColor Green
Write-Host "🏗️ Arquitectura hexagonal aplicada correctamente" -ForegroundColor Green
