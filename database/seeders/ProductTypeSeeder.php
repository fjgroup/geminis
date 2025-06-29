<?php
namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Seeder;

// Import the ProductType model

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductType::updateOrCreate(
            ['slug' => 'web-hosting'],
            [
                'name'                          => 'Hosting Web',
                'status'                        => 'active',
                'requires_domain'               => true,
                'creates_service_instance'      => true,
                'is_publicly_available'         => true,
                'supports_configurable_options' => true,
                'supports_billing_cycles'       => true,
                'supports_discounts'            => true,
                'description'                   => 'Servicios de alojamiento web que requieren un nombre de dominio y crean una instancia de servicio.',
                'display_order'                 => 1,
            ]
        );

        ProductType::updateOrCreate(
            ['slug' => 'vps-hosting'], // Added VPS as it's common and fits criteria
            [
                'name'                     => 'Servidor VPS',
                'requires_domain'          => true, // Often a primary domain/hostname is set, but not strictly like shared hosting
                'creates_service_instance' => true,
                'description'              => 'Servidores Privados Virtuales que crean una instancia de servicio.',
            ]
        );

        ProductType::updateOrCreate(
            ['slug' => 'domain-registration'], // Added Domain Registration
            [
                'name'                          => 'Domain',
                'status'                        => 'active',
                'requires_domain'               => false, // El servicio ES el dominio
                'creates_service_instance'      => true,  // Represents the registered domain as a service
                'is_publicly_available'         => true,
                'supports_configurable_options' => false,
                'supports_billing_cycles'       => true,
                'supports_discounts'            => false, // Los dominios no tienen descuentos
                'description'                   => 'Registro y gestión de nombres de dominio.',
                'display_order'                 => 2,
            ]
        );

        ProductType::updateOrCreate(
            ['slug' => 'ssl-certificate'], // Added SSL Certificate
            [
                'name'                     => 'Certificado SSL',
                'requires_domain'          => true, // SSL is issued for a specific domain
                'creates_service_instance' => true, // Represents the SSL certificate service
                'description'              => 'Certificados SSL para asegurar sitios web.',
            ]
        );

        ProductType::updateOrCreate(
            ['slug' => 'general-service'],
            [
                'name'                     => 'Servicio General',
                'requires_domain'          => false,
                'creates_service_instance' => false, // Example: Consulting, Support Ticket Pack
                'description'              => 'Servicios generales que no requieren un dominio y usualmente no crean una instancia de servicio persistente.',
            ]
        );

        ProductType::updateOrCreate(
            ['slug' => 'software-license'],
            [
                'name'                     => 'Licencia de Software',
                'requires_domain'          => true,
                'creates_service_instance' => false, // Typically, a license key is provided, not a 'service instance'
                'description'              => 'Licencias de software, usualmente un pago único o periódico para el uso de una aplicación.',
            ]
        );

        ProductType::updateOrCreate(
            ['slug' => 'managed-service'], // Added Managed Service
            [
                'name'                     => 'Servicio Gestionado',
                'requires_domain'          => true, // Could be for a server, application, etc.
                'creates_service_instance' => true, // Represents the ongoing management service
                'description'              => 'Servicios de gestión y soporte para infraestructura o aplicaciones.',
            ]
        );
    }
}
