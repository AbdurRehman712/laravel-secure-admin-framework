<x-filament-panels::page>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Database ERD Designer</h2>
        <p class="text-gray-600 mb-6">Visual database design tool with drag & drop interface.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-900 mb-2">🎨 Visual Design</h3>
                <p class="text-blue-700 text-sm">Create database schemas with drag & drop tables and visual relationships.</p>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h3 class="font-semibold text-green-900 mb-2">📥 SQL Import</h3>
                <p class="text-green-700 text-sm">Import existing database schemas from SQL files.</p>
            </div>
            
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <h3 class="font-semibold text-purple-900 mb-2">🔗 Module Integration</h3>
                <p class="text-purple-700 text-sm">Seamlessly integrate with Module Builder for code generation.</p>
            </div>
        </div>
        
        <div class="mt-8 flex space-x-4">
            <button wire:click="createNewProject"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Create New Project</span>
                <span wire:loading>Creating...</span>
            </button>
            <button wire:click="importSQL"
                    class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                Import SQL
            </button>
            <button wire:click="loadDemo"
                    class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Load Demo</span>
                <span wire:loading>Loading...</span>
            </button>
        </div>

        @if(count($projects) > 0)
        <div class="mt-8 bg-white border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-4">Your ERD Projects</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($projects as $project)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <h5 class="font-medium text-gray-900 mb-2">{{ $project['name'] }}</h5>
                    <p class="text-gray-600 text-sm mb-3">{{ $project['description'] }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                        <span>{{ $project['database_type'] ?? 'mysql' }} (ID: {{ $project['id'] }})</span>
                        <span>{{ \Carbon\Carbon::parse($project['updated_at'])->diffForHumans() }}</span>
                    </div>
                    <a href="/admin/advanced-erd-designer?project={{ $project['id'] }}"
                       class="block w-full bg-blue-100 text-blue-700 px-3 py-2 rounded text-sm hover:bg-blue-200 transition-colors text-center">
                        Open Project
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mt-8 bg-gray-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">Features Available:</h4>
            <ul class="text-gray-600 text-sm space-y-1">
                <li>• ✅ Project creation and management</li>
                <li>• ✅ Demo data loading</li>
                <li>• ✅ Permission-based access control</li>
                <li>• ✅ Module Builder integration</li>
                <li>• ✅ SQL import/export functionality</li>
                <li>• 🔄 Advanced canvas with zoom & pan (Coming Soon)</li>
                <li>• 🔄 Real-time relationship visualization (Coming Soon)</li>
            </ul>
        </div>
    </div>

    <!-- Import SQL Modal -->
    @if($showImportModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Import SQL to Create Project
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="project-name" class="block text-sm font-medium text-gray-700">Project Name *</label>
                                    <input type="text" 
                                           wire:model="projectName"
                                           id="project-name" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter project name">
                                </div>
                                
                                <div>
                                    <label for="project-description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <input type="text" 
                                           wire:model="projectDescription"
                                           id="project-description" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Project description (optional)">
                                </div>
                                
                                <div>
                                    <label for="database-name" class="block text-sm font-medium text-gray-700">Database Name</label>
                                    <input type="text" 
                                           wire:model="databaseName"
                                           id="database-name" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Will be auto-generated if empty">
                                </div>
                                
                                <div>
                                    <label for="sql-content" class="block text-sm font-medium text-gray-700">SQL Content *</label>
                                    <textarea wire:model="sqlImportContent"
                                              id="sql-content" 
                                              rows="8" 
                                              class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Paste your SQL content here or copy from an .sql file"></textarea>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Supports CREATE TABLE statements, foreign keys, and constraints.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="processImportSQL"
                            type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Create Project & Import</span>
                        <span wire:loading>Processing...</span>
                    </button>
                    <button wire:click="$set('showImportModal', false)"
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
