@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Create New Invoice</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Back to Invoices</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('invoices.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="prepared_by" class="form-label">Prepared By</label>
                        <input type="text" name="prepared_by" id="prepared_by" class="form-control" value="{{ old('prepared_by') ?? 'Alex' }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="invoice_date" class="form-label">Invoice Date</label>
                        <input type="date" name="invoice_date" id="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                            value="{{ old('invoice_date') ?? date('Y-m-d') }}" required>
                        @error('invoice_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror"
                            value="{{ old('due_date') ?? date('Y-m-d', strtotime('+30 days')) }}" required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <h4 class="mt-4">Products</h4>

                <div id="products-container">
                    <div class="row mb-2 product-item">
                        <div class="col-md-6">
                            <select name="products[0][id]" class="form-control product-select" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-cost="{{ $product->unit_cost }}">
                                        {{ $product->name }} - ${{ number_format($product->unit_cost, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="products[0][quantity]" class="form-control product-quantity" placeholder="Quantity" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-product" disabled>Remove</button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button" id="add-product" class="btn btn-secondary">Add Product</button>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="labor_cost" class="form-label">Labor Cost ($)</label>
                        <input type="number" name="labor_cost" id="labor_cost" class="form-control" value="{{ old('labor_cost') ?? 120.00 }}" step="0.01" min="0">
                    </div>

                    <div class="col-md-6">
                        <label for="machine_cost" class="form-label">Machine Cost ($)</label>
                        <input type="number" name="machine_cost" id="machine_cost" class="form-control" value="{{ old('machine_cost') ?? 27.27 }}" step="0.01" min="0">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="margin_rate" class="form-label">Margin Rate (%)</label>
                        <input type="number" name="margin_rate" id="margin_rate" class="form-control @error('margin_rate') is-invalid @enderror"
                            value="{{ old('margin_rate') ?? 30 }}" min="0" max="100" step="0.1" required>
                        @error('margin_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let productCount = 1;

        // Add new product row
        document.getElementById('add-product').addEventListener('click', function() {
            const container = document.getElementById('products-container');
            const newRow = document.querySelector('.product-item').cloneNode(true);

            // Update input names
            newRow.querySelector('.product-select').name = `products[${productCount}][id]`;
            newRow.querySelector('.product-quantity').name = `products[${productCount}][quantity]`;

            // Clear selections
            newRow.querySelector('.product-select').value = '';
            newRow.querySelector('.product-quantity').value = 1;

            // Enable remove button
            newRow.querySelector('.remove-product').disabled = false;

            container.appendChild(newRow);
            productCount++;

            // Activate remove buttons
            activateRemoveButtons();
        });

        // Function to activate remove buttons
        function activateRemoveButtons() {
            document.querySelectorAll('.remove-product').forEach(button => {
                button.addEventListener('click', function() {
                    if (document.querySelectorAll('.product-item').length > 1) {
                        this.closest('.product-item').remove();
                    }
                });
            });
        }

        // Initial activation
        activateRemoveButtons();
    });
</script>
@endpush
@endsection
