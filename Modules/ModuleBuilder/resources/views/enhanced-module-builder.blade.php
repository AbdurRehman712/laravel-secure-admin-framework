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

        <!-- Quick Start Guide -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">🚀 Enhanced Features</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-green-100 text-green-600 rounded mx-auto mb-2 flex items-center justify-center">
                        <span class="text-lg font-bold">🔗</span>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Relationships</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">belongsTo, hasMany, etc.</p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded mx-auto mb-2 flex items-center justify-center">
                        <span class="text-lg font-bold">📝</span>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Rich Fields</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">File uploads, rich text, etc.</p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-purple-100 text-purple-600 rounded mx-auto mb-2 flex items-center justify-center">
                        <span class="text-lg font-bold">⚙</span>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Advanced Options</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Factories, seeders, tests</p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded mx-auto mb-2 flex items-center justify-center">
                        <span class="text-lg font-bold">🎨</span>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Filament Integration</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Filters, exports, search</p>
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

        <!-- Example Modules -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">💡 Example Module Configurations</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- E-commerce Example -->
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">🛒 E-commerce Module</h4>
                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p><strong>Models:</strong> Product, Category, Order</p>
                        <p><strong>Relationships:</strong> Product belongsTo Category, Order hasMany Products</p>
                        <p><strong>Fields:</strong> Rich text, images, decimals, enums</p>
                        <p><strong>Features:</strong> Filters, exports, global search</p>
                    </div>
                </div>
                
                <!-- Blog Example -->
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">📝 Blog Module</h4>
                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p><strong>Models:</strong> Post, Category, Tag, Comment</p>
                        <p><strong>Relationships:</strong> Many-to-many tags, nested comments</p>
                        <p><strong>Fields:</strong> Rich text editor, featured images</p>
                        <p><strong>Features:</strong> SEO fields, publishing workflow</p>
                    </div>
                </div>
                
                <!-- CRM Example -->
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">👥 CRM Module</h4>
                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p><strong>Models:</strong> Contact, Company, Deal, Activity</p>
                        <p><strong>Relationships:</strong> Complex hierarchies, polymorphic</p>
                        <p><strong>Fields:</strong> JSON data, file attachments</p>
                        <p><strong>Features:</strong> Advanced filters, bulk actions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips Section -->
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-6 border border-amber-200 dark:border-amber-800">
            <h3 class="text-lg font-medium text-amber-900 dark:text-amber-100 mb-3">💡 Pro Tips</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-amber-800 dark:text-amber-200">
                <div>
                    <p class="font-medium mb-1">🎯 Planning Your Module:</p>
                    <ul class="space-y-1 ml-4">
                        <li>• Start with core models and their relationships</li>
                        <li>• Think about data validation early</li>
                        <li>• Consider future extensibility</li>
                    </ul>
                </div>
                <div>
                    <p class="font-medium mb-1">⚡ Performance Tips:</p>
                    <ul class="space-y-1 ml-4">
                        <li>• Use appropriate field types for better performance</li>
                        <li>• Enable indexes for frequently searched fields</li>
                        <li>• Consider eager loading for relationships</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
