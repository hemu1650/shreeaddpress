@extends('layouts.app')

@section('title','Customers')

@section('content')

<style>
/* .btn-sm {
    padding: 4px 10px;
    font-size: 12px;
    border-radius: 6px;
        margin-left: 5px;

}

.table td {
    vertical-align: middle;
}

.pagination {
    margin: 0;
}

.page-link {
    border-radius: 6px !important;
    margin: 0 2px;
}

@media (max-width: 768px) {

    button.btn.btn-primary.btn-sm {
    margin-left: 5px;
}

.mt-3 {
    margin-top: 0px !important;
}


} */

</style>

<div class="card shadow-sm border-0">

    <!-- HEADER -->
    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <h5 class="mb-0"></h5>

        <div class="d-flex gap-2">

            <!-- 🔍 Search -->
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm" placeholder="Search">
                <button class="btn btn-primary btn-sm">Search</button>
            </form>

            <!-- 🔥 Due Button -->
            <a href="{{ route('customers.due') }}" class="btn btn-danger btn-sm">
                Due
            </a>

        </div>

    </div>

    <!-- BODY -->
    <div class="card-body">

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Orders</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $key => $customer)
                    <tr>

                        <td>{{ $customers->firstItem() + $key }}</td>

                        <td>{{ $customer->customer_name }}</td>

                        <td>{{ $customer->mobile }}</td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $customer->total_orders }}
                            </span>
                        </td>

                        <td class="text-success fw-bold">
                            ₹ {{ number_format($customer->total_paid,2) }}
                        </td>

                        <td class="text-danger fw-bold">
                            ₹ {{ number_format($customer->due_amount,2) }}
                        </td>

                        <!-- ✅ FIXED ACTION BUTTONS -->
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">

                                <a href="{{ route('customers.show',$customer->mobile) }}"
                                   class="btn btn-info btn-sm">
                                   View
                                </a>

                                <a href="{{ route('customers.ledger',$customer->mobile) }}"
                                   class="btn btn-secondary btn-sm">
                                   Ledger
                                </a>

                                <form action="{{ route('customers.destroy', $customer->mobile) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete all orders of this customer?')">
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No Customers Found
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <!-- ✅ PAGINATION FIXED -->
        <div class="mt-3"
 {{ $customers->links() }}

        </div>

    </div>

</div>

@endsection