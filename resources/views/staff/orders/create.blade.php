@extends('layouts.app')

@section('title','Add Order')

@section('content')

<style>
.main-card{
    background:#ffffff;
    border-radius:16px;
    padding:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
}
.form-control{
    border-radius:12px;
    padding:10px 14px;
}
.item-card{
    border-radius:14px;
    background:#f8f9fc;
    padding:15px;
    border:1px solid #eee;
    position: relative;
}
.remove-btn{
    position:absolute;
    top:50%;
    right:10px;
    transform:translateY(-50%);
}
.section-title{
    font-weight:600;
    margin-bottom:10px;
}
.btn-add{
    border-radius:20px;
}
.total-box input{
    font-weight:500;
}
</style>

<div class="container-fluid">
<div class="main-card">

<div class="d-flex justify-content-between mb-3">
    <h5>Add Order</h5>
    <a href="{{ route('orders') }}" class="btn btn-secondary">Back</a>
</div>

<form method="POST" action="{{ route('staff.orders.store') }}">
@csrf

<!-- CUSTOMER -->
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="section-title">Customer Name</label>
        <input type="text" name="customer_name" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="section-title">Mobile</label>
        <input type="text" name="mobile" class="form-control" maxlength="10"
        oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    </div>

    <div class="col-md-4">
        <label class="section-title">Address</label>
        <input type="text" name="address" class="form-control">
    </div>
</div>

<!-- ITEMS -->
<h6 class="section-title mt-3">Order Details</h6>

<div id="items-wrapper">
<div class="item-card mb-3 item">
    <div class="row align-items-center g-2">

        <div class="col-md-3">
            <input type="text" name="items[0][size]" placeholder="Size" class="form-control">
        </div>

        <div class="col-md-3">
            <input type="text" name="items[0][material]" placeholder="Material" class="form-control">
        </div>

        <div class="col-md-2">
            <input type="number" name="items[0][qty]" placeholder="Qty" class="form-control qty">
        </div>

        <div class="col-md-3">
            <input type="number" name="items[0][rate]" placeholder="Rate" class="form-control rate">
        </div>

        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-danger btn-sm remove-item">×</button>
        </div>

    </div>
</div>
</div>

<button type="button" class="btn btn-outline-primary btn-add mb-3" id="add-item">
    + Add More Item
</button>

<!-- PAYMENT + TOTAL -->
<div class="row g-3 total-box">

    <!-- Payment Method -->
    <div class="col-md-4">
        <label class="section-title">Payment Method</label>
        <select name="payment_method" class="form-control" required>
            <option value="">Select Payment</option>
            <option value="cash">Cash</option>
            <option value="upi">UPI</option>
            <option value="check">Check</option>
            <option value="neft">NEFT / RTGS</option>
        </select>
    </div>

    <!-- Payment Status -->
    <div class="col-md-4">
        <label class="section-title">Payment Status</label>
        <select name="payment_status" class="form-control">
            <option value="">Select Status</option>
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
            <option value="Partial">Partial</option>
            <option value="Advance">Advance</option>
        </select>
    </div>

    <!-- Order Status -->
    {{-- <div class="col-md-4">
        <label class="section-title">Order Status</label>
        <select name="order_status" class="form-control">
            <option value="">Select Status</option>
            <option value="Pending">Pending</option>
            <option value="Ready">Ready</option>
            <option value="Delivered">Delivered</option>
            <option value="Printing">Printing</option>
        </select>
    </div> --}}

    <div class="col-12"><hr></div>

    <!-- Total -->
    <div class="col-md-4">
        <label class="section-title">Total Amount</label>
        <input type="number" id="total_amount" class="form-control" readonly>
    </div>

    <!-- Paid -->
    <div class="col-md-4">
        <label class="section-title">Paid Amount</label>
        <input type="number" name="paid_amount" id="paid_amount" class="form-control" value="0">
    </div>

    <!-- Balance -->
    <div class="col-md-4">
        <label class="section-title">Balance Amount</label>
        <input type="number" id="balance_amount" class="form-control" readonly>
    </div>

</div>

<div class="col-md-12">
    <label>Notes</label>
    <textarea name="notes" class="form-control" rows="2"></textarea>
</div>

<div class="text-end mt-4">
    <button class="btn btn-dark px-4">Create Order</button>
</div>

</form>
</div>
</div>

<script>
// let index = 1;

// ADD ITEM
// document.getElementById('add-item').addEventListener('click', function () {
//     let html = `
//     <div class="item-card mb-3 item">
//         <div class="row g-2">
//             <div class="col-md-3">
//                 <input type="text" name="items[${index}][size]" class="form-control">
//             </div>
//             <div class="col-md-3">
//                 <input type="text" name="items[${index}][material]" class="form-control">
//             </div>
//             <div class="col-md-2">
//                 <input type="number" name="items[${index}][qty]" class="form-control qty">
//             </div>
//             <div class="col-md-3">
//                 <input type="number" name="items[${index}][rate]" class="form-control rate">
//             </div>
//             <div class="col-md-1 text-end">
//                 <button type="button" class="btn btn-danger btn-sm remove-item">×</button>
//             </div>
//         </div>
//     </div>`;
//     document.getElementById('items-wrapper').insertAdjacentHTML('beforeend', html);
//     index++;
// });

// REMOVE ITEM
// document.addEventListener('click', function (e) {
//     if (e.target.classList.contains('remove-item')) {
//         e.target.closest('.item').remove();
//         calculateTotal();
//     }
// });

// INPUT EVENTS
// document.addEventListener('input', calculateTotal);

// CALCULATION
// function calculateTotal() {
//     let total = 0;

//     document.querySelectorAll('.item').forEach(function (item) {
//         let qty = parseFloat(item.querySelector('.qty')?.value) || 0;
//         let rate = parseFloat(item.querySelector('.rate')?.value) || 0;
//         total += qty * rate;
//     });

//     document.getElementById('total_amount').value = total;

//     let paid = parseFloat(document.getElementById('paid_amount').value) || 0;
//     let balance = total - paid;

//     document.getElementById('balance_amount').value = balance.toFixed(2);

//     // Auto status
//     let status = document.querySelector('[name="payment_status"]');

//     if (paid == 0) {
//         status.value = "Pending";
//     } else if (paid < total) {
//         status.value = "Partial";
//     } else if (paid == total) {
//         status.value = "Paid";
//     } else {
//         status.value = "Advance";
//     }
// }
</script>

<script>
let index = 1;

// ADD ITEM
document.getElementById('add-item').addEventListener('click', function () {
    let html = `
    <div class="item-card mb-3 item">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="items[${index}][size]" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="text" name="items[${index}][material]" class="form-control">
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${index}][qty]" class="form-control qty">
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${index}][rate]" class="form-control rate">
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-item">×</button>
            </div>
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

// INPUT EVENTS
document.addEventListener('input', calculateTotal);

// MAIN CALCULATION
function calculateTotal() {
    let total = 0;

    document.querySelectorAll('.item').forEach(function (item) {
        let qty = parseFloat(item.querySelector('.qty')?.value) || 0;
        let rate = parseFloat(item.querySelector('.rate')?.value) || 0;
        total += qty * rate;
    });

    // TOTAL
    document.getElementById('total_amount').value = total;

    let paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    let balance = total - paid;

    // BALANCE
    document.getElementById('balance_amount').value = balance.toFixed(2);

    // 🔥 AUTO PAYMENT STATUS (ADMIN CAN CHANGE)
    let statusField = document.querySelector('[name="payment_status"]');
    let autoStatus = "";

    if (paid == 0) {
        autoStatus = "Pending";
    } else if (paid < total) {
        autoStatus = "Partial";
    } else if (paid == total) {
        autoStatus = "Paid";
    } else {
        autoStatus = "Advance";
    }

    // 👉 Only set if empty (admin override allowed)
    if (!statusField.value || statusField.dataset.auto == "true") {
        statusField.value = autoStatus;
        statusField.dataset.auto = "true";
    }
}

// 🔥 जब admin manually change करे तो auto override बंद
document.querySelector('[name="payment_status"]').addEventListener('change', function () {
    this.dataset.auto = "false";
});

// INITIAL LOAD
window.addEventListener('load', function () {
    calculateTotal();
});
</script>

@endsection