<!-- filepath: /home/user/web/portfolio-alex/resources/views/invoices/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Edit Invoice: {{ $invoice->invoice_number }}</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('invoices.update', $invoice) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
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
                        <input type="text" name="prepared_by" id="prepared_by" class="form-control" value="{{ old('prepared_by', $invoice->prepared_by) }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="invoice_date" class="form-label">Invoice Date</label>
                        <input type="date" name="invoice_date" id="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                            value="{{ old('invoice_date', $invoice->invoice_date instanceof \DateTime ? $invoice->invoice_date->format('Y-m-d') : $invoice->invoice_date) }}" required>
                        @error('invoice_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror"
                            value="{{ old('due_date', $invoice->due_date instanceof \DateTime ? $invoice->due_date->format('Y-m-d') : $invoice->due_date) }}" required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <h4 class="mt-4">Products</h4>

                <div id="products-container">
                    @foreach($invoice->products as $index => $invoiceProduct)
                        <div class="row mb-2 product-item">
                            <div class="col-md-6">
                                <select name="products[{{ $index }}][id]" class="form-control product-select" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-cost="{{ $product->unit_cost }}"
                                        {{ $invoiceProduct->id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} - ${{ number_format($product->unit_cost, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="products[{{ $index }}][quantity]" class="form-control product-quantity"
                                       placeholder="Quantity" value="{{ $invoiceProduct->pivot->quantity }}" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remove-product" {{ $index === 0 ? 'disabled' : '' }}>Remove</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mb-3">
                    <button type="button" id="add-product" class="btn btn-secondary">Add Product</button>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="labor_cost" class="form-label">Labor Cost ($)</label>
                        <input type="number" name="labor_cost" id="labor_cost" class="form-control"
                               value="{{ old('labor_cost', $invoice->labor_cost) }}" step="0.01" min="0">
                    </div>

                    <div class="col-md-6">
                        <label for="machine_cost" class="form-label">Machine Cost ($)</label>
                        <input type="number" name="machine_cost" id="machine_cost" class="form-control"
                               value="{{ old('machine_cost', $invoice->machine_cost) }}" step="0.01" min="0">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="margin_rate" class="form-label">Margin Rate (%)</label>
                        <input type="number" name="margin_rate" id="margin_rate" class="form-control @error('margin_rate') is-invalid @enderror"
                            value="{{ old('margin_rate', $invoice->margin_rate) }}" min="0" max="100" step="0.1" required>
                        @error('margin_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $invoice->notes) }}</textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Update Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let productCount = {{ count($invoice->products) }};

        // Add new product row
        document.getElementById('add-product').addEventListener('click', function() {
            const container = document.getElementById('products-container');
            const firstRow = document.querySelector('.product-item');
            const newRow = firstRow.cloneNode(true);

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
