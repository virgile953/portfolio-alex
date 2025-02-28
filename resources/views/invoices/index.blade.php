@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Invoices</h2>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">Create New Invoice</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->formatted_invoice_number }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->invoice_date instanceof \DateTime ? $invoice->invoice_date->format('M d, Y') : $invoice->invoice_date }}</td>
                                <td>{{ $invoice->due_date instanceof \DateTime ? $invoice->due_date->format('M d, Y') : $invoice->due_date }}</td>
                                <td class="text-end">${{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge
                                        @if($invoice->status == 'paid') bg-success
                                        @elseif($invoice->status == 'sent') bg-info
                                        @else bg-secondary
                                        @endif">
                                        {{ $invoice->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-info">View</a>
                                        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">Edit</a>
                                        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-secondary" target="_blank">PDF</a>
                                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3">No invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
@endsection
