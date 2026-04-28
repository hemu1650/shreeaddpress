@extends('layouts.app')

@section('title','Staff')

@section('content')

<style>

.staff-card{
background:white;
border-radius:14px;
box-shadow:0 8px 25px rgba(0,0,0,.08);
padding:25px;
}

.staff-table thead{
background:#f8fafc;
}

.staff-table th{
font-weight:600;
color:#475569;
border-bottom:2px solid #e2e8f0;
}

.staff-table td{
vertical-align:middle;
padding:14px;
}

.staff-table tbody tr{
transition:.2s;
}

.staff-table tbody tr:hover{
background:#f1f5f9;
}

.search-box{
border-radius:30px;
padding-left:18px;
}

.badge-role{
background:#e0f2fe;
color:#0369a1;
padding:6px 10px;
border-radius:20px;
font-size:12px;
}

</style>


<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">

<h4 class="fw-bold">Staff Members</h4>

<a href="{{ route('staff.create') }}" class="btn btn-primary">
+ Add Staff
</a>

</div>


{{-- Success Message --}}
@if(session('success'))
<div class="alert alert-success">
{{ session('success') }}
</div>
@endif


<div class="staff-card">

{{-- Filter Row --}}

<form method="GET" action="{{ route('staff') }}">

<div class="row mb-4">

<div class="col-md-4">

<input
type="text"
name="search"
value="{{ request('search') }}"
class="form-control search-box"
placeholder="Search by name or email">

</div>


<div class="col-md-3">

<input
type="date"
name="date"
value="{{ request('date', date('Y-m-d')) }}"
class="form-control search-box">

</div>


<div class="col-md-2">

<button class="btn btn-primary w-100">
Search
</button>

</div>


<div class="col-md-2">

<a href="{{ route('staff') }}" class="btn btn-secondary w-100">
Reset
</a>

</div>

</div>

</form>



<div class="table-responsive">

<table class="table staff-table align-middle">

<thead>

<tr>
<th>#</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Role</th>
<th width="160">Action</th>
</tr>

</thead>

<tbody>

@forelse($staff as $index => $member)

<tr>

<td>
{{ $staff->firstItem() + $index }}
</td>

<td>
<strong>{{ $member->name }}</strong>
</td>

<td>
{{ $member->email }}
</td>
<td>
{{ $member->contact }}
</td>
<td>
<span class="badge-role">
{{ ucfirst($member->role) }}
</span>
</td>

<td>

<a href="{{ route('staff.view',$member->id) }}"
class="btn btn-sm btn-info">
View
</a>

<a href="{{ route('staff.edit',$member->id) }}"
class="btn btn-sm btn-warning">
Edit
</a>

</td>

</tr>

@empty

<tr>
<td colspan="5" class="text-center text-muted">
No staff found
</td>
</tr>

@endforelse

</tbody>

</table>

</div>


{{-- Pagination --}}

<div class="mt-3">
{{ $staff->links() }}
</div>


</div>

</div>

@endsection