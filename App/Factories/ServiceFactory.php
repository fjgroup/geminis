<?php

namespace App\Factories;

use App\Contracts\User\UserFormattingServiceInterface;
use App\Contracts\User\UserRoleServiceInterface;
use App\Contracts\User\UserDeletionServiceInterface;
use App\Contracts\Invoice\InvoiceNumberServiceInterface;
use App\Contracts\Invoice\InvoiceValidationServiceInterface;
use App\Contracts\ClientService\ClientServiceBusinessServiceInterface;
use InvalidArgumentException;

/**
 * Class ServiceFactory
 * 
 * Factory para crear servicios de manera centralizada
 * Implementa Factory Pattern y cumple con Open/Closed Principle
 */
class ServiceFactory
{
    /**
     * Mapa de servicios disponibles
     */
    private const SERVICE_MAP = [
        'user.formatting' => UserFormattingServiceInterface::class,
        'user.role' => UserRoleServiceInterface::class,
        'user.deletion' => UserDeletionServiceInterface::class,
        'invoice.number' => InvoiceNumberServiceInterface::class,
        'invoice.validation' => InvoiceValidationServiceInterface::class,
        'client-service.business' => ClientServiceBusinessServiceInterface::class,
    ];

    /**
     * Crear un servicio por su nombre
     *
     * @param string $serviceName
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function create(string $serviceName)
    {
        if (!isset(self::SERVICE_MAP[$serviceName])) {
            throw new InvalidArgumentException("Servicio '{$serviceName}' no encontrado");
        }

        $serviceClass = self::SERVICE_MAP[$serviceName];
        return app($serviceClass);
    }

    /**
     * Crear múltiples servicios
     *
     * @param array $serviceNames
     * @return array
     */
    public static function createMultiple(array $serviceNames): array
    {
        $services = [];
        
        foreach ($serviceNames as $serviceName) {
            $services[$serviceName] = self::create($serviceName);
        }

        return $services;
    }

    /**
     * Verificar si un servicio existe
     *
     * @param string $serviceName
     * @return bool
     */
    public static function exists(string $serviceName): bool
    {
        return isset(self::SERVICE_MAP[$serviceName]);
    }

    /**
     * Obtener todos los servicios disponibles
     *
     * @return array
     */
    public static function getAvailableServices(): array
    {
        return array_keys(self::SERVICE_MAP);
    }

    /**
     * Registrar un nuevo servicio (extensibilidad)
     *
     * @param string $serviceName
     * @param string $serviceClass
     * @return void
     */
    public static function register(string $serviceName, string $serviceClass): void
    {
        if (!interface_exists($serviceClass) && !class_exists($serviceClass)) {
            throw new InvalidArgumentException("Clase o interfaz '{$serviceClass}' no existe");
        }

        self::SERVICE_MAP[$serviceName] = $serviceClass;
    }

    /**
     * Crear servicios para un dominio específico
     *
     * @param string $domain
     * @return array
     */
    public static function createForDomain(string $domain): array
    {
        $domainServices = array_filter(
            self::SERVICE_MAP,
            fn($key) => str_starts_with($key, $domain . '.'),
            ARRAY_FILTER_USE_KEY
        );

        $services = [];
        foreach ($domainServices as $serviceName => $serviceClass) {
            $services[str_replace($domain . '.', '', $serviceName)] = app($serviceClass);
        }

        return $services;
    }
}
