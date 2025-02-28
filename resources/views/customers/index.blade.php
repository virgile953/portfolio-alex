@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Customers</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('customers.create') }}" class="btn btn-primary">Add New Customer</a>
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

    <div class="card">
        <div class="card-body">
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Contact Info</th>
                                <th>Location</th>
                                <th>Invoices</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td>{{ $customer->id }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>
                                        @if($customer->email)
                                            <div><i class="bi bi-envelope-fill me-2"></i>{{ $customer->email }}</div>
                                        @endif
                                        @if($customer->phone)
                                            <div><i class="bi bi-telephone-fill me-2"></i>{{ $customer->phone }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($customer->city && $customer->state)
                                            {{ $customer->city }}, {{ $customer->state }}
                                        @elseif($customer->city)
                                            {{ $customer->city }}
                                        @elseif($customer->state)
                                            {{ $customer->state }}
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                    <td>{{ $customer->invoices_count ?? $customer->invoices->count() }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-info">
                                                View
                                            </a>
                                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
                                                Edit
                                            </a>
                                            <button type="button" class="btn btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $customer->id }}">
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
                    {{ $customers->links() }}
                </div>

                <!-- Delete Modals -->
                @foreach($customers as $customer)
                    <div class="modal fade" id="deleteModal{{ $customer->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $customer->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel{{ $customer->id }}">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete the customer "{{ $customer->name }}"?
                                    @if($customer->invoices_count ?? $customer->invoices->count() > 0)
                                        <div class="alert alert-warning mt-3">
                                            Warning: This customer has {{ $customer->invoices_count ?? $customer->invoices->count() }} invoice(s). You cannot delete a customer with associated invoices.
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" {{ ($customer->invoices_count ?? $customer->invoices->count() > 0) ? 'disabled' : '' }}>Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <h4>No customers found</h4>
                    <p>Get started by adding your first customer</p>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary">Add New Customer</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
