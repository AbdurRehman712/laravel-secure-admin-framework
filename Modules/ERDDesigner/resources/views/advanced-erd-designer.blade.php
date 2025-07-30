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

                    <button wire:click="applySmartLayout"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                        Smart Layout
                    </button>
                </div>
            </div>
        </div>

        <!-- Canvas Container -->
        <div class="relative bg-gray-50 border border-gray-200 rounded-lg overflow-hidden" style="height: 600px;">
            <!-- Grid Background -->
            <div x-show="showGrid" class="absolute inset-0 opacity-20" 
                 :style="`background-image: repeating-linear-gradient(0deg, #ccc, #ccc 1px, transparent 1px, transparent ${gridSize * zoom}px), repeating-linear-gradient(90deg, #ccc, #ccc 1px, transparent 1px, transparent ${gridSize * zoom}px);`">
            </div>
            
            <!-- Canvas -->
            <div class="absolute inset-0 cursor-move" 
                 @mousedown="startPan($event)"
                 @mousemove="pan($event)"
                 @mouseup="endPan()"
                 @wheel="handleWheel($event)">
                
                <!-- Canvas Content - TABLES ONLY, NO RELATIONSHIPS -->
                <div class="relative" 
                     :style="`transform: translate(${panX}px, ${panY}px) scale(${zoom}); transform-origin: 0 0;`">
                    
                    <!-- Relationship indicators (small tags on tables) -->
                    <template x-for="relationship in relationships" :key="relationship.id">
                        <div class="absolute bg-blue-600 text-white text-xs font-bold px-1 py-0.5 rounded shadow-sm"
                             :style="`
                                left: ${getRelationshipStart(relationship).x + 5}px;
                                top: ${getRelationshipStart(relationship).y - 20}px;
                                z-index: 1001;
                             `">
                            <span class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                                </svg>
                                <span x-text="getRelationshipTypeSymbol(relationship)"></span>
                            </span>
                        </div>
                    </template>
                    
                    <!-- Tables ONLY -->
                    <template x-for="table in tables" :key="table.id">
                        <div class="absolute cursor-pointer bg-white"
                             style="border: 5px solid #1f2937; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);"
                             :style="`left: ${table.position_x}px; top: ${table.position_y}px; width: ${table.width}px; min-height: ${table.height}px;`"
                             @mousedown="startDrag($event, table)"
                             @click="selectTable(table)">
                            
                            <!-- Table Header -->
                            <div class="px-3 py-2 text-white font-semibold text-sm" style="background: #1f2937;">
                                <h3 x-text="table.display_name || table.name"></h3>
                                
                                <!-- Relationship indicators for this table -->
                                <div class="flex flex-wrap mt-1 gap-1">
                                    <template x-for="relationship in getTableRelationships(table)">
                                        <span class="inline-flex items-center text-xs bg-blue-800 px-1 py-0.5 rounded">
                                            <span x-text="getRelatedTableName(relationship, table)"></span>
                                            <span class="ml-1 text-white text-opacity-70" x-text="getRelationshipTypeSymbol(relationship)"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Table Fields -->
                            <div class="p-2 bg-white">
                                <template x-for="field in table.fields" :key="field.id">
                                    <div class="flex items-center justify-between py-1 px-2 text-xs hover:bg-gray-100 rounded"
                                         :class="field.is_primary ? 'font-bold text-yellow-700 bg-yellow-50' : field.is_foreign_key ? 'text-blue-700 bg-blue-50' : 'text-gray-700'">
                                        <div class="flex items-center space-x-2">
                                            <!-- Field Icon -->
                                            <span class="w-3 h-3 flex items-center justify-center">
                                                <template x-if="field.is_primary">
                                                    <span class="text-yellow-500 font-bold">🗝</span>
                                                </template>
                                                <template x-if="field.is_foreign_key && !field.is_primary">
                                                    <span class="text-blue-500">🔗</span>
                                                </template>
                                                <template x-if="!field.is_primary && !field.is_foreign_key">
                                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                                </template>
                                            </span>
                                            <span x-text="field.name"></span>
                                        </div>
                                        <span class="text-gray-500 text-xs" x-text="field.type + (field.length ? '(' + field.length + ')' : '')"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        <!-- Properties Panel -->
        <div x-show="selectedTable" class="mt-4 bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-4">Table Properties</h3>
            <template x-if="selectedTable">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Table Name</label>
                        <input type="text" x-model="selectedTable.name" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                        <input type="text" x-model="selectedTable.display_name" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea x-model="selectedTable.description" 
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" rows="2"></textarea>
                    </div>
                </div>
            </template>
        </div>

        <!-- JavaScript -->
        <script>
        function erdDesigner() {
            return {
                // Canvas state
                zoom: 1.0,
                panX: 0,
                panY: 0,
                showGrid: true,
                gridSize: 20,
                
                // Interaction state
                isPanning: false,
                isDragging: false,
                dragStartX: 0,
                dragStartY: 0,
                dragTable: null,
                selectedTable: null,
                
                // Data
                tables: @js($tables ?? []),
                relationships: @js($relationships ?? []),
                
                // Modals
                showImportModal: false,
                
                init() {
                    // Initialize canvas
                    this.loadData();
                },
                
                loadData() {
                    // Load tables and relationships from Livewire component
                    // This would be connected to the backend
                },
                
                // Zoom functions
                zoomIn() {
                    this.zoom = Math.min(this.zoom * 1.2, 3.0);
                },
                
                zoomOut() {
                    this.zoom = Math.max(this.zoom / 1.2, 0.1);
                },
                
                resetZoom() {
                    this.zoom = 1.0;
                    this.panX = 0;
                    this.panY = 0;
                },
                
                handleWheel(event) {
                    event.preventDefault();
                    if (event.deltaY < 0) {
                        this.zoomIn();
                    } else {
                        this.zoomOut();
                    }
                },
                
                // Pan functions
                startPan(event) {
                    if (event.target === event.currentTarget) {
                        this.isPanning = true;
                        this.dragStartX = event.clientX - this.panX;
                        this.dragStartY = event.clientY - this.panY;
                    }
                },
                
                pan(event) {
                    if (this.isPanning) {
                        this.panX = event.clientX - this.dragStartX;
                        this.panY = event.clientY - this.dragStartY;
                    } else if (this.isDragging && this.dragTable) {
                        const rect = event.currentTarget.getBoundingClientRect();
                        const x = (event.clientX - rect.left - this.panX) / this.zoom;
                        const y = (event.clientY - rect.top - this.panY) / this.zoom;
                        
                        this.dragTable.position_x = x - this.dragStartX;
                        this.dragTable.position_y = y - this.dragStartY;
                    }
                },
                
                endPan() {
                    this.isPanning = false;
                    this.isDragging = false;
                    this.dragTable = null;
                },
                
                // Table functions
                startDrag(event, table) {
                    event.stopPropagation();
                    this.isDragging = true;
                    this.dragTable = table;
                    
                    const rect = event.currentTarget.getBoundingClientRect();
                    this.dragStartX = (event.clientX - rect.left) / this.zoom;
                    this.dragStartY = (event.clientY - rect.top) / this.zoom;
                },
                
                selectTable(table) {
                    this.selectedTable = table;
                },
                
                addTable() {
                    // This would call a Livewire method
                    $wire.addTable();
                },
                
                // Enhanced relationship functions for intelligent line drawing
                getIntelligentPath(relationship) {
                    const sourceTable = this.tables.find(t => t.id === relationship.source_table_id);
                    const targetTable = this.tables.find(t => t.id === relationship.target_table_id);
                    
                    if (!sourceTable || !targetTable) return '';
                    
                    // Calculate optimal connection points
                    const sourcePoint = this.getOptimalConnectionPoint(sourceTable, targetTable);
                    const targetPoint = this.getOptimalConnectionPoint(targetTable, sourceTable);
                    
                    // Create curved path with intelligent routing
                    return this.createCurvedPath(sourcePoint, targetPoint, sourceTable, targetTable);
                },
                
                getOptimalConnectionPoint(fromTable, toTable) {
                    const fromCenter = {
                        x: fromTable.position_x + fromTable.width / 2,
                        y: fromTable.position_y + fromTable.height / 2
                    };
                    const toCenter = {
                        x: toTable.position_x + toTable.width / 2,
                        y: toTable.position_y + toTable.height / 2
                    };
                    
                    // Calculate direction vector
                    const dx = toCenter.x - fromCenter.x;
                    const dy = toCenter.y - fromCenter.y;
                    
                    // Determine which edge to connect to based on direction
                    let connectionPoint = { x: 0, y: 0 };
                    
                    if (Math.abs(dx) > Math.abs(dy)) {
                        // Horizontal connection
                        if (dx > 0) {
                            // Connect from right edge
                            connectionPoint = {
                                x: fromTable.position_x + fromTable.width,
                                y: fromTable.position_y + fromTable.height / 2
                            };
                        } else {
                            // Connect from left edge
                            connectionPoint = {
                                x: fromTable.position_x,
                                y: fromTable.position_y + fromTable.height / 2
                            };
                        }
                    } else {
                        // Vertical connection
                        if (dy > 0) {
                            // Connect from bottom edge
                            connectionPoint = {
                                x: fromTable.position_x + fromTable.width / 2,
                                y: fromTable.position_y + fromTable.height
                            };
                        } else {
                            // Connect from top edge
                            connectionPoint = {
                                x: fromTable.position_x + fromTable.width / 2,
                                y: fromTable.position_y
                            };
                        }
                    }
                    
                    return connectionPoint;
                },
                
                createCurvedPath(start, end, sourceTable, targetTable) {
                    const dx = end.x - start.x;
                    const dy = end.y - start.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    
                    // For very short distances, use straight line
                    if (distance < 50) {
                        return `M ${start.x},${start.y} L ${end.x},${end.y}`;
                    }
                    
                    // Enhanced path routing to avoid tables
                    const midX = (start.x + end.x) / 2;
                    const midY = (start.y + end.y) / 2;
                    
                    // Calculate offset to route around tables
                    const offset = this.calculateRouteOffset(start, end, sourceTable, targetTable);
                    
                    // Create multi-segment path for better routing
                    if (Math.abs(dx) > Math.abs(dy)) {
                        // Horizontal routing
                        const cp1x = start.x + (dx > 0 ? 100 : -100);
                        const cp1y = start.y + offset;
                        const cp2x = end.x + (dx > 0 ? -100 : 100);
                        const cp2y = end.y + offset;
                        
                        return `M ${start.x},${start.y} C ${cp1x},${cp1y} ${cp2x},${cp2y} ${end.x},${end.y}`;
                    } else {
                        // Vertical routing
                        const cp1x = start.x + offset;
                        const cp1y = start.y + (dy > 0 ? 100 : -100);
                        const cp2x = end.x + offset;
                        const cp2y = end.y + (dy > 0 ? -100 : 100);
                        
                        return `M ${start.x},${start.y} C ${cp1x},${cp1y} ${cp2x},${cp2y} ${end.x},${end.y}`;
                    }
                },
                
                calculateRouteOffset(start, end, sourceTable, targetTable) {
                    // Check if direct path would intersect with other tables
                    const directPath = { start, end };
                    let offset = 0;
                    
                    // Simple offset calculation to avoid overlapping tables
                    const otherTables = this.tables.filter(t => 
                        t.id !== sourceTable.id && t.id !== targetTable.id
                    );
                    
                    for (const table of otherTables) {
                        const tableCenter = {
                            x: table.position_x + table.width / 2,
                            y: table.position_y + table.height / 2
                        };
                        
                        const pathCenter = {
                            x: (start.x + end.x) / 2,
                            y: (start.y + end.y) / 2
                        };
                        
                        const distanceToTable = Math.sqrt(
                            Math.pow(pathCenter.x - tableCenter.x, 2) + 
                            Math.pow(pathCenter.y - tableCenter.y, 2)
                        );
                        
                        // If path is too close to a table, add offset
                        if (distanceToTable < 150) {
                            offset = Math.max(offset, 60);
                        }
                    }
                    
                    return offset;
                },
                
                getRelationshipMarker(relationship) {
                    switch(relationship.type) {
                        case 'one_to_one':
                            return 'url(#arrow-one)';
                        case 'one_to_many':
                            return 'url(#arrow-many)';
                        case 'many_to_one':
                            return 'url(#arrow-one)';
                        case 'many_to_many':
                            return 'url(#arrow-many-many)';
                        default:
                            return 'url(#arrow-one)';
                    }
                },
                
                getRelationshipTypeSymbol(relationship) {
                    switch(relationship.type) {
                        case 'one_to_one':
                            return '1:1';
                        case 'one_to_many':
                            return '1:N';
                        case 'many_to_one':
                            return 'N:1';
                        case 'many_to_many':
                            return 'N:N';
                        default:
                            return '1:1';
                    }
                },
                
                getRelationshipLabelPos(relationship) {
                    const sourceTable = this.tables.find(t => t.id === relationship.source_table_id);
                    const targetTable = this.tables.find(t => t.id === relationship.target_table_id);
                    
                    if (!sourceTable || !targetTable) return { x: 0, y: 0 };
                    
                    const sourcePoint = this.getOptimalConnectionPoint(sourceTable, targetTable);
                    const targetPoint = this.getOptimalConnectionPoint(targetTable, sourceTable);
                    
                    // Position label at the midpoint of the curve
                    return {
                        x: (sourcePoint.x + targetPoint.x) / 2,
                        y: (sourcePoint.y + targetPoint.y) / 2
                    };
                },
                
                // Legacy functions for backward compatibility
                getRelationshipStart(relationship) {
                    const sourceTable = this.tables.find(t => t.id === relationship.source_table_id);
                    if (!sourceTable) return { x: 0, y: 0 };
                    
                    return {
                        x: sourceTable.position_x + sourceTable.width,
                        y: sourceTable.position_y + sourceTable.height / 2
                    };
                },
                
                getRelationshipEnd(relationship) {
                    const targetTable = this.tables.find(t => t.id === relationship.target_table_id);
                    if (!targetTable) return { x: 0, y: 0 };
                    
                    return {
                        x: targetTable.position_x,
                        y: targetTable.position_y + targetTable.height / 2
                    };
                },
                
                // Get relationships for a specific table
                getTableRelationships(table) {
                    if (!table || !table.id) return [];
                    
                    return this.relationships.filter(rel => 
                        rel.source_table_id === table.id || 
                        rel.target_table_id === table.id
                    );
                },
                
                // Get the name of the table on the other end of a relationship
                getRelatedTableName(relationship, currentTable) {
                    if (!relationship || !currentTable) return '';
                    
                    // If current table is source, return target name
                    if (relationship.source_table_id === currentTable.id) {
                        const targetTable = this.tables.find(t => t.id === relationship.target_table_id);
                        return targetTable ? targetTable.name : '';
                    } 
                    // If current table is target, return source name
                    else {
                        const sourceTable = this.tables.find(t => t.id === relationship.source_table_id);
                        return sourceTable ? sourceTable.name : '';
                    }
                },
                
                // Export functions
                exportSQL() {
                    $wire.exportSQL();
                },
                
                // Smart Layout function
                applySmartLayout() {
                    if (!this.tables || this.tables.length === 0) {
                        return;
                    }
                    
                    // Build relationship graph
                    const graph = this.buildRelationshipGraph();
                    
                    // Separate connected and isolated tables
                    const connectedTables = [];
                    const isolatedTables = [];
                    
                    this.tables.forEach(table => {
                        const connections = graph[table.name] || [];
                        if (connections.length > 0) {
                            connectedTables.push(table);
                        } else {
                            isolatedTables.push(table);
                        }
                    });
                    
                    // Apply layout
                    this.layoutConnectedTables(connectedTables, graph);
                    this.layoutIsolatedTables(isolatedTables, connectedTables.length > 0);
                },
                
                buildRelationshipGraph() {
                    const graph = {};
                    
                    // Initialize all tables
                    this.tables.forEach(table => {
                        graph[table.name] = [];
                    });
                    
                    // Add relationships
                    this.relationships.forEach(rel => {
                        const sourceTable = this.tables.find(t => t.id === rel.source_table_id);
                        const targetTable = this.tables.find(t => t.id === rel.target_table_id);
                        
                        if (sourceTable && targetTable) {
                            if (!graph[sourceTable.name].includes(targetTable.name)) {
                                graph[sourceTable.name].push(targetTable.name);
                            }
                            if (!graph[targetTable.name].includes(sourceTable.name)) {
                                graph[targetTable.name].push(sourceTable.name);
                            }
                        }
                    });
                    
                    return graph;
                },
                
                layoutConnectedTables(connectedTables, graph) {
                    if (connectedTables.length === 0) return;
                    
                    // Find most connected table
                    let mostConnected = connectedTables[0];
                    let maxConnections = (graph[mostConnected.name] || []).length;
                    
                    connectedTables.forEach(table => {
                        const connections = (graph[table.name] || []).length;
                        if (connections > maxConnections) {
                            mostConnected = table;
                            maxConnections = connections;
                        }
                    });
                    
                    // Position central table
                    const centerX = 300;
                    const centerY = 300;
                    mostConnected.position_x = centerX;
                    mostConnected.position_y = centerY;
                    
                    // Position other tables in circles around the central one
                    const positioned = new Set([mostConnected.name]);
                    let level = 1;
                    let queue = [mostConnected];
                    
                    while (queue.length > 0 && positioned.size < connectedTables.length) {
                        const currentLevel = [];
                        const radius = 250 * level;
                        
                        queue.forEach(currentTable => {
                            const connections = graph[currentTable.name] || [];
                            const unpositioned = connections.filter(name => !positioned.has(name));
                            
                            unpositioned.forEach((tableName, index) => {
                                const table = connectedTables.find(t => t.name === tableName);
                                if (table && !positioned.has(tableName)) {
                                    const angle = (index / Math.max(unpositioned.length, 4)) * 2 * Math.PI;
                                    table.position_x = Math.max(50, Math.min(1800, centerX + radius * Math.cos(angle)));
                                    table.position_y = Math.max(50, Math.min(1200, centerY + radius * Math.sin(angle)));
                                    positioned.add(tableName);
                                    currentLevel.push(table);
                                }
                            });
                        });
                        
                        queue = currentLevel;
                        level++;
                        if (level > 4) break; // Prevent infinite loop
                    }
                },
                
                layoutIsolatedTables(isolatedTables, hasConnectedTables) {
                    if (isolatedTables.length === 0) return;
                    
                    const startX = hasConnectedTables ? 1400 : 50;
                    const startY = 50;
                    const spacing = 280;
                    
                    let currentX = startX;
                    let currentY = startY;
                    let maxY = startY;
                    
                    isolatedTables.forEach((table, index) => {
                        table.position_x = currentX;
                        table.position_y = currentY;
                        
                        currentY += spacing;
                        maxY = Math.max(maxY, currentY);
                        
                        // Move to next column if needed
                        if (currentY > 1000) {
                            currentX += spacing;
                            currentY = startY;
                        }
                    });
                }
            }
        }
    </script>

        <!-- Styles -->
        <style>
            .erd-designer-container {
                user-select: none;
            }

            .erd-designer-container * {
                box-sizing: border-box;
            }
        </style>
    </div>
</x-filament-panels::page>
