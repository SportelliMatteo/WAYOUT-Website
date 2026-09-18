@php
    $paginationTab = match ($perPageName) {
        'waitlist_per_page', 'join_buyers_per_page', 'creator_buyers_per_page' => 'users',
        'orders_per_page' => 'orders',
        'withdrawals_per_page' => 'withdrawals',
        'consents_per_page' => 'consents',
        default => str_starts_with($perPageName, 'legal_') ? 'legal' : 'settings',
    };
    $paginationQuery = ['tab' => $paginationTab, 'lang' => app()->getLocale()];
    if ($paginationTab === 'legal') {
        $paginationDocument = substr($perPageName, 6, -9);
        $paginationQuery['legal_group'] = config('legal.documents.'.$paginationDocument.'.group', 'policies');
    }
    $paginator->appends($paginationQuery)->fragment('pagination-'.$paginator->getPageName());
@endphp
<div id="pagination-{{ $paginator->getPageName() }}" data-admin-pagination="{{ $paginator->getPageName() }}" data-pagination-tab="{{ $paginationTab }}" data-pagination-group="{{ $paginationQuery['legal_group'] ?? '' }}" class="flex flex-col gap-3 border-t border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-2 text-sm font-bold text-slate-600">
        <label for="{{ $perPageName }}">{{ __('messages.admin.rows_per_page') }}</label>
        <select id="{{ $perPageName }}" data-per-page="{{ $perPageName }}" data-page-name="{{ $paginator->getPageName() }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 font-black outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
            @foreach([5, 10, 20] as $size)
                <option value="{{ $size }}" @selected($paginator->perPage() === $size)>{{ $size }}</option>
            @endforeach
        </select>
    </div>

    <div>{{ $paginator->onEachSide(1)->links('admin.partials.pagination-links') }}</div>
    <p data-pagination-error role="alert" hidden class="text-sm font-bold text-rose-700">{{ __('pagination.error') }}</p>
</div>
