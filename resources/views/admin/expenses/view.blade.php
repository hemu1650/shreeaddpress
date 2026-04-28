@extends('layouts.app')

@section('title','Expense Details')

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

.detail-row{
margin-bottom:12px;
}

.label{
font-weight:600;
display:inline-block;
width:150px;
}

</style>

<div class="container-fluid">

<!-- <div class="page-header">
    <div class="page-title">Expense Details</div>
    <a href="{{ route('expenses') }}" class="back-btn">← Back</a>
</div> -->

<div class="form-card">

<div class="form-header">Expense Information</div>

<div class="form-body">

<div class="detail-row">
    <span class="label">Category:</span>
    {{ $expense->category->name ?? '-' }}
</div>

<div class="detail-row">
    <span class="label">Expense:</span>
    {{ $expense->expense_name }}
</div>

<div class="detail-row">
    <span class="label">Amount:</span>
    ₹{{ number_format($expense->amount,2) }}
</div>

<div class="detail-row">
    <span class="label">Date:</span>
    {{ $expense->expense_date }}
</div>

<div class="detail-row">
    <span class="label">Order:</span>
    {{ $expense->order_id ?? '-' }}
</div>

<div class="detail-row">
    <span class="label">Notes:</span>
    {{ $expense->notes ?? '-' }}
</div>

</div>
</div>
</div>

@endsection