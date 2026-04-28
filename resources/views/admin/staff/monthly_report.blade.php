@extends('layouts.app')

@section('title','Monthly Attendance')

@section('content')

<style>

.attendance-card{
background:#fff;
border-radius:14px;
box-shadow:0 6px 18px rgba(0,0,0,.08);
padding:20px;
}

/* table */

.attendance-table th{
background:#f1f5f9;
font-size:13px;
white-space:nowrap;
}

.attendance-table td{
font-size:13px;
text-align:center;
}

.present{
background:#dcfce7;
color:#166534;
font-weight:600;
border-radius:6px;
}

.absent{
background:#fee2e2;
color:#991b1b;
font-weight:600;
border-radius:6px;
}

/* sticky first column */

.sticky-col{
position:sticky;
left:0;
background:#fff;
z-index:2;
}

/* sticky header */

.sticky-header{
position:sticky;
top:0;
z-index:3;
}

/* mobile scroll */

.table-wrapper{
overflow-x:auto;
}

</style>

<div class="container-fluid">

<div class="attendance-card">

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

<h5 class="mb-2">Monthly Attendance</h5>

<form method="GET">
<input 
type="month" 
name="month" 
value="{{ $month }}" 
class="form-control"
onchange="this.form.submit()">
</form>

</div>

<div class="table-wrapper">

<table class="table table-bordered attendance-table">

<thead>
<tr class="sticky-header">

<th class="sticky-col">Staff</th>

@for($d=1; $d <= $end->day; $d++)
<th>{{ $d }}</th>
@endfor

<th class="text-success">P</th>
<th class="text-danger">A</th>

</tr>
</thead>

<tbody>

@foreach($staff as $s)

@php
$totalPresent = 0;
$totalAbsent = 0;
@endphp

<tr>

<td class="sticky-col"><strong>{{ $s->name }}</strong></td>

@for($d=1; $d <= $end->day; $d++)

@php
$day = str_pad($d, 2, '0', STR_PAD_LEFT);
$status = $attendance[$s->id][$day][0]->status ?? null;

if($status == 'present') $totalPresent++;
if($status == 'absent') $totalAbsent++;
@endphp

<td class="
@if($status=='present') present 
@elseif($status=='absent') absent 
@endif
">

@if($status=='present')
P
@elseif($status=='absent')
A
@else
-
@endif

</td>

@endfor

<td class="text-success fw-bold">{{ $totalPresent }}</td>
<td class="text-danger fw-bold">{{ $totalAbsent }}</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

</div>

@endsection