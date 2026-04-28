@extends('layouts.app')

@section('title','Orders')

@section('content')

<style>
.order-card{
background:white;
border-radius:14px;
box-shadow:0 8px 25px rgba(0,0,0,.08);
padding:25px;
}
.order-table thead{ background:#f8fafc; }
.order-table th{
font-weight:600;
color:#475569;
border-bottom:2px solid #e2e8f0;
white-space:nowrap;
}
.order-table td{
vertical-align:middle;
padding:14px;
white-space:nowrap;
}
.order-table tbody tr:hover{ background:#f1f5f9; }
.badge{
padding:6px 10px;
border-radius:20px;
font-size:12px;
}
.search-box{ border-radius:30px; padding-left:20px; }
.filter-select{ border-radius:20px; }
.date-field{
border-radius:30px !important;
padding-left:16px;
height:38px;
}
/* .form-control {
    border-radius: 20px;
} */
/* .btn {
    border-radius: 20px;
} */
</style>

<div class="container-fluid">
<div class="order-card">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="mb-0">Orders</h5>

<!-- ✅ ADD BUTTON -->
<a href="{{ route('orders.create') }}" class="btn btn-success">
+ Add Order
</a>

</div>

<form method="GET">

<!-- 🔹 ROW 1 -->
<div class="row g-2 mb-2">

    <div class="col-lg-3 col-6">
        <select name="order_status" class="form-control filter-select">
            <option value="">Order Status</option>
            <option value="Pending" {{ request('order_status')=='Pending'?'selected':'' }}>Pending</option>
            <option value="Printing" {{ request('order_status')=='Printing'?'selected':'' }}>Printing</option>
            <option value="Ready" {{ request('order_status')=='Ready'?'selected':'' }}>Ready</option>
            <option value="Delivered" {{ request('order_status')=='Delivered'?'selected':'' }}>Delivered</option>
        </select>
    </div>

    <div class="col-lg-3 col-6">
        <select name="payment_status" class="form-control filter-select">
            <option value="">Payment Status</option>
            <option value="Paid" {{ request('payment_status')=='Paid'?'selected':'' }}>Paid</option>
            <option value="Pending" {{ request('payment_status')=='Pending'?'selected':'' }}>Pending</option>
        </select>
    </div>

    <div class="col-lg-3 col-6">
        <select name="staff" class="form-control filter-select">
            <option value="">Staff</option>
            @foreach($orders->pluck('staff')->unique('id') as $staff)
            @if($staff)
            <option value="{{ $staff->name }}">{{ $staff->name }}</option>
            @endif
            @endforeach
        </select>
    </div>

    <div class="col-lg-3 col-6">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control search-box" placeholder="Search Orders">
    </div>

</div>

<!-- 🔹 ROW 2 -->
<div class="row g-2 mb-4">   

    
    <div class="col-lg-3 col-6">
        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
    </div>

    <div class="col-lg-3 col-6">
        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
    </div>

    <div class="col-lg-2 col-6">
        <button class="btn btn-primary w-100">Filter</button>
    </div>

    <div class="col-lg-2 col-6">
        <a href="{{ url()->current() }}" class="btn btn-secondary w-100">Reset</a>
    </div>

    <div class="col-lg-2 col-6">
        <a href="{{ route('orders.export', request()->all()) }}" class="btn btn-success w-100">
            Export Report
        </a>
    </div>

</div>

</form>

<div class="table-responsive">
<table class="table order-table align-middle">

<thead>
<tr>
<!-- <th>ID</th> -->
<th>SR No.</th>
<th>Customer</th>
<th>Mobile</th>
<th>Order Status</th>
<th>Payment</th>
<th>Staff</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>

<tbody>

@forelse($orders as $order)

<tr>

<!-- <td>#{{ $order->id }}</td> -->

<td>
    #{{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
</td>

<!-- ✅ UPDATED CUSTOMER -->
<td>
<strong>
{{ $order->customer->name ?? $order->customer_name }}
</strong>
</td>

<td>{{ $order->mobile }}</td>

<td>
<span class="badge 
@if($order->order_status=='Pending') bg-warning text-dark
@elseif($order->order_status=='Printing') bg-info
@elseif($order->order_status=='Ready') bg-success
@else bg-primary @endif">
{{ $order->order_status }}
</span>
</td>

<td>
<span class="badge {{ $order->payment_status=='Paid'?'bg-success':'bg-danger' }}">
{{ $order->payment_status }}
</span>

@if(auth()->user()->role == 'admin')
<button class="btn btn-sm btn-outline-primary mt-2"
data-bs-toggle="modal"
data-bs-target="#paymentModal{{ $order->id }}">
Edit Payment
</button>
@endif
</td>

<td>{{ $order->staff->name ?? '-' }}</td>

<td class="text-muted">{{ $order->created_at->format('Y-m-d') }}</td>

<td class="d-flex gap-1">

<a href="{{ route('orders.view',$order->id) }}" class="btn btn-sm btn-primary">
View
</a>

<a href="{{ route('orders.edit',$order->id) }}" class="btn btn-sm btn-warning">
Edit
</a>

<form action="{{ route('orders.delete',$order->id) }}" method="POST"
onsubmit="return confirm('Delete this order?')">
@csrf
@method('DELETE')
<button class="btn btn-sm btn-danger">Delete</button>
</form>

</td>

</tr>

@empty

<tr>
<td colspan="8" class="text-center">No Orders Found</td>
</tr>

@endforelse

</tbody>

</table>
</div>

<div class="mt-4">
{{ $orders->links('pagination::bootstrap-5') }}
</div>

</div>
</div>

{{-- PAYMENT MODALS SAME --}}
@foreach($orders as $order)
@if(auth()->user()->role == 'admin')

<div class="modal fade" id="paymentModal{{ $order->id }}" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Update Payment</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form action="{{ route('orders.updatePayment',$order->id) }}" method="POST">
@csrf

<div class="modal-body">

<div class="mb-3">
<label>Status</label>
<select name="payment_status" class="form-control">
<option value="Paid" {{ $order->payment_status=='Paid'?'selected':'' }}>Paid</option>
<option value="Pending" {{ $order->payment_status=='Pending'?'selected':'' }}>Pending</option>
</select>
</div>

<div class="mb-3">
<label>Amount</label>
<input type="number" name="paid_amount" value="{{ $order->paid_amount }}" class="form-control">
</div>

</div>

<div class="modal-footer">
<button class="btn btn-success w-100">Update</button>
</div>

</form>

</div>
</div>
</div>

@endif
@endforeach

@endsection