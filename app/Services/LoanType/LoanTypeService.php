<?php

namespace App\Services\LoanType;

use App\Models\LoanType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\LoanTypeStatus;
class LoanTypeService
{
    /**
     * دریافت لیست صفحه‌بندی شده
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return LoanType::query()
            ->search($search)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * دریافت همه انواع وام
     */
    public function getAll(): Collection
    {
        return LoanType::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * دریافت انواع وام فعال
     */
    public function getActive(): Collection
    {
        return LoanType::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * یافتن یک نوع وام
     */
    public function find(int $id): LoanType
    {
        return LoanType::findOrFail($id);
    }

    /**
     * ایجاد نوع وام
     */
    public function create(array $data): LoanType
    {
        return LoanType::create($data);
    }

    /**
     * بروزرسانی نوع وام
     */
    public function update(LoanType $loanType, array $data): bool
    {
        return $loanType->update($data);
    }

    /**
     * تغییر وضعیت
     */
    public function changeStatus(LoanType $loanType): void
    {
        $isActive = $loanType->is_active === LoanTypeStatus::ACTIVE
            ? LoanTypeStatus::INACTIVE
            : LoanTypeStatus::ACTIVE;
    }

    /**
     * حذف نوع وام
     */
    public function delete(LoanType $loanType): ?bool
    {
        return $loanType->delete();
    }
}
