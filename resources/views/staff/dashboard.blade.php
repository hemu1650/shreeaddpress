@extends('layouts.app')

@section('title','Staff Dashboard')

@section('content')

<style>
body{ background:#f1f5f9; }

.section-box{
background:#fff;
border-radius:20px;
padding:16px;
box-shadow:0 8px 20px rgba(0,0,0,.08);
margin-bottom:20px;
}

.stat-card{
border-radius:16px;
padding:14px;
display:flex;
align-items:center;
gap:12px;
box-shadow:0 6px 14px rgba(0,0,0,.08);
transition:.2s;
}

.stat-card:hover{
transform: translateY(-4px);
cursor:pointer;
}

.blue{ background:linear-gradient(135deg,#3b82f6,#60a5fa); color:#fff;}
.red{ background:linear-gradient(135deg,#ef4444,#f87171); color:#fff;}
.green{ background:linear-gradient(135deg,#22c55e,#4ade80); color:#fff;}
.orange{ background:linear-gradient(135deg,#f59e0b,#fbbf24); color:#fff;}

.icon{
width:42px;
height:42px;
border-radius:12px;
display:flex;
align-items:center;
justify-content:center;
background:rgba(255,255,255,.2);
}

.title{ font-size:13px; opacity:.85; }
.value{ font-size:20px; font-weight:700; }

a{text-decoration:none;}
</style>

<div class="container-fluid">

<!-- ================= ORDERS ================= -->
<div class="section-box">
<strong>My Orders</strong>

<div class="row g-3 mt-2">

<div class="col-md-3 col-6">
<a href="{{ route('staff.orders.index') }}">
<div class="stat-card blue">
<div class="icon"><i class="fas fa-shopping-cart"></i></div>
<div>
<div class="title">Total Orders</div>
<div class="value">{{ $totalOrders }}</div>
</div>
</div>
</a>
</div>

<div class="col-md-3 col-6">
<a href="{{ route('staff.orders.index', ['order_status'=>'pending']) }}">
<div class="stat-card red">
<div class="icon"><i class="fas fa-clock"></i></div>
<div>
<div class="title">Pending</div>
<div class="value">{{ $pending }}</div>
</div>
</div>
</a>
</div>

<div class="col-md-3 col-6">
<a href="{{ route('staff.orders.index', ['order_status'=>'printing']) }}">
<div class="stat-card red">
<div class="icon"><i class="fas fa-print"></i></div>
<div>
<div class="title">Printing</div>
<div class="value">{{ $printing }}</div>
</div>
</div>
</a>
</div>

<div class="col-md-3 col-6">
<a href="{{ route('staff.orders.index', ['order_status'=>'ready']) }}">
<div class="stat-card green">
<div class="icon"><i class="fas fa-check"></i></div>
<div>
<div class="title">Ready</div>
<div class="value">{{ $ready }}</div>
</div>
</div>
</a>
</div>

<div class="col-md-3 col-6">
<a href="{{ route('staff.orders.index', ['order_status'=>'delivered']) }}">
<div class="stat-card green">
<div class="icon"><i class="fas fa-check-double"></i></div>
<div>
<div class="title">Delivered</div>
<div class="value">{{ $delivered }}</div>
</div>
</div>
</a>
</div>

</div>
</div>

<!-- ================= NEW: CUSTOMERS & DUE ================= -->
<div class="section-box">
<strong>Customers & Due</strong>

<div class="row g-3 mt-2">

<!-- TOTAL CUSTOMERS -->
<div class="col-md-3 col-6">
<a href="https://shreegroupsskp.com/staff/customers">
<div class="stat-card blue">
<div class="icon"><i class="fas fa-users"></i></div>
<div>
<div class="title">Total Customers</div>
<div class="value">{{ $totalCustomers }}</div>
</div>
</div>
</a>
</div>

<!-- TOTAL DUE -->
<div class="col-md-3 col-6">
<a href="https://shreegroupsskp.com/admin/customers/due">
<div class="stat-card red">
<div class="icon"><i class="fas fa-rupee-sign"></i></div>
<div>
<div class="title">Total Due</div>
<div class="value">₹{{ number_format($totalDueAmount) }}</div>
</div>
</div>
</a>
</div>

</div>
</div>

<!-- ================= TODAY REMINDERS ================= -->
<div class="section-box">
<strong>Today Reminders</strong>

<div class="row g-3 mt-2">

<div class="col-md-3 col-6">
<a href="{{ route('staff.orders.index', ['type'=>'today_reminder']) }}">
<div class="stat-card orange">
<div class="icon"><i class="fas fa-bell"></i></div>
<div>
<div class="title">Today Reminders</div>
<div class="value">{{ $todayReminderCount }}</div>
</div>
</div>
</a>
</div>

</div>

<div class="mt-3">
<table class="table table-bordered">
<thead>
<tr>
<th>Customer</th>
<th>Mobile</th>
<th>Due Date</th>
<th>Amount</th>
<th>Action</th>
</tr>
</thead>

<tbody>
@forelse($todayReminders as $order)
<tr>
<td>{{ $order->customer_name }}</td>
<td>{{ $order->mobile }}</td>
<td>{{ $order->due_date }}</td>
<td>₹{{ number_format($order->paid_amount) }}</td>
<td>
<a href="tel:{{ $order->mobile }}" class="btn btn-sm btn-primary">Call</a>

<a href="https://wa.me/91{{ $order->mobile }}?text=Payment%20Reminder" 
target="_blank" 
class="btn btn-sm btn-success">WhatsApp</a>
</td>
</tr>
@empty
<tr>
<td colspan="5" class="text-center">No reminders today</td>
</tr>
@endforelse
</tbody>
</table>
</div>

</div>

</div>

@endsection