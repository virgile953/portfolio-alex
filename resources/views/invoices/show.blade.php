<!-- resources/views/invoices/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Invoice: {{ $invoice->invoice_number }}</h1>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Back to Invoices</a>
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-info">Download PDF</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Bill To:</h5>
                    <p>
                        <strong>{{ $invoice->customer->name }}</strong><br>
                        {{ $invoice->customer->address }}<br>
                        {{ $invoice->customer->city }}, {{ $invoice->customer->state }} {{ $invoice->customer->zip }}<br>
                        {{ $invoice->customer->email }}
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p>
                        <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>Date:</strong> {{ $invoice->invoice_date ? $invoice->invoice_date->format('m/d/Y') : 'Not set' }}<br>
                        <strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('m/d/Y') : 'Not set' }}<br>
                        <strong>Prepared By:</strong> {{ $invoice->prepared_by }}
                    </p>
                </div>
            </div>

            <h5>Products</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->pivot->quantity }}</td>
                            <td>${{ number_format($product->pivot->unit_cost, 2) }}</td>
                            <td class="text-end">${{ number_format($product->pivot->total_cost, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row">
                <div class="col-md-6">
                    @if($invoice->notes)
                        <div class="alert alert-info">
                            <h5>Notes:</h5>
                            {!! nl2br(e($invoice->notes)) !!}
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Labor Cost:</th>
                                <td class="text-end">${{ number_format($invoice->labor_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Machine Cost:</th>
                                <td class="text-end">${{ number_format($invoice->machine_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Landed Cost:</th>
                                <td class="text-end">${{ number_format($invoice->total_landed_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Margin Rate:</th>
                                <td class="text-end">{{ $invoice->margin_rate }}%</td>
                            </tr>
                            <tr class="table-primary">
                                <th>Invoice Total:</th>
                                <td class="text-end"><strong>${{ number_format($invoice->invoice_total, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
