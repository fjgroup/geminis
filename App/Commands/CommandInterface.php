<?php

namespace App\Commands;

/**
 * Interface CommandInterface
 * 
 * Contrato base para el patrón Command
 * Permite encapsular operaciones complejas como objetos
 */
interface CommandInterface
{
    /**
     * Ejecutar el comando
     *
     * @return array
     */
    public function execute(): array;

    /**
     * Validar si el comando puede ser ejecutado
     *
     * @return array
     */
    public function validate(): array;

    /**
     * Obtener descripción del comando
     *
     * @return string
     */
    public function getDescription(): string;
}
