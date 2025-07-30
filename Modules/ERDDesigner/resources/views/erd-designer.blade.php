<x-filament-panels::page>
    <div class="erd-designer-container" x-data="erdDesigner()" x-init="init()">
        <!-- Toolbar -->
        <div class="bg-white border-b border-gray-200 p-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $this->getTitle() }}
                </h2>
                
                <div class="flex items-center space-x-2">
                    <button 
                        wire:click="addTable"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Table
                    </button>
                    
                    <button 
                        @click="showImportModal = true"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        Import SQL
                    </button>
                    
                    <button 
                        @click="exportSql()"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Export SQL
                    </button>
                </div>
            </div>
            
            <!-- Canvas Controls -->
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <button @click="zoomOut()" class="p-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <span class="text-sm text-gray-600" x-text="Math.round(zoom * 100) + '%'"></span>
                    <button @click="zoomIn()" class="p-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                    <button @click="resetZoom()" class="p-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="flex items-center space-x-2">
                    <label class="flex items-center">
                        <input type="checkbox" x-model="showGrid" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Grid</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" x-model="snapToGrid" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Snap</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Main Canvas Area -->
        <div class="relative flex-1 overflow-hidden bg-gray-50">
            <!-- Canvas -->
            <div 
                class="absolute inset-0 cursor-move"
                @mousedown="startPan($event)"
                @mousemove="pan($event)"
                @mouseup="endPan()"
                @wheel="handleWheel($event)"
                :style="`transform: scale(${zoom}) translate(${offsetX}px, ${offsetY}px)`"
            >
                <!-- Grid -->
                <div 
                    x-show="showGrid"
                    class="absolute inset-0 opacity-20"
                    :style="gridStyle"
                ></div>

                <!-- Tables -->
                <template x-for="table in tables" :key="table.id">
                    <div 
                        class="absolute bg-white border border-gray-300 rounded-lg shadow-sm cursor-move"
                        :style="`left: ${table.position_x}px; top: ${table.position_y}px; width: ${table.width}px; min-height: ${table.height}px; border-color: ${table.color}`"
                        @mousedown.stop="startDrag(table, $event)"
                        @dblclick="editTable(table.id)"
                    >
                        <!-- Table Header -->
                        <div 
                            class="px-3 py-2 border-b border-gray-200 font-semibold text-sm"
                            :style="`background-color: ${table.color}; color: ${getContrastColor(table.color)}`"
                        >
                            <div class="flex items-center justify-between">
                                <span x-text="table.display_name || table.name"></span>
                                <div class="flex items-center space-x-1">
                                    <button 
                                        @click.stop="editTable(table.id)"
                                        class="p-1 hover:bg-black hover:bg-opacity-10 rounded"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        @click.stop="deleteTable(table.id)"
                                        class="p-1 hover:bg-red-500 hover:bg-opacity-20 rounded text-red-600"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Table Fields -->
                        <div class="p-2">
                            <template x-for="field in table.fields" :key="field.id">
                                <div class="flex items-center justify-between py-1 px-2 text-xs hover:bg-gray-50 rounded">
                                    <div class="flex items-center space-x-2">
                                        <!-- Field Icons -->
                                        <div class="flex space-x-1">
                                            <span x-show="field.is_primary" class="text-yellow-500" title="Primary Key">🔑</span>
                                            <span x-show="field.is_foreign_key" class="text-blue-500" title="Foreign Key">🔗</span>
                                            <span x-show="field.is_unique" class="text-green-500" title="Unique">⭐</span>
                                        </div>
                                        
                                        <span class="font-medium" x-text="field.name"></span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2 text-gray-500">
                                        <span x-text="field.type + (field.length ? '(' + field.length + ')' : '')"></span>
                                        <span x-show="!field.is_nullable" class="text-red-500 font-bold">*</span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Connection Points -->
                        <div class="absolute -top-2 -left-2 w-4 h-4 bg-blue-500 rounded-full cursor-crosshair opacity-0 hover:opacity-100 transition-opacity"
                             @click.stop="startConnection(table.id, 'top')"
                             title="Connect from here">
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-4 h-4 bg-green-500 rounded-full cursor-crosshair opacity-0 hover:opacity-100 transition-opacity"
                             @click.stop="endConnection(table.id, 'bottom')"
                             title="Connect to here">
                        </div>
                    </div>
                </template>

                <!-- Relationships -->
                <svg class="absolute inset-0 pointer-events-none" style="width: 100%; height: 100%;">
                    <template x-for="relationship in relationships" :key="relationship.id">
                        <g>
                            <!-- Relationship Line -->
                            <line 
                                :x1="getTableCenter(relationship.source_table_id).x"
                                :y1="getTableCenter(relationship.source_table_id).y"
                                :x2="getTableCenter(relationship.target_table_id).x"
                                :y2="getTableCenter(relationship.target_table_id).y"
                                stroke="#6B7280"
                                stroke-width="2"
                                :stroke-dasharray="relationship.type === 'many_to_many' ? '5,5' : 'none'"
                            />
                            
                            <!-- Cardinality Labels -->
                            <text 
                                :x="getTableCenter(relationship.source_table_id).x + 10"
                                :y="getTableCenter(relationship.source_table_id).y - 5"
                                fill="#6B7280"
                                font-size="10"
                                x-text="getCardinalitySymbol(relationship.cardinality_source)"
                            />
                            <text 
                                :x="getTableCenter(relationship.target_table_id).x - 10"
                                :y="getTableCenter(relationship.target_table_id).y - 5"
                                fill="#6B7280"
                                font-size="10"
                                x-text="getCardinalitySymbol(relationship.cardinality_target)"
                            />
                        </g>
                    </template>
                </svg>
            </div>
        </div>
    </div>

    <!-- Table Modal -->
    <div x-show="$wire.showTableModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" @click="$wire.showTableModal = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        {{ $selectedTable ? 'Edit Table' : 'Add New Table' }}
                    </h3>
                    
                    <!-- Table Form will be loaded here via Livewire -->
                    @livewire('erd-designer.table-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Import SQL Modal -->
    <div x-show="showImportModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
         @click.self="showImportModal = false">

        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Import SQL Schema</h3>
                <p class="text-sm text-gray-600">Upload a SQL file or paste SQL statements to create tables</p>
            </div>

            <div class="px-6 py-4">
                <!-- File Upload Option -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload SQL File</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                        <input type="file"
                               accept=".sql,.txt"
                               @change="handleFileUpload($event)"
                               class="hidden"
                               id="sql-file-input">
                        <label for="sql-file-input" class="cursor-pointer">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="mt-2">
                                <span class="text-sm font-medium text-blue-600 hover:text-blue-500">Click to upload</span>
                                <span class="text-sm text-gray-500"> or drag and drop</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">SQL files up to 10MB</p>
                        </label>
                    </div>
                </div>

                <!-- OR Divider -->
                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">OR</span>
                    </div>
                </div>

                <!-- Text Input Option -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Paste SQL Statements</label>
                    <textarea
                        x-model="sqlImportText"
                        class="w-full h-64 p-3 border border-gray-300 rounded-lg font-mono text-sm"
                        placeholder="Paste your CREATE TABLE statements here..."></textarea>
                </div>

                <div class="mb-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-800 mb-2">Supported SQL Features:</h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• CREATE TABLE statements</li>
                            <li>• Column definitions with data types</li>
                            <li>• Primary keys and foreign keys</li>
                            <li>• NOT NULL constraints</li>
                            <li>• AUTO_INCREMENT fields</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button @click="showImportModal = false; sqlImportText = '';"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </button>
                <button @click="processImport()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                    Import SQL
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function erdDesigner() {
            return {
                tables: @entangle('tables'),
                relationships: @entangle('relationships'),
                zoom: @entangle('canvasZoom'),
                offsetX: @entangle('canvasOffsetX'),
                offsetY: @entangle('canvasOffsetY'),
                showGrid: true,
                snapToGrid: true,
                isDragging: false,
                dragTable: null,
                dragOffset: { x: 0, y: 0 },
                isPanning: false,
                panStart: { x: 0, y: 0 },
                showImportModal: false,
                sqlImportText: '',
                
                init() {
                    this.$watch('zoom', () => this.updateCanvas());
                    this.$watch('offsetX', () => this.updateCanvas());
                    this.$watch('offsetY', () => this.updateCanvas());
                    
                    // Listen for Livewire events
                    Livewire.on('refreshCanvas', () => {
                        this.tables = this.$wire.tables;
                        this.relationships = this.$wire.relationships;
                    });
                },
                
                updateCanvas() {
                    this.$wire.call('updateCanvasState', this.zoom, this.offsetX, this.offsetY);
                },
                
                startDrag(table, event) {
                    this.isDragging = true;
                    this.dragTable = table;
                    this.dragOffset = {
                        x: event.clientX - table.position_x,
                        y: event.clientY - table.position_y
                    };
                    
                    document.addEventListener('mousemove', this.drag.bind(this));
                    document.addEventListener('mouseup', this.endDrag.bind(this));
                },
                
                drag(event) {
                    if (!this.isDragging || !this.dragTable) return;
                    
                    let newX = event.clientX - this.dragOffset.x;
                    let newY = event.clientY - this.dragOffset.y;
                    
                    if (this.snapToGrid) {
                        newX = Math.round(newX / 20) * 20;
                        newY = Math.round(newY / 20) * 20;
                    }
                    
                    this.dragTable.position_x = newX;
                    this.dragTable.position_y = newY;
                },
                
                endDrag() {
                    if (this.isDragging && this.dragTable) {
                        this.$wire.call('updateTablePosition', 
                            this.dragTable.id, 
                            this.dragTable.position_x, 
                            this.dragTable.position_y
                        );
                    }
                    
                    this.isDragging = false;
                    this.dragTable = null;
                    
                    document.removeEventListener('mousemove', this.drag.bind(this));
                    document.removeEventListener('mouseup', this.endDrag.bind(this));
                },
                
                zoomIn() {
                    this.zoom = Math.min(this.zoom * 1.2, 3);
                },
                
                zoomOut() {
                    this.zoom = Math.max(this.zoom / 1.2, 0.1);
                },
                
                resetZoom() {
                    this.zoom = 1;
                    this.offsetX = 0;
                    this.offsetY = 0;
                },
                
                getTableCenter(tableId) {
                    const table = this.tables.find(t => t.id === tableId);
                    if (!table) return { x: 0, y: 0 };
                    
                    return {
                        x: table.position_x + table.width / 2,
                        y: table.position_y + table.height / 2
                    };
                },
                
                getCardinalitySymbol(cardinality) {
                    const symbols = {
                        'zero_or_one': '0..1',
                        'exactly_one': '1',
                        'zero_or_many': '0..*',
                        'one_or_many': '1..*'
                    };
                    return symbols[cardinality] || '1';
                },
                
                getContrastColor(hexColor) {
                    // Convert hex to RGB
                    const r = parseInt(hexColor.substr(1, 2), 16);
                    const g = parseInt(hexColor.substr(3, 2), 16);
                    const b = parseInt(hexColor.substr(5, 2), 16);
                    
                    // Calculate luminance
                    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                    
                    return luminance > 0.5 ? '#000000' : '#FFFFFF';
                },
                
                editTable(tableId) {
                    this.$wire.call('editTable', tableId);
                },
                
                deleteTable(tableId) {
                    if (confirm('Are you sure you want to delete this table?')) {
                        this.$wire.call('deleteTable', tableId);
                    }
                },
                
                exportSql() {
                    this.$wire.call('exportSql').then(sql => {
                        // Create download
                        const blob = new Blob([sql], { type: 'text/sql' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'database_schema.sql';
                        a.click();
                        window.URL.revokeObjectURL(url);
                    });
                },

                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.sqlImportText = e.target.result;
                        };
                        reader.readAsText(file);
                    }
                },

                processImport() {
                    if (!this.sqlImportText.trim()) {
                        alert('Please provide SQL content to import.');
                        return;
                    }

                    this.$wire.call('processSqlImport', this.sqlImportText).then(() => {
                        this.showImportModal = false;
                        this.sqlImportText = '';
                        // Reset file input
                        const fileInput = document.getElementById('sql-file-input');
                        if (fileInput) fileInput.value = '';
                    }).catch(error => {
                        console.error('Import failed:', error);
                        alert('Import failed. Please check your SQL syntax.');
                    });
                }
            }
        }
    </script>
    @endpush
</x-filament-panels::page>
