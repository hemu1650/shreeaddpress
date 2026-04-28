@extends('layouts.app')

@section('title','Categories')

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

.add-btn{
background:#0d6efd;
color:white;
padding:8px 14px;
border-radius:6px;
text-decoration:none;
border:none;
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
padding:20px;
}
</style>

<div class="container-fluid">

<div class="page-header">
    <div class="page-title"></div>

    <!-- Add Button -->
    <button class="add-btn" data-bs-toggle="modal" data-bs-target="#categoryModal">
        + Add Category
    </button>
</div>

<div class="form-card">
<div class="form-header">Category List</div>

<div class="form-body">

{{-- Success Message --}}
@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th width="250">Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse($categories as $key => $cat)
        <tr>
            <td>{{ $key+1 }}</td>

            {{-- Inline Edit --}}
            <td>
                <form action="{{ route('categories.update',$cat->id) }}" method="POST" class="d-flex">
                    @csrf
                    <input type="text" name="name" value="{{ $cat->name }}" class="form-control me-2">
                    <button class="btn btn-sm btn-warning">Update</button>
                </form>
            </td>

            <td>
                <!-- Delete -->
                <form action="{{ route('categories.delete',$cat->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this category?')">
                        Delete
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center">No categories found</td>
        </tr>
        @endforelse
    </tbody>
</table>

</div>
</div>

</div>

<!-- ================= MODAL ================= -->

<div class="modal fade" id="categoryModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="form-header">Add Category</div>

            <form method="POST" action="{{ route('categories.store') }}">
                @csrf

                <div class="form-body">

                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-control" required>

                </div>

                <div class="p-3 text-end">
                    <button class="btn btn-primary">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection