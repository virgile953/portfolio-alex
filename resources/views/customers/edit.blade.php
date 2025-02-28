@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Edit Customer: {{ $customer->name }}</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Customer Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $customer->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $customer->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $customer->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror"
                            value="{{ old('address', $customer->address) }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror"
                            value="{{ old('city', $customer->city) }}">
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="state" class="form-label">State/Province</label>
                        <input type="text" name="state" id="state" class="form-control @error('state') is-invalid @enderror"
                            value="{{ old('state', $customer->state) }}">
                        @error('state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="zip" class="form-label">ZIP/Postal Code</label>
                        <input type="text" name="zip" id="zip" class="form-control @error('zip') is-invalid @enderror"
                            value="{{ old('zip', $customer->zip) }}">
                        @error('zip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" name="country" id="country" class="form-control @error('country') is-invalid @enderror"
                            value="{{ old('country', $customer->country) }}">
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
