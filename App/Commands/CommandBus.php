<?php

namespace App\Commands;

use Illuminate\Support\Facades\Log;

/**
 * Class CommandBus
 * 
 * Bus de comandos para ejecutar comandos de manera centralizada
 * Implementa Command Pattern con logging y validación
 */
class CommandBus
{
    /**
     * Ejecutar un comando
     *
     * @param CommandInterface $command
     * @return array
     */
    public function execute(CommandInterface $command): array
    {
        $startTime = microtime(true);
        
        Log::info('Iniciando ejecución de comando', [
            'command' => $command->getDescription(),
            'timestamp' => now()->toISOString()
        ]);

        try {
            // Ejecutar comando
            $result = $command->execute();
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Comando ejecutado', [
                'command' => $command->getDescription(),
                'success' => $result['success'] ?? false,
                'execution_time_ms' => $executionTime
            ]);

            return array_merge($result, [
                'execution_time_ms' => $executionTime
            ]);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Error en ejecución de comando', [
                'command' => $command->getDescription(),
                'error' => $e->getMessage(),
                'execution_time_ms' => $executionTime
            ]);

            return [
                'success' => false,
                'message' => 'Error ejecutando comando',
                'error' => $e->getMessage(),
                'command' => $command->getDescription(),
                'execution_time_ms' => $executionTime
            ];
        }
    }

    /**
     * Ejecutar múltiples comandos en secuencia
     *
     * @param array $commands
     * @param bool $stopOnFailure
     * @return array
     */
    public function executeMultiple(array $commands, bool $stopOnFailure = true): array
    {
        $results = [];
        $allSuccessful = true;

        foreach ($commands as $index => $command) {
            if (!$command instanceof CommandInterface) {
                $results[$index] = [
                    'success' => false,
                    'message' => 'Comando inválido en posición ' . $index
                ];
                $allSuccessful = false;
                
                if ($stopOnFailure) {
                    break;
                }
                continue;
            }

            $result = $this->execute($command);
            $results[$index] = $result;

            if (!($result['success'] ?? false)) {
                $allSuccessful = false;
                
                if ($stopOnFailure) {
                    break;
                }
            }
        }

        return [
            'success' => $allSuccessful,
            'results' => $results,
            'total_commands' => count($commands),
            'executed_commands' => count($results)
        ];
    }

    /**
     * Validar un comando sin ejecutarlo
     *
     * @param CommandInterface $command
     * @return array
     */
    public function validate(CommandInterface $command): array
    {
        try {
            $validation = $command->validate();
            
            Log::debug('Comando validado', [
                'command' => $command->getDescription(),
                'can_execute' => $validation['can_execute'] ?? false
            ]);

            return $validation;

        } catch (\Exception $e) {
            Log::error('Error validando comando', [
                'command' => $command->getDescription(),
                'error' => $e->getMessage()
            ]);

            return [
                'can_execute' => false,
                'reason' => 'Error en validación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validar múltiples comandos
     *
     * @param array $commands
     * @return array
     */
    public function validateMultiple(array $commands): array
    {
        $validations = [];
        $allValid = true;

        foreach ($commands as $index => $command) {
            if (!$command instanceof CommandInterface) {
                $validations[$index] = [
                    'can_execute' => false,
                    'reason' => 'Comando inválido en posición ' . $index
                ];
                $allValid = false;
                continue;
            }

            $validation = $this->validate($command);
            $validations[$index] = $validation;

            if (!($validation['can_execute'] ?? false)) {
                $allValid = false;
            }
        }

        return [
            'all_valid' => $allValid,
            'validations' => $validations,
            'total_commands' => count($commands)
        ];
    }
}
