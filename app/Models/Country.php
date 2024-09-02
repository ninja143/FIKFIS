<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'countries';

    // Specify the primary key column (if different from 'id')
    protected $primaryKey = 'id';

    // Indicates if the model should be timestamped.
    public $timestamps = true;

    // Specify which attributes are mass assignable
    protected $fillable = [
        'code',
        'code3',
        'name',
        'number',
        'stdcode',
    ];

    // Specify which attributes should be cast to native types
    protected $casts = [
        // Define casts if necessary (e.g., 'created_at' => 'datetime')
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
    
    // Define any relationships if applicable
    // For example:
    // public function users()
    // {
    //     return $this->hasMany(User::class);
    // }

    // Add any custom methods if needed
    // For example:
    // public function getFullNameAttribute()
    // {
    //     return $this->name . ' (' . $this->code . ')';
    // }
}

