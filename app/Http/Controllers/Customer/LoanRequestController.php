<?php

namespace App\Http\Controllers\Customer;

use App\Enums\LoanRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequest\StoreCustomerLoanRequestRequest;
use App\Models\LoanRequest;
use Carbon\Carbon;

class LoanRequestController extends Controller
{
    /**
     * فرم ثبت درخواست وام
     */
    public function create()
    {
        $customer = auth()->user()->customer;

        /*
        |--------------------------------------------------------------------------
        | 1. بررسی وام فعال
        |--------------------------------------------------------------------------
        */

        if ($customer->loans()->active()->exists()) {

            return redirect()
                ->route('customer.loan-requests.index')
                ->with(
                    'error',
                    'شما در حال حاضر یک وام فعال دارید و امکان ثبت درخواست وام جدید وجود ندارد.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 2. بررسی درخواست در حال بررسی
        |--------------------------------------------------------------------------
        */

        $hasPendingRequest = LoanRequest::query()
            ->where('customer_id', $customer->id)
            ->where(
                'status',
                LoanRequestStatus::PENDING->value
            )
            ->exists();

        if ($hasPendingRequest) {

            return redirect()
                ->route('customer.loan-requests.index')
                ->with(
                    'error',
                    'شما یک درخواست وام در حال بررسی دارید و امکان ثبت درخواست جدید وجود ندارد.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 3. بررسی آخرین درخواست رد شده
        |--------------------------------------------------------------------------
        */

        $latestRejectedRequest = LoanRequest::query()
            ->where('customer_id', $customer->id)
            ->where(
                'status',
                LoanRequestStatus::REJECTED->value
            )
            ->latest('id')
            ->first();


        if ($latestRejectedRequest?->next_review_date) {

            $nextReviewDate = Carbon::parse(
                $latestRejectedRequest->next_review_date
            )->startOfDay();

            $today = now()->startOfDay();


            /*
            |--------------------------------------------------------------------------
            | هنوز تاریخ مراجعه نرسیده
            |--------------------------------------------------------------------------
            */

            if ($today->lt($nextReviewDate)) {

                return redirect()
                    ->route('customer.loan-requests.index')
                    ->with(
                        'error',
                        'امکان ثبت درخواست جدید تا تاریخ ' .
                        jdate($nextReviewDate)->format('Y/m/d') .
                        ' وجود ندارد.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | اجازه ثبت درخواست
        |--------------------------------------------------------------------------
        */

        return view('customer.loan_requests.create');
    }



public function store(
    StoreCustomerLoanRequestRequest $request
) {
    $customer = auth()->user()->customer;

    /*
    |--------------------------------------------------------------------------
    | 1. بررسی وام فعال
    |--------------------------------------------------------------------------
    */

    if ($customer->loans()->active()->exists()) {

        return back()
            ->withInput()
            ->with(
                'error',
                'شما در حال حاضر یک وام فعال دارید و امکان ثبت درخواست وام جدید وجود ندارد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | 2. بررسی درخواست در حال بررسی
    |--------------------------------------------------------------------------
    */

    $hasPendingRequest = LoanRequest::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->where(
            'status',
            LoanRequestStatus::PENDING->value
        )
        ->exists();


    if ($hasPendingRequest) {

        return back()
            ->withInput()
            ->with(
                'error',
                'شما یک درخواست وام در حال بررسی دارید و امکان ثبت درخواست جدید وجود ندارد.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | 3. بررسی آخرین درخواست رد شده
    |--------------------------------------------------------------------------
    */

    $latestRejectedRequest = LoanRequest::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->where(
            'status',
            LoanRequestStatus::REJECTED->value
        )
        ->latest('id')
        ->first();


    if ($latestRejectedRequest?->next_review_date) {

        $nextReviewDate = Carbon::parse(
            $latestRejectedRequest->next_review_date
        )->startOfDay();

        $today = now()->startOfDay();


        /*
        |--------------------------------------------------------------------------
        | هنوز تاریخ مراجعه نرسیده
        |--------------------------------------------------------------------------
        */

        if ($today->lt($nextReviewDate)) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'امکان ثبت درخواست جدید تا تاریخ ' .
                    jdate($nextReviewDate)->format('Y/m/d') .
                    ' وجود ندارد.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | 4. ثبت درخواست جدید
    |--------------------------------------------------------------------------
    */

    LoanRequest::create([

        'customer_id' =>
            $customer->id,

        'requested_amount' =>
            $request->validated('requested_amount'),

        'description' =>
            $request->validated('description'),

        'status' =>
            LoanRequestStatus::PENDING,
    ]);


    /*
    |--------------------------------------------------------------------------
    | 5. موفقیت
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route(
            'customer.loan-requests.index'
        )
        ->with(
            'success',
            'درخواست وام شما با موفقیت ثبت شد و در حال بررسی است.'
        );
}

    /**
     * لیست درخواست‌های وام مشتری
     */
    public function index()
    {
        $customer = auth()->user()->customer;


        /*
        |--------------------------------------------------------------------------
        | وام فعال
        |--------------------------------------------------------------------------
        */

        $hasActiveLoan = $customer->loans()
            ->active()
            ->exists();


        /*
        |--------------------------------------------------------------------------
        | درخواست در حال بررسی
        |--------------------------------------------------------------------------
        */

        $hasPendingRequest = LoanRequest::query()
            ->where('customer_id', $customer->id)
            ->where(
                'status',
                LoanRequestStatus::PENDING->value
            )
            ->exists();


        /*
        |--------------------------------------------------------------------------
        | آخرین درخواست رد شده
        |--------------------------------------------------------------------------
        */

        $latestRejectedRequest = LoanRequest::query()
            ->where('customer_id', $customer->id)
            ->where(
                'status',
                LoanRequestStatus::REJECTED->value
            )
            ->latest('id')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | امکان ثبت درخواست جدید
        |--------------------------------------------------------------------------
        */

        $canCreateNewRequest = true;


        // وام فعال
        if ($hasActiveLoan) {
            $canCreateNewRequest = false;
        }


        // درخواست در حال بررسی
        if ($hasPendingRequest) {
            $canCreateNewRequest = false;
        }


        // محدودیت تاریخ درخواست رد شده
        if (
            $canCreateNewRequest &&
            $latestRejectedRequest?->next_review_date
        ) {

            $nextReviewDate = Carbon::parse(
                $latestRejectedRequest->next_review_date
            )->startOfDay();

            if (
                now()->startOfDay()->lt($nextReviewDate)
            ) {
                $canCreateNewRequest = false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | درخواست‌های مشتری
        |--------------------------------------------------------------------------
        */

        $loanRequests = LoanRequest::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->with([
                'loanType',
                'loan',
            ])
            ->latest('id')
            ->paginate(10);


        return view(
            'customer.loan_requests.index',
            compact(
                'loanRequests',
                'hasActiveLoan',
                'hasPendingRequest',
                'latestRejectedRequest',
                'canCreateNewRequest'
            )
        );
    }


    /**
     * نمایش جزئیات درخواست
     */
    public function show(
        LoanRequest $loanRequest
    ) {
        $customer = auth()->user()->customer;


        /*
        |--------------------------------------------------------------------------
        | امنیت
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $loanRequest->customer_id === $customer->id,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | روابط
        |--------------------------------------------------------------------------
        */

        $loanRequest->load([
            'loanType',
            'loan',
        ]);


        return view(
            'customer.loan_requests.show',
            compact('loanRequest')
        );
    }
}
