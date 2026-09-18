<?php

namespace Tests\Feature;

use App\Support\LegalDocumentService;
use App\Support\WaitlistCreatorPolicyUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WaitlistCreatorPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_updates_existing_documents_without_changing_other_terms_or_history(): void
    {
        $documents = app(LegalDocumentService::class);
        $previous = [];
        $expected = [];

        foreach (['terms', 'passes'] as $key) {
            foreach (['it', 'en'] as $locale) {
                $current = $documents->current($key, $locale);
                $content = $current->content_snapshot;
                foreach (WaitlistCreatorPolicyUpdate::replacements($key, $locale) as [$old, $new]) {
                    $content = str_replace($new, $old, $content);
                }
                $expected[$key.'.'.$locale] = $current->content_snapshot.'<p>Custom published note.</p>';
                $previous[] = $documents->publish($key, $locale, 'before-creator', $current->title, $current->description, $content.'<p>Custom published note.</p>');
            }
        }

        $policies = DB::table('legal_documents')->whereIn('document_key', ['privacy', 'cookies'])->pluck('current_version_id', 'id')->all();
        $migration = require database_path('migrations/2026_09_18_000003_publish_waitlist_creator_features.php');
        $migration->up();
        $count = DB::table('legal_document_versions')->count();
        $migration->up();
        $this->assertSame($count, DB::table('legal_document_versions')->count());
        $this->assertSame($policies, DB::table('legal_documents')->whereIn('document_key', ['privacy', 'cookies'])->pluck('current_version_id', 'id')->all());

        foreach ($previous as $old) {
            $current = $documents->current($old->document_key, $old->locale);
            $this->assertSame($expected[$old->document_key.'.'.$old->locale], $current->content_snapshot);
            $this->assertSame($old->title, $current->title);
            $this->assertDatabaseHas('legal_document_versions', [
                'id' => $old->version_id,
                'content_snapshot' => $old->content_snapshot,
                'content_hash' => $old->content_hash,
            ]);
        }
    }

    public function test_public_pages_distinguish_waitlist_creator_access_from_founder_join(): void
    {
        foreach (['it', 'en'] as $locale) {
            $this->get('/come-funzionano-i-pass?lang='.$locale)->assertOk()
                ->assertSee($locale === 'it' ? 'Sì con il Waitlist Pass; no con Founder Join.' : 'Yes with the Waitlist Pass; no with Founder Join.')
                ->assertSee($locale === 'it' ? 'Massimo 500 pass' : 'Maximum 500 passes')
                ->assertSee($locale === 'it' ? 'Massimo 150 pass' : 'Maximum 150 passes');

            $terms = app(LegalDocumentService::class)->current('terms', $locale)->content_snapshot;
            $this->assertStringContainsString($locale === 'it' ? 'le funzionalità Join e le funzionalità Creator' : 'the Join and Creator features', $terms);
            $this->assertStringContainsString($locale === 'it' ? 'entro 30 giorni di calendario' : 'within 30 calendar days', $terms);
        }
    }

    public function test_partial_dashboard_edits_do_not_cause_founder_join_to_be_changed(): void
    {
        $current = app(LegalDocumentService::class)->current('passes', 'it')->content_snapshot;
        $content = $current;
        foreach (WaitlistCreatorPolicyUpdate::replacements('passes', 'it') as [$old, $new]) {
            $content = str_replace($new, $old, $content);
        }
        $content = preg_replace('/Funzionalità Join · Richieste di partecipazione · Chat dopo l’accettazione/u', 'Funzionalità Join · Funzionalità Creator · Chat dopo l’accettazione', $content, 1);
        $content = preg_replace('/Creazione e gestione dei tavoli · Funzionalità Creator/u', 'Diritto illimitato a creare tavoli · Servizi, prenotazioni o ingressi dei locali', $content, 1);
        $content = str_replace("\n", "\r\n", $content);

        $updated = WaitlistCreatorPolicyUpdate::apply($content, 'passes', 'it');
        $this->assertSame($current, str_replace("\r\n", "\n", $updated));
        $this->assertSame($updated, WaitlistCreatorPolicyUpdate::apply($updated, 'passes', 'it'));
    }
}
