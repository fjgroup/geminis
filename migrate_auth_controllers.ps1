# Script para migrar controladores de Auth a la arquitectura hexagonal
# Ejecutar desde la raíz del proyecto

$sourceDir = "app\Http\Controllers\Auth"
$targetDir = "app\Domains\Users\Infrastructure\Http\Controllers\Auth"

# Crear directorio de destino si no existe
if (!(Test-Path $targetDir)) {
    New-Item -ItemType Directory -Path $targetDir -Force
}

# Lista de controladores a migrar
$controllers = @(
    "ConfirmablePasswordController.php",
    "EmailVerificationNotificationController.php", 
    "EmailVerificationPromptController.php",
    "NewPasswordController.php",
    "VerifyEmailController.php"
)

foreach ($controller in $controllers) {
    $sourcePath = Join-Path $sourceDir $controller
    $targetPath = Join-Path $targetDir $controller
    
    if (Test-Path $sourcePath) {
        Write-Host "Migrando $controller..."
        
        # Leer contenido del archivo
        $content = Get-Content $sourcePath -Raw
        
        # Actualizar namespace
        $content = $content -replace "namespace App\\Http\\Controllers\\Auth;", "namespace App\Domains\Users\Infrastructure\Http\Controllers\Auth;"
        
        # Agregar comentario de arquitectura hexagonal
        $content = $content -replace "(class \w+Controller extends Controller)", "/**`n * Controlador migrado a arquitectura hexagonal`n * Ubicado en Infrastructure layer como adaptador de entrada HTTP`n */`n`$1"
        
        # Escribir archivo migrado
        Set-Content -Path $targetPath -Value $content -Encoding UTF8
        
        Write-Host "✓ $controller migrado exitosamente"
    } else {
        Write-Host "⚠ $controller no encontrado en $sourcePath"
    }
}

Write-Host "`n✅ Migración de controladores Auth completada"
Write-Host "📁 Controladores migrados a: $targetDir"
Write-Host "`n⚠ Recuerda actualizar las rutas para usar los nuevos namespaces"
