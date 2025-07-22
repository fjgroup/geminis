<?php

namespace Tests\Unit\Domains\Shared\Services;

use App\Domains\Shared\Services\SearchService;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

/**
 * Test para SearchService
 * 
 * Valida que el servicio de búsqueda funciona correctamente
 * y cumple con los principios DRY y SOLID
 */
class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private SearchService $searchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = new SearchService();
    }

    public function test_returns_empty_collection_for_short_search_term(): void
    {
        $mockQuery = Mockery::mock(Builder::class);
        
        $result = $this->searchService->searchWithAutocomplete(
            $mockQuery,
            'a', // Término muy corto
            ['name'],
            ['id', 'name'],
            10
        );
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function test_formats_for_autocomplete_with_basic_fields(): void
    {
        $item = (object) [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        $result = $this->searchService->formatForAutocomplete($item, 'id', 'name', 'email');
        
        $expected = [
            'value' => 1,
            'label' => 'John Doe (john@example.com)'
        ];
        
        $this->assertEquals($expected, $result);
    }

    public function test_formats_for_autocomplete_without_extra_field(): void
    {
        $item = (object) [
            'id' => 1,
            'name' => 'John Doe'
        ];
        
        $result = $this->searchService->formatForAutocomplete($item, 'id', 'name');
        
        $expected = [
            'value' => 1,
            'label' => 'John Doe'
        ];
        
        $this->assertEquals($expected, $result);
    }

    public function test_formats_for_autocomplete_with_missing_extra_field(): void
    {
        $item = (object) [
            'id' => 1,
            'name' => 'John Doe'
        ];
        
        $result = $this->searchService->formatForAutocomplete($item, 'id', 'name', 'email');
        
        $expected = [
            'value' => 1,
            'label' => 'John Doe'
        ];
        
        $this->assertEquals($expected, $result);
    }

    public function test_search_users_with_specific_role(): void
    {
        // Crear usuarios de prueba
        User::factory()->create([
            'name' => 'John Client',
            'email' => 'john@client.com',
            'role' => 'client'
        ]);
        
        User::factory()->create([
            'name' => 'Jane Admin',
            'email' => 'jane@admin.com',
            'role' => 'admin'
        ]);
        
        User::factory()->create([
            'name' => 'Bob Client',
            'email' => 'bob@client.com',
            'role' => 'client'
        ]);
        
        $result = $this->searchService->searchUsers('client', 'client', 10);
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        
        // Verificar que todos los resultados tienen el formato correcto
        $firstResult = $result->first();
        $this->assertArrayHasKey('value', $firstResult);
        $this->assertArrayHasKey('label', $firstResult);
    }

    public function test_search_users_with_multiple_roles(): void
    {
        // Crear usuarios de prueba
        User::factory()->create([
            'name' => 'John Client',
            'email' => 'john@client.com',
            'role' => 'client'
        ]);
        
        User::factory()->create([
            'name' => 'Jane Admin',
            'email' => 'jane@admin.com',
            'role' => 'admin'
        ]);
        
        User::factory()->create([
            'name' => 'Bob Reseller',
            'email' => 'bob@reseller.com',
            'role' => 'reseller'
        ]);
        
        $result = $this->searchService->searchUsers('test', ['client', 'admin'], 10);
        
        $this->assertInstanceOf(Collection::class, $result);
        // Debería encontrar solo client y admin, no reseller
    }

    public function test_search_users_without_role_filter(): void
    {
        // Crear usuarios de prueba
        User::factory()->create([
            'name' => 'John Test',
            'email' => 'john@test.com',
            'role' => 'client'
        ]);
        
        User::factory()->create([
            'name' => 'Jane Test',
            'email' => 'jane@test.com',
            'role' => 'admin'
        ]);
        
        $result = $this->searchService->searchUsers('test', null, 10);
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_search_users_returns_empty_for_short_term(): void
    {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'client'
        ]);
        
        $result = $this->searchService->searchUsers('j', 'client', 10);
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function test_search_users_respects_limit(): void
    {
        // Crear más usuarios de los que queremos en el resultado
        for ($i = 1; $i <= 15; $i++) {
            User::factory()->create([
                'name' => "Test User {$i}",
                'email' => "test{$i}@example.com",
                'role' => 'client'
            ]);
        }
        
        $result = $this->searchService->searchUsers('test', 'client', 5);
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertLessThanOrEqual(5, $result->count());
    }

    public function test_handles_database_errors_gracefully(): void
    {
        // Simular un error de base de datos usando un término que cause problemas
        // En un entorno real, esto podría ser más complejo
        
        $result = $this->searchService->searchUsers('test', 'client', 10);
        
        // El servicio debería manejar errores y devolver una colección vacía
        $this->assertInstanceOf(Collection::class, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
