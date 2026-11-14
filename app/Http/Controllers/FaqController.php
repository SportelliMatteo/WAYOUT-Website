<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class FaqController extends Controller
{
    public function __invoke()
    {
        return view('pages.faq', [
            'faq' => File::json(resource_path('content/faq.it.json')),
        ]);
    }
}
