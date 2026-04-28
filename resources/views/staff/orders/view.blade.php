@extends('layouts.app')
@section('title','Order Details')
@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- LEFT SIDE -->
        <div class="col-lg-8">
            <!-- CUSTOMER -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-bold">Customer Details</div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $order->customer_name }}</p>
                    <p><strong>Mobile:</strong> {{ $order->mobile }}</p>
                    <p><strong>Address:</strong> {{ $order->address ?? '-' }}</p>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-bold">Files / Attachments</div>
                <div class="card-body">
                    @if($order->image)
                    <a href="{{ $order->image }}" target="_blank" class="btn btn-info btn-sm mb-2 w-100">View Image</a>
                    @endif
                    @if($order->pdf)
                    <a href="{{ $order->pdf }}" target="_blank" class="btn btn-secondary btn-sm mb-2 w-100">View PDF</a>
                    @endif
                    @if($order->audio)
                    <audio controls style="width:100%;">
                        <source src="{{ $order->audio }}">
                    </audio>
                    @endif
                </div>
            </div>
            <!-- ITEMS -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-bold">Order Items</div>
                <div class="table-responsive">
                    <table class="table mb-0">
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
                        <tbody>
                            @php $grandTotal = 0; @endphp
                            @forelse($order->items as $key => $item)
                            @php
                            $total = ($item->quantity ?? 0) * ($item->rate ?? 0);
                            $grandTotal += $total;
                            @endphp
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $item->size }}</td>
                                <td>{{ $item->material }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₹{{ $item->rate }}</td>
                                <td>₹{{ $total }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No Items Added</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- PAYMENT HISTORY -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Payment History</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->payments->sortByDesc('id') as $payment)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                <td class="text-success">₹{{ $payment->amount }}</td>
                                <td>{{ ucfirst($payment->payment_mode) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No Payments Found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- RIGHT SIDE -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-bold">Order Summary</div>
                <div class="card-body">
                    <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                    @php
                    $discount = $order->discount ?? 0;
                    $finalTotal = max($grandTotal - $discount, 0);
                    $totalPaid = $order->payments->sum('amount');
                    $dueAmount = max($finalTotal - $totalPaid, 0);
                    $advance = max($totalPaid - $finalTotal, 0);
                    $statusColor = match(strtolower($order->payment_status)) {
                    'pending' => 'bg-secondary',
                    'partial' => 'bg-warning text-dark',
                    'paid' => 'bg-success',
                    'advance' => 'bg-primary',
                    default => 'bg-dark'
                    };
                    $remaining = max($finalTotal - $totalPaid, 0);
                    @endphp
                    <p>
                        <strong>Status:</strong>
                        <span class="badge bg-info">{{ ucfirst($order->order_status) }}</span>
                    </p>
                    <p>
                        <strong>Payment:</strong>
                        <span class="badge {{ $statusColor }}">{{ ucfirst($order->payment_status) }}</span>
                    </p>
                    <hr>
                    <p>Total: ₹{{ $grandTotal }}</p>
                    <p class="text-muted">
                        Discount: ₹{{ $discount }}
                        <small>(Admin Applied)</small>
                    </p>
                    <p><strong>Final: ₹{{ $finalTotal }}</strong></p>
                    <p class="text-success">Paid: ₹{{ $totalPaid }}</p>
                    <p class="text-danger">Due: ₹{{ $dueAmount }}</p>
                    @if($advance > 0)
                    <p class="text-primary">Advance: ₹{{ $advance }}</p>
                    @endif
                    <hr>
                    <!-- 🔥 PAYMENT FORM (STAFF CAN ADD) -->
                    @if ($remaining > 0)
                    <div class="alert alert-warning p-2">
                        Remaining: ₹{{ $remaining }}
                    </div>
                    @else
                    <div class="alert alert-info p-2">
                        Already paid. Extra will be <strong>Advance</strong>.
                    </div>
                    @endif
                    <form method="POST" action="{{ route('staff.orders.addPayment.staff',$order->id) }}">
                        @csrf
                        <input type="number" 
                            name="amount" 
                            class="form-control mb-2"
                            placeholder="Enter Amount"
                            min="1"
                            step="0.01"
                            required>
                        <select name="payment_mode" class="form-control mb-2">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="check">Check</option>
                            <option value="neft">NEFT / RTGS</option>
                        </select>
                        <input type="text" name="notes" class="form-control mb-2" placeholder="Notes (optional)">
                        <button class="btn btn-success w-100">Add Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection