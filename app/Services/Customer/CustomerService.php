<?php


namespace App\Services\Customer;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null
    ): LengthAwarePaginator {

        return Customer::query()
            ->search($search)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

    }
    public function create(array $data): Customer
    {
        $data['status'] ??= CustomerStatus::ACTIVE;

        return Customer::create($data);
    }
    /**
     * بروزرسانی اطلاعات مشتری
     */
    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->fresh();
    }

    /**
     * حذف مشتری (Soft Delete)
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * دریافت مشتری بر اساس شناسه
     */
    public function find(int $id): ?Customer
    {
        return Customer::find($id);
    }

    /**
     * تغییر وضعیت مشتری
     */
    public function changeStatus(
        Customer $customer,
        CustomerStatus $status
    ): Customer {
        $customer->update([
            'status' => $status,
        ]);

        return $customer->fresh();
    }

    public function getArchived(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return Customer::onlyTrashed()
            ->latest()
            ->paginate($perPage);
    }

    public function restore(int $id): void
    {
        Customer::onlyTrashed()
            ->findOrFail($id)
            ->restore();
    }
    public function getActive(): Collection
    {
        return Customer::query()
            ->active()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

}
