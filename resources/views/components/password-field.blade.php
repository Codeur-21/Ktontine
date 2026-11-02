@props(['inputId'])
<div class="kt-password-field" x-data="{ passwordVisible: false }" x-on:keydown.escape="passwordVisible = false" x-on:submit.window="passwordVisible = false">
    {{ $slot }}
    <button type="button" class="kt-password-toggle" x-cloak x-on:click="passwordVisible = !passwordVisible" :aria-pressed="passwordVisible" :aria-label="passwordVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" :title="passwordVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" aria-controls="{{ $inputId }}">
        <svg x-show="!passwordVisible" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg x-show="passwordVisible" x-cloak width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18M10.6 5.1 12 5c6.5 0 10 7 10 7a20 20 0 0 1-3.2 4M6.2 6.2C3.4 8.2 2 12 2 12s3.5 7 10 7a11 11 0 0 0 5.8-1.8M10 10a3 3 0 0 0 4 4"/></svg>
    </button>
</div>
@once
<style>
.kt-password-field{position:relative;width:100%;min-width:0}.kt-page .kt-password-field>input{padding-right:50px}.kt-password-field .kt-password-toggle{position:absolute;right:5px;top:50%;transform:translateY(-50%);display:flex;align-items:center;justify-content:center;width:40px;height:36px;padding:0;border:0;border-radius:7px;background:transparent;color:#92705a;cursor:pointer}.kt-password-field .kt-password-toggle:hover{background:#fff0e3;color:#b74709}.kt-password-field .kt-password-toggle:focus-visible{outline:2px solid #c64b08;outline-offset:1px}.kt-password-field .kt-password-toggle[aria-pressed="true"]{color:#b74709}
</style>
@endonce
