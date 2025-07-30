<?php

namespace App\Services;

class AiResponseParser
{
    /**
     * Parse AI response based on content type and role
     */
    public function parse(string $aiResponse, string $contentType, string $role): array
    {
        // Clean and normalize the response
        $cleanResponse = $this->cleanResponse($aiResponse);
        
        // Parse based on content type
        return match ($contentType) {
            'user_stories' => $this->parseUserStories($cleanResponse),
            'acceptance_criteria' => $this->parseAcceptanceCriteria($cleanResponse),
            'wireframes' => $this->parseWireframes($cleanResponse),
            'design_system' => $this->parseDesignSystem($cleanResponse),
            'database_schema' => $this->parseDatabaseSchema($cleanResponse),
            'api_endpoints' => $this->parseApiEndpoints($cleanResponse),
            'frontend_components' => $this->parseFrontendComponents($cleanResponse),
            'backend_logic' => $this->parseBackendLogic($cleanResponse),
            'deployment_config' => $this->parseDeploymentConfig($cleanResponse),
            'docker_config' => $this->parseDockerConfig($cleanResponse),
            'project_planning' => $this->parseProjectPlanning($cleanResponse),
            default => $this->parseGeneric($cleanResponse),
        };
    }

    /**
     * Clean and normalize AI response
     */
    private function cleanResponse(string $response): string
    {
        // Remove common AI response prefixes/suffixes
        $response = preg_replace('/^(Here\'s|Here are|I\'ll|Let me|Based on)/i', '', $response);
        $response = preg_replace('/\n\n+/', "\n\n", $response);
        return trim($response);
    }

    /**
     * Parse user stories
     */
    private function parseUserStories(string $response): array
    {
        $stories = [];
        
        // Look for numbered lists or bullet points
        $lines = explode("\n", $response);
        $currentStory = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check if it's a story title (numbered or bulleted)
            if (preg_match('/^(\d+\.|\*|\-)\s*(.+)/', $line, $matches)) {
                if ($currentStory) {
                    $stories[] = $currentStory;
                }
                $currentStory = [
                    'title' => trim($matches[2]),
                    'description' => '',
                    'acceptance_criteria' => [],
                    'priority' => 'medium',
                    'story_points' => null,
                ];
            } elseif ($currentStory && preg_match('/^(As a|As an|I want|So that)/i', $line)) {
                $currentStory['description'] .= $line . "\n";
            } elseif ($currentStory && preg_match('/^(Given|When|Then|And)/i', $line)) {
                $currentStory['acceptance_criteria'][] = trim($line);
            } elseif ($currentStory) {
                $currentStory['description'] .= $line . "\n";
            }
        }
        
        if ($currentStory) {
            $stories[] = $currentStory;
        }
        
        return [
            'stories' => $stories,
            'total_count' => count($stories),
            'parsed_at' => now()->toISOString(),
        ];
    }

    /**
     * Parse acceptance criteria
     */
    private function parseAcceptanceCriteria(string $response): array
    {
        $criteria = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (preg_match('/^(Given|When|Then|And)\s+(.+)/i', $line, $matches)) {
                $criteria[] = [
                    'type' => strtolower($matches[1]),
                    'description' => trim($matches[2]),
                ];
            } elseif (preg_match('/^(\d+\.|\*|\-)\s*(.+)/', $line, $matches)) {
                $criteria[] = [
                    'type' => 'requirement',
                    'description' => trim($matches[2]),
                ];
            }
        }
        
        return [
            'criteria' => $criteria,
            'total_count' => count($criteria),
            'parsed_at' => now()->toISOString(),
        ];
    }

    /**
     * Parse wireframes description
     */
    private function parseWireframes(string $response): array
    {
        $wireframes = [];
        $sections = preg_split('/\n(?=\d+\.|\*|\-\s*[A-Z])/m', $response);
        
        foreach ($sections as $section) {
            $lines = explode("\n", trim($section));
            if (empty($lines[0])) continue;
            
            $title = preg_replace('/^(\d+\.|\*|\-)\s*/', '', $lines[0]);
            $description = implode("\n", array_slice($lines, 1));
            
            $wireframes[] = [
                'page_name' => trim($title),
                'description' => trim($description),
                'components' => $this->extractComponents($description),
                'layout_type' => $this->detectLayoutType($description),
            ];
        }
        
        return [
            'wireframes' => $wireframes,
            'total_pages' => count($wireframes),
            'parsed_at' => now()->toISOString(),
        ];
    }

    /**
     * Parse database schema
     */
    private function parseDatabaseSchema(string $response): array
    {
        $tables = [];
        $processedTables = [];

        // Split content by table markers (## table_name or CREATE TABLE)
        $sections = preg_split('/(?=##\s*\w+|CREATE\s+TABLE\s+\w+)/i', $response);

        foreach ($sections as $section) {
            $section = trim($section);
            if (empty($section)) continue;

            // Extract table name from section
            $tableName = null;
            if (preg_match('/##\s*(\w+)/i', $section, $match)) {
                $tableName = trim($match[1]);
            } elseif (preg_match('/CREATE\s+TABLE\s+(\w+)/i', $section, $match)) {
                $tableName = trim($match[1]);
            }

            if (!$tableName) continue;

            // Skip if we already processed this table (avoid duplicates from ## header + CREATE TABLE)
            if (isset($processedTables[$tableName])) {
                // If this section has more content (CREATE TABLE), update the existing entry
                if (str_contains(strtoupper($section), 'CREATE TABLE')) {
                    $fields = $this->extractTableFields($section);
                    $relationships = $this->extractRelationships($section);
                    $indexes = $this->extractIndexes($section);

                    // Update the existing table entry
                    foreach ($tables as &$table) {
                        if ($table['name'] === $tableName) {
                            $table['fields'] = $fields;
                            $table['relationships'] = $relationships;
                            $table['indexes'] = $indexes;
                            break;
                        }
                    }
                }
                continue;
            }

            $processedTables[$tableName] = true;

            $fields = $this->extractTableFields($section);
            $relationships = $this->extractRelationships($section);
            $indexes = $this->extractIndexes($section);

            $tables[] = [
                'name' => $tableName,
                'fields' => $fields,
                'relationships' => $relationships,
                'indexes' => $indexes,
            ];
        }

        return [
            'tables' => $tables,
            'total_tables' => count($tables),
            'parsed_at' => now()->toISOString(),
        ];
    }

    /**
     * Parse API endpoints
     */
    private function parseApiEndpoints(string $response): array
    {
        $endpoints = [];
        
        // Look for HTTP methods and endpoints
        preg_match_all('/(GET|POST|PUT|PATCH|DELETE)\s+([\/\w\-\{\}]+)[\s\S]*?(?=(?:GET|POST|PUT|PATCH|DELETE)|$)/i', $response, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $method = strtoupper($match[1]);
            $path = trim($match[2]);
            $description = trim($match[0]);
            
            $endpoints[] = [
                'method' => $method,
                'path' => $path,
                'description' => $this->extractEndpointDescription($description),
                'parameters' => $this->extractParameters($description),
                'response_format' => $this->extractResponseFormat($description),
            ];
        }
        
        return [
            'endpoints' => $endpoints,
            'total_endpoints' => count($endpoints),
            'parsed_at' => now()->toISOString(),
        ];
    }

    /**
     * Parse frontend components
     */
    private function parseFrontendComponents(string $response): array
    {
        $components = [];
        $sections = preg_split('/\n(?=\d+\.|\*|\-\s*[A-Z])/m', $response);
        
        foreach ($sections as $section) {
            $lines = explode("\n", trim($section));
            if (empty($lines[0])) continue;
            
            $name = preg_replace('/^(\d+\.|\*|\-)\s*/', '', $lines[0]);
            $description = implode("\n", array_slice($lines, 1));
            
            $components[] = [
                'name' => trim($name),
                'description' => trim($description),
                'props' => $this->extractComponentProps($description),
                'events' => $this->extractComponentEvents($description),
                'type' => $this->detectComponentType($name, $description),
            ];
        }
        
        return [
            'components' => $components,
            'total_components' => count($components),
            'parsed_at' => now()->toISOString(),
        ];
    }

    /**
     * Helper methods for parsing specific content
     */
    private function extractComponents(string $text): array
    {
        $components = [];
        if (preg_match_all('/\b(header|footer|sidebar|navbar|button|form|table|card|modal)\b/i', $text, $matches)) {
            $components = array_unique(array_map('strtolower', $matches[0]));
        }
        return array_values($components);
    }

    private function detectLayoutType(string $text): string
    {
        if (preg_match('/\b(grid|column|sidebar)\b/i', $text)) return 'grid';
        if (preg_match('/\b(single|simple|basic)\b/i', $text)) return 'single';
        return 'standard';
    }

    private function extractTableFields(string $content): array
    {
        $fields = [];
        $processedFields = [];

        // Match SQL field definitions like: id BIGINT PRIMARY KEY AUTO_INCREMENT,
        // name VARCHAR(255) NOT NULL,
        // price DECIMAL(10,2) NOT NULL,
        preg_match_all('/^\s*(\w+)\s+(BIGINT|VARCHAR|TEXT|BOOLEAN|TIMESTAMP|DECIMAL|INT|ENUM)(\([^)]+\))?\s*(.*?)(?:,|\n|$)/im', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $fieldName = trim($match[1]);
            $fieldType = strtolower(trim($match[2]));
            $length = isset($match[3]) ? trim($match[3], '()') : null;
            $constraints = isset($match[4]) ? trim($match[4]) : '';

            // Skip common SQL keywords that aren't field names
            if (in_array(strtoupper($fieldName), ['CREATE', 'TABLE', 'INDEX', 'FOREIGN', 'KEY', 'REFERENCES', 'PRIMARY'])) {
                continue;
            }

            // Skip duplicate field names
            if (isset($processedFields[$fieldName])) {
                continue;
            }

            $processedFields[$fieldName] = true;

            $fields[] = [
                'name' => $fieldName,
                'type' => $fieldType,
                'length' => $length,
                'nullable' => !str_contains(strtoupper($constraints), 'NOT NULL'),
                'primary' => str_contains(strtoupper($constraints), 'PRIMARY KEY'),
                'auto_increment' => str_contains(strtoupper($constraints), 'AUTO_INCREMENT'),
                'unique' => str_contains(strtoupper($constraints), 'UNIQUE'),
                'default' => $this->extractDefault($constraints),
            ];
        }

        return $fields;
    }

    private function extractRelationships(string $content): array
    {
        $relationships = [];

        // Match FOREIGN KEY constraints like: FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        if (preg_match_all('/FOREIGN\s+KEY\s*\((\w+)\)\s+REFERENCES\s+(\w+)\s*\((\w+)\)(?:\s+ON\s+(DELETE|UPDATE)\s+(\w+))?/i', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $relationships[] = [
                    'type' => 'belongsTo',
                    'foreign_key' => $match[1],
                    'related_table' => $match[2],
                    'related_key' => $match[3],
                    'on_delete' => isset($match[5]) && strtoupper($match[4]) === 'DELETE' ? strtoupper($match[5]) : null,
                    'on_update' => isset($match[5]) && strtoupper($match[4]) === 'UPDATE' ? strtoupper($match[5]) : null,
                ];
            }
        }

        return $relationships;
    }

    private function extractIndexes(string $content): array
    {
        $indexes = [];

        // Match INDEX definitions like: INDEX idx_category (category_id),
        if (preg_match_all('/INDEX\s+(\w+)\s*\(([^)]+)\)/i', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $indexes[] = [
                    'name' => $match[1],
                    'columns' => array_map('trim', explode(',', $match[2])),
                    'type' => 'index',
                ];
            }
        }

        return $indexes;
    }

    private function extractDefault(string $constraints): ?string
    {
        if (preg_match('/DEFAULT\s+([^,\s]+)/i', $constraints, $match)) {
            return trim($match[1], "'\"");
        }
        return null;
    }

    private function parseGeneric(string $response): array
    {
        return [
            'content' => $response,
            'parsed_at' => now()->toISOString(),
            'type' => 'generic',
        ];
    }

    // Additional helper methods would be implemented here...
    private function extractEndpointDescription(string $content): string { return ''; }
    private function extractParameters(string $content): array { return []; }
    private function extractResponseFormat(string $content): array { return []; }
    private function extractComponentProps(string $content): array { return []; }
    private function extractComponentEvents(string $content): array { return []; }
    private function detectComponentType(string $name, string $description): string { return 'component'; }
    private function parseBackendLogic(string $response): array { return $this->parseGeneric($response); }
    private function parseDeploymentConfig(string $response): array { return $this->parseGeneric($response); }
    private function parseDockerConfig(string $response): array { return $this->parseGeneric($response); }
    private function parseDesignSystem(string $response): array { return $this->parseGeneric($response); }
    private function parseProjectPlanning(string $response): array
    {
        $phases = [];
        $overview = [];

        // Extract project overview
        if (preg_match('/##\s*Project Overview(.*?)(?=##|\z)/s', $response, $matches)) {
            $overviewText = trim($matches[1]);
            preg_match_all('/\*\*(.*?):\*\*\s*(.+)/m', $overviewText, $overviewMatches, PREG_SET_ORDER);
            foreach ($overviewMatches as $match) {
                $overview[strtolower(str_replace(' ', '_', $match[1]))] = trim($match[2]);
            }
        }

        // Extract phases
        preg_match_all('/##\s*Phase\s*(\d+):\s*([^#]+)(.*?)(?=##\s*Phase|\z)/s', $response, $phaseMatches, PREG_SET_ORDER);

        foreach ($phaseMatches as $match) {
            $phaseNumber = $match[1];
            $phaseName = trim($match[2]);
            $phaseContent = $match[3];

            $phase = [
                'number' => (int)$phaseNumber,
                'name' => $phaseName,
                'timeline' => '',
                'resources' => [],
                'deliverables' => [],
                'risks' => [],
            ];

            // Extract timeline
            if (preg_match('/###\s*Timeline:\s*(.+)/m', $phaseContent, $timelineMatch)) {
                $phase['timeline'] = trim($timelineMatch[1]);
            }

            // Extract resources
            if (preg_match('/###\s*Resources:(.*?)(?=###|\z)/s', $phaseContent, $resourcesMatch)) {
                preg_match_all('/^\s*-\s*(.+)/m', $resourcesMatch[1], $resourceItems);
                $phase['resources'] = array_map('trim', $resourceItems[1]);
            }

            // Extract deliverables
            if (preg_match('/###\s*Deliverables:(.*?)(?=###|\z)/s', $phaseContent, $deliverablesMatch)) {
                preg_match_all('/^\s*-\s*(.+)/m', $deliverablesMatch[1], $deliverableItems);
                $phase['deliverables'] = array_map('trim', $deliverableItems[1]);
            }

            // Extract risks
            if (preg_match('/###\s*Risks:(.*?)(?=###|\z)/s', $phaseContent, $risksMatch)) {
                preg_match_all('/^\s*-\s*(.+)/m', $risksMatch[1], $riskItems);
                $phase['risks'] = array_map('trim', $riskItems[1]);
            }

            $phases[] = $phase;
        }

        return [
            'overview' => $overview,
            'phases' => $phases,
            'total_phases' => count($phases),
            'parsed_at' => now()->toISOString(),
        ];
    }

    /**
     * Get AI prompt template for specific content type and role
     */
    public static function getPromptTemplate(string $contentType, string $role): string
    {
        $templates = [
            'user_stories' => [
                'product_owner' => "Generate comprehensive user stories for this application:\n\n**Instructions:**\n1. Write stories in format: 'As a [user type], I want [goal] so that [benefit]'\n2. Include both user-facing and admin stories\n3. Add acceptance criteria for each story\n4. Group by feature areas\n\n**Required Sections:**\n- User Management Stories\n- Core Feature Stories\n- Admin Management Stories\n- Reporting Stories\n\n**Output Format:**\n```\n## User Management\n**Story 1:** As a user, I want to register an account so that I can access the system.\n**Acceptance Criteria:**\n- User can register with email and password\n- Email verification is required\n- User receives welcome email\n\n**Story 2:** As a user, I want to login so that I can access my account.\n**Acceptance Criteria:**\n- User can login with email/password\n- Remember me functionality\n- Password reset option\n```\n\nApplication Context: [Describe your application here]",
            ],
            'acceptance_criteria' => [
                'product_owner' => "Create detailed acceptance criteria for the application features:\n\n**Instructions:**\n1. Use Given-When-Then format\n2. Cover happy path and edge cases\n3. Include validation rules\n4. Specify error handling\n\n**Format:**\n```\n## Feature: [Feature Name]\n\n**Scenario 1:** [Scenario Description]\nGiven [initial context]\nWhen [action is performed]\nThen [expected result]\n\n**Scenario 2:** [Error Scenario]\nGiven [error context]\nWhen [invalid action]\nThen [error handling]\n```\n\nApplication Context: [Describe your application here]",
            ],
            'database_schema' => [
                'database_backend_developer' => "Design a comprehensive database schema:\n\n**Instructions:**\n1. Define all required tables with fields\n2. Specify data types and constraints\n3. Define relationships between tables\n4. Include indexes for performance\n5. Add timestamps and soft deletes where appropriate\n\n**Output Format:**\n```\n## Table: [table_name]\n**Fields:**\n- id (primary key, auto-increment)\n- field_name (data_type, constraints)\n- created_at (timestamp)\n- updated_at (timestamp)\n\n**Relationships:**\n- belongsTo: [related_table]\n- hasMany: [related_table]\n\n**Indexes:**\n- [field_name] (for performance)\n```\n\n**Required Tables:**\n[List the main entities for your application]\n\nApplication Context: [Describe your application here]",
            ]
        ];

        return $templates[$contentType][$role] ?? "Generate {$contentType} content for the application.";
    }
}
