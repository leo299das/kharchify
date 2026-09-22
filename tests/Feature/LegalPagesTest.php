<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_terms_page_loads_successfully(): void
    {
        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertSee('Terms & Conditions', false);
        $response->assertSee('darakshaanhussain77@gmail.com');
        $response->assertSee('+91 92095 71683');
    }

    public function test_privacy_page_loads_successfully(): void
    {
        $response = $this->get('/privacy');

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
        $response->assertSee('darakshaanhussain77@gmail.com');
    }

    public function test_contact_page_loads_successfully(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('Contact Us');
        $response->assertSee('darakshaanhussain77@gmail.com');
        $response->assertSee('+91 92095 71683');
    }

    public function test_contact_form_submits_successfully(): void
    {
        $response = $this->post('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Plan Upgrade Support',
            'message' => 'I would like to activate the Pro Plan.',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();
    }

    public function test_contact_form_validation_fails_with_missing_fields(): void
    {
        $response = $this->post('/contact', [
            'name' => '',
            'email' => 'invalid-email',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
    }
}
