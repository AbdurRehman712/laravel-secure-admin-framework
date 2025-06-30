<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section -->
        <x-filament::section>
            <x-slot name="heading">
                Module Editor
            </x-slot>

            <x-slot name="description">
                Edit existing modules to add new tables, fields, and relationships
            </x-slot>

            <!-- Features Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-filament::section
                    :compact="true"
                    icon="heroicon-o-plus-circle"
                    icon-color="primary"
                >
                    <x-slot name="heading">Add New Tables</x-slot>
                    <x-slot name="description">Create new models with rich field types</x-slot>
                </x-filament::section>

                <x-filament::section
                    :compact="true"
                    icon="heroicon-o-link"
                    icon-color="success"
                >
                    <x-slot name="heading">Add Relationships</x-slot>
                    <x-slot name="description">Connect models with proper relationships</x-slot>
                </x-filament::section>

                <x-filament::section
                    :compact="true"
                    icon="heroicon-o-arrow-path"
                    icon-color="warning"
                >
                    <x-slot name="heading">Auto-Generate</x-slot>
                    <x-slot name="description">Resources, migrations, and permissions</x-slot>
                </x-filament::section>
            </div>
        </x-filament::section>

        <!-- Instructions -->
        @if(!$selectedModule)
        <x-filament::section
            icon="heroicon-o-information-circle"
            icon-color="warning"
        >
            <x-slot name="heading">How to Use Module Editor</x-slot>

            <div class="space-y-2">
                <p><strong>1. Select a Module</strong> - Choose an existing module to extend</p>
                <p><strong>2. Add New Tables</strong> - Define new models with fields and types</p>
                <p><strong>3. Add Relationships</strong> - Connect new and existing models</p>
                <p><strong>4. Update Module</strong> - Generate all files and run migrations</p>
            </div>
        </x-filament::section>
        @endif

        <!-- Current Module Info -->
        @if($selectedModule && $moduleData)
        <x-filament::section
            icon="heroicon-o-cube"
            icon-color="primary"
        >
            <x-slot name="heading">Editing Module: {{ $selectedModule }}</x-slot>
            <x-slot name="description">{{ $moduleData['description'] ?? 'No description available' }}</x-slot>

            <x-filament::badge color="primary">
                Version: {{ $moduleData['version'] ?? '1.0.0' }}
            </x-filament::badge>
        </x-filament::section>
        @endif

        <!-- Form Section -->
        <x-filament::section>
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
        </x-filament::section>

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
