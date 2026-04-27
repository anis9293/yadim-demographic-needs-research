<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'state', 'latitude', 'longitude', 'geometry_geojson'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'geometry_geojson' => 'array',
    ];

    public function stats()
    {
        return $this->hasMany(DemographicStat::class);
    }

    public function latestScore()
    {
        return $this->hasOne(CommunityNeedScore::class)->latestOfMany('year');
    }
}
