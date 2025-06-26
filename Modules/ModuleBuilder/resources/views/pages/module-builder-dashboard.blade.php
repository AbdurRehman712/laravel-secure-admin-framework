<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $recentModules = Modules\ModuleBuilder\Models\ModuleProject::latest()->limit(5)->get();
    @endphp
    
    <div class="space-y-6">
        <!-- Stats Overview -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-filament::section>
                <div class="flex items-center">
                    <div class="mr-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
                        <x-filament::icon icon="heroicon-o-cube" class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Modules</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['modules'] }}</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center">
                    <div class="mr-4 flex h-12 w-12 items-center justify-center rounded-full bg-success-100 dark:bg-success-900">
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6 text-success-600 dark:text-success-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Modules</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_modules'] }}</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center">
                    <div class="mr-4 flex h-12 w-12 items-center justify-center rounded-full bg-info-100 dark:bg-info-900">
                        <x-filament::icon icon="heroicon-o-circle-stack" class="h-6 w-6 text-info-600 dark:text-info-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Database Tables</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['tables'] }}</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center">
                    <div class="mr-4 flex h-12 w-12 items-center justify-center rounded-full bg-warning-100 dark:bg-warning-900">
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Fields</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['fields'] }}</p>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Main Dashboard Content -->
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            <!-- Left Column -->
            <div class="grid auto-rows-min gap-6 lg:col-span-2">
                <!-- Recent Modules -->
                <x-filament::section>
                    <x-slot name="heading">
                        Recent Modules
                    </x-slot>
                    <x-slot name="description">
                        Latest module projects
                    </x-slot>
                    
                    @if($recentModules->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentModules as $module)
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $module->name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $module->description ?: 'No description' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">{{ $module->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="/admin/module-projects/{{ $module->id }}/edit" 
                                           class="text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                            <x-filament::icon icon="heroicon-o-pencil" class="h-4 w-4" />
                                        </a>
                                        @if($module->enabled)
                                            <span class="text-success-600 dark:text-success-400">
                                                <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                            <a href="/admin/module-projects" 
                               class="text-sm font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                View all modules →
                            </a>
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <x-filament::icon icon="heroicon-o-cube" class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                            <h4 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">No modules yet</h4>
                            <p class="mb-4 text-gray-600 dark:text-gray-400">Create your first module to get started</p>
                            <x-filament::button href="/admin/module-projects/create" tag="a" icon="heroicon-o-plus">
                                Create Module
                            </x-filament::button>
                        </div>
                    @endif
                </x-filament::section>

                <!-- Features Overview -->
                <x-filament::section>
                    <x-slot name="heading">
                        Module Builder Pro Features
                    </x-slot>
                    <x-slot name="description">
                        Everything you need to build Laravel modules efficiently
                    </x-slot>
                    
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div class="text-center p-4">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900">
                                <x-filament::icon icon="heroicon-o-cube" class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                            </div>
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Unified Interface</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Manage all module components from a single tabbed interface</p>
                        </div>

                        <div class="text-center p-4">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-info-100 dark:bg-info-900">
                                <x-filament::icon icon="heroicon-o-circle-stack" class="h-6 w-6 text-info-600 dark:text-info-400" />
                            </div>
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Database Schema</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Visual database designer with relationships and migrations</p>
                        </div>

                        <div class="text-center p-4">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-success-100 dark:bg-success-900">
                                <x-filament::icon icon="heroicon-o-squares-2x2" class="h-6 w-6 text-success-600 dark:text-success-400" />
                            </div>
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Filament Resources</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Auto-generate beautiful admin interfaces with forms and tables</p>
                        </div>

                        <div class="text-center p-4">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-warning-100 dark:bg-warning-900">
                                <x-filament::icon icon="heroicon-o-cloud" class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                            </div>
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">API Endpoints</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">REST API controllers with validation and documentation</p>
                        </div>

                        <div class="text-center p-4">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-danger-100 dark:bg-danger-900">
                                <x-filament::icon icon="heroicon-o-arrow-path" class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                            </div>
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Relationships</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Define and manage Eloquent relationships visually</p>
                        </div>

                        <div class="text-center p-4">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                <x-filament::icon icon="heroicon-o-rocket-launch" class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                            </div>
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">One-Click Deploy</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Generate and deploy complete modules with a single click</p>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            <!-- Right Column -->
            <div class="grid auto-rows-min gap-6">
                <!-- Quick Actions -->
                <x-filament::section>
                    <x-slot name="heading">
                        Quick Actions
                    </x-slot>
                    <x-slot name="description">
                        Common module building tasks
                    </x-slot>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <x-filament::button 
                            href="/admin/module-projects/create" 
                            tag="a" 
                            color="primary" 
                            class="w-full" 
                            icon="heroicon-o-plus"
                        >
                            New Module
                        </x-filament::button>

                        <x-filament::button 
                            href="/admin/module-tables" 
                            tag="a" 
                            color="info" 
                            class="w-full" 
                            icon="heroicon-o-circle-stack"
                        >
                            Manage Tables
                        </x-filament::button>

                        <x-filament::button 
                            href="/admin/module-fields" 
                            tag="a" 
                            color="success" 
                            class="w-full" 
                            icon="heroicon-o-squares-2x2"
                        >
                            Manage Fields
                        </x-filament::button>

                        <x-filament::button 
                            onclick="window.location.reload()" 
                            color="warning" 
                            class="w-full" 
                            icon="heroicon-o-arrow-path"
                        >
                            Refresh
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
