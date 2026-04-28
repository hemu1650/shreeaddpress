@extends('layouts.app')
@section('title','Order Details')
@section('content')
<style>
    .page-title{font-size:22px;font-weight:600;}
    .order-card{
    background:#fff;
    border-radius:12px;
    box-shadow:0 4px 14px rgba(0,0,0,.06);
    margin-bottom:25px;
    overflow:hidden;
    }
    .card-header-custom{
    background:#f8fafc;
    padding:14px 20px;
    font-weight:600;
    border-bottom:1px solid #e5e7eb;
    }
    .card-body-custom{padding:20px;}
    .info-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:12px;
    }
    .info-label{color:#64748b;font-size:13px;}
    .info-value{font-weight:500;}
    .total-box{
    text-align:right;
    font-size:20px;
    font-weight:600;
    padding:15px 20px;
    border-top:1px solid #eee;
    background:#fafafa;
    }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <div class="page-title">Order #{{ $order->id }}</div>
        <a href="{{ url('/admin/orders') }}" class="btn btn-secondary">← Back</a>
    </div>
    @php
    $grandTotal = 0;
    @endphp
    @foreach($order->items as $item)
    @php
    $grandTotal += ($item->quantity ?? 0) * ($item->rate ?? 0);
    @endphp
    @endforeach
    @php
    $discount = $order->discount ?? 0;
    $finalTotal = max($grandTotal - $discount, 0);
    $totalPaid = $order->payments->sum('amount');
    $dueAmount = max($finalTotal - $totalPaid, 0);
    $advance = max($totalPaid - $finalTotal, 0);
    $statusColor = match($order->payment_status) {
    'Pending' => 'bg-secondary',
    'Partial' => 'bg-warning text-dark',
    'Paid' => 'bg-success',
    'Advance' => 'bg-primary',
    default => 'bg-dark'
    };
    @endphp
    <!-- TOP INFO -->
    <div class="row">
        <div class="col-md-6">
            <div class="order-card">
                <div class="card-header-custom">Customer Information</div>
                <div class="card-body-custom">
                    <div class="info-row">
                        <div class="info-label">Customer Name</div>
                        <div class="info-value">{{ $order->customer_name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Mobile</div>
                        <div class="info-value">{{ $order->mobile }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Address</div>
                        <div class="info-value">{{ $order->address }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Order Date</div>
                        <div class="info-value">{{ $order->created_at->format('d M Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="order-card">
                <div class="card-header-custom">Order Information</div>
                <div class="card-body-custom">
                    <div class="info-row">
                        <div class="info-label">Order Status</div>
                        <span class="badge bg-warning text-dark">{{ $order->order_status }}</span>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Payment Status</div>
                        <span class="badge {{ $statusColor }}">{{ $order->payment_status }}</span>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Paid Amount</div>
                        <div>₹{{ $totalPaid }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Staff</div>
                        <div>{{ optional($order->staff)->name }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ITEMS + FILES -->
    <div class="row">
        <div class="col-md-8">
            <div class="order-card">
                <div class="card-header-custom">Order Items</div>
                <div class="card-body-custom p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Size</th>
                                <th>Material</th>
                                <th>Quantity</th>
                                <th>Rate</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            @php
                            $total = ($item->quantity ?? 0) * ($item->rate ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->size }}</td>
                                <td>{{ $item->material }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₹{{ $item->rate }}</td>
                                <td>₹{{ $total }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="total-box">
                    Grand Total : ₹{{ $grandTotal }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="order-card">
                <div class="card-header-custom">Files / Attachments</div>
                <div class="card-body-custom text-center">
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
        </div>
    </div>
    <!-- PAYMENT -->
    <div class="row">
        <div class="col-md-8">
            <div class="order-card">
                <div class="card-header-custom">Payment History</div>
                <div class="card-body-custom">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Date</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->payments->sortByDesc('id') as $payment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>₹{{ $payment->amount }}</td>
                                <td>{{ $payment->payment_mode }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                <td>{{ $payment->notes }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editPayment{{ $payment->id }}">
                                    Edit
                                    </button>
                                    <form action="{{ route('orders.deletePayment',$payment->id) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <!-- MODAL -->
                            <div class="modal fade" id="editPayment{{ $payment->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST"
                                            action="{{ route('orders.updatePaymentRecord',$payment->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5>Edit Payment</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="number" name="amount" value="{{ $payment->amount }}" class="form-control mb-2" required>
                                                <select name="payment_mode" class="form-control mb-2">
                                                <option {{ $payment->payment_mode=='Cash'?'selected':'' }}>Cash</option>
                                                <option {{ $payment->payment_mode=='UPI'?'selected':'' }}>UPI</option>
                                                <option {{ $payment->payment_mode=='Bank'?'selected':'' }}>Bank</option>
                                                </select>
                                                <input type="text" name="notes" value="{{ $payment->notes }}" class="form-control">
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="order-card">
                <div class="card-header-custom">Payment Details</div>
                <div class="card-body-custom">
                    <div class="info-row">
                        <div class="info-label">Total</div>
                        <div class="info-value">₹{{ $grandTotal }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Discount</div>
                        <div class="info-value">₹{{ $discount }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Final</div>
                        <div class="info-value">₹{{ $finalTotal }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label text-success">Paid</div>
                        <div class="info-value">₹{{ $totalPaid }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label text-danger">Due</div>
                        <div class="info-value">₹{{ $dueAmount }}</div>
                    </div>
                    @if($advance > 0)
                    <div class="info-row">
                        <div class="info-label text-primary">Advance</div>
                        <div class="info-value">₹{{ $advance }}</div>
                    </div>
                    @endif
                    <hr>
                    <form method="POST" action="{{ route('orders.addPayment',$order->id) }}">
                        @csrf
                        <input type="number" name="amount" class="form-control mb-2" placeholder="Amount" required>
                        <select name="payment_mode" class="form-control mb-2">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="check">Check</option>
                            <option value="neft">NEFT / RTGS</option>
                        </select>
                        <input type="text" name="notes" class="form-control mb-2" placeholder="Notes">
                        <button class="btn btn-success w-100">Add Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection