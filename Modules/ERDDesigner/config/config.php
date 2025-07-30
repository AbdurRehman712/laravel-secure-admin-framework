<?php

return [
    'name' => 'ERDDesigner',
    
    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for database connections and supported database types
    |
    */
    'database' => [
        'supported_types' => [
            'mysql' => 'MySQL',
            'postgresql' => 'PostgreSQL',
            'sqlite' => 'SQLite',
            'sqlserver' => 'SQL Server'
        ],
        'default_type' => 'mysql'
    ],

    /*
    |--------------------------------------------------------------------------
    | Canvas Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the visual canvas
    |
    */
    'canvas' => [
        'default_zoom' => 1.0,
        'min_zoom' => 0.1,
        'max_zoom' => 3.0,
        'grid_size' => 20,
        'snap_to_grid' => true,
        'show_grid' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for tables
    |
    */
    'table' => [
        'default_width' => 200,
        'default_height' => 150,
        'default_color' => '#ffffff',
        'default_engine' => 'InnoDB',
        'default_charset' => 'utf8mb4',
        'default_collation' => 'utf8mb4_unicode_ci'
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Types
    |--------------------------------------------------------------------------
    |
    | Supported field types and their configurations
    |
    */
    'field_types' => [
        'string' => [
            'sql_type' => 'varchar',
            'default_length' => 255,
            'form_type' => 'text'
        ],
        'text' => [
            'sql_type' => 'text',
            'form_type' => 'textarea'
        ],
        'integer' => [
            'sql_type' => 'int',
            'form_type' => 'number'
        ],
        'bigint' => [
            'sql_type' => 'bigint',
            'form_type' => 'number'
        ],
        'decimal' => [
            'sql_type' => 'decimal',
            'default_precision' => 10,
            'default_scale' => 2,
            'form_type' => 'number'
        ],
        'boolean' => [
            'sql_type' => 'tinyint',
            'default_length' => 1,
            'form_type' => 'toggle'
        ],
        'date' => [
            'sql_type' => 'date',
            'form_type' => 'date'
        ],
        'datetime' => [
            'sql_type' => 'datetime',
            'form_type' => 'datetime'
        ],
        'timestamp' => [
            'sql_type' => 'timestamp',
            'form_type' => 'datetime'
        ],
        'json' => [
            'sql_type' => 'json',
            'form_type' => 'json'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SQL export
    |
    */
    'export' => [
        'include_drop_statements' => true,
        'include_if_not_exists' => true,
        'include_comments' => true,
        'include_sample_data' => false
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SQL import
    |
    */
    'import' => [
        'auto_position_tables' => true,
        'table_spacing_x' => 250,
        'table_spacing_y' => 200,
        'max_tables_per_row' => 4
    ]
];
