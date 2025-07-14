<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">⚡</span>
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Enhanced Module Builder</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Create sophisticated Laravel modules with relationships, advanced field types, and complete Filament integration.
                        Perfect for building complex applications with proper architecture.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form wire:submit="generateModule">
                {{ $this->form }}

                <div class="mt-6 flex gap-3">
                    <x-filament::button
                        type="submit"
                        color="primary"
                        size="lg"
                        icon="heroicon-o-cog-6-tooth"
                    >
                        Generate Enhanced Module
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="success"
                        wire:click="fillDemoData"
                        icon="heroicon-o-sparkles"
                    >
                        Fill Demo Data (Shop)
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="gray"
                        wire:click="clearForm"
                    >
                        Clear Form
                    </x-filament::button>
                </div>
            </form>
        </div>

    </div>
</x-filament-panels::page>
