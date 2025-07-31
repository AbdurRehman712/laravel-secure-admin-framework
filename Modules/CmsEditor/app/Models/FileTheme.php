<?php

namespace Modules\CmsEditor\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * A pseudo-model that wraps file-based theme data to work with Filament
 */
class FileTheme extends Model
{
    protected $fillable = [
        'code',
        'name', 
        'description',
        'author',
        'path'
    ];

    // Disable database operations
    public $timestamps = false;
    protected $table = null;

    public function __construct(array $attributes = [])
    {
        // Set the primary key to 'code' for routing
        $this->primaryKey = 'code';
        $this->keyType = 'string';
        $this->incrementing = false;
        
        parent::__construct($attributes);
    }

    // Override save to prevent database operations
    public function save(array $options = [])
    {
        return true;
    }

    // Override delete to prevent database operations  
    public function delete()
    {
        return true;
    }

    // Override exists to always return true
    public function exists()
    {
        return true;
    }

    // Override getKey to return the code
    public function getKey()
    {
        return $this->getAttribute('code');
    }

    // Override getRouteKey to return the code
    public function getRouteKey()
    {
        return $this->getAttribute('code');
    }

    // Create a collection of FileTheme instances from theme data
    public static function fromThemeData(array $themes): Collection
    {
        $themeModels = collect($themes)->map(function ($theme) {
            return new static($theme);
        });

        // Convert to Eloquent Collection
        return new Collection($themeModels->all());
    }

    // Override newQuery to return a dummy builder
    public function newQuery()
    {
        // Return a dummy query builder that doesn't interact with database
        return new \Illuminate\Database\Eloquent\Builder(
            new \Illuminate\Database\Query\Builder(
                app('db')->connection(),
                app('db')->connection()->getQueryGrammar(),
                app('db')->connection()->getPostProcessor()
            )
        );
    }

    // Static query method
    public static function query()
    {
        return (new static)->newQuery();
    }
}
