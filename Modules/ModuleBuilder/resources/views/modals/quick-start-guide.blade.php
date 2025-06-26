<div class="space-y-6">
    <!-- Header -->
    <div class="text-center mb-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/50 mb-4">
            <x-filament::icon icon="heroicon-o-bolt" class="h-6 w-6 text-primary-600 dark:text-primary-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Welcome to Module Builder Pro
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Create powerful Laravel modules with an October CMS-like experience
        </p>
    </div>

    <!-- Quick Start Steps -->
    <div class="space-y-4">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/50">
                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">1</span>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Create a New Module</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Click "New Module Project" to start building your module with our guided interface.
                </p>
            </div>
        </div>

        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/50">
                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">2</span>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Design Your Schema</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Define your database tables, fields, and relationships using our visual editor.
                </p>
            </div>
        </div>

        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/50">
                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">3</span>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Configure Resources</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Set up Filament resources, forms, and tables with automatic generation.
                </p>
            </div>
        </div>

        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/50">
                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">4</span>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Deploy & Test</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Generate all files, run migrations, and test your module instantly.
                </p>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Key Features</h4>
        <div class="grid grid-cols-2 gap-3">
            <div class="flex items-center space-x-2">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 text-success-500" />
                <span class="text-sm text-gray-600 dark:text-gray-300">Visual Schema Builder</span>
            </div>
            <div class="flex items-center space-x-2">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 text-success-500" />
                <span class="text-sm text-gray-600 dark:text-gray-300">Auto Code Generation</span>
            </div>
            <div class="flex items-center space-x-2">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 text-success-500" />
                <span class="text-sm text-gray-600 dark:text-gray-300">API Generation</span>
            </div>
            <div class="flex items-center space-x-2">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 text-success-500" />
                <span class="text-sm text-gray-600 dark:text-gray-300">Relationship Builder</span>
            </div>
            <div class="flex items-center space-x-2">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 text-success-500" />
                <span class="text-sm text-gray-600 dark:text-gray-300">One-Click Deploy</span>
            </div>
            <div class="flex items-center space-x-2">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 text-success-500" />
                <span class="text-sm text-gray-600 dark:text-gray-300">Module Management</span>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6 text-center">
        <div class="space-y-3">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Ready to build your first module?
            </p>
            <div class="flex justify-center space-x-3">
                <x-filament::button href="/admin/module-projects/create" tag="a" icon="heroicon-o-plus">
                    Create New Module
                </x-filament::button>
                <x-filament::button href="/admin/module-projects" tag="a" color="gray">
                    Browse Modules
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
