<x-layouts::auth :title="__('Registro')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('BIENVENIDO A EDUNOVA')" :description="__('Ingresa tu correo y contraseña para continuar')" />


        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Correo electrónico o nombre de usuario')"
                :value="old('email')"
                type="text"
                required
                autofocus
                autocomplete="username"
                placeholder="usuario o email@ejemplo.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Contraseña')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

               
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Recordarme')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Iniciar sesión') }}
                </flux:button>
            </div>
        </form>

    </div>
</x-layouts::auth>
