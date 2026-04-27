<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemographicStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id', 'year', 'population', 'poverty_rate', 'median_income',
        'education_gap_rate', 'youth_unemployment_rate', 'religious_access_gap_rate',
        'source_name', 'source_url'
    ];

    protected $casts = [
        'year' => 'integer',
        'population' => 'integer',
        'poverty_rate' => 'float',
        'median_income' => 'float',
        'education_gap_rate' => 'float',
        'youth_unemployment_rate' => 'float',
        'religious_access_gap_rate' => 'float',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
