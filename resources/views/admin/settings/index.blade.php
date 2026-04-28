@extends('layouts.app')

@section('title','Settings')

@section('content')

<div class="card">
<div class="card-body">

<h5>Settings</h5>

<form>

<div class="mb-3">
<label>Company Name</label>
<input type="text" class="form-control" value="Shree Groups">
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" class="form-control">
</div>

<button class="btn btn-primary">Save</button>

</form>

</div>
</div>

@endsection