<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $logo
 * @property string|null $website
 * @property-read string|null $logo_url
 * @property-read int|null $employees_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'logo', 'website'])]
#[Appends(['logo_url'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /**
     * Remove the stored logo file whenever a company is deleted.
     */
    protected static function booted(): void
    {
        static::deleted(function (Company $company): void {
            $company->deleteLogoFile();
        });
    }

    /**
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Public URL of the logo (served through the storage:link symlink).
     *
     * @return Attribute<string|null, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->logo ? Storage::disk('public')->url($this->logo) : null,
        );
    }

    /**
     * Filter companies by name, email or website.
     *
     * @param  Builder<Company>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $query->when($term, function (Builder $query, string $term): void {
            $query->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('website', 'like', "%{$term}%");
            });
        });
    }

    public function deleteLogoFile(): void
    {
        if ($this->logo) {
            Storage::disk('public')->delete($this->logo);
        }
    }
}
