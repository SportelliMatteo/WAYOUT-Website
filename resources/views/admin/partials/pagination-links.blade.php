@if ($paginator->hasPages())
<nav aria-label="{{ __('pagination.navigation') }}" class="flex flex-wrap items-center gap-3">
    <p class="text-sm text-slate-600">{{ __('pagination.summary', ['first' => $paginator->firstItem() ?? 0, 'last' => $paginator->lastItem() ?? 0, 'total' => $paginator->total()]) }}</p>
    <div class="flex flex-wrap items-center gap-1">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400">{{ __('pagination.previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-violet-700 hover:bg-violet-50">{{ __('pagination.previous') }}</a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-slate-500">{{ $element }}</span>
            @else
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span aria-current="page" class="rounded-lg bg-violet-700 px-3 py-2 text-sm font-bold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="{{ __('pagination.page', ['page' => $page]) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-violet-700 hover:bg-violet-50">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-violet-700 hover:bg-violet-50">{{ __('pagination.next') }}</a>
        @else
            <span aria-disabled="true" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400">{{ __('pagination.next') }}</span>
        @endif
    </div>
</nav>
@endif
