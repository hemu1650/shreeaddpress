@extends('layouts.app')

@section('title','Expenses')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">
        <h4>Expenses</h4>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">+ Add Expense</a>
    </div>

    <!-- FILTER -->
    <form method="GET" class="row mb-3">

        {{-- ✅ Category Filter --}}
        <div class="col-md-3">
            <select name="category_id" class="form-control">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" 
                        {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>

        <div class="col-md-3">
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>

        <div class="col-md-3">
            <button class="btn btn-dark w-100">Filter</button>
        </div>

    </form>

    <!-- TABLE -->
    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th> {{-- ✅ changed --}}
                        <th>Expense</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Order</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($expenses as $key => $exp)
                    <tr>
                        <td>{{ $key+1 }}</td>

                        {{-- ✅ category relation --}}
                        <td>{{ $exp->category->name ?? '-' }}</td>

                        <td>{{ $exp->expense_name }}</td>

                        {{-- safer amount --}}
                        <td>₹{{ number_format($exp->amount,2) }}</td>

                        <td>{{ $exp->expense_date }}</td>

                        <td>{{ $exp->order_id ?? '-' }}</td>

                        <td>
                            <a href="{{ route('expenses.view',$exp->id) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('expenses.edit',$exp->id) }}" class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{ route('expenses.delete',$exp->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this expense?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>

            {{ $expenses->links() }}

        </div>
    </div>

</div>

@endsection