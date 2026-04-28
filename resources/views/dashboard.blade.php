@extends('layouts.app')

@section('title','Dashboard')

@section('content')

<style>

</style>

<div class="container-fluid">

<!-- HEADER + FILTER -->
<div class="dashboard-header">
<h4></h4>

<form method="GET" class="d-flex gap-2">
<select name="month" class="form-control">
@for($m=1;$m<=12;$m++)
<option value="{{ $m }}" {{ $month==$m?'selected':'' }}>
{{ \Carbon\Carbon::create()->month($m)->format('F') }}
</option>
@endfor
</select>

<select name="year" class="form-control">
@for($y=2023;$y<=2030;$y++)
<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>
{{ $y }}
</option>
@endfor
</select>

<button class="btn btn-primary">Apply</button>
</form>
</div>

<!-- ================= CHARTS ================= -->
<div class="row">

<div class="col-sm-6">
<div class="section-box">
<strong>Orders & Collection</strong>
<canvas id="orderChart" height="150"></canvas>
</div>
</div>

<div class="col-sm-6">
<div class="section-box">
<strong>Staff Attendance</strong>
<canvas id="staffChart" height="150"></canvas>
</div>
</div>

</div>

<!-- ================= ORDERS ================= -->
 <div class="section-box">
    <strong>Orders</strong>
    <div class="row g-3 mt-2">

        <!-- Total Orders -->
        <div class="col-md-4 col-6">
            <a href="https://shreegroupsskp.com/admin/orders" class="text-decoration-none text-dark">
                <div class="stat-card blue">
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    <div>
                        <div class="title">Total Orders</div>
                        <div class="value">{{ $totalOrders }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Pending -->
        <div class="col-md-2 col-6">
            <a href="https://shreegroupsskp.com/admin/orders?order_status=Pending" class="text-decoration-none text-dark">
                <div class="stat-card red">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="title">Pending</div>
                        <div class="value">{{ $pending }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Printing -->
        <div class="col-md-2 col-6">
            <a href="https://shreegroupsskp.com/admin/orders?order_status=Printing" class="text-decoration-none text-dark">
                <div class="stat-card gray">
                    <div class="icon"><i class="fas fa-print"></i></div>
                    <div>
                        <div class="title">Printing</div>
                        <div class="value">{{ $printing }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Ready -->
        <div class="col-md-2 col-6">
            <a href="https://shreegroupsskp.com/admin/orders?order_status=Ready" class="text-decoration-none text-dark">
                <div class="stat-card green">
                    <div class="icon"><i class="fas fa-check"></i></div>
                    <div>
                        <div class="title">Ready</div>
                        <div class="value">{{ $ready }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Today Ready (assuming Delivered) -->
        <div class="col-md-2 col-6">
            <a href="https://shreegroupsskp.com/admin/orders?order_status=Delivered" class="text-decoration-none text-dark">
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

<!-- ================= COLLECTION ================= -->
 <div class="section-box">
    <strong>Collection</strong>
    <div class="row g-3 mt-2">        

        <!-- Total Collection -->
        <div class="col-md-6 col-6">
            <a href="https://shreegroupsskp.com/admin/customers" class="text-decoration-none text-dark">
                <div class="stat-card green">
                    <div class="icon"><i class="fas fa-wallet"></i></div>
                    <div>
                        <div class="title">Total Collection</div>
                        <div class="value">₹{{ number_format($totalCollection) }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Today Collection -->
        <div class="col-md-6 col-6">
            <a href="https://shreegroupsskp.com/admin/customers" class="text-decoration-none text-dark">
                <div class="stat-card green">
                    <div class="icon"><i class="fas fa-rupee-sign"></i></div>
                    <div>
                        <div class="title">Today Collection</div>
                        <div class="value">₹{{ number_format($todayCollection) }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Total Due -->
        <div class="col-md-6 col-6">
            <a href="http://shreegroupsskp.com/admin/customers/due" class="text-decoration-none text-dark">
                <div class="stat-card red">
                    <div class="icon"><i class="fas fa-exclamation"></i></div>
                    <div>
                        <div class="title">Total Due</div>
                        <div class="value">₹{{ number_format($dueAmount) }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Today Due -->
        <div class="col-md-6 col-6">
            <a href="http://shreegroupsskp.com/admin/customers/due" class="text-decoration-none text-dark">
                <div class="stat-card red">
                    <div class="icon"><i class="fas fa-calendar-day"></i></div>
                    <div>
                        <div class="title">Today Due</div>
                        <div class="value">₹{{ number_format($todayDue) }}</div>
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

        <!-- Reminder Count -->
        <div class="col-md-3 col-6">
            <div class="stat-card orange">
                <div class="icon"><i class="fas fa-bell"></i></div>
                <div>
                    <div class="title">Total Reminders</div>
                    <div class="value">{{ $todayReminderCount }}</div>
                </div>
            </div>
        </div>

    </div>

    <!-- Table -->
    <div class="mt-3">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayReminders as $order)
                    <tr>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->mobile }}</td>
                        <td>{{ $order->due_date }}</td>
                        <td>₹{{ number_format($order->paid_amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No reminders today</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= STAFF ================= -->
 <div class="section-box">
    <strong>Staff</strong>
    <div class="row g-3 mt-2">

        <!-- Total Staff -->
        <div class="col-md-4 col-6">
            <a href="https://shreegroupsskp.com/admin/staff" class="text-decoration-none text-dark">
                <div class="stat-card blue">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="title">Total Staff</div>
                        <div class="value">{{ $staff }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Today Present -->
        <div class="col-md-4 col-6">
            <a href="https://shreegroupsskp.com/admin/staff-monthly-report" class="text-decoration-none text-dark">
                <div class="stat-card green">
                    <div class="icon"><i class="fas fa-user-check"></i></div>
                    <div>
                        <div class="title">Today Present</div>
                        <div class="value">{{ $presentCount }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Today Absent -->
        <div class="col-md-4 col-6">
            <a href="https://shreegroupsskp.com/admin/staff-monthly-report" class="text-decoration-none text-dark">
                <div class="stat-card red">
                    <div class="icon"><i class="fas fa-user-times"></i></div>
                    <div>
                        <div class="title">Today Absent</div>
                        <div class="value">{{ $absentCount }}</div>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<!-- ================= CUSTOMERS ================= -->
 <div class="section-box">
    <strong>Customers</strong>
    <div class="row g-3 mt-2">

        <!-- Total Customers -->
        <div class="col-md-4 col-6">
            <a href="https://shreegroupsskp.com/admin/customers" class="text-decoration-none text-dark">
                <div class="stat-card blue">
                    <div class="icon"><i class="fas fa-user-friends"></i></div>
                    <div>
                        <div class="title">Total Customers</div>
                        <div class="value">{{ $totalCustomers }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- New Customers -->
        <div class="col-md-4 col-6">
            <a href="https://shreegroupsskp.com/admin/customers" class="text-decoration-none text-dark">
                <div class="stat-card green">
                    <div class="icon"><i class="fas fa-user-plus"></i></div>
                    <div>
                        <div class="title">New (This Month)</div>
                        <div class="value">{{ $newCustomers }}</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Today Customers -->
        <div class="col-md-4 col-6">
            <a href="https://shreegroupsskp.com/admin/customers" class="text-decoration-none text-dark">
                <div class="stat-card orange">
                    <div class="icon"><i class="fas fa-calendar-day"></i></div>
                    <div>
                        <div class="title">Today Customers</div>
                        <div class="value">{{ $todayCustomers }}</div>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<!-- ================= STAFF CALENDAR ================= -->
<div class="section-box">
<strong>Staff Attendance Calendar</strong>

<div class="row g-2 mt-2">

@foreach($staffCalendar as $day)
<div class="col-md-2 col-3">
<div class="calendar-box 
{{ $day['present'] > 0 ? 'present' : '' }} 
{{ $day['absent'] > 0 ? 'absent' : '' }}">

<div class="date">{{ $day['date'] }}</div>
<div>
P: {{ $day['present'] }} <br>
A: {{ $day['absent'] }}
</div>

</div>
</div>
@endforeach

</div>
</div>

</div>

<!-- ================= CHART JS ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('orderChart'), {
type:'bar',
data:{
labels:@json($labels),
datasets:[
{label:'Orders',data:@json($dailyOrders)},
{label:'Collection',data:@json($dailyCollection),type:'line'}
]
}
});

new Chart(document.getElementById('staffChart'), {
type:'bar',
data:{
labels:@json($labels),
datasets:[
{label:'Present',data:@json(collect($staffCalendar)->pluck('present'))},
{label:'Absent',data:@json(collect($staffCalendar)->pluck('absent'))}
]
}
});
</script>

@endsection