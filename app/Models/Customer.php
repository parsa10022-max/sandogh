<?php
namespace App\Models;
use App\Enums\CustomerStatus;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory; use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'customer_code',
        'first_name',
        'last_name',
        'father_name',
        'national_code',
        'mobile',
        'mobile_second',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'customer_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CustomerStatus::ACTIVE->value);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! empty($search)) {

            $query->where(function (Builder $q) use ($search) {

                $q->where('customer_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('national_code', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");

            });

        }

        return $query;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->customer_code} - {$this->full_name}";
    }

}
