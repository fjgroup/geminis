<?php

namespace App\Domains\Users\Events;

use App\Domains\Users\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserCreated
 * 
 * Domain Event que se dispara cuando se crea un usuario
 * Permite reaccionar a la creación de usuarios de forma desacoplada
 * Aplica principios de DDD - Domain Events
 */
class UserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly User $user;
    public readonly array $metadata;
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct(User $user, array $metadata = [])
    {
        $this->user = $user;
        $this->metadata = $metadata;
        $this->occurredAt = new \DateTimeImmutable();
    }

    /**
     * Obtener el usuario creado
     * 
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Obtener metadatos del evento
     * 
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Obtener cuándo ocurrió el evento
     * 
     * @return \DateTimeImmutable
     */
    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Verificar si el usuario es de un tipo específico
     * 
     * @param string $role
     * @return bool
     */
    public function isUserRole(string $role): bool
    {
        return $this->user->role === $role;
    }

    /**
     * Verificar si es un cliente creado por reseller
     * 
     * @return bool
     */
    public function isClientCreatedByReseller(): bool
    {
        return $this->user->role === 'client' && !empty($this->user->reseller_id);
    }

    /**
     * Obtener información del evento para logging
     * 
     * @return array
     */
    public function toLogArray(): array
    {
        return [
            'event' => 'UserCreated',
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'user_role' => $this->user->role,
            'reseller_id' => $this->user->reseller_id,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Convertir a array para serialización
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'name' => $this->user->name,
                'role' => $this->user->role,
                'reseller_id' => $this->user->reseller_id,
            ],
            'metadata' => $this->metadata,
            'occurred_at' => $this->occurredAt->format('c'),
        ];
    }
}
