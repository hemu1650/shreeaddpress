@extends('layouts.app')

@section('title','Edit Order')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">

        <h4 class="mb-4">Edit Order #{{ $order->id }}</h4>

        <form method="POST" action="{{ route('staff.orders.update',$order->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">

            <!-- Customer Info -->
            <div class="col-md-4">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" value="{{ $order->customer_name }}" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Mobile</label>
                <input type="tel" name="mobile" value="{{ $order->mobile }}" class="form-control" maxlength="10">
            </div>

            <div class="col-md-4">
                <label class="form-label">Address</label>
                <input type="text" name="address" value="{{ $order->address }}" class="form-control">
            </div>

            <!-- Amount Section -->
            <div class="col-md-3">
                <label class="form-label">Amount</label>
                <input type="number" id="amount" value="{{ $order->paid_amount }}" class="form-control">
            </div>

            {{-- <div class="col-md-3">
                <label class="form-label">Discount</label>
                <input type="number" id="discount" value="0" class="form-control">
            </div> --}}

            <div class="col-md-3">
                <label class="form-label">Final Amount</label>
                <input type="number" name="paid_amount" id="final" value="{{ $order->paid_amount }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Order Status</label>
                <select name="order_status" class="form-control">

                    @php
                        $orderStatus = old('order_status', strtolower($order->order_status ?? 'pending'));
                    @endphp

                    <option value="pending" {{ $orderStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="printing" {{ $orderStatus == 'printing' ? 'selected' : '' }}>Printing</option>
                    <option value="ready" {{ $orderStatus == 'ready' ? 'selected' : '' }}>Ready</option>
                    <option value="delivered" {{ $orderStatus == 'delivered' ? 'selected' : '' }}>Delivered</option>
                </select>
            </div>


            <div class="col-md-3">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" class="form-control">

                    @php
                        $paymentStatus = old('payment_status', strtolower($order->payment_status ?? 'pending'));
                    @endphp

                    <option value="pending" {{ $paymentStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ $paymentStatus == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ $paymentStatus == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="advance" {{ $paymentStatus == 'advance' ? 'selected' : '' }}>Advance</option>
                </select>
            </div>

        </div>

        <!-- Order Items -->
        <hr class="my-4">

        <h5>Order Items</h5>

        <div class="table-responsive">
            <table class="table table-bordered mt-2">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Size</th>
                        <th>Material</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="itemsTable">

                    @foreach($order->items as $index => $item)
                    <tr>
                        <td>{{ $index+1 }}</td>

                        <td>
                            <input type="text" name="items[{{ $index }}][size]" value="{{ $item->size }}" class="form-control">
                        </td>

                        <td>
                            <input type="text" name="items[{{ $index }}][material]" value="{{ $item->material }}" class="form-control">
                        </td>

                        <td>
                            <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" class="form-control qty">
                        </td>

                        <td>
                            <input type="number" name="items[{{ $index }}][rate]" value="{{ $item->rate }}" class="form-control rate">
                        </td>

                        <td>
                            <input type="number" name="items[{{ $index }}][total]" value="{{ $item->total }}" class="form-control total" readonly>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <!-- Grand Total -->
        <div class="text-end mt-3">
            <h5>Grand Total: ₹ <span id="grandTotal">{{ $order->paid_amount }}</span></h5>
        </div>

        <!-- Buttons -->
        <div class="mt-4">
            <button class="btn btn-success">Update Order</button>
            <a href="{{ route('staff.orders.index') }}" class="btn btn-secondary">Back</a>
        </div>

        </form>
    </div>
</div>

<script>
function calculateTotals() {
    let grandTotal = 0;

    document.querySelectorAll('#itemsTable tr').forEach(row => {

        let qtyInput = row.querySelector('.qty');
        let rateInput = row.querySelector('.rate');
        let totalInput = row.querySelector('.total');

        let qty = parseFloat(qtyInput?.value) || 0;
        let rate = parseFloat(rateInput?.value) || 0;

        let total = qty * rate;

        if (totalInput) {
            totalInput.value = total.toFixed(2);
        }

        grandTotal += total;
    });

    document.getElementById('amount').value = grandTotal.toFixed(2);
    document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);

    let discount = parseFloat(document.getElementById('discount').value) || 0;
    document.getElementById('final').value = (grandTotal - discount).toFixed(2);
}

// 🔥 Important: event delegation properly lagao
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('qty') || 
        e.target.classList.contains('rate') || 
        e.target.id === 'discount') {

        calculateTotals();
    }
});

// 🔥 Page load pe bhi calculate ho
window.addEventListener('load', calculateTotals);
</script>

@endsection