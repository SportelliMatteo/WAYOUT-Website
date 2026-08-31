<div class="flex flex-col gap-3 border-t border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-2 text-sm font-bold text-slate-600">
        <label for="{{ $perPageName }}">{{ __('messages.admin.rows_per_page') }}</label>
        <select id="{{ $perPageName }}" data-per-page="{{ $perPageName }}" data-page-name="{{ $paginator->getPageName() }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 font-black outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
            @foreach([5, 10, 20] as $size)
                <option value="{{ $size }}" @selected($paginator->perPage() === $size)>{{ $size }}</option>
            @endforeach
        </select>
    </div>

    <div>{{ $paginator->links() }}</div>
</div>
