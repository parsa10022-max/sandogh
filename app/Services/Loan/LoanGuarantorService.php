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
            | تبدیل Enum به مقدار دیتابیس
            |--------------------------------------------------------------------------
            */

            $guarantorType = $data['guarantor_type'];

            if ($guarantorType instanceof GuarantorType) {
                $guarantorType = $guarantorType->value;
            }


            $guaranteeType = $data['guarantee_type'];

            if ($guaranteeType instanceof GuaranteeType) {
                $guaranteeType = $guaranteeType->value;
            }


            /*
            |--------------------------------------------------------------------------
            | ثبت ضامن
            |--------------------------------------------------------------------------
            */

            return LoanGuarantor::create([

                'loan_id' => $loan->id,

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
                    $data['guarantee_number'] ?? null,

                'guarantee_account_number' =>
                    $data['guarantee_account_number'] ?? null,

                'guarantee_amount' =>
                    $data['guarantee_amount'] ?? null,

            ]);

        });
    }

    public function update(
        LoanGuarantor $guarantor,
        array $data
    ): LoanGuarantor {


        $guarantorType = $data['guarantor_type'];

        if ($guarantorType instanceof GuarantorType) {
            $guarantorType = $guarantorType->value;
        }


        $guaranteeType = $data['guarantee_type'];

        if ($guaranteeType instanceof GuaranteeType) {
            $guaranteeType = $guaranteeType->value;
        }



        $guarantor->update([

            'guarantor_type' => $guarantorType,

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

        ]);


        return $guarantor;
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
                $data['customer_id'] == $loan->customer_id
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

        /*
        |--------------------------------------------------------------------------
        | غیر عضو صندوق
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

        }


        if (
            ($data['guarantor_type'] ?? null) === GuarantorType::EXTERNAL->value
        ) {

            if (empty($data['national_code'])) {

                throw new Exception(
                    'کد ملی ضامن الزامی است.'
                );

            }

        }

    }
}
