<x-filament-panels::page>
    <div class="module-builder-container">
        <!-- Instructions -->
        @if(!$selectedModule)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 p-6">
            <div class="flex items-center gap-2 mb-4">
                <h2 class="text-lg font-medium text-yellow-900 dark:text-yellow-100">How to Use Module Editor</h2>
            </div>

            <div class="space-y-2 text-sm text-yellow-800 dark:text-yellow-200">
                <p><strong>1. Select a Module</strong> - Choose an existing module to extend</p>
                <p><strong>2. Add New Tables</strong> - Define new models with fields and types</p>
                <p><strong>3. Add Relationships</strong> - Connect new and existing models</p>
                <p><strong>4. Update Module</strong> - Generate all files and run migrations</p>
            </div>
        </div>
        @endif

        <!-- Current Module Info -->
        @if($selectedModule && $moduleData)
        <div class="module-info-card">
            <div class="flex items-center gap-2 mb-2">
                <x-heroicon-o-cube class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                <h2 class="text-lg font-medium text-blue-900 dark:text-blue-100">Editing Module: {{ $selectedModule }}</h2>
            </div>
            <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">{{ $moduleData['description'] ?? 'No description available' }}</p>

            <x-filament::badge color="primary">
                Version: {{ $moduleData['version'] ?? '1.0.0' }}
            </x-filament::badge>
        </div>
        @endif

        <!-- Form Section -->
        <div class="module-form-card">
            <form wire:submit="updateModule">
                {{ $this->form }}

                <div class="mt-6 flex gap-3">
                    @if($selectedModule)
                        <x-filament::button
                            type="submit"
                            color="primary"
                            size="lg"
                            icon="heroicon-o-arrow-path"
                        >
                            Update Module
                        </x-filament::button>

                        @if($this->hasModuleSeeders())
                            <x-filament::button
                                type="button"
                                color="success"
                                wire:click="runModuleSeeders"
                                icon="heroicon-o-play"
                            >
                                Run Seeders
                            </x-filament::button>
                        @endif

                        @if($this->hasModuleFactories())
                            <x-filament::button
                                type="button"
                                color="warning"
                                wire:click="createTestData"
                                icon="heroicon-o-beaker"
                            >
                                Create Test Data
                            </x-filament::button>
                        @endif
                    @endif

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

        <!-- Examples Section -->
        @if(!$selectedModule)
        <x-filament::section>
            <x-slot name="heading">Example Use Cases</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">🛒 Extend E-commerce Module</h3>
                    <div class="space-y-2">
                        <p><strong>Add Tables:</strong></p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>Review (rating, comment, product_id)</li>
                            <li>Coupon (code, discount, expires_at)</li>
                            <li>Wishlist (user_id, product_id)</li>
                        </ul>
                        <p><strong>Add Relationships:</strong></p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>Product hasMany Reviews</li>
                            <li>Product belongsToMany Wishlists</li>
                        </ul>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-medium">📝 Extend Blog Module</h3>
                    <div class="space-y-2">
                        <p><strong>Add Tables:</strong></p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>Comment (content, author, post_id)</li>
                            <li>Tag (name, slug, color)</li>
                            <li>Series (title, description)</li>
                        </ul>
                        <p><strong>Add Relationships:</strong></p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>Post hasMany Comments</li>
                            <li>Post belongsToMany Tags</li>
                            <li>Post belongsTo Series</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
