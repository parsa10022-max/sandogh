<?php

namespace App\Services\Loan;

use App\Enums\GuarantorType;
use App\Enums\GuaranteeType;
use App\Models\Loan;
use App\Models\LoanGuarantor;
use Exception;
use Illuminate\Support\Facades\DB;

class LoanGuarantorService
{
    public function create(
        Loan $loan,
        array $data
    ): LoanGuarantor {

        return DB::transaction(function () use ($loan, $data) {

            /*
            |--------------------------------------------------------------------------
            | هر وام فقط دو ضامن دارد
            |--------------------------------------------------------------------------
            */

            if ($loan->guarantors()->count() >= 2) {
                throw new Exception(
                    'هر وام فقط دو ضامن دارد.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | اعتبارسنجی قوانین
            |--------------------------------------------------------------------------
            */

            $this->validateGuarantor(
                $loan,
                $data
            );


            /*
            |--------------------------------------------------------------------------
            | تبدیل Enum
            |--------------------------------------------------------------------------
            */

            $guarantorType = $this->normalizeGuarantorType(
                $data['guarantor_type']
            );

            $guaranteeType = $this->normalizeGuaranteeType(
                $data['guarantee_type']
            );


            /*
            |--------------------------------------------------------------------------
            | نرمال‌سازی اطلاعات ضمانت
            |--------------------------------------------------------------------------
            |
            | چک:
            | مبلغ + سریال + شماره حساب
            |
            | سفته:
            | مبلغ + سریال
            |
            | سایر:
            | اطلاعات ضمانت خالی
            |
            */

            $guaranteeData = $this->normalizeGuaranteeData(
                $guaranteeType,
                $data
            );


            /*
            |--------------------------------------------------------------------------
            | ثبت ضامن
            |--------------------------------------------------------------------------
            */

            return LoanGuarantor::create([

                'loan_id' =>
                    $loan->id,

                'guarantor_order' =>
                    $data['guarantor_order'],

                'guarantor_type' =>
                    $guarantorType,

                'customer_id' =>
                    $data['customer_id'] ?? null,

                'first_name' =>
                    $data['first_name'] ?? null,

                'last_name' =>
                    $data['last_name'] ?? null,

                'national_code' =>
                    $data['national_code'] ?? null,

                'mobile' =>
                    $data['mobile'] ?? null,

                'guarantee_type' =>
                    $guaranteeType,

                'guarantee_number' =>
                    $guaranteeData['guarantee_number'],

                'guarantee_account_number' =>
                    $guaranteeData['guarantee_account_number'],

                'guarantee_amount' =>
                    $guaranteeData['guarantee_amount'],

            ]);

        });
    }


    /**
     * ویرایش ضامن
     */
    public function update(
        LoanGuarantor $guarantor,
        array $data
    ): LoanGuarantor {

        return DB::transaction(function () use (
            $guarantor,
            $data
        ) {

            /*
            |--------------------------------------------------------------------------
            | تبدیل Enum
            |--------------------------------------------------------------------------
            */

            $guarantorType = $this->normalizeGuarantorType(
                $data['guarantor_type']
            );

            $guaranteeType = $this->normalizeGuaranteeType(
                $data['guarantee_type']
            );


            /*
            |--------------------------------------------------------------------------
            | اطلاعات ضمانت
            |--------------------------------------------------------------------------
            */

            $guaranteeData = $this->normalizeGuaranteeData(
                $guaranteeType,
                $data
            );


            /*
            |--------------------------------------------------------------------------
            | بروزرسانی
            |--------------------------------------------------------------------------
            */

            $guarantor->update([

                'guarantor_type' =>
                    $guarantorType,

                'customer_id' =>
                    $data['customer_id'] ?? null,

                'first_name' =>
                    $data['first_name'] ?? null,

                'last_name' =>
                    $data['last_name'] ?? null,

                'national_code' =>
                    $data['national_code'] ?? null,

                'mobile' =>
                    $data['mobile'] ?? null,

                'guarantee_type' =>
                    $guaranteeType,

                'guarantee_number' =>
                    $guaranteeData['guarantee_number'],

                'guarantee_account_number' =>
                    $guaranteeData['guarantee_account_number'],

                'guarantee_amount' =>
                    $guaranteeData['guarantee_amount'],

            ]);


            return $guarantor->fresh();

        });
    }


    /**
     * تبدیل نوع ضامن به مقدار دیتابیس
     */
    private function normalizeGuarantorType(
        GuarantorType|string $type
    ): string {

        if ($type instanceof GuarantorType) {
            return $type->value;
        }

        return GuarantorType::from($type)->value;
    }


    /**
     * تبدیل نوع ضمانت به مقدار دیتابیس
     */
    private function normalizeGuaranteeType(
        GuaranteeType|string $type
    ): string {

        if ($type instanceof GuaranteeType) {
            return $type->value;
        }

        return GuaranteeType::from($type)->value;
    }


    /**
     * نرمال‌سازی اطلاعات ضمانت
     */
    private function normalizeGuaranteeData(
        string $guaranteeType,
        array $data
    ): array {

        /*
        |--------------------------------------------------------------------------
        | مبلغ
        |--------------------------------------------------------------------------
        */

        $amount = $data['guarantee_amount'] ?? null;

        if (is_string($amount)) {
            $amount = str_replace(
                ',',
                '',
                $amount
            );
        }


        /*
        |--------------------------------------------------------------------------
        | چک صیادی
        |--------------------------------------------------------------------------
        */

        if ($guaranteeType === GuaranteeType::CHECK->value) {

            return [

                'guarantee_amount' =>
                    $amount,

                'guarantee_number' =>
                    $data['guarantee_number'] ?? null,

                'guarantee_account_number' =>
                    $data['guarantee_account_number'] ?? null,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | سفته
        |--------------------------------------------------------------------------
        */

        if (
            $guaranteeType ===
            GuaranteeType::PROMISSORY_NOTE->value
        ) {

            return [

                'guarantee_amount' =>
                    $amount,

                'guarantee_number' =>
                    $data['guarantee_number'] ?? null,

                /*
                | سفته شماره حساب ندارد
                */

                'guarantee_account_number' =>
                    null,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | سایر
        |--------------------------------------------------------------------------
        */

        return [

            'guarantee_amount' =>
                null,

            'guarantee_number' =>
                null,

            'guarantee_account_number' =>
                null,

        ];
    }


    /**
     * اعتبارسنجی قوانین ضامن
     */
    private function validateGuarantor(
        Loan $loan,
        array $data
    ): void {

        $type = $data['guarantor_type'];


        /*
        |--------------------------------------------------------------------------
        | تبدیل string به Enum
        |--------------------------------------------------------------------------
        */

        if (is_string($type)) {
            $type = GuarantorType::from($type);
        }


        /*
        |--------------------------------------------------------------------------
        | ضامن اول باید عضو صندوق باشد
        |--------------------------------------------------------------------------
        */

        if (
            $data['guarantor_order'] == 1 &&
            $type !== GuarantorType::CUSTOMER
        ) {
            throw new Exception(
                'ضامن اول باید عضو صندوق باشد.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | عضو صندوق
        |--------------------------------------------------------------------------
        */

        if ($type === GuarantorType::CUSTOMER) {

            if (empty($data['customer_id'])) {

                throw new Exception(
                    'کد مشتری ضامن الزامی است.'
                );

            }


            if (
                $data['customer_id'] ==
                $loan->customer_id
            ) {

                throw new Exception(
                    'برای انتخاب خود وام‌گیرنده، نوع ضامن را "خود وام‌گیرنده" انتخاب کنید.'
                );

            }

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | خود وام گیرنده
        |--------------------------------------------------------------------------
        */

        if ($type === GuarantorType::BORROWER) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | شخص خارج از صندوق
        |--------------------------------------------------------------------------
        */

        if ($type === GuarantorType::EXTERNAL) {

            if (empty($data['first_name'])) {

                throw new Exception(
                    'نام ضامن الزامی است.'
                );
            }


            if (empty($data['last_name'])) {

                throw new Exception(
                    'نام خانوادگی ضامن الزامی است.'
                );
            }


            if (empty($data['national_code'])) {

                throw new Exception(
                    'کد ملی ضامن الزامی است.'
                );
            }
        }
    }
}

