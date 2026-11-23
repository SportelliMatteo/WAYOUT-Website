@foreach(['SITE' => 'legal_site', 'APP' => 'legal_app'] as $scope => $label)
    <div class="mt-5">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('experience.'.$label) }}</h3>
        <nav class="mt-2 flex flex-col gap-1" aria-label="{{ __('experience.'.$label) }}">
            @foreach($footerLegalDocuments[$scope] ?? [] as $legalDocument)
                <a class="rounded-lg px-3 py-2 text-sm font-bold text-slate-600 hover:bg-violet-50 hover:text-violet-700" href="{{ \App\Support\LegalDocumentService::route('legal.document', ['document' => $legalDocument['code'], 'scope' => $scope, 'lang' => app()->getLocale()]) }}">{{ $legalDocument['name'] }}</a>
            @endforeach
            <a class="px-3 py-2 text-sm font-bold text-violet-700 underline" href="{{ \App\Support\LegalDocumentService::route('legal.index', ['scope' => $scope, 'lang' => app()->getLocale()]) }}">{{ __('experience.legal_all_documents') }}</a>
        </nav>
    </div>
@endforeach
