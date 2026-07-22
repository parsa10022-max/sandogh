<?php

namespace App\Services\Loan;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Services\Date\JalaliDateService;
use App\Services\Installment\InstallmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        private readonly LoanCalculationService $calculator,
        private readonly InstallmentService $installmentService,
        private readonly JalaliDateService $jalaliDateService,
    ) {
    }

    /**
     * دریافت لیست صفحه‌بندی شده
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null
    ): LengthAwarePaginator {

        return Loan::query()
            ->with(['customer', 'loanType'])
            ->search($search)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * ثبت وام
     */
    public function create(array $data): Loan
    {
        return DB::transaction(function () use ($data) {

            $calculation = $this->calculator->generate(

                loanAmount: (int) $data['loan_amount'],

                installmentCount: (int) $data['installment_count'],

                startDate: $data['start_date'],

                interval: (int) $data['installment_interval'],

            );

            $loan = Loan::create([

                'customer_id' => $data['customer_id'],

                'loan_type_id' => $data['loan_type_id'],

                'loan_number' => $data['loan_number'],

                'loan_amount' => $calculation['loan_amount'],

                'installment_amount' => $calculation['base_installment_amount'],

                'installment_count' => $calculation['installment_count'],

                'installment_interval' => $data['installment_interval'],

                // ذخیره میلادی
                'start_date' => $this->jalaliDateService->toDatabase(
                    $data['start_date']
                ),

                // ذخیره میلادی
                'first_due_date' => $calculation['first_due_date'],

                // ذخیره میلادی
                'last_due_date' => $calculation['last_due_date'],

                'status' => LoanStatus::ACTIVE,

                'description' => $data['description'] ?? null,

                'created_by' => auth()->id(),

            ]);

            $this->installmentService->createForLoan(
                $loan,
                $calculation['schedule']
            );

            return $loan;
        });
    }

    /**
     * بروزرسانی وام
     */
    /**
     * بروزرسانی وام
     */
    public function update(
        Loan $loan,
        array $data
    ): Loan {

        return DB::transaction(function () use ($loan, $data) {

            $calculation = $this->calculator->generate(

                loanAmount: (int) $data['loan_amount'],

                installmentCount: (int) $data['installment_count'],

                startDate: $data['start_date'],

                interval: (int) $data['installment_interval'],

            );

            $loan->update([

                'customer_id' => $data['customer_id'],

                'loan_type_id' => $data['loan_type_id'],

                'loan_number' => $data['loan_number'],

                'loan_amount' => $calculation['loan_amount'],

                'installment_amount' => $calculation['base_installment_amount'],

                'installment_count' => $calculation['installment_count'],

                'installment_interval' => $data['installment_interval'],

                'start_date' => $this->jalaliDateService->toDatabase(
                    $data['start_date']
                ),

                'first_due_date' => $calculation['first_due_date'],

                'last_due_date' => $calculation['last_due_date'],

                'description' => $data['description'] ?? null,

                'updated_by' => auth()->id(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | بازسازی برنامه اقساط
            |--------------------------------------------------------------------------
            */

            $loan->installments()->delete();

            $this->installmentService->createForLoan(

                $loan,

                $calculation['schedule']

            );

            return $loan->fresh();

        });

    }

    /**
     * حذف وام
     */
    public function delete(Loan $loan): bool
    {
        return $loan->delete();
    }

    /**
     * یافتن وام
     */
    public function find(int $id): ?Loan
    {
        return Loan::find($id);
    }

    /**
     * تغییر وضعیت
     */
    public function changeStatus(
        Loan $loan,
        LoanStatus $status
    ): Loan {

        $loan->update([
            'status' => $status,
            'updated_by' => auth()->id(),
        ]);

        return $loan->fresh();
    }

    /**
     * آرشیو
     */
    public function getArchived(
        int $perPage = 15
    ): LengthAwarePaginator {

        return Loan::onlyTrashed()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * بازگردانی
     */
    public function restore(int $id): void
    {
        Loan::onlyTrashed()
            ->findOrFail($id)
            ->restore();
    }
}
