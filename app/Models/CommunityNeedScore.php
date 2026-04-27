<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityNeedScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id', 'year', 'poverty_score', 'education_score', 'youth_risk_score',
        'religious_access_score', 'cni_score', 'priority_level', 'recommended_actions'
    ];

    protected $casts = [
        'year' => 'integer',
        'poverty_score' => 'float',
        'education_score' => 'float',
        'youth_risk_score' => 'float',
        'religious_access_score' => 'float',
        'cni_score' => 'float',
        'recommended_actions' => 'array',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
