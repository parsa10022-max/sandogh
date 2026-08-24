<?php

namespace App\Http\Controllers\Loan;

use App\Enums\LoanRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequest\ApproveLoanRequestRequest;
use App\Http\Requests\LoanRequest\RejectLoanRequestRequest;
use App\Http\Requests\LoanRequest\StoreLoanRequestRequest;
use App\Models\Customer;
use App\Models\LoanRequest;
use App\Models\Notification;
use App\Services\Date\JalaliDateService;
use App\Services\LoanType\LoanTypeService;
use Illuminate\Http\Request;

class LoanRequestController extends Controller
{
    private LoanTypeService $loanTypeService;

    public function __construct(
        LoanTypeService $loanTypeService
    ) {
        $this->loanTypeService = $loanTypeService;
    }

    /**
     * لیست درخواست‌ها
     */
    public function index()
    {
        $loanRequests = LoanRequest::query()
            ->with([
                'customer',
                'loan',
            ])
            ->latest()
            ->paginate(15);

        return view(
            'loan_requests.index',
            compact('loanRequests')
        );
    }

    /**
     * فرم ثبت درخواست توسط مدیر
     */
    public function create()
    {
        $customers = Customer::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view(
            'loan_requests.create',
            compact('customers')
        );
    }

    /**
     * ثبت درخواست توسط مدیر
     */
    public function store(
        StoreLoanRequestRequest $request
    ) {
        LoanRequest::create([
            'customer_id' =>
                $request->validated('customer_id'),

            'requested_amount' =>
                $request->validated('requested_amount'),

            'description' =>
                $request->validated('description'),

            'status' =>
                LoanRequestStatus::PENDING,
        ]);

        return redirect()
            ->route('loan-requests.index')
            ->with(
                'success',
                'درخواست وام ثبت شد.'
            );
    }

    /**
     * نمایش درخواست
     */
    public function show(
        LoanRequest $loanRequest
    ) {
        $loanRequest->load([
            'customer',
            'customer.user',
            'loanType',
            'loan',
        ]);

        $loanTypes =
            $this->loanTypeService->getActive();

        return view(
            'loan_requests.show',
            compact(
                'loanRequest',
                'loanTypes'
            )
        );
    }

    /**
     * فرم ویرایش درخواست
     */
    public function edit(
        LoanRequest $loanRequest
    ) {
        $loanRequest->load([
            'customer',
            'customer.user',
            'loanType',
            'loan',
        ]);

        $loanTypes =
            $this->loanTypeService->getActive();

        return view(
            'loan_requests.edit',
            compact(
                'loanRequest',
                'loanTypes'
            )
        );
    }

    /**
     * بروزرسانی درخواست توسط مدیر
     */
    public function update(
        Request $request,
        LoanRequest $loanRequest
    ) {
        $validated = $request->validate([

            'status' => [
                'required',
                'in:' . implode(',', [
                    LoanRequestStatus::PENDING->value,
                    LoanRequestStatus::APPROVED->value,
                    LoanRequestStatus::REJECTED->value,
                ]),
            ],

            'approved_amount' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'loan_type_id' => [
                'nullable',
                'exists:loan_types,id',
            ],

            'approved_installment_count' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'approved_installment_interval' => [
                'nullable',
                'integer',
                'in:1,2,3',
            ],

            'review_note' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'next_review_date' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | تبدیل تاریخ شمسی به میلادی
        |--------------------------------------------------------------------------
        */

        $nextReviewDate = null;

        if (
            !empty($validated['next_review_date'])
        ) {
            $nextReviewDate =
                app(JalaliDateService::class)
                    ->toGregorian(
                        $validated['next_review_date']
                    );
        }

        /*
        |--------------------------------------------------------------------------
        | تایید شده → تاریخ مراجعه مجدد حذف شود
        |--------------------------------------------------------------------------
        */

        if (
            $validated['status'] ===
            LoanRequestStatus::APPROVED->value
        ) {
            $nextReviewDate = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Pending → تاریخ مراجعه مجدد حذف شود
        |--------------------------------------------------------------------------
        */

        if (
            $validated['status'] ===
            LoanRequestStatus::PENDING->value
        ) {
            $nextReviewDate = null;
        }

        /*
        |--------------------------------------------------------------------------
        | بروزرسانی
        |--------------------------------------------------------------------------
        */

        $loanRequest->update([

            'status' =>
                $validated['status'],

            'approved_amount' =>
                $validated['approved_amount'] ?? null,

            'loan_type_id' =>
                $validated['loan_type_id'] ?? null,

            'approved_installment_count' =>
                $validated['approved_installment_count'] ?? null,

            'approved_installment_interval' =>
                $validated['approved_installment_interval'] ?? null,

            'review_note' =>
                $validated['review_note'] ?? null,

            'next_review_date' =>
                $nextReviewDate,

            'reviewed_by' =>
                auth()->id(),

            'reviewed_at' =>
                now(),
        ]);

        return redirect()
            ->route(
                'loan-requests.show',
                $loanRequest
            )
            ->with(
                'success',
                'اطلاعات درخواست وام با موفقیت ویرایش شد.'
            );
    }

    /**
     * تایید درخواست
     */
    /**
     * تایید درخواست
     */
    public function approve(
        ApproveLoanRequestRequest $request,
        LoanRequest $loanRequest
    ) {
        /*
        |--------------------------------------------------------------------------
        | فقط درخواست Pending قابل تایید است
        |--------------------------------------------------------------------------
        */

        if (
            $loanRequest->status !==
            LoanRequestStatus::PENDING
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'این درخواست قبلاً بررسی شده است.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | دریافت اطلاعات مشتری و User
        |--------------------------------------------------------------------------
        */

        $loanRequest->load([
            'customer.user',
        ]);

        $user = $loanRequest->customer?->user;

        /*
        |--------------------------------------------------------------------------
        | دریافت اطلاعات تایید
        |--------------------------------------------------------------------------
        */

        $approvedAmount =
            $request->validated('approved_amount');

        $loanTypeId =
            $request->validated('loan_type_id');

        $approvedInstallmentCount =
            $request->validated(
                'approved_installment_count'
            );

        $approvedInstallmentInterval =
            $request->validated(
                'approved_installment_interval'
            );

        $reviewNote =
            $request->validated('review_note');

        /*
        |--------------------------------------------------------------------------
        | تایید درخواست
        |--------------------------------------------------------------------------
        */

        $loanRequest->update([

            'status' =>
                LoanRequestStatus::APPROVED,

            'approved_amount' =>
                $approvedAmount,

            'loan_type_id' =>
                $loanTypeId,

            'approved_installment_count' =>
                $approvedInstallmentCount,

            'approved_installment_interval' =>
                $approvedInstallmentInterval,

            'review_note' =>
                $reviewNote,

            'reviewed_by' =>
                auth()->id(),

            'reviewed_at' =>
                now(),

            /*
            |--------------------------------------------------------------------------
            | درخواست تایید شده دیگر تاریخ مراجعه ندارد
            |--------------------------------------------------------------------------
            */

            'next_review_date' =>
                null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | ایجاد اعلان برای مشتری
        |--------------------------------------------------------------------------
        |
        | اطلاعات مهم نتیجه تایید داخل data ذخیره می‌شود
        | تا اعلان مستقل از تغییرات بعدی LoanRequest باشد.
        |
        */

        if ($user) {

            Notification::create([

                'user_id' =>
                    $user->id,

                'type' =>
                    'loan_request_approved',

                'title' =>
                    'درخواست وام تأیید شد',

                'message' =>
                    'درخواست وام شما با مبلغ ' .
                    number_format($approvedAmount) .
                    ' ریال تأیید شد.',

                'data' => [

                    /*
                    |--------------------------------------------------------------------------
                    | شناسه درخواست
                    |--------------------------------------------------------------------------
                    */

                    'loan_request_id' =>
                        $loanRequest->id,

                    /*
                    |--------------------------------------------------------------------------
                    | مبلغ تایید شده
                    |--------------------------------------------------------------------------
                    */

                    'approved_amount' =>
                        $approvedAmount,

                    /*
                    |--------------------------------------------------------------------------
                    | تعداد اقساط
                    |--------------------------------------------------------------------------
                    */

                    'approved_installment_count' =>
                        $approvedInstallmentCount,

                    /*
                    |--------------------------------------------------------------------------
                    | دوره بازپرداخت
                    |--------------------------------------------------------------------------
                    */

                    'approved_installment_interval' =>
                        $approvedInstallmentInterval,

                    /*
                    |--------------------------------------------------------------------------
                    | پیام مدیر
                    |--------------------------------------------------------------------------
                    */

                    'review_note' =>
                        $reviewNote,
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'درخواست وام تایید شد.'
            );
    }


    /**
     * رد درخواست
     */
    /**
     * رد درخواست
     */
    public function reject(
        RejectLoanRequestRequest $request,
        LoanRequest $loanRequest
    ) {
        /*
        |--------------------------------------------------------------------------
        | فقط درخواست Pending قابل رد است
        |--------------------------------------------------------------------------
        */

        if (
            $loanRequest->status !==
            LoanRequestStatus::PENDING
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'این درخواست قبلاً بررسی شده است.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | دریافت اطلاعات مشتری و User
        |--------------------------------------------------------------------------
        */

        $loanRequest->load([
            'customer.user',
        ]);

        $user = $loanRequest->customer?->user;


        /*
        |--------------------------------------------------------------------------
        | ذخیره اطلاعات قبل از تغییر
        |--------------------------------------------------------------------------
        */

        $requestedAmount =
            $loanRequest->requested_amount;

        $reviewNote =
            $request->validated('review_note');


        /*
        |--------------------------------------------------------------------------
        | تاریخ مراجعه مجدد
        |--------------------------------------------------------------------------
        |
        | nextReviewDateJalali:
        | همان تاریخ شمسی که مدیر وارد کرده
        |
        | nextReviewDate:
        | تبدیل شده به میلادی برای ذخیره در LoanRequest
        |
        */

        $nextReviewDate = null;

        $nextReviewDateJalali = null;


        if (
            $request->filled('next_review_date')
        ) {

            /*
            |--------------------------------------------------------------------------
            | تاریخ شمسی اصلی
            |--------------------------------------------------------------------------
            */

            $nextReviewDateJalali =
                $request->validated(
                    'next_review_date'
                );


            /*
            |--------------------------------------------------------------------------
            | تبدیل تاریخ شمسی به میلادی
            |--------------------------------------------------------------------------
            */

            $nextReviewDate =
                app(JalaliDateService::class)
                    ->toGregorian(
                        $nextReviewDateJalali
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | رد درخواست
        |--------------------------------------------------------------------------
        */

        $loanRequest->update([

            'status' =>
                LoanRequestStatus::REJECTED,

            'review_note' =>
                $reviewNote,

            /*
            |--------------------------------------------------------------------------
            | در LoanRequest تاریخ میلادی ذخیره می‌شود
            |--------------------------------------------------------------------------
            */

            'next_review_date' =>
                $nextReviewDate,

            'reviewed_by' =>
                auth()->id(),

            'reviewed_at' =>
                now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | ایجاد اعلان برای مشتری
        |--------------------------------------------------------------------------
        |
        | اعلان مستقل از LoanRequest ذخیره می‌شود.
        |
        | تاریخ داخل Notification عمداً شمسی ذخیره می‌شود
        | تا مشکل اختلاف یک روز به دلیل Timezone ایجاد نشود.
        |
        */

        if ($user) {

            Notification::create([

                /*
                |--------------------------------------------------------------------------
                | User مشتری
                |--------------------------------------------------------------------------
                */

                'user_id' =>
                    $user->id,


                /*
                |--------------------------------------------------------------------------
                | نوع اعلان
                |--------------------------------------------------------------------------
                */

                'type' =>
                    'loan_request_rejected',


                /*
                |--------------------------------------------------------------------------
                | عنوان
                |--------------------------------------------------------------------------
                */

                'title' =>
                    'درخواست وام تأیید نشد',


                /*
                |--------------------------------------------------------------------------
                | پیام عمومی سیستم
                |--------------------------------------------------------------------------
                |
                | پیام مدیر در این قسمت قرار نمی‌گیرد
                | تا در داشبورد دوبار نمایش داده نشود.
                |
                */

                'message' =>
                    'درخواست وام شما پس از بررسی مورد موافقت قرار نگرفت.',


                /*
                |--------------------------------------------------------------------------
                | اطلاعات کامل اعلان
                |--------------------------------------------------------------------------
                */

                'data' => [

                    /*
                    |--------------------------------------------------------------------------
                    | شناسه درخواست
                    |--------------------------------------------------------------------------
                    */

                    'loan_request_id' =>
                        $loanRequest->id,


                    /*
                    |--------------------------------------------------------------------------
                    | مبلغ درخواستی
                    |--------------------------------------------------------------------------
                    */

                    'requested_amount' =>
                        $requestedAmount,


                    /*
                    |--------------------------------------------------------------------------
                    | پیام مدیر
                    |--------------------------------------------------------------------------
                    */

                    'review_note' =>
                        $reviewNote,


                    /*
                    |--------------------------------------------------------------------------
                    | تاریخ مراجعه مجدد
                    |--------------------------------------------------------------------------
                    |
                    | اینجا تاریخ شمسی ذخیره می‌شود.
                    |
                    */

                    'next_review_date' =>
                        $nextReviewDateJalali,
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | بازگشت به صفحه مدیریت
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->back()
            ->with(
                'success',
                'درخواست وام رد شد.'
            );
    }

/**
 * تغییر تاریخ مراجعه مجدد
 */
public function updateReviewDate(
    Request $request,
    LoanRequest $loanRequest
) {
    /*
    |--------------------------------------------------------------------------
    | فقط درخواست رد شده
    |--------------------------------------------------------------------------
    */

    if (
        $loanRequest->status !==
        LoanRequestStatus::REJECTED
    ) {
        return redirect()
            ->back()
            ->with(
                'error',
                'فقط درخواست رد شده امکان تغییر تاریخ مراجعه مجدد دارد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([

        'next_review_date' => [
            'required',
            'string',
        ],

    ]);


    /*
    |--------------------------------------------------------------------------
    | تاریخ شمسی جدید
    |--------------------------------------------------------------------------
    |
    | مثال:
    | 1405/09/30
    |
    */

    $nextReviewDateJalali =
        $validated['next_review_date'];


    /*
    |--------------------------------------------------------------------------
    | تبدیل تاریخ شمسی به میلادی
    |--------------------------------------------------------------------------
    |
    | برای ذخیره در LoanRequest
    |
    */

    $nextReviewDate =
        app(JalaliDateService::class)
            ->toGregorian(
                $nextReviewDateJalali
            );


    /*
    |--------------------------------------------------------------------------
    | ذخیره تاریخ جدید در LoanRequest
    |--------------------------------------------------------------------------
    */

    $loanRequest->update([

        'next_review_date' =>
            $nextReviewDate,

        'reviewed_by' =>
            auth()->id(),

        'reviewed_at' =>
            now(),

    ]);


    /*
    |--------------------------------------------------------------------------
    | دریافت User مشتری
    |--------------------------------------------------------------------------
    */

    $loanRequest->load([
        'customer.user',
    ]);

    $user =
        $loanRequest->customer?->user;


    /*
    |--------------------------------------------------------------------------
    | به‌روزرسانی Notification قبلی
    |--------------------------------------------------------------------------
    |
    | اعلان جدید ایجاد نمی‌کنیم.
    |
    | همان اعلان رد درخواست را پیدا می‌کنیم.
    |
    */

    if ($user) {

        $notification =
            Notification::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'type',
                    'loan_request_rejected'
                )
                ->whereJsonContains(
                    'data->loan_request_id',
                    $loanRequest->id
                )
                ->latest('id')
                ->first();


        /*
        |--------------------------------------------------------------------------
        | اعلان پیدا شد
        |--------------------------------------------------------------------------
        */

        if ($notification) {

            $data =
                $notification->data ?? [];


            /*
            |--------------------------------------------------------------------------
            | اطلاعات Notification
            |--------------------------------------------------------------------------
            */

            $data['loan_request_id'] =
                $loanRequest->id;

            $data['requested_amount'] =
                $loanRequest->requested_amount;

            $data['review_note'] =
                $loanRequest->review_note;

            /*
            |--------------------------------------------------------------------------
            | تاریخ جدید به صورت شمسی
            |--------------------------------------------------------------------------
            */

            $data['next_review_date'] =
                $nextReviewDateJalali;


            /*
            |--------------------------------------------------------------------------
            | ذخیره Notification
            |--------------------------------------------------------------------------
            |
            | read_at = null
            |
            | یعنی این تغییر برای مشتری یک اعلان
            | جدید محسوب می‌شود و دوباره نمایش داده خواهد شد.
            |
            */

            $notification->update([

                'data' =>
                    $data,

                'read_at' =>
                    null,

            ]);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | بازگشت به صفحه مدیریت
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->back()
        ->with(
            'success',
            'تاریخ مراجعه مجدد با موفقیت تغییر کرد.'
        );
}



    /**
     * حذف درخواست
     */
    public function destroy(
        LoanRequest $loanRequest
    ) {
        /*
        |--------------------------------------------------------------------------
        | اگر برای درخواست وام ساخته شده باشد،
        | حذف درخواست خطرناک است.
        |--------------------------------------------------------------------------
        */

        if ($loanRequest->loan_id) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'این درخواست دارای وام است و امکان حذف آن وجود ندارد.'
                );
        }

        $loanRequest->delete();

        return redirect()
            ->route(
                'loan-requests.index'
            )
            ->with(
                'success',
                'درخواست وام حذف شد.'
            );
    }
}
