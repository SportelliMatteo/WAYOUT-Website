<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConsentAuditService
{
    public function __construct(private readonly LegalDocumentService $legalDocuments)
    {
    }

    /**
     * @param array{waitlist_entry_id?: int|null, purchase_id?: int|null, contact_message_id?: int|null} $references
     * @param array<string, mixed> $metadata
     * @param list<string> $documents
     */
    public function record(
        Request $request,
        string $email,
        string $type,
        string $action,
        string $source,
        array $documents,
        array $references = [],
        array $metadata = [],
        ?int $revokesEventId = null,
    ): int {
        if (! in_array($action, ['granted', 'revoked'], true)) {
            throw new InvalidArgumentException('Unsupported consent action: '.$action);
        }

        $snapshot = $this->documentSnapshot($documents);

        return DB::table('consent_events')->insertGetId([
            'waitlist_entry_id' => $references['waitlist_entry_id'] ?? null,
            'purchase_id' => $references['purchase_id'] ?? null,
            'contact_message_id' => $references['contact_message_id'] ?? null,
            'revokes_event_id' => $revokesEventId,
            'subject_email' => strtolower($email),
            'consent_type' => $type,
            'action' => $action,
            'source' => $source,
            'document_versions' => json_encode($snapshot['versions'], JSON_THROW_ON_ERROR),
            'document_hashes' => json_encode($snapshot['hashes'], JSON_THROW_ON_ERROR),
            'document_urls' => json_encode($snapshot['urls'], JSON_THROW_ON_ERROR),
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'session_id_hash' => $request->hasSession() && $request->session()->getId()
                ? hash('sha256', $request->session()->getId())
                : null,
            'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
    }

    public function latestMarketingEvent(?int $waitlistEntryId): ?object
    {
        if (! $waitlistEntryId) {
            return null;
        }

        return DB::table('consent_events')
            ->where('waitlist_entry_id', $waitlistEntryId)
            ->where('consent_type', 'marketing')
            ->latest('occurred_at')
            ->latest('id')
            ->first();
    }

    public function hasWaitlistLegalAcceptance(?int $waitlistEntryId): bool
    {
        return $waitlistEntryId
            && DB::table('consent_events')
                ->where('waitlist_entry_id', $waitlistEntryId)
                ->where('consent_type', 'waitlist_legal')
                ->where('action', 'granted')
                ->exists();
    }

    /** @param list<string> $documents */
    private function documentSnapshot(array $documents): array
    {
        $versions = [];
        $hashes = [];
        $urls = [];

        foreach ($documents as $document) {
            $current = $this->legalDocuments->current($document);
            $config = config('legal.documents.'.$document);

            $versions[$document] = $current->version;
            $hashes[$document] = $current->content_hash;
            $urls[$document] = route($config['route'], [], true);
        }

        return compact('versions', 'hashes', 'urls');
    }
}
