<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BackendFaqTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '*/faqs/grouped?type=SITE';

    public function test_page_uses_public_site_faqs_and_preserves_backend_group_and_question_order(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['success' => true, 'data' => [
            ['group' => ['id' => 'group-b', 'name' => 'Secondo gruppo in ordine alfabetico'], 'faqs' => [
                ['id' => 'question-b', 'question' => 'Domanda B dal backend?', 'answer' => "Risposta pubblicata.\nSeconda riga."],
                ['id' => 'question-a', 'question' => 'Domanda A dal backend?', 'answer' => 'Altra risposta.'],
            ]],
            ['group' => ['id' => 'group-a', 'name' => 'Altro gruppo'], 'faqs' => [
                ['id' => 'question-c', 'question' => 'Domanda C dal backend?', 'answer' => 'Risposta C.'],
            ]],
            ['group' => null, 'faqs' => [['id' => 'question-free', 'question' => 'Domanda senza gruppo?', 'answer' => 'Risposta senza gruppo.']]],
        ]])]);
        $this->get('/faq')->assertOk()
            ->assertSeeInOrder(['Domanda B dal backend?', 'Domanda A dal backend?', 'Domanda C dal backend?', 'Domanda senza gruppo?'])
            ->assertSee('Risposta pubblicata.')->assertSee('Seconda riga.')
            ->assertSee('id="faq-group-group-b"', false)->assertSee('id="faq-ungrouped"', false)
            ->assertDontSee('faq-ungrouped-heading')->assertDontSee('Listino di riferimento')
            ->assertDontSee('Contenuti del documento aggiornato');
        Http::assertSent(fn ($request) => $request->url() === 'https://staging-app.wayoutapp.test/api/v1/faqs/grouped?type=SITE'
            && $request->hasHeader('Accept-Language', 'it') && ! $request->hasHeader('Authorization')
            && ! $request->hasHeader('X-Wayout-Signature'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'type=APP'));
    }

    public function test_empty_groups_are_omitted_and_empty_publication_is_handled(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['success' => true, 'data' => [
            ['group' => ['id' => 'empty', 'name' => 'Gruppo vuoto'], 'faqs' => []],
        ]])]);
        $this->get('/faq')->assertOk()->assertSee('Nessuna FAQ pubblicata al momento.')->assertDontSee('Gruppo vuoto');
    }

    public function test_backend_unavailability_shows_a_retry_message_instead_of_local_faqs(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['message' => 'Unavailable'], 503)]);
        $this->get('/faq')->assertStatus(503)->assertSee('Le FAQ non sono al momento disponibili.')
            ->assertSee('Riprova')->assertDontSee('Listino di riferimento');
    }

    public function test_invalid_backend_content_is_not_treated_as_an_empty_publication(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['success' => true, 'data' => [
            ['group' => null, 'faqs' => [['id' => 'bad', 'question' => 'Malformed FAQ']]],
        ]])]);
        $this->get('/faq')->assertStatus(503)->assertSee('Le FAQ non sono al momento disponibili.')
            ->assertDontSee('Nessuna FAQ pubblicata');
    }

    public function test_backend_strings_are_escaped_in_questions_answers_and_group_names(): void
    {
        Http::fake([self::ENDPOINT => Http::response(['success' => true, 'data' => [
            ['group' => ['id' => 'safe', 'name' => '<script>group()</script>'], 'faqs' => [
                ['id' => 'safe', 'question' => '<script>question()</script>', 'answer' => '<img src=x onerror=alert(1)>'],
            ]],
        ]])]);
        $this->get('/faq')->assertOk()->assertSee('&lt;script&gt;question()', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<script>group()', false)->assertDontSee('<img src=x', false);
    }
}
