@extends('layouts.app')

@section('title','Customer Details')

@section('content')

<div class="card p-3">

    <!-- 👤 Customer Info -->
    <div class="mb-4">
        <h4>{{ $customer->customer_name }}</h4>
        <p><strong>Mobile:</strong> {{ $customer->mobile }}</p>
        <p><strong>Address:</strong> {{ $customer->address }}</p>
    </div>

    <!-- 📋 Orders Table -->
    <h5 class="mb-3">Orders</h5>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>

                    <td>
                        <span class="badge bg-info">
                            {{ $order->order_status }}
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-warning text-dark">
                            {{ $order->payment_status }}
                        </span>
                    </td>

                    <td class="fw-bold">
                        ₹ {{ number_format($order->paid_amount,2) }}
                    </td>

                    <td>
                        {{ $order->due_date ?? '-' }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>



</div>

@endsection