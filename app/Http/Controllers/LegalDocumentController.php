<?php

namespace App\Http\Controllers;

use App\Support\LegalDocumentService;

class LegalDocumentController extends Controller
{
    public function show(string $document, LegalDocumentService $legalDocuments)
    {
        return view('pages.legal.document', [
            'document' => $legalDocuments->current($document),
        ]);
    }
}
