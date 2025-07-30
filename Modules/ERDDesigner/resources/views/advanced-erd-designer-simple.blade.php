<x-filament-panels::page>
    <div class="erd-designer-container">
        <!-- Project Header -->
        <div class="bg-white border-b border-gray-200 p-4 mb-4 rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $project->name ?? 'ERD Project' }}</h2>
                    <p class="text-gray-600 text-sm">{{ $project->description ?? 'Database design project' }}</p>
                </div>

                <div class="flex items-center space-x-2">
                    <button wire:click="addTable"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Table
                    </button>

                    <button wire:click="importSQL"
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m3-3V10"></path>
                        </svg>
                        Import SQL
                    </button>

                    <button wire:click="exportSQL"
                            class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Export SQL
                    </button>

                    <button wire:click="exportToModuleBuilder"
                            class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3"></path>
                        </svg>
                        To Module Builder
                    </button>

                    <button wire:click="createDemoRelationships"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        Create Relationships
                    </button>
                </div>
            </div>
        </div>

        <!-- Canvas Area -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6" style="min-height: 600px;">
            @if(count($tables) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($tables as $table)
                    <div class="bg-white border border-gray-300 rounded-lg shadow-lg" 
                         style="background-color: {{ $table['color'] ?? '#ffffff' }};">
                        
                        <!-- Table Header -->
                        <div class="bg-gray-800 text-white px-3 py-2 rounded-t-lg">
                            <h3 class="font-semibold text-sm">{{ $table['display_name'] ?? $table['name'] }}</h3>
                        </div>
                        
                        <!-- Table Fields -->
                        <div class="p-3">
                            @if(count($table['fields']) > 0)
                                @foreach($table['fields'] as $field)
                                <div class="flex items-center justify-between py-1 px-2 text-xs hover:bg-gray-100 rounded
                                           {{ $field['is_primary'] ? 'font-bold text-yellow-700' : ($field['is_foreign_key'] ? 'text-blue-700' : 'text-gray-700') }}">
                                    <div class="flex items-center space-x-2">
                                        <!-- Field Icon -->
                                        <span class="w-3 h-3 flex items-center justify-center">
                                            @if($field['is_primary'])
                                                <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"/>
                                                </svg>
                                            @elseif($field['is_foreign_key'])
                                                <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"/>
                                                </svg>
                                            @else
                                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                            @endif
                                        </span>
                                        <span>{{ $field['name'] }}</span>
                                    </div>
                                    <span class="text-gray-500 text-xs">
                                        {{ $field['type'] }}{{ $field['length'] ? '(' . $field['length'] . ')' : '' }}
                                    </span>
                                </div>
                                @endforeach
                            @else
                                <p class="text-gray-500 text-xs italic">No fields defined</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No tables yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first table or importing SQL.</p>
                    <div class="mt-6">
                        <button wire:click="addTable" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Table
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Relationships Section -->
        @if(count($relationships) > 0)
        <div class="mt-4 bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Relationships</h3>
            <div class="space-y-2">
                @foreach($relationships as $relationship)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="text-sm">
                            <span class="font-medium text-blue-600">
                                {{ collect($tables)->firstWhere('id', $relationship['source_table_id'])['name'] ?? 'Unknown' }}
                            </span>
                            <span class="text-gray-500 mx-2">→</span>
                            <span class="font-medium text-green-600">
                                {{ collect($tables)->firstWhere('id', $relationship['target_table_id'])['name'] ?? 'Unknown' }}
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $relationship['type'] }} ({{ $relationship['cardinality_source'] }}:{{ $relationship['cardinality_target'] }})
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Project Stats -->
        @if(count($tables) > 0)
        <div class="mt-4 bg-white border border-gray-200 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-blue-600">{{ count($tables) }}</div>
                    <div class="text-sm text-gray-600">Tables</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ collect($tables)->sum(function($table) { return count($table['fields']); }) }}
                    </div>
                    <div class="text-sm text-gray-600">Fields</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ count($relationships) }}</div>
                    <div class="text-sm text-gray-600">Relationships</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-orange-600">{{ $project->database_type ?? 'MySQL' }}</div>
                    <div class="text-sm text-gray-600">Database Type</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Add Table Modal -->
        @if($showTableModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Table</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Table Name</label>
                            <input type="text" wire:model="tableForm.name" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                            <input type="text" wire:model="tableForm.display_name" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model="tableForm.description" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button wire:click="$set('showTableModal', false)" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button wire:click="saveTable" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Create Table
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Import SQL Modal -->
        @if($showImportModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-3/4 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Import SQL Schema</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SQL Code</label>
                            <textarea wire:model="sqlImport" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono" 
                                      rows="15" 
                                      placeholder="Paste your SQL CREATE TABLE statements here..."></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button wire:click="$set('showImportModal', false)" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button wire:click="processImportSQL" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                            Import SQL
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Export SQL Modal -->
        @if($showExportModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-3/4 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Export SQL Schema</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Generated SQL</label>
                            <textarea readonly 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono bg-gray-50" 
                                      rows="15">{{ $sqlExport }}</textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button wire:click="$set('showExportModal', false)" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Close
                        </button>
                        <button onclick="navigator.clipboard.writeText('{{ addslashes($sqlExport) }}')" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-medium hover:bg-purple-700">
                            Copy to Clipboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <style>
            .erd-designer-container {
                user-select: none;
            }
        </style>
    </div>
</x-filament-panels::page>
