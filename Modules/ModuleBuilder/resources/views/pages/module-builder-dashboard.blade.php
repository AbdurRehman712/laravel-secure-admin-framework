<x-filament-panels::page>
    @php
        $data = $this->getViewData();
        $stats = $data['stats'];
        $recentModules = $data['recentModules'];
    @endphp
    
    <div class="space-y-6">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <x-heroicon-o-cube class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Modules</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['modules'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30">
                        <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Modules</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_modules'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <x-heroicon-o-circle-stack class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Database Tables</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['tables'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <x-heroicon-o-squares-2x2 class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Fields</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['fields'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Modules -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Modules</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Latest module projects</p>
                </div>
                <div class="p-6">
                    @if($recentModules->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentModules as $module)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $module->name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $module->description ?: 'No description' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">{{ $module->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="/admin/unified-module-builder/{{ $module->id }}/edit" 
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            <x-heroicon-o-pencil class="w-4 h-4" />
                                        </a>
                                        @if($module->enabled)
                                            <span class="text-green-600 dark:text-green-400">
                                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="/admin/unified-module-builder" 
                               class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                View all modules →
                            </a>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-cube class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No modules yet</h4>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">Create your first module to get started</p>
                            <a href="/admin/unified-module-builder/create" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                                Create Module
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Common module building tasks</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="/admin/unified-module-builder/create" 
                           class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-lg hover:from-blue-100 hover:to-blue-200 dark:hover:from-blue-800/30 dark:hover:to-blue-700/30 transition-colors">
                            <x-heroicon-o-plus class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" />
                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">New Module</span>
                        </a>

                        <a href="/admin/module-tables" 
                           class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-lg hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-800/30 dark:hover:to-purple-700/30 transition-colors">
                            <x-heroicon-o-circle-stack class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" />
                            <span class="text-sm font-medium text-purple-900 dark:text-purple-100">Manage Tables</span>
                        </a>

                        <a href="/admin/module-fields" 
                           class="flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-lg hover:from-green-100 hover:to-green-200 dark:hover:from-green-800/30 dark:hover:to-green-700/30 transition-colors">
                            <x-heroicon-o-squares-2x2 class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" />
                            <span class="text-sm font-medium text-green-900 dark:text-green-100">Manage Fields</span>
                        </a>

                        <button onclick="window.location.reload()" 
                                class="flex flex-col items-center p-4 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/30 dark:to-amber-800/30 rounded-lg hover:from-amber-100 hover:to-amber-200 dark:hover:from-amber-800/30 dark:hover:to-amber-700/30 transition-colors">
                            <x-heroicon-o-arrow-path class="w-8 h-8 text-amber-600 dark:text-amber-400 mb-2" />
                            <span class="text-sm font-medium text-amber-900 dark:text-amber-100">Refresh</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Overview -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl p-8 border border-blue-200 dark:border-blue-800">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Module Builder Pro Features</h2>
                <p class="text-gray-600 dark:text-gray-400">Everything you need to build Laravel modules efficiently</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <x-heroicon-o-cube class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Unified Interface</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Manage all module components from a single tabbed interface</p>
                </div>

                <div class="text-center">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <x-heroicon-o-circle-stack class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Database Schema</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Visual database designer with relationships and migrations</p>
                </div>

                <div class="text-center">
                    <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <x-heroicon-o-squares-2x2 class="w-8 h-8 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Filament Resources</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Auto-generate beautiful admin interfaces with forms and tables</p>
                </div>

                <div class="text-center">
                    <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <x-heroicon-o-cloud class="w-8 h-8 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">API Endpoints</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">REST API controllers with validation and documentation</p>
                </div>

                <div class="text-center">
                    <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <x-heroicon-o-arrow-path class="w-8 h-8 text-red-600 dark:text-red-400" />
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Relationships</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Define and manage Eloquent relationships visually</p>
                </div>

                <div class="text-center">
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <x-heroicon-o-rocket-launch class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">One-Click Deploy</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Generate and deploy complete modules with a single click</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
