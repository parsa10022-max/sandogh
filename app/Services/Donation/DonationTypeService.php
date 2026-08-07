<?php

namespace App\Services\Donation;

use App\Models\DonationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DonationTypeService
{
    /**
     * لیست نوع کمک‌ها
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null
    ): LengthAwarePaginator {

        return DonationType::query()

            ->when($search, function ($query) use ($search) {

                $query->where(
                    'title',
                    'like',
                    "%{$search}%"
                );

            })

            ->with('account')

            ->latest()

            ->paginate($perPage)

            ->withQueryString();
    }

    /**
     * ایجاد
     */
    public function create(array $data): DonationType
    {
        return DonationType::create([

            'title' => $data['title'],

            'account_id' => $data['account_id'],

            'is_active' => true,

        ]);
    }

    /**
     * ویرایش
     */
    public function update(
        DonationType $donationType,
        array $data
    ): DonationType {

        $donationType->update([

            'title' => $data['title'],

            'account_id' => $data['account_id'],

        ]);

        return $donationType->fresh();
    }

    /**
     * تغییر وضعیت
     */
    public function changeStatus(
        DonationType $donationType
    ): DonationType {

        $donationType->update([

            'is_active' => ! $donationType->is_active,

        ]);

        return $donationType->fresh();
    }
}
