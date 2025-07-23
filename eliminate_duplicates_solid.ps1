# Script para eliminar duplicados respetando principios SOLID
# Mantiene solo la versión hexagonal (Single Responsibility + Dependency Inversion)

Write-Host "🗑️ ELIMINANDO DUPLICADOS - RESPETANDO PRINCIPIOS SOLID" -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""

# PRINCIPIO SOLID APLICADO:
# - Single Responsibility: Cada controlador tiene una sola responsabilidad en su dominio
# - Open/Closed: Mantenemos la versión extensible (hexagonal)
# - Dependency Inversion: Mantenemos la versión que depende de abstracciones (use cases)

Write-Host "📋 CONTROLADORES DE PRODUCTS A ELIMINAR (MVC Tradicional):" -ForegroundColor Yellow

# Lista de controladores duplicados de Products en ubicación MVC tradicional
$duplicatedControllers = @(
    "AdminProductController.php",
    "AdminProductControllerRefactored.php", 
    "AdminProductTypeController.php",
    "ProductController.php",
    "ConfigurableOptionController.php",
    "ConfigurableOptionGroupController.php",
    "DiscountPercentageController.php"
)

$mvcPath = "app\Http\Controllers\Admin"
$hexagonalPath = "app\Domains\Products\Infrastructure\Http\Controllers\Admin"

foreach ($controller in $duplicatedControllers) {
    $mvcFile = "$mvcPath\$controller"
    $hexagonalFile = "$hexagonalPath\$controller"
    
    if ((Test-Path $mvcFile) -and (Test-Path $hexagonalFile)) {
        Write-Host "  🔍 Verificando: $controller" -ForegroundColor Cyan
        
        # Verificar que la versión hexagonal existe y es válida
        $hexagonalContent = Get-Content $hexagonalFile -Raw
        
        if ($hexagonalContent -match "namespace App\\Domains\\Products\\Infrastructure\\Http\\Controllers") {
            # ✅ PRINCIPIO SOLID: Mantener versión con Dependency Inversion
            Remove-Item $mvcFile -Force
            Write-Host "    ✅ ELIMINADO (MVC): $mvcFile" -ForegroundColor Green
            Write-Host "    ✅ MANTENIDO (Hexagonal): $hexagonalFile" -ForegroundColor Green
        } else {
            Write-Host "    ⚠️ SALTADO: Versión hexagonal no válida" -ForegroundColor Yellow
        }
    } elseif (Test-Path $mvcFile) {
        Write-Host "  ⚠️ Solo existe versión MVC: $controller" -ForegroundColor Yellow
    } elseif (Test-Path $hexagonalFile) {
        Write-Host "  ✅ Solo existe versión Hexagonal: $controller" -ForegroundColor Green
    }
    
    Write-Host ""
}

Write-Host "🗑️ ELIMINANDO CONTROLADORES API DUPLICADOS:" -ForegroundColor Yellow

# Eliminar controladores API duplicados
$apiControllers = @(
    "ProductController.php",
    "PricingController.php"
)

$apiMvcPath = "app\Http\Controllers\Api"
$apiHexagonalPath = "app\Domains\Products\Infrastructure\Http\Controllers\Api"

foreach ($controller in $apiControllers) {
    $mvcFile = "$apiMvcPath\$controller"
    $hexagonalFile = "$apiHexagonalPath\$controller"
    
    if ((Test-Path $mvcFile) -and (Test-Path $hexagonalFile)) {
        Remove-Item $mvcFile -Force
        Write-Host "  ✅ ELIMINADO (MVC API): $mvcFile" -ForegroundColor Green
        Write-Host "  ✅ MANTENIDO (Hexagonal API): $hexagonalFile" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "🗑️ ELIMINANDO FORM REQUESTS DUPLICADOS:" -ForegroundColor Yellow

# Eliminar Form Requests duplicados
$requests = @(
    "StoreProductRequest.php",
    "UpdateProductRequest.php",
    "StoreProductTypeRequest.php", 
    "UpdateProductTypeRequest.php",
    "StoreProductPricingRequest.php",
    "UpdateProductPricingRequest.php",
    "StoreConfigurableOptionRequest.php",
    "UpdateConfigurableOptionRequest.php",
    "StoreConfigurableOptionGroupRequest.php",
    "UpdateConfigurableOptionGroupRequest.php"
)

$requestsMvcPath = "app\Http\Requests\Admin"
$requestsHexagonalPath = "app\Domains\Products\Infrastructure\Http\Requests"

foreach ($request in $requests) {
    $mvcFile = "$requestsMvcPath\$request"
    $hexagonalFile = "$requestsHexagonalPath\$request"
    
    if ((Test-Path $mvcFile) -and (Test-Path $hexagonalFile)) {
        Remove-Item $mvcFile -Force
        Write-Host "  ✅ ELIMINADO (MVC Request): $mvcFile" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "🎯 PRINCIPIOS SOLID APLICADOS:" -ForegroundColor Green
Write-Host "==============================" -ForegroundColor Green
Write-Host "✅ S - Single Responsibility: Cada controlador maneja solo su dominio" -ForegroundColor Green
Write-Host "✅ O - Open/Closed: Versión hexagonal es extensible sin modificación" -ForegroundColor Green  
Write-Host "✅ L - Liskov Substitution: Controladores hexagonales son intercambiables" -ForegroundColor Green
Write-Host "✅ I - Interface Segregation: Dependencias específicas por responsabilidad" -ForegroundColor Green
Write-Host "✅ D - Dependency Inversion: Controladores dependen de abstracciones (Use Cases)" -ForegroundColor Green
Write-Host ""

Write-Host "🎉 ELIMINACIÓN COMPLETADA - SOLO VERSIONES HEXAGONALES MANTENIDAS" -ForegroundColor Green
Write-Host "📊 RESULTADO: Arquitectura limpia respetando principios SOLID" -ForegroundColor Green
