<?php

namespace App\Patterns\Observer;

use Illuminate\Support\Facades\Log;

/**
 * Class EventDispatcher
 * 
 * Implementa el patrón Observer para manejar eventos del sistema
 * Permite desacoplar componentes mediante eventos
 */
class EventDispatcher
{
    private array $listeners = [];

    /**
     * Registrar un listener para un evento
     *
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return void
     */
    public function listen(string $eventName, callable $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = [
            'listener' => $listener,
            'priority' => $priority
        ];

        // Ordenar por prioridad (mayor prioridad primero)
        usort($this->listeners[$eventName], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Disparar un evento
     *
     * @param string $eventName
     * @param array $data
     * @return array
     */
    public function dispatch(string $eventName, array $data = []): array
    {
        $results = [];

        if (!isset($this->listeners[$eventName])) {
            Log::debug("No hay listeners para el evento: {$eventName}");
            return $results;
        }

        Log::info("Disparando evento: {$eventName}", [
            'listeners_count' => count($this->listeners[$eventName]),
            'data' => $data
        ]);

        foreach ($this->listeners[$eventName] as $listenerData) {
            try {
                $listener = $listenerData['listener'];
                $result = $listener($data);
                $results[] = $result;

                Log::debug("Listener ejecutado exitosamente", [
                    'event' => $eventName,
                    'priority' => $listenerData['priority']
                ]);

            } catch (\Exception $e) {
                Log::error("Error ejecutando listener para evento: {$eventName}", [
                    'error' => $e->getMessage(),
                    'priority' => $listenerData['priority']
                ]);

                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Remover listeners de un evento
     *
     * @param string $eventName
     * @return void
     */
    public function forget(string $eventName): void
    {
        unset($this->listeners[$eventName]);
        Log::debug("Listeners removidos para evento: {$eventName}");
    }

    /**
     * Remover todos los listeners
     *
     * @return void
     */
    public function forgetAll(): void
    {
        $this->listeners = [];
        Log::debug("Todos los listeners han sido removidos");
    }

    /**
     * Obtener listeners de un evento
     *
     * @param string $eventName
     * @return array
     */
    public function getListeners(string $eventName): array
    {
        return $this->listeners[$eventName] ?? [];
    }

    /**
     * Verificar si un evento tiene listeners
     *
     * @param string $eventName
     * @return bool
     */
    public function hasListeners(string $eventName): bool
    {
        return isset($this->listeners[$eventName]) && count($this->listeners[$eventName]) > 0;
    }

    /**
     * Obtener todos los eventos registrados
     *
     * @return array
     */
    public function getEvents(): array
    {
        return array_keys($this->listeners);
    }

    /**
     * Obtener estadísticas del dispatcher
     *
     * @return array
     */
    public function getStats(): array
    {
        $stats = [
            'total_events' => count($this->listeners),
            'total_listeners' => 0,
            'events' => []
        ];

        foreach ($this->listeners as $eventName => $listeners) {
            $listenerCount = count($listeners);
            $stats['total_listeners'] += $listenerCount;
            $stats['events'][$eventName] = $listenerCount;
        }

        return $stats;
    }
}
