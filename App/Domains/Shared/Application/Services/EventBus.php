<?php

namespace App\Domains\Shared\Application\Services;

/**
 * Event Bus para publicar eventos de dominio
 * 
 * Implementación simple para arquitectura hexagonal
 */
class EventBus
{
    private array $listeners = [];

    public function publish(object $event): void
    {
        $eventClass = get_class($event);
        
        if (isset($this->listeners[$eventClass])) {
            foreach ($this->listeners[$eventClass] as $listener) {
                $listener($event);
            }
        }

        // Log del evento para debugging
        \Log::info('Domain Event Published', [
            'event' => $eventClass,
            'data' => method_exists($event, 'toArray') ? $event->toArray() : []
        ]);
    }

    public function subscribe(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }
}
