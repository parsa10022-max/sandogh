<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Savings\SavingsTransferService;
use Illuminate\Http\Request;

class SavingsTransferCallbackController extends Controller
{

    public function __construct(

        private readonly SavingsTransferService $service

    ) {
    }



    public function handle(Request $request)
    {

        try {


            $transfer =
                $this->service->verifyPayment(

                    $request->all()

                );



            return redirect()

                ->route(
                    'customer.savings-transfer.success',
                    $transfer->id
                );


        } catch (\Throwable $e) {


            return redirect()

                ->route(
                    'customer.savings-transfer.failed'
                )

                ->with(
                    'error',
                    $e->getMessage()
                );

        }

    }

}
