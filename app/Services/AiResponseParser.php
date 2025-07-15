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
        
        // Look for table definitions
        preg_match_all('/(?:CREATE TABLE|Table:|##\s*)([a-zA-Z_]+)[\s\S]*?(?=(?:CREATE TABLE|Table:|##\s*[a-zA-Z_]+|$))/i', $response, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $tableName = trim($match[1]);
            $tableContent = $match[0];
            
            $fields = $this->extractTableFields($tableContent);
            $relationships = $this->extractRelationships($tableContent);
            
            $tables[] = [
                'name' => $tableName,
                'fields' => $fields,
                'relationships' => $relationships,
                'indexes' => $this->extractIndexes($tableContent),
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
        preg_match_all('/(\w+)\s+(varchar|int|text|boolean|timestamp|decimal|json)(\(\d+\))?/i', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $fields[] = [
                'name' => $match[1],
                'type' => strtolower($match[2]),
                'length' => isset($match[3]) ? trim($match[3], '()') : null,
            ];
        }
        
        return $fields;
    }

    private function extractRelationships(string $content): array
    {
        $relationships = [];
        if (preg_match_all('/(\w+)_id.*?references?\s+(\w+)/i', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $relationships[] = [
                    'type' => 'belongsTo',
                    'related_table' => $match[2],
                    'foreign_key' => $match[1] . '_id',
                ];
            }
        }
        return $relationships;
    }

    private function extractIndexes(string $content): array
    {
        $indexes = [];
        if (preg_match_all('/index\s+on\s+(\w+)/i', $content, $matches)) {
            $indexes = $matches[1];
        }
        return $indexes;
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
}
