@extends('layouts.app')

@section('title','Add Order')

@section('content')

<style>
.order-card{
    background:white;
    border-radius:14px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);
    padding:25px;
}
.form-control{
    border-radius:10px;
}
.item-card{
    background:#f9fafc;
    padding:10px;
    border-radius:10px;
}
</style>

<div class="container-fluid">
<div class="order-card">

<div class="d-flex justify-content-between mb-3">
    <h5>Add Order</h5>
    <a href="{{ route('orders') }}" class="btn btn-secondary">Back</a>
</div>

<form method="POST" action="{{ route('orders.store') }}">
@csrf

<!-- CUSTOMER + STAFF -->
<div class="row g-3 mb-3">

    <div class="col-md-6">
        <label>Select Customer</label>
        <select name="customer_id" id="customerSelect" class="form-control">
            <option value="">Select existing customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}">
                    {{ $customer->name }} ({{ $customer->phone }})
                </option>
            @endforeach
            <option value="new">+ Add New Customer</option>
        </select>
    </div>

    <div class="col-md-6">
        <label>Assign Staff</label>

        @if(auth()->user()->role == 'admin')
            <select name="staff_id" class="form-control" required>
                <option value="">Select staff member</option>
                @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                @endforeach
            </select>
        @else
            <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
            <input type="hidden" name="staff_id" value="{{ auth()->id() }}">
        @endif
    </div>

    <div class="col-md-4">
        <input type="text" name="customer_name" class="form-control" placeholder="Enter customer name">
    </div>

    <div class="col-md-4">
        <input type="text" name="mobile" class="form-control" placeholder="Enter 10-digit mobile number"
        maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    </div>

    <div class="col-md-4">
        <input type="text" name="address" class="form-control" placeholder="Enter address">
    </div>

</div>

<!-- ITEMS -->
<h6>Order Items</h6>

<div id="items-wrapper">
<div class="item-card mb-2 item">
    <div class="row g-2">

        <div class="col-md-3">
            <input type="text" name="items[0][size]" class="form-control" placeholder="Size">
        </div>

        <div class="col-md-3">
            <input type="text" name="items[0][material]" class="form-control" placeholder="Material">
        </div>

        <div class="col-md-2">
            <input type="number" name="items[0][qty]" class="form-control qty" placeholder="Quantity">
        </div>

        <div class="col-md-3">
            <input type="number" name="items[0][rate]" class="form-control rate" placeholder="Rate">
        </div>

        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-danger btn-sm remove-item">×</button>
        </div>

    </div>
</div>
</div>

<button type="button" class="btn btn-outline-primary mb-3" id="add-item">
    + Add Item
</button>

<!-- PAYMENT -->
<div class="row g-3">

    <div class="col-md-4">
        <label>Payment Method</label>
        <select name="payment_method" class="form-control">
            <option value="">Select payment method</option>
            <option value="cash">Cash</option>
            <option value="upi">UPI</option>
            <option value="check">Check</option>
            <option value="neft">NEFT / RTGS</option>
        </select>
    </div>

    <div class="col-md-4">
        <label>Payment Status</label>
        <select name="payment_status" class="form-control">
            <option value="">Auto detect</option>
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
            <option value="Partial">Partial</option>
            <option value="Advance">Advance</option>
        </select>
    </div>

    <div class="col-md-4">
        <label>Order Status</label>
        <select name="order_status" class="form-control">
            <option value="">Auto detect</option>
            <option value="Pending">Pending</option>
            <option value="Ready">Ready</option>
            <option value="Delivered">Delivered</option>
            <option value="Printing">Printing</option>
        </select>
    </div>

    <div class="col-12"><hr></div>

    <div class="col-md-2">
        <label>Total</label>
        <input type="number" id="total_amount" name="total_amount" class="form-control" placeholder="Auto calculated" readonly>
    </div>

    <div class="col-md-2">
        <label>Discount</label>
        <input type="number" id="discount" name="discount" class="form-control" placeholder="Enter discount ₹" value="0">
    </div>

    <div class="col-md-2">
        <label>Final</label>
        <input type="number" id="final_amount" class="form-control" placeholder="After discount" readonly>
    </div>

    <div class="col-md-2">
        <label>Paid</label>
        <input type="number" id="paid_amount" name="paid_amount" class="form-control" placeholder="Enter paid amount">
    </div>

    <div class="col-md-2">
        <label>Balance</label>
        <input type="number" id="balance_amount" class="form-control" placeholder="Remaining amount" readonly>
    </div>

    <div class="col-md-2">
        <label>Advance</label>
        <input type="number" id="advance_amount" class="form-control" placeholder="Extra paid" readonly>
    </div>

</div>

<div class="col-md-12 mt-3">
    <label>Notes</label>
    <textarea name="notes" class="form-control" rows="2" placeholder="Enter order notes (optional)"></textarea>
</div>

<div class="text-end mt-4">
    <button class="btn btn-primary px-4">Save Order</button>
</div>

</form>
</div>
</div>

<script>
let index = 1;

// ADD ITEM
document.getElementById('add-item').addEventListener('click', function () {
    let html = `
    <div class="item-card mb-2 item">
        <div class="row g-2">
            <div class="col-md-3"><input type="text" name="items[${index}][size]" class="form-control" placeholder="Size"></div>
            <div class="col-md-3"><input type="text" name="items[${index}][material]" class="form-control" placeholder="Material"></div>
            <div class="col-md-2"><input type="number" name="items[${index}][qty]" class="form-control qty" placeholder="Qty"></div>
            <div class="col-md-3"><input type="number" name="items[${index}][rate]" class="form-control rate" placeholder="Rate ₹"></div>
            <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-item">×</button></div>
        </div>
    </div>`;
    document.getElementById('items-wrapper').insertAdjacentHTML('beforeend', html);
    index++;
});

// REMOVE ITEM
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-item')) {
        e.target.closest('.item').remove();
        calculateTotal();
    }
});

// CALCULATE
document.addEventListener('input', calculateTotal);

function calculateTotal() {
    let total = 0;

    document.querySelectorAll('.item').forEach(function (item) {
        let qty = parseFloat(item.querySelector('.qty')?.value) || 0;
        let rate = parseFloat(item.querySelector('.rate')?.value) || 0;
        total += qty * rate;
    });

    document.getElementById('total_amount').value = total;

    let discount = parseFloat(document.getElementById('discount').value) || 0;
    let final = Math.max(total - discount, 0);

    document.getElementById('final_amount').value = final;

    let paid = parseFloat(document.getElementById('paid_amount').value) || 0;

    let balance = 0, advance = 0;

    if (paid <= final) {
        balance = final - paid;
    } else {
        advance = paid - final;
    }

    document.getElementById('balance_amount').value = balance.toFixed(2);
    document.getElementById('advance_amount').value = advance.toFixed(2);
}
</script>

@endsection