@extends('layouts.app')

@section('title','Edit Expense')

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

<!-- <div class="page-header">
    <div class="page-title">Edit Expense</div>
    <a href="{{ route('expenses') }}" class="back-btn">← Back</a>
</div> -->

<div class="form-card">

<div class="form-header">Expense Details</div>

<div class="form-body">

{{-- ✅ Error Messages --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin-bottom:0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('expenses.update',$expense->id) }}">
@csrf
@method('PUT')

{{-- Category --}}
<div class="mb-3">
<label class="form-label">Category *</label>
<select name="category_id" class="form-control" required>
    <option value="">-- Select Category --</option>
    @foreach($categories as $cat)
        <option value="{{ $cat->id }}"
            {{ old('category_id', $expense->category_id) == $cat->id ? 'selected' : '' }}>
            {{ $cat->name }}
        </option>
    @endforeach
</select>
</div>

{{-- Expense Name --}}
<div class="mb-3">
<label class="form-label">Expense Name *</label>
<input type="text" name="expense_name" class="form-control"
       value="{{ old('expense_name', $expense->expense_name) }}" required>
</div>

{{-- Amount --}}
<div class="mb-3">
<label class="form-label">Amount *</label>
<input type="number" step="0.01" name="amount" class="form-control"
       value="{{ old('amount', $expense->amount) }}" required>
</div>

{{-- Date --}}
<div class="mb-3">
<label class="form-label">Date *</label>
<input type="date" name="expense_date" class="form-control"
       value="{{ old('expense_date', $expense->expense_date) }}" required>
</div>

{{-- Order ID --}}
<div class="mb-3">
<label class="form-label">Order ID</label>
<input type="text" name="order_id" class="form-control"
       value="{{ old('order_id', $expense->order_id) }}">
</div>

{{-- Notes --}}
<div class="mb-3">
<label class="form-label">Notes</label>
<textarea name="notes" class="form-control">{{ old('notes', $expense->notes) }}</textarea>
</div>

<button class="save-btn">Update Expense</button>

</form>

</div>
</div>
</div>

@endsection