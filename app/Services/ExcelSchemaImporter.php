<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Illuminate\Support\Str;

class ExcelSchemaImporter
{
    public function generateSampleExcel(): string
    {
        $spreadsheet = new Spreadsheet();
        
        // Create Tables sheet
        $tablesSheet = $spreadsheet->getActiveSheet();
        $tablesSheet->setTitle('Tables');
        
        // Headers for Tables sheet
        $tablesSheet->setCellValue('A1', 'Table Name');
        $tablesSheet->setCellValue('B1', 'Description');
        $tablesSheet->setCellValue('C1', 'Display Name');
        
        // Sample data for Tables
        $tablesSheet->setCellValue('A2', 'products');
        $tablesSheet->setCellValue('B2', 'Product catalog management');
        $tablesSheet->setCellValue('C2', 'Products');
        
        $tablesSheet->setCellValue('A3', 'categories');
        $tablesSheet->setCellValue('B3', 'Product categories');
        $tablesSheet->setCellValue('C3', 'Categories');
        
        // Create Fields sheet
        $fieldsSheet = $spreadsheet->createSheet();
        $fieldsSheet->setTitle('Fields');
        
        // Headers for Fields sheet
        $fieldsSheet->setCellValue('A1', 'Table Name');
        $fieldsSheet->setCellValue('B1', 'Field Name');
        $fieldsSheet->setCellValue('C1', 'Field Type');
        $fieldsSheet->setCellValue('D1', 'Length');
        $fieldsSheet->setCellValue('E1', 'Nullable');
        $fieldsSheet->setCellValue('F1', 'Default Value');
        $fieldsSheet->setCellValue('G1', 'Display Name');
        $fieldsSheet->setCellValue('H1', 'Form Type');
        $fieldsSheet->setCellValue('I1', 'Validation Rules');
        $fieldsSheet->setCellValue('J1', 'Relationship');
        $fieldsSheet->setCellValue('K1', 'Related Table');
        
        // Sample data for Fields
        $sampleFields = [
            ['products', 'id', 'bigint', '', 'no', '', 'ID', 'hidden', '', '', ''],
            ['products', 'name', 'varchar', '255', 'no', '', 'Product Name', 'text', 'required|max:255', '', ''],
            ['products', 'description', 'text', '', 'yes', '', 'Description', 'textarea', '', '', ''],
            ['products', 'price', 'decimal', '10,2', 'no', '0.00', 'Price', 'number', 'required|numeric|min:0', '', ''],
            ['products', 'category_id', 'bigint', '', 'yes', '', 'Category', 'select', '', 'belongsTo', 'categories'],
            ['products', 'is_active', 'boolean', '', 'no', 'true', 'Active', 'toggle', '', '', ''],
            ['products', 'created_at', 'timestamp', '', 'yes', '', 'Created At', 'datetime', '', '', ''],
            ['products', 'updated_at', 'timestamp', '', 'yes', '', 'Updated At', 'datetime', '', '', ''],
            
            ['categories', 'id', 'bigint', '', 'no', '', 'ID', 'hidden', '', '', ''],
            ['categories', 'name', 'varchar', '100', 'no', '', 'Category Name', 'text', 'required|max:100', '', ''],
            ['categories', 'slug', 'varchar', '100', 'no', '', 'Slug', 'text', 'required|unique:categories,slug', '', ''],
            ['categories', 'description', 'text', '', 'yes', '', 'Description', 'textarea', '', '', ''],
            ['categories', 'sort_order', 'integer', '', 'no', '0', 'Sort Order', 'number', 'integer|min:0', '', ''],
            ['categories', 'is_active', 'boolean', '', 'no', 'true', 'Active', 'toggle', '', '', ''],
            ['categories', 'created_at', 'timestamp', '', 'yes', '', 'Created At', 'datetime', '', '', ''],
            ['categories', 'updated_at', 'timestamp', '', 'yes', '', 'Updated At', 'datetime', '', '', ''],
        ];
        
        $row = 2;
        foreach ($sampleFields as $field) {
            $fieldsSheet->setCellValue('A' . $row, $field[0]);
            $fieldsSheet->setCellValue('B' . $row, $field[1]);
            $fieldsSheet->setCellValue('C' . $row, $field[2]);
            $fieldsSheet->setCellValue('D' . $row, $field[3]);
            $fieldsSheet->setCellValue('E' . $row, $field[4]);
            $fieldsSheet->setCellValue('F' . $row, $field[5]);
            $fieldsSheet->setCellValue('G' . $row, $field[6]);
            $fieldsSheet->setCellValue('H' . $row, $field[7]);
            $fieldsSheet->setCellValue('I' . $row, $field[8]);
            $fieldsSheet->setCellValue('J' . $row, $field[9]);
            $fieldsSheet->setCellValue('K' . $row, $field[10]);
            $row++;
        }
        
        // Create Seeder Data sheet
        $seederSheet = $spreadsheet->createSheet();
        $seederSheet->setTitle('Seeder Data');
        
        // Headers for Seeder Data
        $seederSheet->setCellValue('A1', 'Table Name');
        $seederSheet->setCellValue('B1', 'Field Name');
        $seederSheet->setCellValue('C1', 'Sample Data (JSON Array)');
        
        // Sample seeder data
        $seederData = [
            ['categories', 'name', '["Electronics", "Clothing", "Books", "Home & Garden", "Sports"]'],
            ['categories', 'slug', '["electronics", "clothing", "books", "home-garden", "sports"]'],
            ['categories', 'description', '["Electronic devices and gadgets", "Fashion and apparel", "Books and literature", "Home improvement and gardening", "Sports equipment and gear"]'],
            ['categories', 'sort_order', '[1, 2, 3, 4, 5]'],
            
            ['products', 'name', '["iPhone 15 Pro", "Samsung Galaxy S24", "MacBook Air M3", "Nike Air Max", "Adidas Ultraboost"]'],
            ['products', 'description', '["Latest iPhone with advanced features", "Flagship Android smartphone", "Lightweight laptop with M3 chip", "Comfortable running shoes", "High-performance running shoes"]'],
            ['products', 'price', '[999.99, 899.99, 1299.99, 129.99, 179.99]'],
            ['products', 'category_id', '[1, 1, 1, 5, 5]'],
        ];
        
        $row = 2;
        foreach ($seederData as $data) {
            $seederSheet->setCellValue('A' . $row, $data[0]);
            $seederSheet->setCellValue('B' . $row, $data[1]);
            $seederSheet->setCellValue('C' . $row, $data[2]);
            $row++;
        }
        
        // Create Instructions sheet
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        
        $instructions = [
            'Database Schema Excel Template - Instructions',
            '',
            '1. TABLES SHEET:',
            '   - Table Name: Database table name (lowercase, plural)',
            '   - Description: Brief description of table purpose',
            '   - Display Name: Human-readable name for admin interface',
            '',
            '2. FIELDS SHEET:',
            '   - Table Name: Must match table name from Tables sheet',
            '   - Field Name: Database column name (lowercase, snake_case)',
            '   - Field Type: varchar, text, integer, bigint, decimal, boolean, timestamp, date, json',
            '   - Length: For varchar/decimal (e.g., "255" or "10,2")',
            '   - Nullable: "yes" or "no"',
            '   - Default Value: Default value for field',
            '   - Display Name: Label shown in admin interface',
            '   - Form Type: text, textarea, number, select, toggle, datetime, file, etc.',
            '   - Validation Rules: Laravel validation rules (e.g., "required|max:255")',
            '   - Relationship: belongsTo, hasMany, belongsToMany (if applicable)',
            '   - Related Table: Target table for relationships',
            '',
            '3. SEEDER DATA SHEET:',
            '   - Table Name: Target table for sample data',
            '   - Field Name: Field to populate',
            '   - Sample Data: JSON array of sample values',
            '',
            '4. SUPPORTED FIELD TYPES:',
            '   - varchar: String with length limit',
            '   - text: Long text without length limit',
            '   - integer: Whole numbers',
            '   - bigint: Large integers (for IDs)',
            '   - decimal: Numbers with decimal places',
            '   - boolean: true/false values',
            '   - timestamp: Date and time',
            '   - date: Date only',
            '   - json: JSON data',
            '',
            '5. FORM TYPES:',
            '   - text: Single line text input',
            '   - textarea: Multi-line text input',
            '   - number: Numeric input',
            '   - select: Dropdown selection',
            '   - toggle: Boolean switch',
            '   - datetime: Date/time picker',
            '   - file: File upload',
            '   - hidden: Hidden field',
            '',
            '6. RELATIONSHIPS:',
            '   - belongsTo: Many-to-one (e.g., product belongs to category)',
            '   - hasMany: One-to-many (e.g., category has many products)',
            '   - belongsToMany: Many-to-many (requires pivot table)',
            '',
            'After filling this template, upload it to the Module Builder for automatic code generation.'
        ];
        
        $row = 1;
        foreach ($instructions as $instruction) {
            $instructionsSheet->setCellValue('A' . $row, $instruction);
            $row++;
        }
        
        // Auto-size columns
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            foreach (range('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }
        
        // Create demo folder if it doesn't exist
        $demoPath = storage_path('app/public/demo');
        if (!file_exists($demoPath)) {
            mkdir($demoPath, 0755, true);
        }

        // Save file in demo folder
        $filename = 'database_schema_template_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = $demoPath . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return 'demo/' . $filename;
    }

    public function generateSampleCSV(): array
    {
        // Create demo folder if it doesn't exist
        $demoPath = storage_path('app/public/demo');
        if (!file_exists($demoPath)) {
            mkdir($demoPath, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $files = [];

        // Generate Tables CSV
        $tablesFile = 'database_tables_' . $timestamp . '.csv';
        $tablesPath = $demoPath . '/' . $tablesFile;

        $tablesData = [
            ['Table Name', 'Description', 'Display Name'],
            ['products', 'Product catalog management', 'Products'],
            ['categories', 'Product categories', 'Categories'],
        ];

        $this->writeCSV($tablesPath, $tablesData);
        $files['tables'] = 'demo/' . $tablesFile;

        // Generate Fields CSV
        $fieldsFile = 'database_fields_' . $timestamp . '.csv';
        $fieldsPath = $demoPath . '/' . $fieldsFile;

        $fieldsData = [
            ['Table Name', 'Field Name', 'Field Type', 'Length', 'Nullable', 'Default Value', 'Display Name', 'Form Type', 'Validation Rules', 'Relationship', 'Related Table'],
            ['products', 'id', 'bigint', '', 'no', '', 'ID', 'hidden', '', '', ''],
            ['products', 'name', 'varchar', '255', 'no', '', 'Product Name', 'text', 'required|max:255', '', ''],
            ['products', 'description', 'text', '', 'yes', '', 'Description', 'textarea', '', '', ''],
            ['products', 'price', 'decimal', '10,2', 'no', '0.00', 'Price', 'number', 'required|numeric|min:0', '', ''],
            ['products', 'category_id', 'bigint', '', 'yes', '', 'Category', 'select', '', 'belongsTo', 'categories'],
            ['products', 'is_active', 'boolean', '', 'no', 'true', 'Active', 'toggle', '', '', ''],
            ['products', 'created_at', 'timestamp', '', 'yes', '', 'Created At', 'datetime', '', '', ''],
            ['products', 'updated_at', 'timestamp', '', 'yes', '', 'Updated At', 'datetime', '', '', ''],
            ['categories', 'id', 'bigint', '', 'no', '', 'ID', 'hidden', '', '', ''],
            ['categories', 'name', 'varchar', '100', 'no', '', 'Category Name', 'text', 'required|max:100', '', ''],
            ['categories', 'slug', 'varchar', '100', 'no', '', 'Slug', 'text', 'required|unique:categories,slug', '', ''],
            ['categories', 'description', 'text', '', 'yes', '', 'Description', 'textarea', '', '', ''],
            ['categories', 'sort_order', 'integer', '', 'no', '0', 'Sort Order', 'number', 'integer|min:0', '', ''],
            ['categories', 'is_active', 'boolean', '', 'no', 'true', 'Active', 'toggle', '', '', ''],
            ['categories', 'created_at', 'timestamp', '', 'yes', '', 'Created At', 'datetime', '', '', ''],
            ['categories', 'updated_at', 'timestamp', '', 'yes', '', 'Updated At', 'datetime', '', '', ''],
        ];

        $this->writeCSV($fieldsPath, $fieldsData);
        $files['fields'] = 'demo/' . $fieldsFile;

        // Generate Seeder Data CSV
        $seederFile = 'database_seeder_' . $timestamp . '.csv';
        $seederPath = $demoPath . '/' . $seederFile;

        $seederData = [
            ['Table Name', 'Field Name', 'Sample Data (JSON Array)'],
            ['categories', 'name', '["Electronics", "Clothing", "Books", "Home & Garden", "Sports"]'],
            ['categories', 'slug', '["electronics", "clothing", "books", "home-garden", "sports"]'],
            ['categories', 'description', '["Electronic devices and gadgets", "Fashion and apparel", "Books and literature", "Home improvement and gardening", "Sports equipment and gear"]'],
            ['categories', 'sort_order', '[1, 2, 3, 4, 5]'],
            ['products', 'name', '["iPhone 15 Pro", "Samsung Galaxy S24", "MacBook Air M3", "Nike Air Max", "Adidas Ultraboost"]'],
            ['products', 'description', '["Latest iPhone with advanced features", "Flagship Android smartphone", "Lightweight laptop with M3 chip", "Comfortable running shoes", "High-performance running shoes"]'],
            ['products', 'price', '[999.99, 899.99, 1299.99, 129.99, 179.99]'],
            ['products', 'category_id', '[1, 1, 1, 5, 5]'],
        ];

        $this->writeCSV($seederPath, $seederData);
        $files['seeder'] = 'demo/' . $seederFile;

        // Generate Instructions CSV
        $instructionsFile = 'database_instructions_' . $timestamp . '.csv';
        $instructionsPath = $demoPath . '/' . $instructionsFile;

        $instructionsData = [
            ['Section', 'Content'],
            ['Title', 'Database Schema CSV Template - Instructions'],
            ['Overview', 'This template consists of 3 CSV files: Tables, Fields, and Seeder Data'],
            ['Tables File', 'Define your database tables with names, descriptions, and display names'],
            ['Fields File', 'Define all fields for each table with types, validation, and relationships'],
            ['Seeder File', 'Provide sample data for each field in JSON array format'],
            ['Field Types', 'varchar, text, integer, bigint, decimal, boolean, timestamp, date, json'],
            ['Form Types', 'text, textarea, number, select, toggle, datetime, file, hidden'],
            ['Relationships', 'belongsTo, hasMany, belongsToMany'],
            ['Validation', 'Use Laravel validation rules (e.g., required|max:255)'],
            ['Usage', 'Fill all 3 CSV files and upload them together to the Module Builder'],
        ];

        $this->writeCSV($instructionsPath, $instructionsData);
        $files['instructions'] = 'demo/' . $instructionsFile;

        return $files;
    }

    private function writeCSV(string $filePath, array $data): void
    {
        $handle = fopen($filePath, 'w');
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }

    public function parseExcelFile(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        
        $result = [
            'tables' => [],
            'fields' => [],
            'seeder_data' => []
        ];
        
        // Parse Tables sheet
        if ($spreadsheet->getSheetByName('Tables')) {
            $tablesSheet = $spreadsheet->getSheetByName('Tables');
            $highestRow = $tablesSheet->getHighestRow();
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $tableName = $tablesSheet->getCell('A' . $row)->getValue();
                if ($tableName) {
                    $result['tables'][] = [
                        'name' => $tableName,
                        'description' => $tablesSheet->getCell('B' . $row)->getValue(),
                        'display_name' => $tablesSheet->getCell('C' . $row)->getValue() ?: Str::title($tableName)
                    ];
                }
            }
        }
        
        // Parse Fields sheet
        if ($spreadsheet->getSheetByName('Fields')) {
            $fieldsSheet = $spreadsheet->getSheetByName('Fields');
            $highestRow = $fieldsSheet->getHighestRow();
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $tableName = $fieldsSheet->getCell('A' . $row)->getValue();
                $fieldName = $fieldsSheet->getCell('B' . $row)->getValue();
                
                if ($tableName && $fieldName) {
                    $result['fields'][] = [
                        'table_name' => $tableName,
                        'name' => $fieldName,
                        'type' => $fieldsSheet->getCell('C' . $row)->getValue(),
                        'length' => $fieldsSheet->getCell('D' . $row)->getValue(),
                        'nullable' => strtolower($fieldsSheet->getCell('E' . $row)->getValue()) === 'yes',
                        'default' => $fieldsSheet->getCell('F' . $row)->getValue(),
                        'display_name' => $fieldsSheet->getCell('G' . $row)->getValue() ?: Str::title($fieldName),
                        'form_type' => $fieldsSheet->getCell('H' . $row)->getValue() ?: 'text',
                        'validation' => $fieldsSheet->getCell('I' . $row)->getValue(),
                        'relationship' => $fieldsSheet->getCell('J' . $row)->getValue(),
                        'related_table' => $fieldsSheet->getCell('K' . $row)->getValue()
                    ];
                }
            }
        }
        
        // Parse Seeder Data sheet
        if ($spreadsheet->getSheetByName('Seeder Data')) {
            $seederSheet = $spreadsheet->getSheetByName('Seeder Data');
            $highestRow = $seederSheet->getHighestRow();
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $tableName = $seederSheet->getCell('A' . $row)->getValue();
                $fieldName = $seederSheet->getCell('B' . $row)->getValue();
                $sampleData = $seederSheet->getCell('C' . $row)->getValue();
                
                if ($tableName && $fieldName && $sampleData) {
                    if (!isset($result['seeder_data'][$tableName])) {
                        $result['seeder_data'][$tableName] = [];
                    }
                    
                    $result['seeder_data'][$tableName][$fieldName] = json_decode($sampleData, true) ?: [];
                }
            }
        }
        
        return $result;
    }

    public function parseCSVFiles(array $filePaths): array
    {
        $result = [
            'tables' => [],
            'fields' => [],
            'seeder_data' => []
        ];

        // Parse Tables CSV
        if (isset($filePaths['tables']) && file_exists($filePaths['tables'])) {
            $tablesData = $this->readCSV($filePaths['tables']);
            if (count($tablesData) > 1) { // Skip header row
                for ($i = 1; $i < count($tablesData); $i++) {
                    $row = $tablesData[$i];
                    if (isset($row[0]) && !empty($row[0])) {
                        $result['tables'][] = [
                            'name' => $row[0],
                            'description' => $row[1] ?? '',
                            'display_name' => $row[2] ?? Str::title($row[0])
                        ];
                    }
                }
            }
        }

        // Parse Fields CSV
        if (isset($filePaths['fields']) && file_exists($filePaths['fields'])) {
            $fieldsData = $this->readCSV($filePaths['fields']);
            if (count($fieldsData) > 1) { // Skip header row
                for ($i = 1; $i < count($fieldsData); $i++) {
                    $row = $fieldsData[$i];
                    if (isset($row[0], $row[1]) && !empty($row[0]) && !empty($row[1])) {
                        $result['fields'][] = [
                            'table_name' => $row[0],
                            'name' => $row[1],
                            'type' => $row[2] ?? 'varchar',
                            'length' => $row[3] ?? '',
                            'nullable' => strtolower($row[4] ?? 'yes') === 'yes',
                            'default' => $row[5] ?? '',
                            'display_name' => $row[6] ?? Str::title($row[1]),
                            'form_type' => $row[7] ?? 'text',
                            'validation' => $row[8] ?? '',
                            'relationship' => $row[9] ?? '',
                            'related_table' => $row[10] ?? ''
                        ];
                    }
                }
            }
        }

        // Parse Seeder Data CSV
        if (isset($filePaths['seeder']) && file_exists($filePaths['seeder'])) {
            $seederData = $this->readCSV($filePaths['seeder']);
            if (count($seederData) > 1) { // Skip header row
                for ($i = 1; $i < count($seederData); $i++) {
                    $row = $seederData[$i];
                    if (isset($row[0], $row[1], $row[2]) && !empty($row[0]) && !empty($row[1])) {
                        $tableName = $row[0];
                        $fieldName = $row[1];
                        $sampleData = $row[2];

                        if (!isset($result['seeder_data'][$tableName])) {
                            $result['seeder_data'][$tableName] = [];
                        }

                        $result['seeder_data'][$tableName][$fieldName] = json_decode($sampleData, true) ?: [];
                    }
                }
            }
        }

        return $result;
    }

    private function readCSV(string $filePath): array
    {
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

    public function parseFile(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->parseExcelFile($filePath);
        } elseif ($extension === 'csv') {
            // For single CSV file, assume it's a fields file
            return $this->parseCSVFiles(['fields' => $filePath]);
        }

        throw new \Exception('Unsupported file format. Please use Excel (.xlsx, .xls) or CSV (.csv) files.');
    }
}
