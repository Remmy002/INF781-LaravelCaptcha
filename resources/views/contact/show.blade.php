<x-guest-layout>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-md">
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}">
            @csrf
            
            {{-- HONEYPOT: invisible para humanos, irresistible para bots --}}
            <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
                <label for="website">Sitio web</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
            </div>

            <div>
                <x-input-label for="name" value="Nombre" />
                <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name')" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="email" value="Correo" />
                <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="message" value="Mensaje" />
                <textarea id="message" name="message" rows="5" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('message') }}</textarea>
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="captcha" value="Código de verificación" />
                <img src="{{ route('captcha.default') }}?t={{ time() }}" id="cap-img" class="mt-1 rounded border" />
                <x-text-input id="captcha" name="captcha" type="text" class="block mt-1 w-full" required />
                <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
            </div>

            <x-primary-button class="mt-4">Enviar</x-primary-button>
        </form>
    </div>
</x-guest-layout>