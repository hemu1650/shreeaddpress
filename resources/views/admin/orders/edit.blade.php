@extends('layouts.app')

@section('title','Edit Order')

@section('content')

<div class="container-fluid">
<div class="card p-4 shadow-sm">

<h5 class="mb-4">Edit Order #{{ $order->id }}</h5>

<form action="{{ route('orders.update',$order->id) }}" method="POST">
@csrf
@method('PUT')

<div class="row">

{{-- CUSTOMER --}}
<div class="col-md-4 mb-3">
<label>Customer Name</label>
<input type="text" name="customer_name" value="{{ $order->customer_name }}" class="form-control" placeholder="Enter customer name">
</div>

<div class="col-md-4 mb-3">
<label>Mobile</label>
<input type="text" name="mobile" value="{{ $order->mobile }}" class="form-control" placeholder="Enter mobile">
</div>

<div class="col-md-4 mb-3">
<label>Address</label>
<input type="text" name="address" value="{{ $order->address }}" class="form-control" placeholder="Enter address">
</div>

{{-- STATUS --}}
<div class="col-md-3 mb-3">
<label>Order Status</label>
<select name="order_status" class="form-control">
<option value="Pending" {{ $order->order_status=='Pending'?'selected':'' }}>Pending</option>
<option value="Printing" {{ $order->order_status=='Printing'?'selected':'' }}>Printing</option>
<option value="Ready" {{ $order->order_status=='Ready'?'selected':'' }}>Ready</option>
<option value="Delivered" {{ $order->order_status=='Delivered'?'selected':'' }}>Delivered</option>
</select>
</div>

<div class="col-md-3 mb-3">
<label>Payment Status</label>
<select name="payment_status" class="form-control">
<option value="Pending" {{ $order->payment_status=='Pending'?'selected':'' }}>Pending</option>
<option value="Partial" {{ $order->payment_status=='Partial'?'selected':'' }}>Partial</option>
<option value="Paid" {{ $order->payment_status=='Paid'?'selected':'' }}>Paid</option>
<option value="Advance" {{ $order->payment_status=='Advance'?'selected':'' }}>Advance</option>
</select>
</div>

<div class="col-md-3 mb-3">
<label>Paid Amount</label>
<input type="number" name="paid_amount" id="paid_amount" value="{{ $order->paid_amount }}" class="form-control" placeholder="Enter paid amount">
</div>

<div class="col-md-3 mb-3">
<label>Assign Staff</label>
<select name="staff_id" class="form-control">
@foreach($staffs as $staff)
<option value="{{ $staff->id }}" {{ $order->staff_id == $staff->id ? 'selected' : '' }}>
{{ $staff->name }}
</option>
@endforeach
</select>
</div>

</div>

<hr>

{{-- ITEMS --}}
<h6 class="mb-3">Order Items</h6>

<div class="table-responsive">
<table class="table table-bordered align-middle">

<thead>
<tr>
<th>#</th>
<th>Size</th>
<th>Material</th>
<th>Qty</th>
<th>Rate</th>
<th>Total</th>
</tr>
</thead>

<tbody>

@foreach($order->items as $index => $item)

<tr>

<td>{{ $loop->iteration }}</td>

<td>
<input type="text" name="items[{{ $index }}][size]" value="{{ $item->size }}" class="form-control">
</td>

<td>
<input type="text" name="items[{{ $index }}][material]" value="{{ $item->material }}" class="form-control">
</td>

<td>
{{-- <input type="number" name="items[{{ $index }}][qty]" value="{{ $item->qty }}" class="form-control qty"> --}}
{{-- <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" class="form-control qty"> --}}
<input type="number"
       name="items[{{ $index }}][quantity]"
       value="{{ $item->quantity }}"
       class="form-control qty">
</td>

<td>
<input type="number" name="items[{{ $index }}][rate]" value="{{ $item->rate }}" class="form-control rate">
</td>

<td>
<input type="text" value="{{ $item->qty * $item->rate }}" class="form-control total" readonly>
</td>

<input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">

</tr>
@endforeach

</tbody>

</table>
</div>

{{-- TOTAL SECTION --}}
<div class="row mt-3">

<div class="col-md-3">
<label>Total</label>
<input type="number" id="total_amount" class="form-control" readonly>
</div>

<div class="col-md-3">
<label>Discount</label>
<input type="number" name="discount" id="discount" value="{{ $order->discount ?? 0 }}" class="form-control">
</div>

<div class="col-md-3">
<label>Final</label>
<input type="number" id="final_amount" class="form-control" readonly>
</div>

<div class="col-md-3">
<label>Balance</label>
<input type="number" id="balance_amount" class="form-control" readonly>
</div>

</div>

<hr>

<div class="d-flex gap-2">
<button class="btn btn-success">Update Order</button>
<a href="{{ route('orders') }}" class="btn btn-secondary">Back</a>
</div>

</form>

</div>
</div>

{{-- JS --}}
<script>
function calculateTotals() {
    let total = 0;

    document.querySelectorAll('tbody tr').forEach(row => {
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let rate = parseFloat(row.querySelector('.rate').value) || 0;

        let rowTotal = qty * rate;
        row.querySelector('.total').value = rowTotal;

        total += rowTotal;
    });

    document.getElementById('total_amount').value = total;

    let discount = parseFloat(document.getElementById('discount').value) || 0;
    let final = Math.max(total - discount, 0);

    document.getElementById('final_amount').value = final;

    let paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    let balance = final - paid;

    document.getElementById('balance_amount').value = balance;
}

document.querySelectorAll('.qty, .rate, #discount, #paid_amount').forEach(el => {
    el.addEventListener('input', calculateTotals);
});

window.onload = calculateTotals;
</script>

@endsection