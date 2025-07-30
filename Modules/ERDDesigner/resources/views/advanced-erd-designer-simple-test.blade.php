<x-filament-panels::page>
    <div x-data="simpleErd()" x-init="init()">
        <!-- Debug Info -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
            <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $project->name ?? 'ERD Project' }}</h2>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
                <span>Server Tables: <strong>{{ count($tables ?? []) }}</strong></span>
                <span>Server Relations: <strong>{{ count($relationships ?? []) }}</strong></span>
                <span>JS Tables: <strong x-text="tables.length"></strong></span>
                <span>JS Relations: <strong x-text="relationships.length"></strong></span>
            </div>
            
            <div class="mt-4 space-x-2">
                <button wire:click="testDataLoad" class="bg-blue-600 text-white px-3 py-2 rounded">Test Data</button>
                <button wire:click="createDemoTables" class="bg-green-600 text-white px-3 py-2 rounded">Create Demo</button>
                <button wire:click="createDemoRelationships" class="bg-purple-600 text-white px-3 py-2 rounded">Add Relations</button>
            </div>
        </div>

        <!-- Simple Canvas -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4" style="height: 500px; position: relative;">
            <h3 class="text-lg font-semibold mb-4">Tables Canvas</h3>
            
            <!-- Tables List -->
            <template x-for="table in tables" :key="table.id">
                <div class="bg-white border border-gray-300 rounded-lg p-3 mb-3 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold" x-text="table.display_name || table.name"></h4>
                        <span class="text-xs text-gray-500" x-text="'ID: ' + table.id"></span>
                    </div>
                    <div class="text-sm text-gray-600 mb-2" x-text="table.description || 'No description'"></div>
                    <div class="text-xs text-gray-500">
                        Fields: <span x-text="table.fields ? table.fields.length : 0"></span>
                    </div>
                    
                    <!-- Fields List -->
                    <template x-if="table.fields && table.fields.length > 0">
                        <div class="mt-2 space-y-1">
                            <template x-for="field in table.fields.slice(0, 3)" :key="field.id">
                                <div class="text-xs bg-gray-100 px-2 py-1 rounded flex justify-between">
                                    <span x-text="field.name"></span>
                                    <span x-text="field.type"></span>
                                </div>
                            </template>
                            <div x-show="table.fields.length > 3" class="text-xs text-gray-400">
                                + <span x-text="table.fields.length - 3"></span> more fields
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            
            <!-- No Tables Message -->
            <div x-show="tables.length === 0" class="text-center text-gray-500 mt-8">
                <p>No tables found. Click "Create Demo" to add sample tables.</p>
            </div>
        </div>

        <!-- Relationships -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 mt-4" x-show="relationships.length > 0">
            <h3 class="text-lg font-semibold mb-4">Relationships</h3>
            <template x-for="rel in relationships" :key="rel.id">
                <div class="bg-gray-50 border border-gray-200 rounded p-3 mb-2">
                    <div class="flex items-center justify-between">
                        <span class="font-medium" x-text="rel.type"></span>
                        <span class="text-sm text-gray-600" x-text="'ID: ' + rel.id"></span>
                    </div>
                    <div class="text-sm text-gray-600 mt-1">
                        Source: <span x-text="rel.source_table_id"></span> → 
                        Target: <span x-text="rel.target_table_id"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        window.simpleErd = function() {
            return {
                tables: @js($tables ?? []),
                relationships: @js($relationships ?? []),
                
                init() {
                    console.log('Simple ERD initialized');
                    console.log('Tables data:', this.tables);
                    console.log('Tables length:', this.tables.length);
                    console.log('Tables content:', JSON.stringify(this.tables, null, 2));
                    console.log('Relationships data:', this.relationships);
                    console.log('Relationships length:', this.relationships.length);
                    console.log('Relationships content:', JSON.stringify(this.relationships, null, 2));
                }
            }
        }
    </script>
</x-filament-panels::page>
