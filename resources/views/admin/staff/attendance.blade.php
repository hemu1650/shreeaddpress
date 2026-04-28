@extends('layouts.app')

@section('title','Staff Attendance')

@section('content')

<style>
.attendance-card{
background:#fff;
border-radius:14px;
box-shadow:0 6px 18px rgba(0,0,0,.08);
padding:20px;
}

.table th{
background:#f8fafc;
}
</style>

<div class="container-fluid">

<div class="attendance-card">

<div class="d-flex justify-content-between align-items-center mb-3">

<h5 class="mb-0">Staff Attendance</h5>

<form method="GET">
<label class="me-2">Select Date:</label>
<input 
type="date" 
name="date" 
value="{{ $date }}" 
class="form-control"
onchange="this.form.submit()">
</form>

</div>

<form method="POST" action="{{ route('staff.attendance.save') }}">
@csrf

<input type="hidden" name="date" value="{{ $date }}">

<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead>
<tr>
<th>Staff Name</th>
<th class="text-center">Present</th>
<th class="text-center">Absent</th>
</tr>
</thead>

<tbody>

@foreach($staff as $s)

<tr>

<td><strong>{{ $s->name }}</strong></td>

<td class="text-center">
<input type="radio" 
name="attendance[{{ $s->id }}]" 
value="present"
{{ (isset($attendance[$s->id]) && $attendance[$s->id] == 'present') ? 'checked' : '' }}>
</td>

<td class="text-center">
<input type="radio" 
name="attendance[{{ $s->id }}]" 
value="absent"
{{ (isset($attendance[$s->id]) && $attendance[$s->id] == 'absent') ? 'checked' : '' }}>
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

<button class="btn btn-success mt-3">
Save Attendance
</button>

</form>

</div>

</div>

@endsection