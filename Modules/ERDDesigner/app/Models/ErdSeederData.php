<?php

namespace Modules\ERDDesigner\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErdSeederData extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'table_id',
        'field_name',
        'sample_data',
        'data_type',
        'generation_rule',
        'is_active'
    ];

    protected $casts = [
        'sample_data' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Data generation types
     */
    const DATA_TYPES = [
        'static' => 'Static Values',
        'faker' => 'Faker Generated',
        'sequence' => 'Sequential',
        'random' => 'Random',
        'formula' => 'Formula Based'
    ];

    /**
     * Get the table this seeder data belongs to
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(ErdTable::class, 'table_id');
    }

    /**
     * Generate sample data based on type
     */
    public function generateData(int $count = 10): array
    {
        switch ($this->data_type) {
            case 'static':
                return $this->generateStaticData($count);
                
            case 'faker':
                return $this->generateFakerData($count);
                
            case 'sequence':
                return $this->generateSequenceData($count);
                
            case 'random':
                return $this->generateRandomData($count);
                
            case 'formula':
                return $this->generateFormulaData($count);
                
            default:
                return [];
        }
    }

    /**
     * Generate static data
     */
    private function generateStaticData(int $count): array
    {
        $data = [];
        $values = $this->sample_data ?? [];
        
        for ($i = 0; $i < $count; $i++) {
            $data[] = $values[$i % count($values)] ?? null;
        }
        
        return $data;
    }

    /**
     * Generate faker data
     */
    private function generateFakerData(int $count): array
    {
        $faker = \Faker\Factory::create();
        $data = [];
        $rule = $this->generation_rule ?? 'word';
        
        for ($i = 0; $i < $count; $i++) {
            try {
                $data[] = $faker->{$rule};
            } catch (\Exception $e) {
                $data[] = $faker->word;
            }
        }
        
        return $data;
    }

    /**
     * Generate sequence data
     */
    private function generateSequenceData(int $count): array
    {
        $data = [];
        $start = $this->sample_data['start'] ?? 1;
        $step = $this->sample_data['step'] ?? 1;
        
        for ($i = 0; $i < $count; $i++) {
            $data[] = $start + ($i * $step);
        }
        
        return $data;
    }

    /**
     * Generate random data
     */
    private function generateRandomData(int $count): array
    {
        $data = [];
        $min = $this->sample_data['min'] ?? 1;
        $max = $this->sample_data['max'] ?? 100;
        
        for ($i = 0; $i < $count; $i++) {
            $data[] = rand($min, $max);
        }
        
        return $data;
    }

    /**
     * Generate formula-based data
     */
    private function generateFormulaData(int $count): array
    {
        $data = [];
        $formula = $this->generation_rule ?? 'return $i;';
        
        for ($i = 0; $i < $count; $i++) {
            try {
                $data[] = eval($formula);
            } catch (\Exception $e) {
                $data[] = $i;
            }
        }
        
        return $data;
    }

    /**
     * Get Laravel seeder code
     */
    public function generateSeederCode(): string
    {
        switch ($this->data_type) {
            case 'faker':
                return "\$this->faker->{$this->generation_rule}";
                
            case 'static':
                $values = json_encode($this->sample_data);
                return "\$this->faker->randomElement({$values})";
                
            case 'sequence':
                return "\$this->faker->numberBetween({$this->sample_data['start']}, {$this->sample_data['max']})";
                
            default:
                return "null";
        }
    }
}
