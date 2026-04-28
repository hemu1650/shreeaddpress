@extends('layouts.app')

@section('title','Add Customer')

@section('content')

<div class="card p-3">

<form method="POST" action="{{ route('customers.store') }}">
@csrf

<div class="mb-3">
    <label>Name</label>
    <input type="text" name="name" class="form-control" required>
</div>

<div class="mb-3">
    <label>Phone</label>
    <input type="text" name="phone" class="form-control" required>
</div>

<div class="mb-3">
    <label>Email</label>
    <input type="email" name="email" class="form-control">
</div>

<div class="mb-3">
    <label>Address</label>
    <textarea name="address" class="form-control"></textarea>
</div>

<button class="btn btn-success">Save</button>

</form>

</div>

@endsection