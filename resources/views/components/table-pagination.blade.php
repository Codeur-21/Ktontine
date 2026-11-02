@if($paginator->total() > 0)
<nav class="kt-table-pagination" role="navigation" aria-label="Pagination du tableau">
    <p>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur <strong>{{ $paginator->total() }}</strong> résultats</p>
    @if($paginator->hasPages())<div class="kt-pagination-buttons">
        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled($paginator->onFirstPage()) aria-label="Page précédente">‹ <span>Précédent</span></button>
        @foreach($elements as $element)
            @if(is_string($element))<span class="kt-pagination-dots" aria-hidden="true">{{ $element }}</span>@endif
            @if(is_array($element))@foreach($element as $page => $url)
                @if($page === $paginator->currentPage())<span class="kt-pagination-current" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                @else<button type="button" wire:key="pagination-{{ $paginator->getPageName() }}-{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" aria-label="Aller à la page {{ $page }}">{{ $page }}</button>@endif
            @endforeach@endif
        @endforeach
        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled(!$paginator->hasMorePages()) aria-label="Page suivante"><span>Suivant</span> ›</button>
    </div>@endif
</nav>
@endif
