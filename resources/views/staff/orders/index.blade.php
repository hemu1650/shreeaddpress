@extends('layouts.app')

@section('title','My Orders')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0 rounded-3">

        <!-- Header -->
        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0 fw-bold"></h5>

                <a href="{{ route('staff.orders.create') }}" class="btn btn-primary btn-sm">
                    + Add Order
                </a>
            </div>           

            <!-- Search -->
            <form method="GET">

                <div class="row g-2 mb-2">                    

                    <!-- Search -->
                    <div class="col-lg-3 col-6">
                        <input type="text" name="search" 
                            value="{{ request('search') }}" 
                            class="form-control" 
                            placeholder="Search name / mobile">
                    </div>

                    <!-- From Date -->
                    <div class="col-lg-3 col-6">
                        <input type="date" name="from_date" 
                            value="{{ request('from_date') }}" 
                            class="form-control">
                    </div>

                    <!-- To Date -->
                    <div class="col-lg-3 col-6">
                        <input type="date" name="to_date" 
                            value="{{ request('to_date') }}" 
                            class="form-control">
                    </div>

                     <!-- Order Status -->
                    <div class="col-lg-3 col-6">
                        <select name="order_status" class="form-control">
                            <option value="">Order Status</option>
                            <option value="pending" {{ request('order_status')=='pending'?'selected':'' }}>Pending</option>
                            <option value="printing" {{ request('order_status')=='printing'?'selected':'' }}>Printing</option>
                            <option value="ready" {{ request('order_status')=='ready'?'selected':'' }}>Ready</option>
                            <option value="completed" {{ request('order_status')=='completed'?'selected':'' }}>Completed</option>
                        </select>
                    </div>

                </div>

                <div class="row g-2 mb-4">

                   

                    <!-- Payment Status -->
                    <div class="col-lg-3 col-6">
                        <select name="payment_status" class="form-control">
                            <option value="">Payment Status</option>
                            <option value="pending" {{ request('payment_status')=='pending'?'selected':'' }}>Pending</option>
                            <option value="partial" {{ request('payment_status')=='partial'?'selected':'' }}>Partial</option>
                            <option value="paid" {{ request('payment_status')=='paid'?'selected':'' }}>Paid</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-lg-2 col-6">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>

                    <div class="col-lg-2 col-6">
                        <a href="{{ route('staff.orders.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>

                    <div class="col-lg-2 col-6">
                        <a href="{{ route('staff.orders.export', request()->all()) }}" class="btn btn-success w-100">
                            Export
                        </a>
                    </div>

                </div>

                </form>

        </div>

        <!-- Table -->
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Customer</th>
                            <th>Mobile</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $order)
                        <tr>

                            <td><strong>#{{ $order->id }}</strong></td>

                            <td>
                                {{ $order->customer_name }} <br>
                                <!-- <small class="text-muted">
                                    {{ $order->staff->name ?? '' }}
                                </small> -->
                            </td>

                            <td>{{ $order->mobile }}</td>

                            <!-- Amount -->
                            @php
                                $total = $order->total_amount ?? 0;
                                $discount = $order->discount ?? 0;
                                $paid = $order->paid_amount ?? 0;
                                $due = ($total - $discount) - $paid;
                            @endphp

                            <td>
                                <span class="fw-semibold">
                                    ₹{{ $total }}
                                </span><br>

                                @if($discount > 0)
                                    <small class="text-warning">
                                        Discount: ₹{{ $discount }}
                                    </small><br>
                                @endif

                                <small class="text-success">
                                    Paid: ₹{{ $paid }}
                                </small><br>

                                <small class="text-danger">
                                    Due: ₹{{ $due }}
                                </small>
                            </td>

                            <!-- Payment Status -->
                            <td>
                                <span class="badge 
                                    @if($order->payment_status == 'paid') bg-success
                                    @elseif($order->payment_status == 'partial') bg-warning text-dark
                                    @else bg-danger
                                    @endif">
                                    {{ ucfirst($order->payment_status ?? 'pending') }}
                                </span>
                            </td>

                            <!-- Order Status -->
                            <td>
                                <span class="badge 
                                    @if($order->order_status == 'completed') bg-success
                                    @elseif($order->order_status == 'pending') bg-warning text-dark
                                    @elseif($order->order_status == 'printing') bg-info
                                    @elseif($order->order_status == 'ready') bg-primary
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td>
                                {{ $order->created_at ? $order->created_at->format('d M Y') : '-' }}<br>
                                <small class="text-muted">
                                    Due: {{ $order->due_date ?? '-' }}
                                </small>
                            </td>

                            <!-- Items -->
                            <td>
                                {{ $order->items->count() }}
                            </td>

                            <!-- Actions -->
                            <td class="text-end">

                                <a href="{{ route('staff.orders.view',$order->id) }}" 
                                   class="btn btn-sm btn-info">View</a>

                                <a href="{{ route('staff.orders.edit',$order->id) }}" 
                                   class="btn btn-sm btn-warning">Edit</a>

                                <!-- <a href="#" class="btn btn-sm btn-success">
                                    + Payment
                                </a> -->

                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No Orders Found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
{{ $orders->links('pagination::bootstrap-5') }}
</div>

        </div>

    </div>

</div>

@endsection