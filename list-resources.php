<?php

// List registered resources
echo "Checking registered Filament resources...\n\n";

$panel = \Filament\Facades\Filament::getDefaultPanel();

if (!$panel) {
    echo "No default panel found.\n";
    exit;
}

echo "Panel ID: " . $panel->getId() . "\n";
echo "Resources:\n";

$resources = $panel->getResources();
foreach ($resources as $resource) {
    echo "- " . $resource . "\n";
}

echo "\nRoutes:\n";
$routes = Route::getRoutes();
foreach ($routes as $route) {
    if (strpos($route->uri, 'admin/resources') !== false) {
        echo $route->uri . " - " . implode('|', $route->methods) . " - " . $route->getName() . "\n";
    }
}
