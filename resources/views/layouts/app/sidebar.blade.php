<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    <flux:sidebar
        sticky
        collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        {{-- Logo de la plataforma --}}
        <flux:sidebar.header>
            <x-app-logo
                :sidebar="true"
                href="{{ route('dashboard') }}"
                wire:navigate />

            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        {{-- Navegación principal --}}
        <flux:sidebar.nav>
            <flux:sidebar.group
                :heading="__('Plataforma educativa')"
                class="grid">
                {{-- Disponible para todos los usuarios --}}
                <flux:sidebar.item
                    icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Inicio') }}
                </flux:sidebar.item>

                {{-- Menú del estudiante --}}
                @hasanyrole('estudiante|administrador')
                estudiante
                <flux:sidebar.item
                    icon="book-open"
                    href="#">
                    {{ __('Actividades') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="clipboard-document-check"
                    href="#">
                    {{ __('Mis tareas') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="chart-bar"
                    href="#">
                    {{ __('Mi progreso') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="academic-cap"
                    href="#">
                    {{ __('Calificaciones') }}
                </flux:sidebar.item>
                @endhasanyrole

                {{-- Menú del docente --}}
                @hasanyrole('docente|administrador')
                profesor
                <flux:sidebar.item
                    icon="users"
                    href="#">
                    {{ __('Mis grupos') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="book-open"
                    href="#">
                    {{ __('Actividades') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="plus-circle"
                    href="#">
                    {{ __('Crear actividad') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="clipboard-document-check"
                    href="#">
                    {{ __('Calificar entregas') }}
                </flux:sidebar.item>
                @endrole

                {{-- Menú del administrador --}}
                @role('administrador')
                administrador
                <flux:sidebar.item
                    icon="users"
                    :href="route('users.index')"
                    :current="request()->routeIs('users.*')"
                    wire:navigate>
                    {{ __('Usuarios') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="calendar-days"
                    :href="route('academic-periods.index')"
                    :current="request()->routeIs('academic-periods.*')"
                    wire:navigate>
                    {{ __('Periodos académicos') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="academic-cap"
                    :href="route('school-grades.index')"
                    :current="request()->routeIs('school-grades.*')"
                    wire:navigate>
                    {{ __('Grados escolares') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="users"
                    :href="route('school-groups.index')"
                    :current="request()->routeIs('school-groups.*')"
                    wire:navigate>
                    {{ __('Grupos escolares') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="book-open"
                    :href="route('subjects.index')"
                    :current="request()->routeIs('subjects.*')"
                    wire:navigate>
                    {{ __('Materias') }}
                </flux:sidebar.item>
                @endrole
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        {{-- Menú de usuario para escritorio --}}
        <x-desktop-user-menu
            class="hidden lg:block"
            :name="auth()->user()->name" />
    </flux:sidebar>

    {{-- Menú superior para dispositivos móviles --}}
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle
            class="lg:hidden"
            icon="bars-2"
            inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile
                :initials="auth()->user()->initials()"
                icon-trailing="chevron-down" />

            <flux:menu>
                {{-- Información del usuario --}}
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar
                                :name="auth()->user()->name"
                                :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">
                                    {{ auth()->user()->name }}
                                </flux:heading>

                                <flux:text class="truncate">
                                    {{ auth()->user()->email }}
                                </flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                {{-- Configuración del perfil --}}
                <flux:menu.radio.group>
                    <flux:menu.item
                        :href="route('profile.edit')"
                        icon="cog"
                        wire:navigate>
                        {{ __('Configuración') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                {{-- Cerrar sesión --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="w-full">
                    @csrf

                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer"
                        data-test="logout-button">
                        {{ __('Cerrar sesión') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{-- Contenido de cada página --}}
    {{ $slot }}

    {{-- Notificaciones --}}
    @persist('toast')
    <flux:toast.group>
        <flux:toast />
    </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>