<?php

namespace App\Services\Loan;

use App\Enums\GuarantorType;
use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Services\Date\JalaliDateService;
use App\Services\Installment\InstallmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LoanService
{
    protected LoanCalculationService $calculator;

    protected InstallmentService $installmentService;

    protected JalaliDateService $jalaliDateService;

    protected LoanGuarantorService $loanGuarantorService;

    public function __construct(
        LoanCalculationService $calculator,
        InstallmentService     $installmentService,
        JalaliDateService      $jalaliDateService,
        LoanGuarantorService   $loanGuarantorService,
    )
    {
        $this->calculator = $calculator;
        $this->installmentService = $installmentService;
        $this->jalaliDateService = $jalaliDateService;
        $this->loanGuarantorService = $loanGuarantorService;
    }

    /**
     * دریافت لیست وام‌ها
     */
    public function getPaginated(
        int     $perPage = 15,
        ?string $search = null
    ): LengthAwarePaginator
    {

        return Loan::query()
            ->with([
                'customer',
                'loanType',
                'guarantors.customer',
            ])
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
                loanAmount: (int)$data['loan_amount'],
                installmentCount: (int)$data['installment_count'],
                startDate: $data['start_date'],
                interval: (int)$data['installment_interval'],
            );

            /*
            |--------------------------------------------------------------------------
            | ایجاد وام
            |--------------------------------------------------------------------------
            */

            $loan = Loan::create([

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

                'status' => LoanStatus::ACTIVE,

                'description' => $data['description'] ?? null,

                'created_by' => auth()->id(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | ایجاد اقساط
            |--------------------------------------------------------------------------
            */

            $this->installmentService->createForLoan(
                $loan,
                $calculation['schedule']
            );

            /*
            |--------------------------------------------------------------------------
            | ثبت ضامن‌ها
            |--------------------------------------------------------------------------
            */

            $this->createGuarantors(
                $loan,
                $data
            );

            return $loan->fresh([
                'customer',
                'loanType',
                'installments',
                'guarantors.customer',
            ]);

        });
    }

    /**
     * ویرایش وام
     */
    public function update(
        Loan $loan,
        array $data
    ): Loan
    {
        /*
     |--------------------------------------------------------------------------
     | جلوگیری از ویرایش وام دارای پرداخت
     |--------------------------------------------------------------------------
     */

        if (
            $loan->installments()
                ->whereHas('payment')
                ->exists()
        ) {
            throw new \RuntimeException(
                'به دلیل ثبت پرداخت، اطلاعات این وام قابل ویرایش نیست.'
            );
        }

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
            | حذف اقساط قبلی
            |--------------------------------------------------------------------------
            */

            $loan->installments()->delete();

            $this->installmentService->createForLoan(
                $loan,
                $calculation['schedule']
            );

            /*
            |--------------------------------------------------------------------------
            | حذف ضامن‌های قبلی
            |--------------------------------------------------------------------------
            */

            $loan->guarantors()->delete();

            $this->createGuarantors(
                $loan,
                $data
            );

            return $loan->fresh([
                'customer',
                'loanType',
                'installments',
                'guarantors.customer',
            ]);

        });
    }

    /**
     * دریافت یک وام
     */
    public function find(int $id): ?Loan
    {
        return Loan::with([
            'customer',
            'loanType',
            'installments',
            'guarantors.customer',
        ])->find($id);
    }

    /**
     * حذف وام
     */
    public function delete(Loan $loan): bool
    {
        return DB::transaction(function () use ($loan) {

            // در آینده اگر پرداخت ثبت شده باشد
            // اجازه حذف داده نخواهد شد.

            return (bool)$loan->delete();

        });
    }

    /**
     * تغییر وضعیت وام
     */
    public function changeStatus(
        Loan       $loan,
        LoanStatus $status
    ): Loan
    {

        $loan->update([

            'status' => $status,

            'updated_by' => auth()->id(),

        ]);

        return $loan->fresh();
    }

    /**
     * آرشیو وام‌ها
     */
    public function getArchived(
        int $perPage = 15
    ): LengthAwarePaginator
    {

        return Loan::onlyTrashed()
            ->with([
                'customer',
                'loanType',
            ])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

    }

    /**
     * بازگردانی وام حذف شده
     */
    public function restore(
        int $id
    ): void
    {

        Loan::onlyTrashed()
            ->findOrFail($id)
            ->restore();

    }

    /**
     * ثبت ضامن‌های وام
     */
    private function createGuarantors(
        Loan  $loan,
        array $data
    ): void
    {

        /*
        |--------------------------------------------------------------------------
        | ضامن اول (همیشه عضو صندوق)
        |--------------------------------------------------------------------------
        */

        $this->loanGuarantorService->create($loan, [

            'guarantor_order' => 1,

            'guarantor_type' => GuarantorType::CUSTOMER,

            'customer_id' => $data['guarantor1_customer_id'],

            'first_name' => null,

            'last_name' => null,

            'national_code' => null,

            'mobile' => null,

            'guarantee_type' => $data['guarantor1_guarantee_type'],

            'guarantee_number' =>
                $data['guarantor1_guarantee_number'] ?? null,

            'guarantee_account_number' =>
                $data['guarantor1_guarantee_account_number'] ?? null,

            'guarantee_amount' =>
                $data['guarantor1_guarantee_amount'] ?? null,

        ]);

        /*
        |--------------------------------------------------------------------------
        | ضامن دوم
        |--------------------------------------------------------------------------
        */

        $type = GuarantorType::from(
            $data['guarantor2_type']
        );

        $guarantor = [

            'guarantor_order' => 2,

            'guarantor_type' => $type,

            'customer_id' => null,

            'first_name' => null,

            'last_name' => null,

            'national_code' => null,

            'mobile' => null,

            'guarantee_type' =>
                $data['guarantor2_guarantee_type'],

            'guarantee_number' =>
                $data['guarantor2_guarantee_number'] ?? null,

            'guarantee_account_number' =>
                $data['guarantor2_guarantee_account_number'] ?? null,

            'guarantee_amount' =>
                $data['guarantor2_guarantee_amount'] ?? null,

        ];

        switch ($type) {

            /*
            |--------------------------------------------------------------------------
            | عضو صندوق
            |--------------------------------------------------------------------------
            */

            case GuarantorType::CUSTOMER:

                $guarantor['customer_id'] =
                    $data['guarantor2_customer_id'];

                break;

            /*
            |--------------------------------------------------------------------------
            | خود وام گیرنده
            |--------------------------------------------------------------------------
            */

            case GuarantorType::BORROWER:

                $guarantor['customer_id'] =
                    $loan->customer_id;

                break;

            /*
            |--------------------------------------------------------------------------
            | شخص خارج از صندوق
            |--------------------------------------------------------------------------
            */

            case GuarantorType::EXTERNAL:

                $guarantor['first_name'] =
                    $data['guarantor2_first_name'] ?? null;

                $guarantor['last_name'] =
                    $data['guarantor2_last_name'] ?? null;

                $guarantor['national_code'] =
                    $data['guarantor2_national_code'] ?? null;

                $guarantor['mobile'] =
                    $data['guarantor2_mobile'] ?? null;

                break;

        }

        $this->loanGuarantorService->create(
            $loan,
            $guarantor
        );
    }

    public function latest(int $limit = 5)
    {
        return Loan::query()
            ->with([
                'customer',
                'loanType',
            ])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function overdue()
    {
        return Loan::query()

            ->whereHas('installments', function ($q) {

                $q->where('status', \App\Enums\InstallmentStatus::PENDING)
                    ->whereDate('due_date', '<', today());

            })

            ->with([

                'customer',

                'loanType',

                'installments' => function ($q) {

                    $q->where('status', \App\Enums\InstallmentStatus::PENDING)
                        ->whereDate('due_date', '<', today())
                        ->orderBy('due_date');

                }

            ])

            ->withCount([

                'installments as overdue_count' => function ($q) {

                    $q->where('status', \App\Enums\InstallmentStatus::PENDING)
                        ->whereDate('due_date', '<', today());

                }

            ])

            ->latest()
            ->get()

            ->sortByDesc(function ($loan) {

                return optional(
                        $loan->installments->first()
                    )->overdue_days ?? 0;

            })

            ->values();
    }

}
