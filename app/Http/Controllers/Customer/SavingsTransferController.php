<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Savings\SavingsTransferService;
use Illuminate\Http\Request;


class SavingsTransferController extends Controller
{

    public function __construct(
        private readonly SavingsTransferService $service
    ) {
    }



    /**
     * فرم واریز
     */
    public function create()
    {
        return view(
            'customer.savings-transfer.create'
        );
    }



    /**
     * جستجوی عضو مقصد
     */
    public function search(Request $request)
    {
        $request->validate([

            'keyword' => 'required|string',

        ]);


        $keyword = $request->keyword;



        $customer = Customer::query()

            ->active()

            ->where(function ($query) use ($keyword) {


                $query->where(
                    'customer_code',
                    $keyword
                )


                    ->orWhere(
                        'national_code',
                        $keyword
                    )


                    ->orWhereHas(
                        'accounts',
                        function ($q) use ($keyword) {

                            $q->where(
                                'account_number',
                                $keyword
                            );

                        }
                    );


            })

            ->first();



        if (! $customer) {

            return response()->json([

                'found' => false,

                'message' =>
                    'عضو مورد نظر پیدا نشد.'

            ]);

        }



        return response()->json([

            'found' => true,


            'customer' => [

                'id' =>
                    $customer->id,


                'code' =>
                    $customer->customer_code,


                'name' =>
                    $customer->full_name,

            ]

        ]);

    }



    /**
     * شروع پرداخت
     */
    public function store(Request $request)
    {

        $request->validate([

            'receiver_customer_id' =>
                'required|exists:customers,id',

            'amount' =>
                'required|integer|min:1000',

        ]);



        $receiver = Customer::findOrFail(
            $request->receiver_customer_id
        );



        $result =
            $this->service->startPayment(

                $receiver,

                $request->amount

            );



        return redirect(
            $result['gateway']['redirect_url']
        );

    }

}
