<?php

namespace App\Http\Controllers;

use App\Support\LegalDocumentService;
use Illuminate\Http\Request;

class LegalDocumentController extends Controller
{
    public function index(LegalDocumentService $legalDocuments, Request $request)
    {
        return view('pages.legal.index', ['documents' => $legalDocuments->published(($request->route('scope') ?? $request->query('scope')) === null ? null : $this->scope($request))]);
    }

    public function show(LegalDocumentService $legalDocuments, Request $request)
    {
        $document = $request->route('document');
        $scope = $this->scope($request);

        return view('pages.legal.document', [
            'document' => $legalDocuments->current($document, scope: $scope),
            'documentKey' => $document,
            'documentScope' => $scope,
            'currentDocumentUrl' => $this->currentUrl($document, $scope),
        ]);
    }

    public function history(LegalDocumentService $legalDocuments, Request $request)
    {
        $document = $request->route('document');
        $scope = $this->scope($request);

        return view('pages.legal.history', [
            'document' => $legalDocuments->current($document, scope: $scope),
            'documentKey' => $document,
            'documentScope' => $scope,
            'currentDocumentUrl' => $this->currentUrl($document, $scope),
            'versions' => $legalDocuments->history($document, $scope),
        ]);
    }

    public function version(LegalDocumentService $legalDocuments, Request $request)
    {
        $document = $request->route('document');
        $version = $request->route('version');
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
        return LegalDocumentService::route('legal.document', ['document' => $document, 'scope' => $scope]);
    }

    private function scope(Request $request): string
    {
        if ($scope = $request->route('scope')) {
            return strtoupper($scope);
        }

        $validated = $request->validate(['scope' => ['sometimes', 'string', 'in:APP,SITE']]);

        return $validated['scope'] ?? 'SITE';
    }
}
