<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();

        return view('admin.reports.index', compact('totalOrders'));
    }
}
