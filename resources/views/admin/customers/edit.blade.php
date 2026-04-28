@extends('layouts.app')

@section('title','Edit Customer')

@section('content')

<div class="card p-3">

<form method="POST" action="{{ route('customers.update',$customer->id) }}">
@csrf
@method('PUT')

<div class="mb-3">
    <label>Name</label>
    <input type="text" name="name" value="{{ $customer->name }}" class="form-control" required>
</div>

<div class="mb-3">
    <label>Phone</label>
    <input type="text" name="phone" value="{{ $customer->phone }}" class="form-control" required>
</div>

<div class="mb-3">
    <label>Email</label>
    <input type="email" name="email" value="{{ $customer->email }}" class="form-control">
</div>

<div class="mb-3">
    <label>Address</label>
    <textarea name="address" class="form-control">{{ $customer->address }}</textarea>
</div>

<button class="btn btn-primary">Update</button>

</form>

</div>

@endsection