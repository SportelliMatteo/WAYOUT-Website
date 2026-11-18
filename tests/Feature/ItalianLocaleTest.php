<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItalianLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_links_and_saved_preferences_display_italian_without_language_selectors(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get('/contatti?lang=en')
            ->assertOk()
            ->assertSee('<html lang="it">', false)
            ->assertDontSee('lang=en', false)
            ->assertDontSee('>EN</a>', false)
            ->assertDontSee('>IT</a>', false)
            ->assertSessionHas('locale', 'it');

        $this->get('/contatti')->assertOk()->assertSee('<html lang="it">', false);
        $this->assertSame('it', app()->getLocale());
        $this->assertSame('it', config('app.fallback_locale'));
    }
}
