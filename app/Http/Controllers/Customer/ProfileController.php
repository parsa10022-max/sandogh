<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $customer = $user->customer;

        return view(
            'customer.profile.index',
            compact('user', 'customer')
        );
    }
}

