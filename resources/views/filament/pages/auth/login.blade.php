<x-filament-panels::page.simple>
    @if (filament()->hasLogin())
        <x-slot name="heading">
            {{ __('filament-panels::pages/auth/login.heading') }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ new \Illuminate\Support\HtmlString(__('filament-panels::pages/auth/login.actions.register.before') . ' ' . $this->registerAction . '.') }}
        </x-slot>
    @endif
    
    <!-- Custom Footer Branding -->
    <div class="text-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2L16 5v6c0 5.55-3.84 9.74-6 10.5-2.16-.76-6-4.95-6-10.5V5l6-3z"/>
            </svg>
            <span>Powered by <strong class="text-amber-600 dark:text-amber-400">H. Sol</strong></span>
            <span class="text-xs">(Hereafter Solutions)</span>
        </div>
        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
            Secure Admin Framework
        </div>
    </div>
</x-filament-panels::page.simple>
