<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;
    protected $table = 'holidays';
    protected $fillable = [
        'created_by_id',
        'updated_by_id',
        'title',
        'description',
        'location',
        'date',
        'is_local',
    ];
    // public function createdBy()
    // {
    //     return $this->belongsTo(PersonalInformation::class, 'created_by_id', 'id');
    // }
    // public function updatedBy()
    // {
    //     return $this->belongsTo(PersonalInformation::class, 'updated_by_id', 'id');
    // }
}
