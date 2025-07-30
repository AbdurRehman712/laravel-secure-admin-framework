<x-filament-panels::page>
    <script>
        window.erdDesigner = function() {
            return {
                zoomLevel: 1.0,
                panX: 0,
                panY: 0,
                showGrid: true,
                showRelationships: true,
                gridSize: 20,
                isPanning: false,
                isDragging: false,
                dragTable: null,
                dragOffset: { x: 0, y: 0 },
                selectedTable: null,
                lastPanX: 0,
                lastPanY: 0,
                saveTimeout: null,
                tables: @js($tables ?? []),
                relationships: @js($relationships ?? []),
                showTableDialog: false,
                showFieldDialog: false,
                showRelationshipDialog: false,
                editingTable: null,
                editingField: null,
                editingRelationship: {
                    source_table_id: null,
                    target_table_id: null,
                    source_field_id: null,
                    target_field_id: null,
                    type: 'hasMany',
                    foreign_key: '',
                    relationship_name: '',
                    local_key: '',
                    pivot_table: ''
                },
                fieldTypes: [
                    'varchar', 'text', 'longtext', 'bigint', 'int', 'tinyint',
                    'decimal', 'float', 'double', 'timestamp', 'datetime', 'date',
                    'time', 'boolean', 'json', 'enum'
                ],
                relationshipTypes: [
                    { value: 'belongsTo', label: 'belongsTo (Many to One)' },
                    { value: 'hasOne', label: 'hasOne (One to One)' },
                    { value: 'hasMany', label: 'hasMany (One to Many)' },
                    { value: 'belongsToMany', label: 'belongsToMany (Many to Many)' },
                    { value: 'hasOneThrough', label: 'hasOneThrough' },
                    { value: 'hasManyThrough', label: 'hasManyThrough' },
                    { value: 'morphTo', label: 'morphTo (Polymorphic)' },
                    { value: 'morphOne', label: 'morphOne (Polymorphic)' },
                    { value: 'morphMany', label: 'morphMany (Polymorphic)' },
                    { value: 'morphToMany', label: 'morphToMany (Polymorphic)' }
                ],
                fieldTypes: [
                    { value: 'string', label: 'String (Text)', hasLength: true },
                    { value: 'text', label: 'Text (Long Text)', hasLength: false },
                    { value: 'longtext', label: 'Long Text', hasLength: false },
                    { value: 'integer', label: 'Integer (Number)', hasLength: false },
                    { value: 'bigint', label: 'Big Integer', hasLength: false },
                    { value: 'decimal', label: 'Decimal (Money/Float)', hasLength: true },
                    { value: 'float', label: 'Float', hasLength: false },
                    { value: 'double', label: 'Double', hasLength: false },
                    { value: 'boolean', label: 'Boolean (Yes/No)', hasLength: false },
                    { value: 'date', label: 'Date', hasLength: false },
                    { value: 'datetime', label: 'Date & Time', hasLength: false },
                    { value: 'timestamp', label: 'Timestamp', hasLength: false },
                    { value: 'time', label: 'Time', hasLength: false },
                    { value: 'json', label: 'JSON', hasLength: false },
                    { value: 'enum', label: 'Enum (Select Options)', hasLength: false },
                    { value: 'varchar', label: 'Varchar', hasLength: true },
                    { value: 'char', label: 'Char', hasLength: true },
                    { value: 'binary', label: 'Binary', hasLength: true },
                    { value: 'varbinary', label: 'Varbinary', hasLength: true },
                    { value: 'tinyint', label: 'Tiny Integer', hasLength: false },
                    { value: 'smallint', label: 'Small Integer', hasLength: false },
                    { value: 'mediumint', label: 'Medium Integer', hasLength: false },
                    { value: 'year', label: 'Year', hasLength: false }
                ],
                
                init() {
                    console.log('ERD Designer initialized');
                    console.log('Tables count:', this.tables.length);
                    console.log('Relationships count:', this.relationships.length);
                    console.log('Tables data:', this.tables);
                    console.log('Relationships data:', this.relationships);

                    // Listen for Livewire updates
                    this.$wire.$on('dataUpdated', (data) => {
                        console.log('Livewire data updated:', data);
                        this.refreshCanvasData(data);
                    });

                    // Check if tables have saved positions before applying auto-layout
                    const hasSavedPositions = this.hasSavedPositions();

                    if (hasSavedPositions) {
                        console.log('Tables have saved positions, skipping auto-layout');
                        this.loadSavedPositions();
                        // Just auto-fit the zoom without changing positions
                        this.$nextTick(() => {
                            this.autoFitZoom();
                        });
                    } else {
                        console.log('No saved positions found, applying auto-layout');
                        this.autoLayoutAndFit();
                    }

                    // Run overlap detection after initial setup
                    this.$nextTick(() => {
                        this.detectAndResolveOverlaps();
                        console.log('After layout and overlap resolution, first table:', this.tables[0]);
                    });
                },
                
                autoLayoutTables() {
                    // Calculate actual table heights first
                    this.tables.forEach((table) => {
                        const fieldCount = table.fields?.length || 0;
                        table.width = 280;
                        table.height = Math.max(fieldCount * 35 + 120, 250);
                    });

                    // Use the new smart auto-layout system
                    this.autoLayoutAndFit();
                },

                layoutTablesHierarchically() {
                    // Dynamic auto-adjustment based on canvas size and relationships
                    this.createSmartAutoLayout();
                },

                createSmartAutoLayout() {
                    if (this.tables.length === 0) return;

                    // Get canvas dimensions for dynamic sizing
                    const canvas = this.$refs.canvas;
                    const canvasWidth = canvas ? canvas.clientWidth : 1200;
                    const canvasHeight = canvas ? canvas.clientHeight : 900;

                    // Improved compact layout parameters
                    const padding = 40;
                    const baseTableWidth = 260;
                    const minTableHeight = 140;
                    const spacingX = 60; // Reduced horizontal spacing
                    const spacingY = 80; // Reduced vertical spacing

                    // Calculate optimal grid for compact arrangement
                    const tableCount = this.tables.length;
                    let cols = 3; // Default to 3 columns for better screen usage

                    if (tableCount <= 2) cols = 2;
                    else if (tableCount <= 6) cols = 3;
                    else if (tableCount <= 12) cols = 4;
                    else cols = Math.min(5, Math.ceil(Math.sqrt(tableCount)));

                    // Ensure we don't exceed canvas width
                    const maxPossibleCols = Math.floor((canvasWidth - padding * 2) / (baseTableWidth + spacingX));
                    cols = Math.min(cols, Math.max(1, maxPossibleCols));

                    console.log(`Smart Layout: Canvas ${canvasWidth}x${canvasHeight}, ${tableCount} tables, ${cols} cols`);

                    // Apply compact grid layout
                    this.applyCompactGridLayout(padding, baseTableWidth, minTableHeight, spacingX, spacingY, cols);
                },

                applyCompactGridLayout(padding, baseWidth, minHeight, spacingX, spacingY, cols) {
                    // Sort tables by name for consistent layout
                    const sortedTables = [...this.tables].sort((a, b) =>
                        (a.display_name || a.name).localeCompare(b.display_name || b.name)
                    );

                    sortedTables.forEach((table, index) => {
                        const col = index % cols;
                        const row = Math.floor(index / cols);

                        // Calculate dynamic height based on field count
                        const fieldCount = table.fields ? table.fields.length : 0;
                        const dynamicHeight = Math.max(minHeight, 100 + (fieldCount * 22));

                        // Position table in grid
                        table.position_x = padding + col * (baseWidth + spacingX);
                        table.position_y = padding + row * (dynamicHeight + spacingY);
                        table.width = baseWidth;
                        table.height = dynamicHeight;

                        // Save position to backend
                        this.$wire.call('saveTablePosition', table.id, table.position_x, table.position_y);
                    });

                    console.log(`Positioned ${sortedTables.length} tables in ${cols} columns`);
                },

                // Grid layout button handler
                applyGridLayout() {
                    this.createSmartAutoLayout();
                    this.$nextTick(() => {
                        this.autoFitZoom();
                    });
                },

                // Check if tables have meaningful saved positions
                hasSavedPositions() {
                    return this.tables.some(table =>
                        (table.position_x && table.position_x > 0) ||
                        (table.position_y && table.position_y > 0)
                    );
                },

                // Load saved positions without applying auto-layout
                loadSavedPositions() {
                    console.log('Loading saved positions for tables');
                    this.tables.forEach(table => {
                        console.log(`Table ${table.name}: position (${table.position_x}, ${table.position_y})`);
                    });
                },

                applyRelationshipAwareLayout(horizontalSpacing, verticalSpacing, maxCols, canvasWidth, canvasHeight) {
                    const padding = 60;
                    const startX = padding;
                    const startY = padding;

                    // Analyze relationships to group related tables
                    const relationshipGroups = this.analyzeTableRelationships();

                    // Position tables in groups with guaranteed spacing
                    let currentX = startX;
                    let currentY = startY;
                    let maxHeightInRow = 0;
                    let tablesInCurrentRow = 0;

                    // First, position tables that have relationships
                    const positionedTables = new Set();

                    relationshipGroups.forEach((group, groupIndex) => {
                        group.forEach((table, tableIndex) => {
                            if (positionedTables.has(table.id)) return;

                            // Check if we need to move to next row
                            if (tablesInCurrentRow >= maxCols) {
                                currentY += maxHeightInRow + verticalSpacing;
                                currentX = startX;
                                tablesInCurrentRow = 0;
                                maxHeightInRow = 0;
                            }

                            // Position the table
                            table.position_x = currentX;
                            table.position_y = currentY;
                            table.width = 280;
                            table.height = Math.max(table.fields?.length * 35 + 120, 250);

                            // Save position to backend
                            this.$wire.call('saveTablePosition', table.id, table.position_x, table.position_y);

                            positionedTables.add(table.id);

                            // Update positioning variables
                            currentX += horizontalSpacing;
                            maxHeightInRow = Math.max(maxHeightInRow, table.height);
                            tablesInCurrentRow++;

                            console.log(`Smart: ${table.name} positioned at (${table.position_x}, ${table.position_y})`);
                        });
                    });

                    // Position any remaining tables that don't have relationships
                    this.tables.forEach(table => {
                        if (positionedTables.has(table.id)) return;

                        // Check if we need to move to next row
                        if (tablesInCurrentRow >= maxCols) {
                            currentY += maxHeightInRow + verticalSpacing;
                            currentX = startX;
                            tablesInCurrentRow = 0;
                            maxHeightInRow = 0;
                        }

                        // Position the table
                        table.position_x = currentX;
                        table.position_y = currentY;
                        table.width = 280;
                        table.height = Math.max(table.fields?.length * 35 + 120, 250);

                        // Save position to backend
                        this.$wire.call('saveTablePosition', table.id, table.position_x, table.position_y);

                        // Update positioning variables
                        currentX += horizontalSpacing;
                        maxHeightInRow = Math.max(maxHeightInRow, table.height);
                        tablesInCurrentRow++;

                        console.log(`Smart (orphan): ${table.name} positioned at (${table.position_x}, ${table.position_y})`);
                    });
                },

                analyzeTableRelationships() {
                    // Group tables by their relationships
                    const groups = [];
                    const processedTables = new Set();

                    // Create groups based on relationships
                    this.relationships.forEach(rel => {
                        const sourceTable = this.tables.find(t => t.id === rel.source_table_id);
                        const targetTable = this.tables.find(t => t.id === rel.target_table_id);

                        if (sourceTable && targetTable) {
                            // Find existing group that contains either table
                            let existingGroup = groups.find(group =>
                                group.some(t => t.id === sourceTable.id || t.id === targetTable.id)
                            );

                            if (existingGroup) {
                                // Add tables to existing group
                                if (!existingGroup.some(t => t.id === sourceTable.id)) {
                                    existingGroup.push(sourceTable);
                                }
                                if (!existingGroup.some(t => t.id === targetTable.id)) {
                                    existingGroup.push(targetTable);
                                }
                            } else {
                                // Create new group
                                groups.push([sourceTable, targetTable]);
                            }

                            processedTables.add(sourceTable.id);
                            processedTables.add(targetTable.id);
                        }
                    });

                    // Add isolated tables as individual groups
                    this.tables.forEach(table => {
                        if (!processedTables.has(table.id)) {
                            groups.push([table]);
                        }
                    });

                    return groups;
                },



                autoFitZoom() {
                    if (this.tables.length === 0) return;

                    // Wait for DOM updates before calculating
                    this.$nextTick(() => {
                        // Calculate bounding box of all tables
                        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

                        this.tables.forEach(table => {
                            const tableWidth = table.width || 280;
                            const tableHeight = table.height || 250;
                            minX = Math.min(minX, table.position_x || 0);
                            minY = Math.min(minY, table.position_y || 0);
                            maxX = Math.max(maxX, (table.position_x || 0) + tableWidth);
                            maxY = Math.max(maxY, (table.position_y || 0) + tableHeight);
                        });

                        // Add padding around the content
                        const padding = 80;
                        const contentWidth = maxX - minX + (padding * 2);
                        const contentHeight = maxY - minY + (padding * 2);

                        // Get canvas dimensions
                        const canvas = this.$refs.canvas;
                        if (!canvas) return;

                        const canvasWidth = canvas.clientWidth;
                        const canvasHeight = canvas.clientHeight;

                        // Calculate zoom to fit with better margins
                        const zoomX = canvasWidth / contentWidth;
                        const zoomY = canvasHeight / contentHeight;
                        const optimalZoom = Math.min(zoomX, zoomY, 1.2); // Allow slight zoom in

                        // Apply zoom with comfortable margin (85% of optimal)
                        this.zoomLevel = Math.max(0.3, Math.min(1.0, optimalZoom * 0.85));

                        console.log(`Auto-fit zoom: ${(this.zoomLevel * 100).toFixed(0)}% (Content: ${contentWidth}x${contentHeight}, Canvas: ${canvasWidth}x${canvasHeight})`);
                    });
                },

                // Auto-layout and auto-fit when tables are loaded
                autoLayoutAndFit() {
                    this.layoutTablesHierarchically();
                    setTimeout(() => {
                        this.autoFitZoom();
                    }, 100); // Small delay to ensure layout is applied
                },

                // Simple grid layout
                arrangeInGrid() {
                    const tableWidth = 280;
                    const horizontalSpacing = 380; // Increased spacing to prevent overlaps
                    const verticalSpacing = 320;   // Increased spacing to prevent overlaps
                    const startX = 60;
                    const startY = 60;
                    const tablesPerRow = 3;

                    // Calculate actual table heights first
                    this.tables.forEach((table) => {
                        const fieldCount = table.fields?.length || 0;
                        table.width = tableWidth;
                        table.height = Math.max(fieldCount * 35 + 120, 250);
                    });

                    // Position tables in grid with guaranteed spacing
                    this.tables.forEach((table, index) => {
                        const row = Math.floor(index / tablesPerRow);
                        const col = index % tablesPerRow;

                        table.position_x = startX + (col * horizontalSpacing);
                        table.position_y = startY + (row * verticalSpacing);

                        // Save position to backend immediately
                        this.$wire.call('saveTablePosition', table.id, table.position_x, table.position_y);

                        console.log(`Grid: ${table.name} positioned at (${table.position_x}, ${table.position_y}) with height ${table.height}`);
                    });

                    // Auto-fit after arranging with delay to ensure positions are set
                    setTimeout(() => {
                        this.autoFitZoom();
                    }, 200);
                },
                
                zoomIn() { this.zoomLevel = Math.min(this.zoomLevel * 1.2, 3.0); },
                zoomOut() { this.zoomLevel = Math.max(this.zoomLevel / 1.2, 0.3); },
                resetZoom() { this.zoomLevel = 1.0; this.panX = 0; this.panY = 0; },
                
                startPan(event) {
                    if (event.target.closest('.absolute.bg-white')) return;
                    this.isPanning = true;
                    this.lastPanX = event.clientX;
                    this.lastPanY = event.clientY;
                },
                
                pan(event) {
                    if (!this.isPanning) return;
                    const deltaX = event.clientX - this.lastPanX;
                    const deltaY = event.clientY - this.lastPanY;
                    this.panX += deltaX;
                    this.panY += deltaY;
                    this.lastPanX = event.clientX;
                    this.lastPanY = event.clientY;
                },
                
                endPan() { this.isPanning = false; },
                
                handleWheel(event) {
                    if (event.deltaY < 0) this.zoomIn();
                    else this.zoomOut();
                },
                
                handleMouseMove(event) {
                    if (this.isPanning) this.pan(event);
                    else if (this.isDragging) this.dragTableMove(event);
                },
                
                handleMouseUp() {
                    this.endPan();
                    this.endDrag();

                    // Check for overlaps after any drag operation
                    if (this.isDragging) {
                        this.$nextTick(() => {
                            this.detectAndResolveOverlaps();
                        });
                    }
                },

                // Function to detect and resolve overlaps
                detectAndResolveOverlaps() {
                    const padding = 30; // Reduced padding for more compact layout
                    let hasOverlaps = true;
                    let iterations = 0;
                    const maxIterations = 8;

                    while (hasOverlaps && iterations < maxIterations) {
                        hasOverlaps = false;

                        for (let i = 0; i < this.tables.length; i++) {
                            for (let j = i + 1; j < this.tables.length; j++) {
                                const table1 = this.tables[i];
                                const table2 = this.tables[j];

                                // Check if tables overlap
                                if (this.tablesOverlap(table1, table2, padding)) {
                                    hasOverlaps = true;
                                    // Move table2 to resolve overlap
                                    this.resolveOverlap(table1, table2, padding);
                                }
                            }
                        }
                        iterations++;
                    }

                    console.log(`Overlap resolution completed in ${iterations} iterations`);
                },

                tablesOverlap(table1, table2, padding = 0) {
                    return !(table1.position_x + table1.width + padding < table2.position_x ||
                            table1.position_x > table2.position_x + table2.width + padding ||
                            table1.position_y + table1.height + padding < table2.position_y ||
                            table1.position_y > table2.position_y + table2.height + padding);
                },

                resolveOverlap(table1, table2, padding) {
                    // Calculate overlap amounts
                    const overlapX = Math.min(table1.position_x + table1.width + padding - table2.position_x,
                                            table2.position_x + table2.width + padding - table1.position_x);
                    const overlapY = Math.min(table1.position_y + table1.height + padding - table2.position_y,
                                            table2.position_y + table2.height + padding - table1.position_y);

                    // Move in the direction with less overlap, but prefer horizontal movement for better relationship flow
                    if (overlapX <= overlapY) {
                        // Move horizontally
                        if (table1.position_x < table2.position_x) {
                            table2.position_x = table1.position_x + table1.width + padding;
                        } else {
                            table2.position_x = Math.max(30, table1.position_x - table2.width - padding);
                        }
                    } else {
                        // Move vertically
                        if (table1.position_y < table2.position_y) {
                            table2.position_y = table1.position_y + table1.height + padding;
                        } else {
                            table2.position_y = Math.max(30, table1.position_y - table2.height - padding);
                        }
                    }

                    // Ensure table stays within reasonable bounds
                    table2.position_x = Math.max(30, Math.min(table2.position_x, 1200));
                    table2.position_y = Math.max(30, table2.position_y);
                },

                startDrag(event, table) {
                    event.stopPropagation();
                    event.preventDefault();

                    console.log('Starting drag for table:', table.name);

                    this.isDragging = true;
                    this.dragTable = table;
                    this.selectTable(table);

                    // Find the table element
                    const tableElement = event.target.closest('.absolute.bg-white') ||
                                       event.target.closest('[class*="absolute"]');

                    if (tableElement) {
                        const rect = tableElement.getBoundingClientRect();
                        this.dragOffset = {
                            x: event.clientX - rect.left,
                            y: event.clientY - rect.top
                        };
                        console.log('Drag offset:', this.dragOffset);
                    } else {
                        this.dragOffset = { x: 50, y: 50 }; // Default offset
                        console.log('Table element not found, using default offset');
                    }
                },
                
                dragTableMove(event) {
                    if (!this.isDragging || !this.dragTable) return;

                    // Find the canvas container - try multiple selectors
                    let canvasElement = this.$el.querySelector('.relative.bg-gray-50');
                    if (!canvasElement) {
                        canvasElement = this.$el.querySelector('.relative');
                    }
                    if (!canvasElement) {
                        canvasElement = this.$el.querySelector('[x-data]');
                    }
                    if (!canvasElement) {
                        canvasElement = this.$el;
                    }

                    const canvasRect = canvasElement.getBoundingClientRect();

                    // Calculate new position with simpler math
                    const newX = Math.max(0, event.clientX - canvasRect.left - this.dragOffset.x);
                    const newY = Math.max(0, event.clientY - canvasRect.top - this.dragOffset.y);

                    this.dragTable.position_x = newX;
                    this.dragTable.position_y = newY;

                    // Snap to grid if enabled
                    if (this.showGrid) {
                        this.dragTable.position_x = Math.round(this.dragTable.position_x / this.gridSize) * this.gridSize;
                        this.dragTable.position_y = Math.round(this.dragTable.position_y / this.gridSize) * this.gridSize;
                    }

                    // Force Alpine to re-evaluate relationship positions
                    this.$nextTick(() => {
                        // This will trigger reactive updates for relationship lines
                    });

                    console.log(`Dragging ${this.dragTable.name} to (${this.dragTable.position_x}, ${this.dragTable.position_y})`);
                },
                
                endDrag() {
                    if (this.isDragging && this.dragTable) {
                        console.log(`Saving table position: ${this.dragTable.name} at (${this.dragTable.position_x}, ${this.dragTable.position_y})`);

                        // Save position to backend with proper rounding
                        const x = Math.round(this.dragTable.position_x);
                        const y = Math.round(this.dragTable.position_y);
                        const tableId = this.dragTable.id; // Store ID before clearing reference
                        const tableName = this.dragTable.name; // Store name for logging

                        this.$wire.call('saveTablePosition', tableId, x, y).then(() => {
                            console.log(`Position saved successfully for table ${tableId} (${tableName})`);
                        }).catch((error) => {
                            console.error(`Failed to save table position for ${tableName}:`, error);
                        });

                        // Update local position to rounded values
                        this.dragTable.position_x = x;
                        this.dragTable.position_y = y;

                        // Check for overlaps after dragging
                        this.$nextTick(() => {
                            this.detectAndResolveOverlaps();
                        });
                    }
                    this.isDragging = false;
                    this.dragTable = null;
                },
                
                selectTable(table) { this.selectedTable = table; },
                
                getRelationshipStart(relationship) {
                    const sourceTable = this.tables.find(t => t.id === relationship.source_table_id);
                    if (!sourceTable) {
                        console.log('Source table not found for relationship:', relationship);
                        return { x: 0, y: 0 };
                    }

                    const tableWidth = sourceTable.width || 280;
                    const tableHeight = sourceTable.height || 250;

                    // Try to find the specific field if source_field_id exists
                    if (relationship.source_field_id && sourceTable.fields) {
                        const fieldIndex = sourceTable.fields.findIndex(f => f.id === relationship.source_field_id);
                        if (fieldIndex >= 0) {
                            const fieldY = sourceTable.position_y + 60 + (fieldIndex * 24) + 12; // Header + field offset
                            return {
                                x: sourceTable.position_x + tableWidth,
                                y: fieldY
                            };
                        }
                    }

                    // Default to table center if no specific field
                    return {
                        x: sourceTable.position_x + tableWidth,
                        y: sourceTable.position_y + (tableHeight / 2)
                    };
                },
                
                getRelationshipEnd(relationship) {
                    const targetTable = this.tables.find(t => t.id === relationship.target_table_id);
                    if (!targetTable) {
                        console.log('Target table not found for relationship:', relationship);
                        return { x: 0, y: 0 };
                    }

                    const tableHeight = targetTable.height || 250;

                    // Try to find the specific field if target_field_id exists
                    if (relationship.target_field_id && targetTable.fields) {
                        const fieldIndex = targetTable.fields.findIndex(f => f.id === relationship.target_field_id);
                        if (fieldIndex >= 0) {
                            const fieldY = targetTable.position_y + 60 + (fieldIndex * 24) + 12; // Header + field offset
                            return {
                                x: targetTable.position_x,
                                y: fieldY
                            };
                        }
                    }

                    // Default to table center if no specific field
                    return {
                        x: targetTable.position_x,
                        y: targetTable.position_y + (tableHeight / 2)
                    };
                },
                
                getMarkerEnd(relationship) {
                    return relationship.cardinality_target === 'zero_or_many' || relationship.cardinality_target === 'one_or_many' 
                        ? 'url(#arrowhead-many)' : 'url(#arrowhead-one)';
                },
                
                getRelationshipLabel(relationship) {
                    // Get table names
                    const sourceTable = this.tables.find(t => t.id === relationship.source_table_id);
                    const targetTable = this.tables.find(t => t.id === relationship.target_table_id);

                    if (!sourceTable || !targetTable) {
                        return {
                            line1: 'Unknown',
                            line2: 'Relationship',
                            single: 'Unknown Relationship'
                        };
                    }

                    // Clean table names for better display - capitalize and remove table prefixes
                    const getCleanName = (tableName) => {
                        let name = (tableName || '').replace(/^table_?/i, '').replace(/_/g, ' ');
                        return name.charAt(0).toUpperCase() + name.slice(1);
                    };

                    const sourceName = getCleanName(sourceTable.display_name || sourceTable.name);
                    const targetName = getCleanName(targetTable.display_name || targetTable.name);

                    // Create meaningful relationship descriptions based on table relationships
                    // Default to belongsTo pattern for foreign key relationships
                    let relationshipDescription = '';
                    
                    // Detect relationship pattern based on foreign key field
                    const sourceFieldName = relationship.source_field_id ? 
                        sourceTable.fields?.find(f => f.id === relationship.source_field_id)?.name : '';
                    
                    if (sourceFieldName && sourceFieldName.includes('_id')) {
                        // This is a belongsTo relationship (many to one)
                        const referencedEntity = sourceFieldName.replace('_id', '').replace(/_/g, ' ');
                        const cleanReferencedEntity = referencedEntity.charAt(0).toUpperCase() + referencedEntity.slice(1);
                        
                        // Use singular forms for more natural language
                        const sourceSingular = this.makeSingular(sourceName);
                        const targetSingular = this.makeSingular(targetName);
                        
                        relationshipDescription = `${sourceSingular} belongsTo`;
                        
                        return {
                            line1: relationshipDescription,
                            line2: targetSingular,
                            single: `${sourceSingular} belongsTo ${targetSingular}`
                        };
                    } else {
                        // Determine relationship type based on cardinality
                        const cardinalityMap = {
                            'exactly_one': '1', 'zero_or_one': '0..1',
                            'zero_or_many': '0..*', 'one_or_many': '1..*'
                        };

                        const sourceCard = cardinalityMap[relationship.cardinality_source] || '1';
                        const targetCard = cardinalityMap[relationship.cardinality_target] || '*';

                        const sourceSingular = this.makeSingular(sourceName);
                        const targetSingular = this.makeSingular(targetName);

                        if (sourceCard === '1' && (targetCard === '0..*' || targetCard === '1..*')) {
                            relationshipDescription = `${sourceSingular} hasMany`;
                            return {
                                line1: relationshipDescription,
                                line2: targetName, // Use plural for hasMany
                                single: `${sourceSingular} hasMany ${targetName}`
                            };
                        } else if (sourceCard === '1' && (targetCard === '1' || targetCard === '0..1')) {
                            relationshipDescription = `${sourceSingular} hasOne`;
                            return {
                                line1: relationshipDescription,
                                line2: targetSingular,
                                single: `${sourceSingular} hasOne ${targetSingular}`
                            };
                        } else if ((sourceCard === '0..*' || sourceCard === '1..*') && (targetCard === '0..*' || targetCard === '1..*')) {
                            relationshipDescription = `${sourceName} belongsToMany`;
                            return {
                                line1: relationshipDescription,
                                line2: targetName,
                                single: `${sourceName} belongsToMany ${targetName}`
                            };
                        } else {
                            // Default fallback
                            relationshipDescription = `${sourceSingular} relates to`;
                            return {
                                line1: relationshipDescription,
                                line2: targetSingular,
                                single: `${sourceSingular} relates to ${targetSingular}`
                            };
                        }
                    }
                },

                // Helper function to make table names singular
                makeSingular(name) {
                    if (!name) return '';
                    
                    const lowerName = name.toLowerCase();
                    
                    // Handle common plural patterns
                    if (lowerName.endsWith('ies')) {
                        return name.slice(0, -3) + 'y';
                    } else if (lowerName.endsWith('es') && !lowerName.endsWith('ses')) {
                        return name.slice(0, -2);
                    } else if (lowerName.endsWith('s') && !lowerName.endsWith('ss')) {
                        return name.slice(0, -1);
                    }
                    
                    return name;
                },
                
                editTable(table) {
                    // Call Livewire method to populate the form and show modal
                    this.$wire.call('editTable', table.id);
                    console.log('Edit table:', table.name);
                },



                editField(table, field) {
                    // Use Livewire method for consistency
                    this.$wire.call('editField', field.id);
                },

                addNewField(table) {
                    // Use Livewire method for consistency
                    this.$wire.call('addNewFieldToTable', table.id);
                },

                saveFieldChanges() {
                    if (this.editingField) {
                        const fieldData = {
                            name: this.editingField.name,
                            type: this.editingField.type,
                            length: this.editingField.length,
                            is_nullable: this.editingField.is_nullable,
                            is_primary: this.editingField.is_primary,
                            is_foreign_key: this.editingField.is_foreign_key,
                            default_value: this.editingField.default_value
                        };

                        if (this.editingField.id) {
                            // Update existing field
                            this.$wire.call('updateField', this.editingField.id, fieldData);
                        } else {
                            // Create new field
                            this.$wire.call('createField', this.editingField.table_id, fieldData);
                        }

                        this.showFieldDialog = false;
                        this.editingField = null;
                    }
                },

                addNewField(table) {
                    this.editingField = {
                        id: null,
                        table_id: table.id,
                        name: 'new_field',
                        type: 'varchar',
                        length: 255,
                        is_nullable: true,
                        is_primary: false,
                        is_foreign_key: false,
                        default_value: ''
                    };
                    this.showFieldDialog = true;
                },

                deleteFieldConfirm(fieldId) {
                    if (confirm('Are you sure you want to delete this field?')) {
                        this.$wire.call('deleteField', fieldId);
                    }
                },

                // Relationship management
                openRelationshipDialog() {
                    this.editingRelationship = {
                        id: null,
                        type: 'belongsTo',
                        source_table_id: '',
                        target_table_id: '',
                        source_field_id: '',
                        target_field_id: '',
                        foreign_key: '',
                        local_key: 'id',
                        pivot_table: '',
                        relationship_name: ''
                    };
                    this.showRelationshipDialog = true;
                },

                saveRelationship() {
                    if (this.editingRelationship) {
                        if (this.editingRelationship.id) {
                            // Update existing relationship
                            this.$wire.call('updateRelationship', this.editingRelationship.id, this.editingRelationship);
                        } else {
                            // Create new relationship
                            this.$wire.call('createRelationship', this.editingRelationship);
                        }
                        this.showRelationshipDialog = false;
                        this.editingRelationship = null;
                    }
                },

                deleteRelationship(relationshipId) {
                    if (confirm('Are you sure you want to delete this relationship?')) {
                        this.$wire.call('deleteRelationship', relationshipId);
                    }
                },

                getTableFields(tableId) {
                    const table = this.tables.find(t => t.id == tableId);
                    return table ? table.fields || [] : [];
                },

                // Table form field management
                addNewFieldToForm() {
                    if (!this.$wire.tableForm.fields) {
                        this.$wire.tableForm.fields = [];
                    }

                    this.$wire.tableForm.fields.push({
                        name: '',
                        type: 'varchar',
                        length: 255,
                        is_nullable: true,
                        is_primary: false,
                        is_foreign_key: false,
                        is_auto_increment: false,
                        default_value: '',
                        validation: '',
                        enum_options: ''
                    });
                },

                removeFieldFromForm(index) {
                    if (this.$wire.tableForm.fields && this.$wire.tableForm.fields.length > index) {
                        this.$wire.tableForm.fields.splice(index, 1);
                    }
                },

                deleteTable(table) {
                    if (confirm(`Are you sure you want to delete table "${table.display_name || table.name}"?`)) {
                        console.log('Delete table:', table.id);
                        // Call Livewire method to delete table
                        this.$wire.call('deleteTable', table.id);
                    }
                },

                addField(table) {
                    console.log('Add field to table:', table.id);
                    // Call Livewire method to add field
                    this.$wire.call('addFieldToTable', table.id);
                },
                updateTable(table) {
                    clearTimeout(this.updateTimeout);
                    this.updateTimeout = setTimeout(() => {
                        console.log('Update table:', table.id, {
                            name: table.name,
                            display_name: table.display_name,
                            description: table.description,
                            position_x: table.position_x,
                            position_y: table.position_y
                        });
                    }, 500);
                },

                // Refresh canvas data when backend updates
                refreshCanvasData(data) {
                    console.log('Refreshing canvas data:', data);
                    if (data && data.tables) {
                        this.tables = data.tables;
                        console.log('Updated tables:', this.tables.length);
                    }
                    if (data && data.relationships) {
                        this.relationships = data.relationships;
                        console.log('Updated relationships:', this.relationships.length);
                    }

                    // Force re-render of the canvas
                    this.$nextTick(() => {
                        console.log('Canvas data refreshed');
                    });
                }
            }
        }
    </script>

    <div class="erd-designer-container" x-data="erdDesigner()" x-init="init()" @data-updated.window="refreshCanvasData($event.detail)"
        <!-- Toolbar -->
        <div class="bg-white border-b border-gray-200 p-4 mb-4 rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $project->name ?? 'ERD Project' }}</h2>
                    <p class="text-gray-600 text-sm">{{ $project->description ?? 'Database design project' }}</p>
                </div>

                <div class="flex items-center space-x-2">
                    <button wire:click="addTable" class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        Add Table
                    </button>
                    <button @click="openRelationshipDialog()" class="bg-purple-600 text-white px-3 py-2 rounded-lg hover:bg-purple-700 transition-colors text-sm">
                        Add Relationship
                    </button>
                    <button @click="applyGridLayout()" class="bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                        Smart Layout
                    </button>
                    <button wire:click="importSQL" class="bg-purple-600 text-white px-3 py-2 rounded-lg hover:bg-purple-700 transition-colors text-sm">
                        Import SQL
                    </button>
                    <button wire:click="exportSQL" class="bg-orange-600 text-white px-3 py-2 rounded-lg hover:bg-orange-700 transition-colors text-sm">
                        Export SQL
                    </button>
                    
                    <!-- Project Management Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="bg-gray-600 text-white px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm flex items-center space-x-1">
                            <span>Project</span>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                            <div class="py-1">
                                <button wire:click="showProjectSettings" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Project Settings
                                </button>
                                
                                <button wire:click="clearProjectData" onclick="return confirm('Are you sure you want to clear all tables and relationships? This cannot be undone.')" class="block w-full text-left px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50">
                                    <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 102 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 10-2 0v3a1 1 0 102 0V9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Clear Project Data
                                </button>
                                
                                <div class="border-t border-gray-100"></div>
                                
                                <button wire:click="deleteProject" onclick="return confirm('Are you sure you want to delete this entire project? This action cannot be undone!')" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                    <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm5 4a1 1 0 102 0v6a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Delete Project
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Canvas Controls -->
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center space-x-4">
                    <!-- Zoom Controls -->
                    <div class="flex items-center space-x-2 bg-gray-100 rounded-lg p-2">
                        <button @click="zoomOut" class="p-1 hover:bg-gray-200 rounded">-</button>
                        <span class="text-sm font-medium min-w-[60px] text-center" x-text="Math.round(zoomLevel * 100) + '%'"></span>
                        <button @click="zoomIn" class="p-1 hover:bg-gray-200 rounded">+</button>
                        <button @click="autoFitZoom" class="px-2 py-1 text-xs bg-blue-100 hover:bg-blue-200 rounded">Fit</button>
                        <button @click="resetZoom" class="px-2 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">100%</button>
                    </div>
                    
                    <!-- View Controls -->
                    <button @click="showGrid = !showGrid"
                            :class="showGrid ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'"
                            class="p-2 rounded-lg transition-colors">
                        Grid
                    </button>

                    <button @click="showRelationships = !showRelationships"
                            :class="showRelationships ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                            class="p-2 rounded-lg transition-colors">
                        Relations
                    </button>
                </div>
                
                <!-- Stats -->
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <span>Tables: <strong x-text="tables.length"></strong></span>
                    <span>Relations: <strong x-text="relationships.length"></strong></span>
                </div>
            </div>
        </div>

        <!-- Canvas Container -->
        <div x-ref="canvas" class="relative bg-gray-50 border border-gray-200 rounded-lg overflow-hidden" style="height: 900px; width: 100%;">
            <!-- Grid Background -->
            <div x-show="showGrid" class="absolute inset-0 opacity-20"
                 :style="`background-image: repeating-linear-gradient(0deg, #ccc, #ccc 1px, transparent 1px, transparent ${gridSize * zoomLevel}px), repeating-linear-gradient(90deg, #ccc, #ccc 1px, transparent 1px, transparent ${gridSize * zoomLevel}px);`">
            </div>

            <!-- Canvas -->
            <div class="absolute inset-0 cursor-move"
                 @mousedown="startPan"
                 @mousemove="handleMouseMove($event)"
                 @mouseup="handleMouseUp()"
                 @wheel.prevent="handleWheel">

                <!-- Canvas Content -->
                <div class="relative"
                     :style="`transform: translate(${panX}px, ${panY}px) scale(${zoomLevel}); transform-origin: 0 0;`">

                    <!-- Tables -->
                    <div wire:key="tables-container-{{ count($tables) }}">
                        <template x-for="table in tables" :key="table.id">
                        <div class="absolute bg-white border rounded-lg shadow-lg cursor-pointer"
                             :style="`left: ${table.position_x || 0}px; top: ${table.position_y || 0}px; width: ${table.width || 280}px; z-index: 10;`"
                             :class="selectedTable && selectedTable.id === table.id ? 'border-blue-500 shadow-xl' : 'border-gray-400'"
                             @mousedown="startDrag($event, table)"
                             @click="selectTable(table)">

                            <!-- Table Header with Dynamic Colors -->
                            <div class="text-white px-3 py-2 rounded-t-lg flex items-center justify-between"
                                 :class="{
                                    'bg-cyan-500': table.name.includes('categories') || table.name.includes('bears'),
                                    'bg-yellow-500': table.name.includes('fish'),
                                    'bg-green-500': table.name.includes('trees'),
                                    'bg-pink-400': table.name.includes('picnics'),
                                    'bg-gray-500': table.name.includes('bears_picnics'),
                                    'bg-blue-600': !table.name.includes('categories') && !table.name.includes('bears') && !table.name.includes('fish') && !table.name.includes('trees') && !table.name.includes('picnics')
                                 }">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h3 class="font-semibold text-sm" x-text="`Table ${table.display_name || table.name}`"></h3>
                                    </div>
                                </div>

                                <!-- Table Actions -->
                                <div class="flex items-center space-x-1">
                                    <button @click.stop="editTable(table)"
                                            class="text-white hover:text-gray-200 p-1 rounded">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.828-2.828z"></path>
                                        </svg>
                                    </button>
                                    <button @click.stop="deleteTable(table)"
                                            class="text-white hover:text-red-200 p-1 rounded">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Table Fields -->
                            <div class="p-3">
                                <template x-for="field in table.fields || []" :key="field.id">
                                    <div class="group flex items-center justify-between py-1 text-xs border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                                        <div class="flex items-center space-x-2">
                                            <!-- Primary Key Icon -->
                                            <template x-if="field.is_primary">
                                                <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            </template>
                                            <!-- Foreign Key Icon -->
                                            <template x-if="field.is_foreign_key">
                                                <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"></path>
                                                </svg>
                                            </template>
                                            <span class="font-medium" x-text="field.name"></span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <div class="flex items-center space-x-1 text-gray-500">
                                                <span x-text="field.type"></span>
                                                <template x-if="!field.is_nullable">
                                                    <span class="text-red-500 font-bold">NOT NULL</span>
                                                </template>
                                            </div>
                                            <!-- Field Actions (shown on hover) -->
                                            <div class="opacity-0 group-hover:opacity-100 flex items-center space-x-1 transition-opacity">
                                                <button @click.stop="editField(table, field)"
                                                        class="text-blue-500 hover:text-blue-700 p-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.828-2.828z"></path>
                                                    </svg>
                                                </button>
                                                <button @click.stop="deleteFieldConfirm(field.id)"
                                                        class="text-red-500 hover:text-red-700 p-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 102 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 10-2 0v3a1 1 0 102 0V9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Add Field Button -->
                                <button @click.stop="addNewField(table)"
                                        class="w-full mt-2 text-xs text-green-600 hover:text-green-800 py-1 border-t border-gray-200">
                                    + add new column
                                </button>
                            </div>
                        </div>
                    </template>
                    </div>

                    <!-- Relationship Lines (On Top of Tables) -->
                    <div x-show="showRelationships" class="absolute inset-0 pointer-events-none" style="z-index: 20;">
                        <!-- Relationship lines with proper names -->
                        <template x-for="(relationship, index) in relationships" :key="`rel-${index}`">
                            <div class="absolute pointer-events-none" style="z-index: 25;"
                                 x-data="{
                                    get startPos() { return getRelationshipStart(relationship); },
                                    get endPos() { return getRelationshipEnd(relationship); },
                                    get lineData() {
                                        const start = this.startPos;
                                        const end = this.endPos;
                                        const dx = end.x - start.x;
                                        const dy = end.y - start.y;
                                        const length = Math.sqrt(dx * dx + dy * dy);
                                        const angle = Math.atan2(dy, dx) * 180 / Math.PI;
                                        const midX = (start.x + end.x) / 2;
                                        const midY = (start.y + end.y) / 2;

                                        return {
                                            start, end, length, angle, midX, midY
                                        };
                                    }
                                 }">

                                <!-- Relationship Line with enhanced styling -->
                                <div class="absolute bg-black shadow-lg"
                                     :style="`
                                        left: ${lineData.start.x}px;
                                        top: ${lineData.start.y - 2}px;
                                        width: ${lineData.length}px;
                                        height: 4px;
                                        transform: rotate(${lineData.angle}deg);
                                        transform-origin: 0 50%;
                                        z-index: 30;
                                     `">
                                </div>

                                <!-- Arrow Head with enhanced styling -->
                                <div class="absolute w-0 h-0 border-l-8 border-r-8 border-b-8 border-transparent border-b-black shadow-lg"
                                     :style="`
                                        left: ${lineData.end.x - 8}px;
                                        top: ${lineData.end.y - 8}px;
                                        transform: rotate(${lineData.angle + 90}deg);
                                        z-index: 30;
                                     `">
                                </div>

                                <!-- Relationship Label with multi-line support and enhanced styling -->
                                <div class="absolute bg-black text-white px-3 py-2 rounded-lg text-xs font-bold shadow-lg text-center border-2 border-white"
                                     :style="`
                                        left: ${lineData.midX - 80}px;
                                        top: ${lineData.midY - 20}px;
                                        z-index: 30;
                                        min-width: 160px;
                                        transform: translateX(-50%);
                                     `"
                                     x-data="{ label: getRelationshipLabel(relationship) }">
                                    <div x-text="label.line1" class="leading-tight text-white font-semibold"></div>
                                    <div x-text="label.line2" class="leading-tight text-yellow-300 font-medium"></div>
                                </div>
                            </div>
                        </template>
                    </div>


                </div>
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
                               @input="updateTable(selectedTable)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                        <input type="text" x-model="selectedTable.display_name"
                               @input="updateTable(selectedTable)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea x-model="selectedTable.description"
                                  @input="updateTable(selectedTable)"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" rows="2"></textarea>
                    </div>
                </div>
            </template>
        </div>

        <!-- Table Creation Modal -->
        <div x-show="$wire.showTableModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
             @click.self="$wire.showTableModal = false">

            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                    <h3 class="text-lg font-medium text-gray-900" x-text="$wire.editingTableId ? 'Edit Table' : 'Create New Table'"></h3>
                    <p class="text-sm text-gray-600" x-text="$wire.editingTableId ? 'Modify your table structure and properties' : 'Define your table structure with fields and properties'"></p>
                </div>

                <div class="px-6 py-4 space-y-6">
                    <!-- Table Basic Info -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Table Name *</label>
                            <input type="text" wire:model="tableForm.name"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., users, products, orders">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Display Name *</label>
                            <input type="text" wire:model="tableForm.display_name"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., Users, Products, Orders">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model="tableForm.description" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Describe what this table represents..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Table Color</label>
                            <input type="color" wire:model="tableForm.color"
                                   class="w-full h-20 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Fields Section -->
                    <div class="border-t pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-medium text-gray-900">Table Fields</h4>
                            <div class="flex space-x-2">
                                <button type="button" wire:click="refreshTableForm" x-show="$wire.editingTableId"
                                        class="px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                    🔄 Refresh
                                </button>
                                <button type="button" wire:click="addFieldToForm"
                                        class="px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                                    + Add Field
                                </button>
                            </div>
                        </div>

                        <!-- Fields List -->
                        @if(!empty($tableForm['fields']))
                            <div class="space-y-4">
                                <div class="text-xs text-gray-500 mb-2">{{ count($tableForm['fields']) }} fields loaded</div>
                                @foreach($tableForm['fields'] as $index => $field)
                                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50" wire:key="field-{{ $field['id'] ?? $index }}">
                                        <div class="grid grid-cols-12 gap-3 items-start">
                                            <!-- Field Name -->
                                            <div class="col-span-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Field Name *</label>
                                                <input type="text"
                                                       wire:model="tableForm.fields.{{ $index }}.name"
                                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                       placeholder="field_name">
                                            </div>

                                            <!-- Field Type -->
                                            <div class="col-span-2">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Type *</label>
                                                <select wire:model="tableForm.fields.{{ $index }}.type"
                                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                    @foreach([
                                                        ['value' => 'string', 'label' => 'String (Text)'],
                                                        ['value' => 'text', 'label' => 'Text (Long Text)'],
                                                        ['value' => 'longtext', 'label' => 'Long Text'],
                                                        ['value' => 'integer', 'label' => 'Integer (Number)'],
                                                        ['value' => 'bigint', 'label' => 'Big Integer'],
                                                        ['value' => 'decimal', 'label' => 'Decimal (Money/Float)'],
                                                        ['value' => 'float', 'label' => 'Float'],
                                                        ['value' => 'double', 'label' => 'Double'],
                                                        ['value' => 'boolean', 'label' => 'Boolean (Yes/No)'],
                                                        ['value' => 'date', 'label' => 'Date'],
                                                        ['value' => 'datetime', 'label' => 'Date & Time'],
                                                        ['value' => 'timestamp', 'label' => 'Timestamp'],
                                                        ['value' => 'time', 'label' => 'Time'],
                                                        ['value' => 'json', 'label' => 'JSON'],
                                                        ['value' => 'enum', 'label' => 'Enum (Select Options)'],
                                                        ['value' => 'varchar', 'label' => 'Varchar'],
                                                        ['value' => 'char', 'label' => 'Char']
                                                    ] as $type)
                                                        <option value="{{ $type['value'] }}" {{ ($field['type'] ?? '') === $type['value'] ? 'selected' : '' }}>
                                                            {{ $type['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Length -->
                                            <div class="col-span-1">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Length</label>
                                                <input type="number"
                                                       wire:model="tableForm.fields.{{ $index }}.length"
                                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                       placeholder="255">
                                            </div>

                                            <!-- Default Value -->
                                            <div class="col-span-2">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Default</label>
                                                <input type="text"
                                                       wire:model="tableForm.fields.{{ $index }}.default_value"
                                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                       placeholder="default value">
                                            </div>

                                            <!-- Checkboxes -->
                                            <div class="col-span-3 grid grid-cols-2 gap-2 text-xs">
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           wire:model="tableForm.fields.{{ $index }}.is_nullable"
                                                           class="mr-1">
                                                    <span>Nullable</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           wire:model="tableForm.fields.{{ $index }}.is_primary"
                                                           class="mr-1">
                                                    <span>Primary</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           wire:model="tableForm.fields.{{ $index }}.is_foreign_key"
                                                           class="mr-1">
                                                    <span>Foreign</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           wire:model="tableForm.fields.{{ $index }}.is_auto_increment"
                                                           class="mr-1">
                                                    <span>Auto Inc</span>
                                                </label>
                                            </div>

                                            <!-- Delete Button -->
                                            <div class="col-span-1 flex justify-end">
                                                <button type="button" wire:click="removeFieldFromForm({{ $index }})"
                                                        class="text-red-500 hover:text-red-700 p-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 102 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 10-2 0v3a1 1 0 102 0V9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- No Fields Message -->
                        <div x-show="!$wire.tableForm.fields || $wire.tableForm.fields.length === 0"
                             class="text-center py-8 text-gray-500">
                            <p>No fields added yet. Click "Add Field" to start building your table structure.</p>
                        </div>

                        <!-- Debug Info (remove in production) -->
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-xs" x-show="$wire.editingTableId">
                            <strong>Debug Info:</strong><br>
                            Editing Table ID: <span x-text="$wire.editingTableId"></span><br>
                            Fields Count: <span x-text="$wire.tableForm.fields ? $wire.tableForm.fields.length : 'undefined'"></span><br>
                            <details class="mt-2">
                                <summary>Raw Field Data</summary>
                                <pre x-text="JSON.stringify($wire.tableForm.fields, null, 2)" class="mt-1 text-xs overflow-auto max-h-32"></pre>
                            </details>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3 sticky bottom-0 bg-white">
                    <button @click="$wire.showTableModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="saveTable"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                            x-text="$wire.editingTableId ? 'Update Table' : 'Create Table'">
                    </button>
                </div>
            </div>
        </div>



        <!-- Field Edit Modal -->
        <div x-show="$wire.showFieldModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
             @click.self="$wire.closeFieldModal()">

            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                    <h3 class="text-lg font-medium text-gray-900" x-text="$wire.editingFieldId ? 'Edit Field' : 'Add New Field'"></h3>
                    <p class="text-sm text-gray-600" x-text="$wire.editingFieldId ? 'Modify field properties and settings' : 'Define a new field for your table'"></p>
                </div>

                <div class="px-6 py-6 space-y-6">
                    <!-- Basic Field Information -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Field Name *</label>
                            <input type="text" wire:model="fieldForm.name"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., user_name, email, price">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Field Type *</label>
                            <select wire:model="fieldForm.type"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="varchar">String (Text)</option>
                                <option value="text">Text (Long Text)</option>
                                <option value="longtext">Long Text</option>
                                <option value="integer">Integer (Number)</option>
                                <option value="bigint">Big Integer</option>
                                <option value="decimal">Decimal (Money/Float)</option>
                                <option value="float">Float</option>
                                <option value="double">Double</option>
                                <option value="boolean">Boolean (Yes/No)</option>
                                <option value="date">Date</option>
                                <option value="datetime">Date & Time</option>
                                <option value="timestamp">Timestamp</option>
                                <option value="time">Time</option>
                                <option value="json">JSON</option>
                                <option value="enum">Enum (Select Options)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Field Properties -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Length</label>
                            <input type="number" wire:model="fieldForm.length"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="255">
                            <p class="text-xs text-gray-500 mt-1">For varchar, char, etc. Leave empty for other types.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Default Value</label>
                            <input type="text" wire:model="fieldForm.default_value"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Optional default value">
                        </div>
                    </div>

                    <!-- Field Options -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Field Options</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <input type="checkbox" wire:model="fieldForm.is_nullable" class="mr-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Nullable</span>
                                    <p class="text-xs text-gray-500">Allow empty values</p>
                                </div>
                            </label>

                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <input type="checkbox" wire:model="fieldForm.is_primary" class="mr-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Primary Key</span>
                                    <p class="text-xs text-gray-500">Unique identifier</p>
                                </div>
                            </label>

                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <input type="checkbox" wire:model="fieldForm.is_foreign_key" class="mr-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Foreign Key</span>
                                    <p class="text-xs text-gray-500">References another table</p>
                                </div>
                            </label>

                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <input type="checkbox" wire:model="fieldForm.is_auto_increment" class="mr-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Auto Increment</span>
                                    <p class="text-xs text-gray-500">Automatically increase value</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Validation Rules -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Validation Rules</label>
                        <input type="text" wire:model="fieldForm.validation"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., required|email|max:255">
                        <p class="text-xs text-gray-500 mt-1">Laravel validation rules (optional)</p>
                    </div>

                    <!-- Enum Options (show only for enum type) -->
                    <div x-show="$wire.fieldForm.type === 'enum'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enum Options</label>
                        <textarea wire:model="fieldForm.enum_options"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  rows="3"
                                  placeholder="active,inactive,pending (comma-separated)"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Comma-separated list of options for enum type</p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3 sticky bottom-0 bg-white">
                    <button wire:click="closeFieldModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="saveField"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                            x-text="$wire.editingFieldId ? 'Update Field' : 'Create Field'">
                    </button>
                </div>
            </div>
        </div>

        <!-- Relationship Management Dialog -->
        <div x-show="showRelationshipDialog"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
             @click.self="showRelationshipDialog = false">

            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Create Relationship</h3>
                </div>

                <div class="px-6 py-4 space-y-4" x-show="editingRelationship">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Source Table</label>
                            <select x-model="editingRelationship.source_table_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select source table...</option>
                                <template x-for="table in tables" :key="table.id">
                                    <option :value="table.id" x-text="table.display_name || table.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target Table</label>
                            <select x-model="editingRelationship.target_table_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select target table...</option>
                                <template x-for="table in tables" :key="table.id">
                                    <option :value="table.id" x-text="table.display_name || table.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Source Field</label>
                            <select x-model="editingRelationship.source_field_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select source field...</option>
                                <template x-for="field in getTableFields(editingRelationship?.source_table_id)" :key="field.id">
                                    <option :value="field.id" x-text="field.name + ' (' + field.type + ')'"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target Field</label>
                            <select x-model="editingRelationship.target_field_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select target field...</option>
                                <template x-for="field in getTableFields(editingRelationship?.target_table_id)" :key="field.id">
                                    <option :value="field.id" x-text="field.name + ' (' + field.type + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Relationship Type</label>
                        <select x-model="editingRelationship.type"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <template x-for="type in relationshipTypes" :key="type.value">
                                <option :value="type.value" x-text="type.label"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Foreign Key</label>
                            <input type="text" x-model="editingRelationship.foreign_key"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Auto-generated if empty">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Relationship Name</label>
                            <input type="text" x-model="editingRelationship.relationship_name"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Method name">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div x-show="['hasMany', 'hasOne'].includes(editingRelationship?.type)">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Local Key</label>
                            <input type="text" x-model="editingRelationship.local_key"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="id (default)">
                        </div>

                        <div x-show="editingRelationship?.type === 'belongsToMany'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pivot Table</label>
                            <input type="text" x-model="editingRelationship.pivot_table"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Auto-generated if empty">
                        </div>
                    </div>

                    <!-- Existing Relationships List -->
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Existing Relationships</h4>
                        <div class="max-h-32 overflow-y-auto space-y-2">
                            <template x-for="rel in relationships" :key="rel.id">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm">
                                    <span x-text="`${tables.find(t => t.id == rel.source_table_id)?.name || 'Unknown'} → ${tables.find(t => t.id == rel.target_table_id)?.name || 'Unknown'} (${rel.type.replace('_', ' ')})`"></span>
                                    <button @click="deleteRelationship(rel.id)"
                                            class="text-red-500 hover:text-red-700 p-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 102 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 10-2 0v3a1 1 0 102 0V9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button @click="showRelationshipDialog = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button @click="saveRelationship()"
                            class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                        Create Relationship
                    </button>
                </div>
            </div>
        </div>

        <style>
            .erd-designer-container {
                user-select: none;
            }

            .erd-designer-container * {
                box-sizing: border-box;
            }

            .absolute.bg-white:hover {
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }

            .absolute.bg-white.border-blue-500 {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
        </style>

        <!-- Export SQL Modal -->
        <div x-show="$wire.showExportModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
             @click.self="$wire.showExportModal = false">

            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                    <h3 class="text-lg font-medium text-gray-900">Export SQL Schema</h3>
                    <p class="text-sm text-gray-600">Generated SQL schema for your ERD project</p>
                </div>

                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">SQL Schema</label>
                        <textarea
                            readonly
                            x-model="$wire.sqlExport"
                            class="w-full h-96 p-3 border border-gray-300 rounded-lg font-mono text-sm bg-gray-50"
                            placeholder="SQL will appear here..."></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3 sticky bottom-0 bg-white">
                    <button @click="$wire.showExportModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Close
                    </button>
                    <button @click="navigator.clipboard.writeText($wire.sqlExport); $wire.showExportModal = false;"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Copy to Clipboard
                    </button>
                    <button wire:click="downloadSQL"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                        Download SQL File
                    </button>
                </div>
            </div>
        </div>

        <!-- Import SQL Modal -->
        <div x-show="$wire.showImportModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
             @click.self="$wire.showImportModal = false">

            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                    <h3 class="text-lg font-medium text-gray-900">Import SQL Schema</h3>
                    <p class="text-sm text-gray-600">Paste your SQL schema to automatically create tables and relationships</p>
                </div>

                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">SQL Schema</label>
                        <textarea
                            wire:model="sqlImport"
                            class="w-full h-96 p-3 border border-gray-300 rounded-lg font-mono text-sm"
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

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3 sticky bottom-0 bg-white">
                    <button @click="$wire.showImportModal = false; $wire.sqlImport = '';"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="processImportSQL"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Import SQL
                    </button>
                </div>
            </div>
        </div>

        <!-- Project Settings Modal -->
        <div x-show="$wire.showProjectSettingsModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
             @click.self="$wire.showProjectSettingsModal = false">

            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                    <h3 class="text-lg font-medium text-gray-900">Project Settings</h3>
                    <p class="text-sm text-gray-600">Configure your ERD project properties</p>
                </div>

                <div class="px-6 py-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Project Name *</label>
                        <input type="text" wire:model="projectSettingsForm.name"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea wire:model="projectSettingsForm.description" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Describe your database project..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>
                            <input type="text" wire:model="projectSettingsForm.database_name"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="my_database">
                            <p class="text-xs text-gray-500 mt-1">Used in SQL export</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Type</label>
                            <select wire:model="projectSettingsForm.database_type"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="mysql">MySQL</option>
                                <option value="postgresql">PostgreSQL</option>
                                <option value="sqlite">SQLite</option>
                                <option value="sqlserver">SQL Server</option>
                            </select>
                        </div>
                    </div>

                    <!-- Project Statistics -->
                    <div class="border-t pt-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Project Statistics</h4>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-blue-50 p-3 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600" x-text="tables.length"></div>
                                <div class="text-xs text-blue-600">Tables</div>
                            </div>
                            <div class="bg-purple-50 p-3 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600" x-text="relationships.length"></div>
                                <div class="text-xs text-purple-600">Relationships</div>
                            </div>
                            <div class="bg-green-50 p-3 rounded-lg">
                                <div class="text-2xl font-bold text-green-600" x-text="tables.reduce((sum, t) => sum + (t.fields?.length || 0), 0)"></div>
                                <div class="text-xs text-green-600">Total Fields</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3 sticky bottom-0 bg-white">
                    <button @click="$wire.showProjectSettingsModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="updateProjectSettings"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Save Settings
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
