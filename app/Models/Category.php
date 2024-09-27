<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'categories'; // Table name

    // Define fillable fields (mass assignment protection)
    protected $fillable = [
        'category_name', 
        'category_id'
    ];

    // Primary key column (if other than 'id')
    protected $primaryKey = 'category_id';

    // Disable auto-incrementing (if the primary key is not auto-incrementing)
    public $incrementing = false;

    // Set the data type of the primary key
    protected $keyType = 'int';

    // Timestamps can be disabled if the table does not have created_at and updated_at fields
    public $timestamps = false;

    /**
     * Get all products for a category.
     * Assuming a category has many products.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }

    /**
     * Scope method for retrieving categories with a specific name.
     */
    public function scopeByName($query, $name)
    {
        return $query->where('category_name', $name);
    }

    /**
     * Predefined method to get a category by its ID.
     */
    public static function getCategoryById($categoryId)
    {
        return self::find($categoryId);
    }

    /**
     * Predefined method to update the category name.
     */
    public function updateCategoryName($newName)
    {
        $this->category_name = $newName;
        $this->save();
    }

    /**
     * Predefined method to delete a category by ID.
     */
    public static function deleteCategoryById($categoryId)
    {
        $category = self::find($categoryId);
        if ($category) {
            $category->delete();
            return true;
        }
        return false;
    }
}
