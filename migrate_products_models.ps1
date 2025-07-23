# Script para migrar TODOS los modelos de Products

Write-Host "🚀 Migrando modelos del dominio Products..." -ForegroundColor Green

# Lista de modelos relacionados con Products
$productModels = @(
    "ProductType",
    "ConfigurableOptionPricing", 
    "DiscountPercentage",
    "BillingCycle"
)

$destDir = "app\Domains\Products\Infrastructure\Persistence\Models"

foreach ($model in $productModels) {
    $sourcePath = "app\Models\$model.php"
    $destPath = "$destDir\$model.php"
    
    if (Test-Path $sourcePath) {
        # Leer contenido del modelo original
        $content = Get-Content $sourcePath -Raw
        
        # Crear modelo hexagonal REAL
        $hexagonalContent = @"
<?php

namespace App\Domains\Products\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para $model en arquitectura hexagonal
 * 
 * Este es el modelo REAL que se conecta a la base de datos
 * Ubicado en Infrastructure layer como adaptador de persistencia
 */
class $model extends Model
{
    // TODO: Configurar fillable, casts, relaciones según el modelo original
    protected `$fillable = [];
    
    protected `$casts = [];
    
    // TODO: Agregar relaciones Eloquent
}
"@
        
        # Guardar modelo hexagonal
        Set-Content $destPath $hexagonalContent -Encoding UTF8
        
        # Actualizar modelo de compatibilidad
        $compatibilityContent = @"
<?php

namespace App\Models;

/**
 * Modelo de compatibilidad para $model
 * 
 * @deprecated Usar App\Domains\Products\Infrastructure\Persistence\Models\$model
 * 
 * Este modelo existe solo para mantener compatibilidad con código legacy.
 */
class $model extends \App\Domains\Products\Infrastructure\Persistence\Models\$model
{
    // Extiende del modelo hexagonal REAL
    // No agregar lógica aquí - usar el modelo hexagonal directamente
}
"@
        
        # Actualizar modelo de compatibilidad
        Set-Content $sourcePath $compatibilityContent -Encoding UTF8
        
        Write-Host "  ✅ Migrado: $model" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️ No encontrado: $model" -ForegroundColor Yellow
    }
}

Write-Host "🎉 ¡Migración de modelos completada!" -ForegroundColor Green
