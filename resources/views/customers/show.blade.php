@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>{{ $customer->name }}</h1>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back to Customers</a>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">Edit</a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    Delete
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        @if($customer->email)
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">
                            <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                        </dd>
                        @endif

                        @if($customer->phone)
                        <dt class="col-sm-3">Phone</dt>
                        <dd class="col-sm-9">
                            <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                        </dd>
                        @endif

                        @if($customer->address)
                        <dt class="col-sm-3">Address</dt>
                        <dd class="col-sm-9">
                            {{ $customer->address }}
                        </dd>
                        @endif

                        @if($customer->city || $customer->state || $customer->zip)
                        <dt class="col-sm-3">Location</dt>
                        <dd class="col-sm-9">
                            {{ $customer->city }}{{ $customer->city && ($customer->state || $customer->zip) ? ',' : '' }}
                            {{ $customer->state }} {{ $customer->zip }}
                        </dd>
                        @endif

                        @if($customer->country)
                        <dt class="col-sm-3">Country</dt>
                        <dd class="col-sm-9">
                            {{ $customer->country }}
                        </dd>
                        @endif

                        <dt class="col-sm-3">Created On</dt>
                        <dd class="col-sm-9">{{ $customer->created_at->format('M d, Y') }}</dd>

                        <dt class="col-sm-3">Last Updated</dt>
                        <dd class="col-sm-9">{{ $customer->updated_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Customer Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total Invoices</h6>
                                    <h3>{{ $customer->invoices->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total Spent</h6>
                                    <h3>${{ number_format($customer->total_spent ?? $customer->invoices->sum('total_amount'), 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Last Invoice</h6>
                                    @if($customer->invoices->count() > 0)
                                        <p class="mb-1">
                                            <strong>Invoice #:</strong>
                                            <a href="{{ route('invoices.show', $customer->invoices->sortByDesc('invoice_date')->first()) }}">
                                                {{ $customer->invoices->sortByDesc('invoice_date')->first()->invoice_number }}
                                            </a>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Date:</strong>
                                            {{ $customer->invoices->sortByDesc('invoice_date')->first()->invoice_date->format('M d, Y') }}
                                        </p>
                                    @else
                                        <p class="text-muted mb-0">No invoices yet</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Invoice History</h5>
            <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">Create New Invoice</a>
        </div>
        <div class="card-body">
            @if($customer->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->invoices->sortByDesc('invoice_date') as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                    <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                                    <td>${{ number_format($invoice->total_amount, 2) }}</td>
                                    <td>
                                        @if($invoice->status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($invoice->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($invoice->status == 'overdue')
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-info">View</a>
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <p>No invoices found for this customer.</p>
                    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">Create First Invoice</a>
                </div>
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
                    Are you sure you want to delete the customer "{{ $customer->name }}"?
                    @if($customer->invoices->count() > 0)
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle-fill"></i> Warning: This customer has {{ $customer->invoices->count() }} invoice(s).
                            You cannot delete a customer with associated invoices.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('customers.destroy', $customer) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" {{ $customer->invoices->count() > 0 ? 'disabled' : '' }}>Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
