@extends('layouts.app')

@section('title','Due Customers')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        {{-- <h5 class="mb-0 text-danger">Due Customers</h5> --}}

        {{-- <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary">
            Back
        </a> --}}
    </div>

    <div class="card-body">

        <table class="table table-hover align-middle">

            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Due</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                @php $total = 0; @endphp

                @foreach($customers as $key => $customer)

                @php $total += $customer->due_amount; @endphp

                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $customer->customer_name }}</td>
                    <td>{{ $customer->mobile }}</td>
                    <td class="fw-bold text-danger">₹ {{ number_format($customer->due_amount,2) }}</td>

                    <td class="text-end">
                        <a href="{{ route('customers.show',$customer->mobile) }}" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>

                @endforeach
            </tbody>

            <tfoot>
                <tr class="table-dark">
                    <th colspan="3" class="text-end">Total</th>
                    <th colspan="2">₹ {{ number_format($total,2) }}</th>
                </tr>
            </tfoot>

        </table>

    </div>

</div>

@endsection