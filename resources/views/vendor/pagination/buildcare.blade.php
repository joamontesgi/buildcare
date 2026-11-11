@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-slate-600">
            Mostrando
            <span class="font-semibold text-sky-700">{{ $paginator->firstItem() ?? 0 }}</span>
            a
            <span class="font-semibold text-sky-700">{{ $paginator->lastItem() ?? 0 }}</span>
            de
            <span class="font-semibold text-sky-700">{{ $paginator->total() }}</span>
            registros
        </div>

        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 text-sm text-slate-300 bg-slate-50 border border-slate-200 rounded-lg cursor-not-allowed">
                    &laquo; Anterior
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" class="px-3 py-2 text-sm text-sky-700 bg-white border border-sky-200 rounded-lg hover:bg-sky-50 transition-colors">
                    &laquo; Anterior
                </button>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-2 text-sm text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-2 text-sm font-bold text-white bg-sky-500 border border-sky-500 rounded-lg">{{ $page }}</span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" class="px-3 py-2 text-sm text-sky-700 bg-white border border-sky-200 rounded-lg hover:bg-sky-50 transition-colors">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" class="px-3 py-2 text-sm text-sky-700 bg-white border border-sky-200 rounded-lg hover:bg-sky-50 transition-colors">
                    Siguiente &raquo;
                </button>
            @else
                <span class="px-3 py-2 text-sm text-slate-300 bg-slate-50 border border-slate-200 rounded-lg cursor-not-allowed">
                    Siguiente &raquo;
                </span>
            @endif
        </div>
    </nav>
@endif
