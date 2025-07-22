# Script para migrar Form Requests a arquitectura hexagonal

# Crear directorios para requests
Write-Host "🏗️ Creando estructura de directorios..." -ForegroundColor Green

$domains = @(
    "Products",
    "Users", 
    "Invoices",
    "BillingAndPayments",
    "ClientServices",
    "Orders"
)

foreach ($domain in $domains) {
    $requestsPath = "app\Domains\$domain\Infrastructure\Http\Requests"
    if (!(Test-Path $requestsPath)) {
        New-Item -ItemType Directory -Path $requestsPath -Force | Out-Null
        Write-Host "✅ Creado: $requestsPath" -ForegroundColor Green
    }
}

# Mapeo de requests por dominio
$requestMapping = @{
    "Products" = @(
        "StoreProductRequest",
        "UpdateProductRequest", 
        "StoreProductTypeRequest",
        "UpdateProductTypeRequest",
        "StoreProductPricingRequest",
        "UpdateProductPricingRequest",
        "StoreConfigurableOptionRequest",
        "UpdateConfigurableOptionRequest",
        "StoreConfigurableOptionGroupRequest", 
        "UpdateConfigurableOptionGroupRequest"
    )
    "Users" = @(
        "StoreUserRequest",
        "UpdateUserRequest"
    )
    "Invoices" = @(
        "StoreManualInvoiceRequest",
        "UpdateInvoiceRequest"
    )
    "BillingAndPayments" = @(
        "StoreTransactionRequest",
        "ConfirmManualPaymentRequest"
    )
    "ClientServices" = @(
        "StoreClientServiceRequest",
        "UpdateClientServiceRequest"
    )
    "Orders" = @(
        "UpdateOrderRequest"
    )
}

Write-Host "🚀 Migrando Form Requests..." -ForegroundColor Green

foreach ($domain in $requestMapping.Keys) {
    Write-Host "📦 Procesando dominio: $domain" -ForegroundColor Yellow
    
    foreach ($request in $requestMapping[$domain]) {
        $sourcePath = "app\Http\Requests\Admin\$request.php"
        $destPath = "app\Domains\$domain\Infrastructure\Http\Requests\$request.php"
        
        if (Test-Path $sourcePath) {
            # Copiar archivo
            Copy-Item $sourcePath $destPath -Force
            
            # Leer contenido
            $content = Get-Content $destPath -Raw
            
            # Actualizar namespace
            $oldNamespace = "namespace App\\Http\\Requests\\Admin;"
            $newNamespace = "namespace App\\Domains\\$domain\\Infrastructure\\Http\\Requests;"
            $content = $content -replace [regex]::Escape($oldNamespace), $newNamespace
            
            # Guardar cambios
            Set-Content $destPath $content -Encoding UTF8
            
            Write-Host "  ✅ Migrado: $request" -ForegroundColor Green
        } else {
            Write-Host "  ⚠️ No encontrado: $request" -ForegroundColor Yellow
        }
    }
}

Write-Host "🎉 ¡Migración de Form Requests completada!" -ForegroundColor Green
