<?php

namespace Tests\Feature;

use App\Support\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ConsentAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_waitlist_legal_and_marketing_consents_are_versioned_and_marketing_can_be_revoked(): void
    {
        Mail::fake();

        $this->beginPhoneRegistration();

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Consent test browser',
        ])->post(route('waitlist.profile'), [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'birth_date' => '1990-01-01',
            'gender' => 'FEMALE',
            'marketing_consent' => '1',
        ])->assertSessionHas('waitlist_offer', true);

        $entryId = DB::table('waitlist_entries')->where('email', 'ada@example.com')->value('id');

        $legal = DB::table('consent_events')
            ->where('waitlist_entry_id', $entryId)
            ->where('consent_type', 'waitlist_legal')
            ->first();
        $marketing = DB::table('consent_events')
            ->where('waitlist_entry_id', $entryId)
            ->where('consent_type', 'marketing')
            ->where('action', 'granted')
            ->first();

        $this->assertNotNull($legal);
        $this->assertSame('granted', $legal->action);
        $this->assertSame('waitlist_profile', $legal->source);
        $this->assertSame('203.0.113.10', $legal->ip_address);
        $this->assertSame('Consent test browser', $legal->user_agent);
        $this->assertSame(config('legal.documents.privacy.initial_version'), json_decode($legal->document_versions, true)['privacy']);
        $this->assertSame(config('legal.documents.waitlist_acceptance.initial_version'), json_decode($legal->document_versions, true)['waitlist_acceptance']);
        $this->assertSame(64, strlen(json_decode($legal->document_hashes, true)['privacy']));
        $this->assertNotNull($legal->occurred_at);
        $this->assertNotNull($marketing);
        $this->assertDatabaseHas('legal_document_versions', [
            'document_key' => 'privacy',
            'version' => config('legal.documents.privacy.initial_version'),
            'locale' => 'it',
            'content_hash' => json_decode($legal->document_hashes, true)['privacy'],
            'content_format' => 'html',
        ]);

        $url = URL::signedRoute('consent.marketing.revoke.show', ['waitlist' => $entryId]);
        $this->get($url)->assertOk();
        $this->post($url)->assertOk()->assertSee('Consenso marketing revocato');

        $this->assertDatabaseHas('waitlist_entries', [
            'id' => $entryId,
            'marketing_consent' => false,
        ]);
        $this->assertDatabaseHas('consent_events', [
            'waitlist_entry_id' => $entryId,
            'consent_type' => 'marketing',
            'action' => 'revoked',
            'source' => 'signed_marketing_revocation',
            'revokes_event_id' => $marketing->id,
        ]);
        $this->assertSame(2, DB::table('consent_events')
            ->where('waitlist_entry_id', $entryId)
            ->where('consent_type', 'marketing')
            ->count());

        $this->post($url)->assertOk()->assertSee('già revocato o non concesso');
        $this->assertSame(2, DB::table('consent_events')
            ->where('waitlist_entry_id', $entryId)
            ->where('consent_type', 'marketing')
            ->count());
    }

    public function test_contact_acceptance_references_the_saved_message(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'subject' => 'information',
            'message' => 'Vorrei informazioni.',
            'privacy_accepted' => '1',
        ])->assertSessionHas('contact_success', true);

        $messageId = DB::table('contact_messages')->where('email', 'mario@example.com')->value('id');

        $this->assertDatabaseHas('consent_events', [
            'contact_message_id' => $messageId,
            'subject_email' => 'mario@example.com',
            'consent_type' => 'contact_legal',
            'action' => 'granted',
            'source' => 'contact_form',
        ]);

        $contactEvent = DB::table('consent_events')->where('contact_message_id', $messageId)->first();
        $this->assertArrayHasKey('contact_acceptance', json_decode($contactEvent->document_versions, true));
    }

    public function test_unsigned_marketing_revocation_is_rejected(): void
    {
        $this->post('/preferenze/marketing/999')->assertForbidden();
    }

    public function test_consent_audit_uses_the_current_database_document_version(): void
    {
        Mail::fake();

        app(LegalDocumentService::class)->publish(
            'privacy',
            'it',
            '2026-07-15.1',
            'Privacy policy',
            'Versione aggiornata.',
            '<div><h2>Privacy aggiornata</h2><p>Testo corrente dal database.</p></div>',
        );

        $this->beginPhoneRegistration();
        $this->post(route('waitlist.profile'), [
            'email' => 'versioned@example.com',
            'first_name' => 'Versioned',
            'last_name' => 'Member',
            'birth_date' => '1990-01-01',
            'gender' => 'MALE',
        ]);

        $event = DB::table('consent_events')
            ->where('subject_email', 'versioned@example.com')
            ->where('consent_type', 'waitlist_legal')
            ->first();

        $this->assertNotNull($event);
        $this->assertSame('2026-07-15.1', json_decode($event->document_versions, true)['privacy']);
        $this->assertSame(
            DB::table('legal_document_versions')->where('version', '2026-07-15.1')->value('content_hash'),
            json_decode($event->document_hashes, true)['privacy'],
        );
    }
}
