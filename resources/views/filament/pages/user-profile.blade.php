<x-filament-panels::page>
    <div class="kt-page grid gap-6 lg:grid-cols-3">
        <aside class="space-y-5 lg:col-span-1">
            <section class="relative overflow-hidden rounded-2xl border border-orange-200 bg-white p-7 dark:border-white/10 dark:bg-gray-900">
                <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-r from-orange-600 to-amber-500" aria-hidden="true"></div>
                <form wire:submit="saveAvatar" class="relative mt-7" x-data="{ preview: null }">
                    <img src="{{ auth()->user()->getFilamentAvatarUrl() }}" data-default="{{ auth()->user()->getFilamentAvatarUrl() }}" :src="preview || $el.dataset.default" alt="Votre photo de profil" class="mx-auto size-28 rounded-full border-4 border-white object-cover shadow-sm dark:border-gray-900">
                    <input id="profile-avatar" name="avatar" wire:model="avatar" x-ref="avatarInput" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" x-on:change="const file = $event.target.files[0]; if (file &amp;&amp; ['image/png','image/jpeg','image/webp'].includes(file.type) &amp;&amp; file.size &lt;= 2097152) { const reader = new FileReader(); reader.onload = () => preview = reader.result; reader.readAsDataURL(file); }" aria-describedby="profile-avatar-help" tabindex="-1">
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <x-filament::button type="button" size="sm" color="gray" icon="heroicon-o-camera" x-on:click="$refs.avatarInput.click()" aria-controls="profile-avatar">Changer la photo</x-filament::button>
                        <x-filament::button type="submit" size="sm" wire:loading.attr="disabled" wire:target="avatar,saveAvatar">Enregistrer la photo</x-filament::button>
                    </div>
                    @if(auth()->user()->avatar_path)<button type="button" wire:click="removeAvatar" wire:confirm="Retirer votre photo et remettre l’avatar par défaut ?" wire:loading.attr="disabled" class="mt-3 w-full text-center text-xs text-orange-700 dark:text-orange-300">Utiliser l’avatar par défaut</button>@endif
                    <p id="profile-avatar-help" class="mt-3 text-center text-[11px] leading-5 text-gray-500 dark:text-gray-400">PNG, JPG ou WebP · 2 Mo maximum.<br>Votre photo apparaît aussi dans le menu du panel.</p>
                    <p wire:loading wire:target="avatar" class="mt-2 text-center text-xs text-orange-600" role="status">Chargement de la photo…</p>
                    @error('avatar')<p class="mt-2 text-center text-xs text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>@enderror
                </form>
                <div class="mt-5 text-center"><h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h2><p class="mt-2 break-all text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p><span class="mt-4 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-medium text-orange-800 dark:bg-orange-500/10 dark:text-orange-300"><x-filament::icon icon="heroicon-o-shield-check" class="size-4" />Administrateur de la plateforme</span></div>
                <dl class="mt-7 space-y-4 border-t border-gray-100 pt-5 text-sm dark:border-white/10"><div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Statut du compte</dt><dd class="font-medium text-green-700 dark:text-green-400">Actif</dd></div><div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Membre depuis</dt><dd class="font-medium text-gray-700 dark:text-gray-200">{{ auth()->user()->created_at?->format('d/m/Y') ?? '—' }}</dd></div></dl>
            </section>
            <section class="rounded-2xl border border-orange-100 bg-orange-50/70 p-6 dark:border-orange-500/15 dark:bg-orange-500/5"><x-filament::icon icon="heroicon-o-lock-closed" class="mb-3 size-6 text-orange-600 dark:text-orange-400" /><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Votre compte, votre accès.</h3><p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">Gardez vos coordonnées à jour et choisissez un mot de passe réservé à votre compte Ktontine.</p></section>
        </aside>
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-7 dark:border-white/10 dark:bg-gray-900">
                <div class="mb-6 flex items-start gap-3"><span class="rounded-xl bg-orange-50 p-3 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400"><x-filament::icon icon="heroicon-o-user-circle" class="size-5" /></span><div><h2 class="font-semibold text-gray-900 dark:text-white">Mes informations</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Les coordonnées associées à votre compte administrateur.</p></div></div>
                <form wire:submit="saveProfile" class="space-y-5">
                    @foreach ([['name', 'Nom complet', 'text', 'heroicon-o-user', 'name'], ['email', 'Adresse e-mail', 'email', 'heroicon-o-envelope', 'email'], ['phone', 'Téléphone', 'tel', 'heroicon-o-phone', 'tel']] as [$field, $label, $type, $icon, $autocomplete])
                        <div><label for="profile-{{ $field }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</label><div class="relative"><x-filament::icon :icon="$icon" class="pointer-events-none absolute top-3 left-3 size-5 text-gray-400" /><input id="profile-{{ $field }}" name="{{ $field }}" type="{{ $type }}" wire:model="{{ $field }}" autocomplete="{{ $autocomplete }}" class="w-full rounded-xl border border-gray-300 bg-gray-50 py-3 pr-4 pl-11 text-sm text-gray-900 focus:border-orange-500 focus:outline-orange-500 dark:border-white/15 dark:bg-white/5 dark:text-white" aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" @error($field) aria-describedby="profile-{{ $field }}-error" @enderror></div>@error($field)<p id="profile-{{ $field }}-error" class="mt-2 text-xs text-red-600" role="alert">{{ $message }}</p>@enderror</div>
                    @endforeach
                    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5 dark:border-white/10"><x-filament::button type="button" color="gray" wire:click="cancelProfile">Annuler</x-filament::button><x-filament::button type="submit" wire:loading.attr="disabled" wire:target="saveProfile">Enregistrer les modifications</x-filament::button></div>
                </form>
            </section>
            <section class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-7 dark:border-white/10 dark:bg-gray-900">
                <div class="mb-6 flex items-start gap-3"><span class="rounded-xl bg-orange-50 p-3 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400"><x-filament::icon icon="heroicon-o-key" class="size-5" /></span><div><h2 class="font-semibold text-gray-900 dark:text-white">Mot de passe</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Renforcez la sécurité de votre accès.</p></div></div>
                <form wire:submit="changePassword" class="space-y-5">
                    @foreach ([['currentPassword', 'Mot de passe actuel', 'current-password'], ['password', 'Nouveau mot de passe', 'new-password'], ['password_confirmation', 'Confirmer le nouveau mot de passe', 'new-password']] as [$field, $label, $autocomplete])
                        <div><label for="profile-{{ $field }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</label><x-password-field input-id="profile-{{ $field }}"><input id="profile-{{ $field }}" name="{{ $field }}" type="password" wire:model="{{ $field }}" :type="passwordVisible ? 'text' : 'password'" autocomplete="{{ $autocomplete }}" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-orange-500 focus:outline-orange-500 dark:border-white/15 dark:bg-white/5 dark:text-white" aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" @error($field) aria-describedby="profile-{{ $field }}-error" @enderror></x-password-field>@error($field)<p id="profile-{{ $field }}-error" class="mt-2 text-xs text-red-600" role="alert">{{ $message }}</p>@enderror</div>
                    @endforeach
                    <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">Prévoyez au moins 8 caractères avec une majuscule, une minuscule, un chiffre et un symbole.</p>
                    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5 dark:border-white/10"><x-filament::button type="button" color="gray" wire:click="cancelPassword">Annuler</x-filament::button><x-filament::button type="submit" wire:loading.attr="disabled" wire:target="changePassword">Modifier le mot de passe</x-filament::button></div>
                </form>
            </section>
        </div>
    </div>
</x-filament-panels::page>
