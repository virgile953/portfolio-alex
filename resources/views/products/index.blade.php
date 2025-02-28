@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Products</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Unit Cost</th>
                                <th>SKU</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ Str::limit($product->description, 50) }}</td>
                                    <td>${{ number_format($product->unit_cost, 2) }}</td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('products.show', $product) }}" class="btn btn-info">
                                                View
                                            </a>
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
                                                Edit
                                            </a>
                                            <button type="button" class="btn btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $product->id }}">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>

                <!-- Delete Modals -->
                @foreach($products as $product)
                    <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $product->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel{{ $product->id }}">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete the product "{{ $product->name }}"?
                                    @if($product->invoices->count() > 0)
                                        <div class="alert alert-warning mt-3">
                                            Warning: This product is associated with {{ $product->invoices->count() }} invoice(s).
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <h4>No products found</h4>
                    <p>Get started by adding your first product</p>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
