@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-sans">
        {{-- Results Info --}}
        <div class="text-slate-500 font-medium order-2 sm:order-1 text-center sm:text-left rtl:sm:text-right">
            {{ __('Menampilkan') }}
            @if ($paginator->firstItem())
                <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span>
                {{ __('sampai') }}
                <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {{ __('dari') }}
            <span class="font-bold text-slate-800">{{ $paginator->total() }}</span>
            {{ __('data') }}
        </div>

        {{-- Page Buttons Container --}}
        <div class="flex items-center gap-1.5 order-1 sm:order-2 flex-wrap justify-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100 text-slate-400 cursor-not-allowed flex items-center gap-1.5 font-medium select-none">
                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>{{ __('Sebelumnya') }}</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 shadow-2xs font-medium flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5 text-slate-600 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>{{ __('Sebelumnya') }}</span>
                </a>
            @endif

            {{-- Numeric Page Links --}}
            <div class="hidden sm:flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="px-2 py-1 text-slate-400 font-semibold select-none">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="min-w-[32px] h-8 px-2 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center shadow-xs shadow-blue-500/25 select-none">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="min-w-[32px] h-8 px-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 font-medium flex items-center justify-center shadow-2xs transition">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 shadow-2xs font-medium flex items-center gap-1.5 transition">
                    <span>{{ __('Berikutnya') }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-600 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100 text-slate-400 cursor-not-allowed flex items-center gap-1.5 font-medium select-none">
                    <span>{{ __('Berikutnya') }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
