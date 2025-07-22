# Script para migrar modelos a arquitectura hexagonal

# Modelos de Products
$productsModels = @(
    "BillingCycle",
    "ConfigurableOption", 
    "ConfigurableOptionGroup",
    "ConfigurableOptionPricing",
    "DiscountPercentage",
    "Product",
    "ProductPricing",
    "ProductType"
)

# Modelos de Users
$usersModels = @(
    "User"
)

# Modelos de BillingAndPayments
$billingModels = @(
    "Transaction"
)

# Modelos de Orders
$ordersModels = @(
    "Order",
    "OrderItem",
    "OrderConfigurableOption"
)

# Modelos de ClientServices
$clientServicesModels = @(
    "ClientService"
)

# Modelos de Invoices
$invoicesModels = @(
    "Invoice",
    "InvoiceItem"
)

Write-Host "🚀 Migrando modelos a arquitectura hexagonal..." -ForegroundColor Green

# Migrar modelos de Products
foreach ($model in $productsModels) {
    $source = "app\Models\$model.php"
    $dest = "app\Domains\Products\Infrastructure\Persistence\Models\$model.php"
    
    if (Test-Path $source) {
        Write-Host "📦 Migrando $model a Products domain..." -ForegroundColor Yellow
        Copy-Item $source $dest -Force
        
        # Actualizar namespace
        $content = Get-Content $dest -Raw
        $content = $content -replace "namespace App\\Models;", "namespace App\Domains\Products\Infrastructure\Persistence\Models;"
        Set-Content $dest $content
        
        Write-Host "✅ $model migrado exitosamente" -ForegroundColor Green
    }
}

# Migrar modelos de Users
foreach ($model in $usersModels) {
    $source = "app\Models\$model.php"
    $dest = "app\Domains\Users\Infrastructure\Persistence\Models\$model.php"
    
    if (Test-Path $source) {
        Write-Host "👥 Migrando $model a Users domain..." -ForegroundColor Yellow
        Copy-Item $source $dest -Force
        
        # Actualizar namespace
        $content = Get-Content $dest -Raw
        $content = $content -replace "namespace App\\Models;", "namespace App\Domains\Users\Infrastructure\Persistence\Models;"
        Set-Content $dest $content
        
        Write-Host "✅ $model migrado exitosamente" -ForegroundColor Green
    }
}

# Migrar modelos de BillingAndPayments
foreach ($model in $billingModels) {
    $source = "app\Models\$model.php"
    $dest = "app\Domains\BillingAndPayments\Infrastructure\Persistence\Models\$model.php"
    
    if (Test-Path $source) {
        Write-Host "💳 Migrando $model a BillingAndPayments domain..." -ForegroundColor Yellow
        Copy-Item $source $dest -Force
        
        # Actualizar namespace
        $content = Get-Content $dest -Raw
        $content = $content -replace "namespace App\\Models;", "namespace App\Domains\BillingAndPayments\Infrastructure\Persistence\Models;"
        Set-Content $dest $content
        
        Write-Host "✅ $model migrado exitosamente" -ForegroundColor Green
    }
}

# Migrar modelos de Orders
foreach ($model in $ordersModels) {
    $source = "app\Models\$model.php"
    $dest = "app\Domains\Orders\Infrastructure\Persistence\Models\$model.php"
    
    if (Test-Path $source) {
        Write-Host "🛒 Migrando $model a Orders domain..." -ForegroundColor Yellow
        Copy-Item $source $dest -Force
        
        # Actualizar namespace
        $content = Get-Content $dest -Raw
        $content = $content -replace "namespace App\\Models;", "namespace App\Domains\Orders\Infrastructure\Persistence\Models;"
        Set-Content $dest $content
        
        Write-Host "✅ $model migrado exitosamente" -ForegroundColor Green
    }
}

# Migrar modelos de ClientServices
foreach ($model in $clientServicesModels) {
    $source = "app\Models\$model.php"
    $dest = "app\Domains\ClientServices\Infrastructure\Persistence\Models\$model.php"
    
    if (Test-Path $source) {
        Write-Host "🔧 Migrando $model a ClientServices domain..." -ForegroundColor Yellow
        Copy-Item $source $dest -Force
        
        # Actualizar namespace
        $content = Get-Content $dest -Raw
        $content = $content -replace "namespace App\\Models;", "namespace App\Domains\ClientServices\Infrastructure\Persistence\Models;"
        Set-Content $dest $content
        
        Write-Host "✅ $model migrado exitosamente" -ForegroundColor Green
    }
}

# Migrar modelos de Invoices
foreach ($model in $invoicesModels) {
    $source = "app\Models\$model.php"
    $dest = "app\Domains\Invoices\Infrastructure\Persistence\Models\$model.php"
    
    if (Test-Path $source) {
        Write-Host "🧾 Migrando $model a Invoices domain..." -ForegroundColor Yellow
        Copy-Item $source $dest -Force
        
        # Actualizar namespace
        $content = Get-Content $dest -Raw
        $content = $content -replace "namespace App\\Models;", "namespace App\Domains\Invoices\Infrastructure\Persistence\Models;"
        Set-Content $dest $content
        
        Write-Host "✅ $model migrado exitosamente" -ForegroundColor Green
    }
}

Write-Host "🎉 ¡Migración de modelos completada!" -ForegroundColor Green
Write-Host "📋 Próximo paso: Crear entidades de dominio puras" -ForegroundColor Cyan
