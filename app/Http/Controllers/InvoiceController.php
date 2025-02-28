<?php
// app/Http/Controllers/InvoiceController.php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema; // Added Schema facade import
use PDF;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the invoices.
     */
    public function index()
    {
        $invoices = Invoice::with('customer')->latest()->paginate(10);
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        return view('invoices.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'margin_rate' => 'required|numeric|min:0|max:100',
        ]);

        $invoiceData = [
            'customer_id' => $validated['customer_id'],
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(100, 999),
            'issue_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'margin_rate' => $validated['margin_rate'],
            'notes' => $request->notes,
        ];

        // Only add prepared_by if it exists in the database
        if (Schema::hasColumn('invoices', 'prepared_by')) {
            $invoiceData['prepared_by'] = $request->prepared_by ?? 'Admin';
        }

        $invoice = Invoice::create($invoiceData);

        $totalLandedCost = 0;

        foreach ($validated['products'] as $productData) {
            $product = Product::findOrFail($productData['id']);
            $quantity = $productData['quantity'];

            $invoice->products()->attach($product->id, [
                'quantity' => $quantity,
                'unit_cost' => $product->unit_cost,
                'total_cost' => $product->unit_cost * $quantity,
            ]);

            $totalLandedCost += $product->unit_cost * $quantity;
        }

        // Add labor and machine costs
        $totalLandedCost += $request->labor_cost ?? 0;
        $totalLandedCost += $request->machine_cost ?? 0;

        $invoice->total_landed_cost = $totalLandedCost;
        $invoice->invoice_total = $totalLandedCost / (1 - ($validated['margin_rate'] / 100));
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully!');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'products');
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Generate a PDF version of the invoice.
     */
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load('customer', 'products');

        // Sample implementation - you'll need to install a PDF package like dompdf
        // $pdf = PDF::loadView('invoice', [
        //     'invoice' => $invoice,
        //     'customerName' => $invoice->customer->name,
        //     'customerAddress' => $invoice->customer->address,
        //     'customerEmail' => $invoice->customer->email,
        //     'invoiceNumber' => $invoice->invoice_number,
        //     'invoiceDate' => $invoice->invoice_date,
        //     'dueDate' => $invoice->due_date,
        //     'preparedBy' => $invoice->prepared_by,
        //     'marginRate' => $invoice->margin_rate,
        //     'notes' => $invoice->notes,
        // ]);

        // For now, just return the view
        return view('invoice', [
            'invoice' => $invoice,
            'customerName' => $invoice->customer->name,
            'customerAddress' => $invoice->customer->address,
            'customerEmail' => $invoice->customer->email,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => $invoice->invoice_date,
            'dueDate' => $invoice->due_date,
            'preparedBy' => $invoice->prepared_by,
            'marginRate' => $invoice->margin_rate,
            'notes' => $invoice->notes,
        ]);
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        $customers = Customer::all();
        $products = Product::all();
        $invoice->load('customer', 'products');
        return view('invoices.edit', compact('invoice', 'customers', 'products'));
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'margin_rate' => 'required|numeric|min:0|max:100',
        ]);

        $invoice->update([
            'customer_id' => $validated['customer_id'],
            'issue_date' => $validated['invoice_date'], // Changed from invoice_date to issue_date
            'due_date' => $validated['due_date'],
            'prepared_by' => $request->prepared_by ?? $invoice->prepared_by,
            'margin_rate' => $validated['margin_rate'],
            'notes' => $request->notes,
        ]);

        // Detach all current products
        $invoice->products()->detach();

        $totalLandedCost = 0;

        foreach ($validated['products'] as $productData) {
            $product = Product::findOrFail($productData['id']);
            $quantity = $productData['quantity'];

            $invoice->products()->attach($product->id, [
                'quantity' => $quantity,
                'unit_cost' => $product->unit_cost,
                'total_cost' => $product->unit_cost * $quantity,
            ]);

            $totalLandedCost += $product->unit_cost * $quantity;
        }

        // Add labor and machine costs
        $totalLandedCost += $request->labor_cost ?? 0;
        $totalLandedCost += $request->machine_cost ?? 0;

        $invoice->total_landed_cost = $totalLandedCost;
        $invoice->invoice_total = $totalLandedCost / (1 - ($validated['margin_rate'] / 100));
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully!');
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->products()->detach();
        $invoice->delete();
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }
}
