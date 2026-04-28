@extends('layouts.app')

@section('title','Add Staff')

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

.form-control{
padding:10px;
border-radius:6px;
}

.save-btn{
background:#0d6efd;
border:none;
padding:10px 18px;
color:white;
border-radius:6px;
}

</style>


<div class="container-fluid">

<!-- Page Header -->

<div class="page-header">

<div class="page-title">
Add New Staff
</div>

<a href="{{ route('staff') }}" class="back-btn">
← Back to Staff
</a>

</div>


<!-- Form Card -->

<div class="form-card">

<div class="form-header">
Staff Information
</div>

<div class="form-body">

<form method="POST" action="{{ route('staff.store') }}">

@csrf


<div class="mb-3">

<label class="form-label">Name *</label>

<input 
type="text"
name="name"
class="form-control"
placeholder="Enter full name"
required>

</div>


<div class="mb-3">

<label class="form-label">Email *</label>

<input 
type="email"
name="email"
class="form-control"
placeholder="Enter email address"
required>

</div>

<div class="mb-3">

<label class="form-label">Phone *</label>

<input 
type="text"
name="contact"
class="form-control"
placeholder="Enter phone number"
minlength="8"
maxlength="15"
required>

</div>


<div class="mb-3">

<label class="form-label">Password *</label>

<input 
type="password"
name="password"
class="form-control"
placeholder="Minimum 6 characters"
required>

</div>


<div class="mb-3">

<label class="form-label">Confirm Password *</label>

<input 
type="password"
name="password_confirmation"
class="form-control"
placeholder="Re-enter password"
required>

</div>


<button type="submit" class="save-btn">
Save
</button>

</form>

</div>

</div>

</div>

@endsection