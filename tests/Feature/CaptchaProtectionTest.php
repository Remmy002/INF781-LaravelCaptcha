<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaptchaProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Verificar que el login falle de forma controlada si no se envía el parámetro del captcha.
     */
    public function test_login_falla_sin_captcha(): void
    {
        $user = User::factory()->create([
            'email' => 'usuario@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'usuario@example.com',
            'password' => 'password123',
            // Se omite intencionalmente el campo 'captcha'
        ]);

        // Verifica que el sistema retorne errores de validación específicos para el captcha
        $response->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    /**
     * Test 2: Verificar que el mecanismo Honeypot intercepte los bots y simule un envío exitoso falsificado.
     */
    public function test_contacto_honeypot_descarta_envio_silenciosamente(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Spam Bot',
            'email' => 'bot@spam.com',
            'message' => 'Este es un mensaje masivo automatizado.',
            'website' => 'http://spam.com', // El campo honeypot va lleno, simulando un Bot
        ]);

        // Defensa por engaño: Asegura que el bot reciba un mensaje de éxito aparente
        $response->assertSessionHas('status', 'Tu mensaje fue enviado correctamente.');
        
        // Verifica que la base de datos se mantenga limpia y libre de spam
        $this->assertDatabaseCount('contacts', 0);
    }

    /**
     * Test 3: Verificar el flujo feliz del formulario de contacto enviando un CAPTCHA correcto simulado.
     */
    public function test_contacto_exitoso_con_captcha_valido(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Estudiante UATF',
            'email' => 'estudiante@uatf.edu.bo',
            'message' => 'Consulta sobre el laboratorio de seguridad.',
            'website' => '', // Honeypot vacío
            'captcha' => 'cualquier_codigo', // Pasará directo por la bandera de entorno de pruebas
        ]);

        // Comprobamos la respuesta de éxito auténtica
        $response->assertSessionHas('status', 'Tu mensaje fue enviado correctamente.');
        
        // Validamos que el registro se guardara correctamente en la persistencia
        $this->assertDatabaseHas('contacts', [
            'email' => 'estudiante@uatf.edu.bo',
            'name' => 'Estudiante UATF'
        ]);
    }
}