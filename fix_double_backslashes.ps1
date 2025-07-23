# Script para corregir dobles barras invertidas en todos los archivos PHP

Write-Host "🔧 Corrigiendo dobles barras invertidas..." -ForegroundColor Green

# Buscar todos los archivos PHP en el dominio Products
$phpFiles = Get-ChildItem -Path "app\Domains\Products" -Filter "*.php" -Recurse

foreach ($file in $phpFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Corregir dobles barras en namespaces
    $content = $content -replace "namespace App\\\\", "namespace App\"
    
    # Corregir dobles barras en use statements
    $content = $content -replace "use App\\\\", "use App\"
    
    # Corregir dobles barras en extends/implements
    $content = $content -replace "extends \\\\App\\\\", "extends \App\"
    
    # Solo guardar si hubo cambios
    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        Write-Host "  ✅ Corregido: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host "🎉 ¡Corrección de dobles barras completada!" -ForegroundColor Green
