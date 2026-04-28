@extends('layouts.app')

@section('title','Customer Ledger')

@section('content')

<div class="card p-3">

    <h4>{{ $customer->customer_name }} - Ledger</h4>
    <p><strong>Mobile:</strong> {{ $customer->mobile }}</p>

    <div class="table-responsive mt-3">
        <table class="table table-bordered">

            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Amount</th>
                </tr>
            </thead>

            <tbody>
                @php $total = 0; @endphp

                @foreach($orders as $order)

                    @php
                        $amount = $order->paid_amount ?? 0;
                        $total += $amount;
                    @endphp

                    <tr>
                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</td>

                        <td>#{{ $order->id }}</td>

                        <td>{{ $order->order_status }}</td>

                        <td>{{ $order->payment_status }}</td>

                        <td>₹ {{ number_format($amount,2) }}</td>
                    </tr>

                @endforeach

                <!-- 🔢 Total -->
                <tr class="table-success">
                    <td colspan="4"><strong>Total</strong></td>
                    <td><strong>₹ {{ number_format($total,2) }}</strong></td>
                </tr>

            </tbody>

        </table>
    </div>

</div>

@endsection