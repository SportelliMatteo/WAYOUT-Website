<?php

namespace App\Http\Controllers;

use App\Support\LegalDocumentService;
use Illuminate\Http\Request;

class LegalDocumentController extends Controller
{
    public function index(LegalDocumentService $legalDocuments, Request $request)
    {
        return view('pages.legal.index', ['documents' => $legalDocuments->published($request->query('scope') === null ? null : $this->scope($request))]);
    }

    public function show(string $document, LegalDocumentService $legalDocuments, Request $request)
    {
        $scope = $this->scope($request);

        return view('pages.legal.document', [
            'document' => $legalDocuments->current($document, scope: $scope),
            'documentKey' => $document,
            'documentScope' => $scope,
            'currentDocumentUrl' => $this->currentUrl($document, $scope),
        ]);
    }

    public function history(string $document, LegalDocumentService $legalDocuments, Request $request)
    {
        $scope = $this->scope($request);

        return view('pages.legal.history', [
            'document' => $legalDocuments->current($document, scope: $scope),
            'documentKey' => $document,
            'documentScope' => $scope,
            'currentDocumentUrl' => $this->currentUrl($document, $scope),
            'versions' => $legalDocuments->history($document, $scope),
        ]);
    }

    public function version(string $document, string $version, LegalDocumentService $legalDocuments, Request $request)
    {
        $scope = $this->scope($request);

        return view('pages.legal.document', [
            'document' => $legalDocuments->version($document, $version, scope: $scope),
            'documentKey' => $document,
            'documentScope' => $scope,
            'currentDocumentUrl' => $this->currentUrl($document, $scope),
            'archivedVersion' => true,
        ]);
    }

    private function currentUrl(string $document, string $scope): string
    {
        $route = $scope === 'SITE' ? config('legal.documents.'.$document.'.route') : null;

        return $route ? route($route, ['lang' => app()->getLocale()])
            : route('legal.document', ['document' => $document, 'scope' => $scope, 'lang' => app()->getLocale()]);
    }

    private function scope(Request $request): string
    {
        $validated = $request->validate(['scope' => ['sometimes', 'string', 'in:APP,SITE']]);

        return $validated['scope'] ?? 'SITE';
    }
}
