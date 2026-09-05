<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Salesman extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'address', 'notes', 'id_document_path', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** Vans this salesman is the default driver for. */
    public function vans(): HasMany
    {
        return $this->hasMany(Van::class, 'default_salesman_id');
    }

    public function vanLoads(): HasMany
    {
        return $this->hasMany(VanLoad::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function idDocumentUrl(): ?string
    {
        return $this->id_document_path ? Storage::disk('public')->url($this->id_document_path) : null;
    }
}
