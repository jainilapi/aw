<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class AwCategory extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'status' => 'boolean'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AwCategory::class, 'parent_id')->with('parent');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AwCategory::class, 'parent_id');
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(AwProductCategory::class, 'category_id');
    }

    public static function buildCategoryTree($parentId = null) {
        $categories = AwCategory::where('parent_id', $parentId)
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        foreach ($categories as $category) {
            $category->children = self::buildCategoryTree($category->id);
        }

        return $categories;
    }

    public function getLogoPathAttribute()
    {
        if ($this->logo) {
            $path = storage_path('app/public/categories/' . $this->logo);

            if (file_exists($path) && is_file($path)) {
                return Storage::path('public/categories/' . $this->logo);
            }
        }

        return public_path('assets/images/default-category.png');
    }
    
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            $path = storage_path('app/public/categories/' . $this->logo);

            if (file_exists($path) && is_file($path)) {
                return Storage::url('categories/' . $this->logo);
            }
        }

        return asset('assets/images/default-category.png');
    }
}
