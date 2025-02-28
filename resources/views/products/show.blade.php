@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>{{ $product->name }}</h1>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to Products</a>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Edit</a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Product Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <dl class="row">
                        <dt class="col-sm-3">SKU</dt>
                        <dd class="col-sm-9">{{ $product->sku }}</dd>

                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $product->description }}</dd>

                        <dt class="col-sm-3">Unit Cost</dt>
                        <dd class="col-sm-9">${{ number_format($product->unit_cost, 2) }}</dd>

                        <dt class="col-sm-3">Current Stock</dt>
                        <dd class="col-sm-9">{{ $product->stock }}</dd>

                        <dt class="col-sm-3">Created On</dt>
                        <dd class="col-sm-9">{{ $product->created_at->format('M d, Y') }}</dd>

                        <dt class="col-sm-3">Last Updated</dt>
                        <dd class="col-sm-9">{{ $product->updated_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
                <div class="col-md-4">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" class="img-fluid rounded" alt="{{ $product->name }}">
                    @else
                        <div class="bg-light d-flex justify-content-center align-items-center rounded" style="height: 200px;">
                            <p class="text-secondary">No image available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Usage History</h5>
        </div>
        <div class="card-body">
            <h6>Invoices Containing This Product</h6>
            @if($product->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Quantity Used</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                    <td>{{ $invoice->customer->name }}</td>
                                    <td>{{ $invoice->pivot->quantity }}</td>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="fst-italic text-muted">This product has not been used in any invoices yet.</p>
            @endif
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete "{{ $product->name }}"?
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
</div>
@endsection
