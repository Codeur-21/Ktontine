<footer id="workspace-footer" class="mx-4 mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-orange-100 bg-white/70 px-5 py-4 text-xs text-gray-500 sm:mx-8 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
    <div class="flex items-center gap-3">
        <img src="{{ $platform->logoUrl() }}" alt="Logo {{ $platform->name }}" width="32" height="32" class="size-8 rounded-full border border-orange-100 bg-white object-contain p-0.5 dark:border-white/15">
        <div><span class="font-semibold text-gray-700 dark:text-gray-200">{{ $platform->name }}</span><span class="mt-1 block text-[10px] tracking-wide">Administration de la plateforme</span></div>
    </div>
    <p>© {{ now()->year }} {{ $platform->name }} · Tous droits réservés.</p>
</footer>
