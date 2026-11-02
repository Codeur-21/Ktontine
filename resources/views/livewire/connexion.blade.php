<div x-data="{ logType: $wire.entangle('logType') }" class="max-w-md mx-auto p-6 bg-white rounded-xl shadow">

    <h2 class="text-2xl font-bold mb-2">
        Connexion à K Tontine
    </h2>

    <p class="text-gray-500 mb-6">
        Connectez-vous à votre espace.
    </p>

    @if ($errors->any())
        <p class="text-red-500">
            {{ $errors->first() }}
        </p>
    @endif

    <!-- Choix du mode de connexion -->
    <div class="flex gap-3 mb-6">
        <button @click="logType='email';; $wire.clearErrors() " type="button" class="px-4 py-2 border rounded-lg">
            Email
        </button>

        <button @click="logType = 'phone'; $wire.clearErrors() " type="button" class="px-4 py-2 border rounded-lg">
            Téléphone
        </button>
        <button @click="logType = 'linkMagic'; $wire.clearErrors() " type="button" class="px-4 py-2 border rounded-lg">
            lien Magique
        </button>
    </div>

    <form wire:submit='save'>

        <!-- Email -->
        <div x-show="logType==='email'" class="mb-4">
            <label for="email">Adresse email</label>
            <input id="email" wire:model='email' type="email" class="w-full border p-3 rounded-lg"
                placeholder="exemple@gmail.com" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @error('email') aria-describedby="email-error" @enderror>
            @error('email')
                <p id="email-error" class="field-error" role="alert">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Téléphone -->
        <div x-show="logType==='phone'" class="mb-4">
            <label for="phone">Téléphone</label>
            <input id="phone" wire:model='phone' type="tel" class="w-full border p-3 rounded-lg"
                placeholder="620185893" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" @error('phone') aria-describedby="phone-error" @enderror>
            @error('phone')
                <p id="phone-error" class="field-error" role="alert">
                    {{ $message }}
                </p>
            @enderror
        </div>
        <div x-show="logType === 'linkMagic'" class="mb-4">
            <label for="linkMagic">Lien magique</label>

            <input id="linkMagic" wire:model="linkMagic" type="email" class="w-full border p-3 rounded-lg"
                placeholder="exemple@gmail.com" aria-invalid="{{ $errors->has('linkMagic') ? 'true' : 'false' }}" @error('linkMagic') aria-describedby="linkMagic-error" @enderror>

            @error('linkMagic')
                <p id="linkMagic-error" class="field-error" role="alert">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Mot de passe -->
        <div class="mb-4">
            <label for="password">Mot de passe</label>
            <input id="password" wire:model='password' type="password" class="w-full border p-3 rounded-lg"
                placeholder="Votre mot de passe" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @error('password') aria-describedby="password-error" @enderror>

            @error('password')
                <p id="password-error" class="field-error" role="alert">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-5">
            <label class="flex items-center gap-2">
                <input type="checkbox">
                Se souvenir de moi
            </label>
        </div>

        <!-- Soumission -->
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg">
            Se connecter
        </button>

    </form>
</div>
