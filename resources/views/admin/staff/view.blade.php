@extends('layouts.app')

@section('title','View Staff')

@section('content')

<style>

.page-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.page-title{
font-size:28px;
font-weight:600;
}

.back-btn{
background:#6c757d;
color:white;
padding:8px 14px;
border-radius:6px;
text-decoration:none;
}

.form-card{
background:white;
border-radius:6px;
box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

.form-header{
background:#f7b500;
color:black;
padding:12px 18px;
font-weight:600;
border-top-left-radius:6px;
border-top-right-radius:6px;
}

.form-body{
padding:25px;
}

.form-label{
font-weight:600;
margin-bottom:6px;
}

.view-field{
background:#f8f9fa;
padding:10px;
border-radius:6px;
border:1px solid #dee2e6;
}

</style>


<div class="container-fluid">

<!-- Page Header -->

<div class="page-header">

<div class="page-title">
View Staff
</div>

<a href="{{ route('staff') }}" class="back-btn">
← Back to Staff
</a>

</div>


<!-- Card -->

<div class="form-card">

<div class="form-header">
Staff Information
</div>

<div class="form-body">


<div class="mb-3">

<label class="form-label">Name</label>

<div class="view-field">
{{ $staff->name }}
</div>

</div>


<div class="mb-3">

<label class="form-label">Email</label>

<div class="view-field">
{{ $staff->email }}
</div>

</div>

<div class="mb-3">

<label class="form-label">Phone</label>

<div class="view-field">
{{ $staff->contact }}
</div>

</div>


<div class="mb-3">

<label class="form-label">Role</label>

<div class="view-field">
{{ ucfirst($staff->role) }}
</div>

</div>


<div class="mb-3">

<label class="form-label">Created At</label>

<div class="view-field">
{{ $staff->created_at->format('d M Y') }}
</div>

</div>


<a href="{{ route('staff.edit',$staff->id) }}" class="btn btn-primary">
Edit Staff
</a>


</div>

</div>

</div>

@endsection