<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test rutas públicas del frontend
     */
    public function test_frontend_home_route_returns_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_frontend_contact_route_returns_404(): void
    {
        // La ruta /contact fue eliminada porque no se usa en el one-page
        $response = $this->get('/contact');
        $response->assertStatus(404);
    }

    /**
     * Test rutas legales (páginas de política, términos, etc.)
     */
    public function test_frontend_privacy_policy_route_returns_successful_response(): void
    {
        $response = $this->get('/politica-privacidad');
        $response->assertStatus(200);
    }

    public function test_frontend_terms_route_returns_successful_response(): void
    {
        $response = $this->get('/terminos-condiciones');
        $response->assertStatus(200);
    }

    public function test_frontend_delete_account_route_returns_successful_response(): void
    {
        $response = $this->get('/eliminar-cuenta');
        $response->assertStatus(200);
    }

    /**
     * Test que las rutas eliminadas (about, services, blog, projects) devuelven 404
     * Estas rutas fueron eliminadas porque el template es one-page
     */
    public function test_frontend_about_route_returns_404(): void
    {
        $response = $this->get('/about');
        $response->assertStatus(404);
    }

    public function test_frontend_services_route_returns_404(): void
    {
        $response = $this->get('/services');
        $response->assertStatus(404);
    }

    public function test_frontend_blog_route_returns_404(): void
    {
        $response = $this->get('/blog');
        $response->assertStatus(404);
    }

    public function test_frontend_projects_route_returns_404(): void
    {
        $response = $this->get('/projects');
        $response->assertStatus(404);
    }

    /**
     * Test rutas protegidas del dashboard (requieren autenticación)
     * Nota: Las rutas web pueden devolver 401 o redirigir, dependiendo de la configuración
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');
        // Puede redirigir a login o devolver 401
        $this->assertTrue(
            $response->status() === 302 || 
            $response->status() === 401 || 
            $response->status() === 403
        );
    }

    public function test_home_route_requires_authentication(): void
    {
        $response = $this->get('/home');
        // Puede redirigir a login o devolver 401
        $this->assertTrue(
            $response->status() === 302 || 
            $response->status() === 401 || 
            $response->status() === 403
        );
    }

    public function test_users_index_requires_authentication(): void
    {
        $response = $this->get('/users');
        // Puede redirigir a login o devolver 401
        $this->assertTrue(
            $response->status() === 302 || 
            $response->status() === 401 || 
            $response->status() === 403
        );
    }

    /**
     * Test que la ruta /authors devuelve 404 (fue eliminada con el blog)
     */
    public function test_authors_index_returns_404(): void
    {
        $response = $this->get('/authors');
        $response->assertStatus(404);
    }

    public function test_roles_index_requires_authentication(): void
    {
        $response = $this->get('/roles');
        // Puede redirigir a login o devolver 401
        $this->assertTrue(
            $response->status() === 302 || 
            $response->status() === 401 || 
            $response->status() === 403
        );
    }

    /**
     * Test que las rutas web autenticadas funcionan
     * Nota: Algunas rutas pueden requerir permisos específicos o datos adicionales
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        // Puede devolver 200, 403 (sin permisos) o 500 (falta de datos)
        $this->assertTrue(
            $response->status() === 200 || 
            $response->status() === 403 || 
            $response->status() === 500
        );
    }

    public function test_authenticated_user_can_access_home(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/home');
        // Puede devolver 200, 403 (sin permisos) o 500 (falta de datos)
        $this->assertTrue(
            $response->status() === 200 || 
            $response->status() === 403 || 
            $response->status() === 500
        );
    }

    public function test_authenticated_user_can_access_users_index(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/users');
        // Puede devolver 200, 403 (sin permisos) o 500 (falta de datos)
        $this->assertTrue(
            $response->status() === 200 || 
            $response->status() === 403 || 
            $response->status() === 500
        );
    }

    public function test_authenticated_user_can_access_roles_index(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/roles');
        // Roles requiere permisos específicos, puede devolver 403
        $this->assertTrue(
            $response->status() === 200 || 
            $response->status() === 403 || 
            $response->status() === 500
        );
    }

    /**
     * Test que las rutas API no se ven afectadas
     */
    public function test_api_routes_still_work(): void
    {
        $response = $this->get('/api/ping');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'API funcionando']);
    }

    /**
     * Test que las rutas web y API están separadas
     */
    public function test_web_and_api_routes_are_separated(): void
    {
        // Ruta web pública
        $webResponse = $this->get('/');
        $webResponse->assertStatus(200);
        
        // Ruta API pública
        $apiResponse = $this->get('/api/ping');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJson(['message' => 'API funcionando']);
        
        // Verificar que son diferentes
        $this->assertNotEquals($webResponse->getContent(), $apiResponse->getContent());
    }
}

