<!-- invoice.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Printing Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .company-info {
            text-align: right;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .invoice-details-left, .invoice-details-right {
            width: 48%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .total-row {
            font-weight: bold;
            background-color: #eaf7ff;
        }

        .price-table {
            width: 50%;
            margin-left: auto;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #2c3e50;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>3D Printing Invoice</h1>
        <div class="company-info">
            <p><strong>Your 3D Printing Business</strong><br>
            Address Line 1<br>
            City, State ZIP<br>
            Phone: (123) 456-7890<br>
            Email: contact@your3dprintingbusiness.com</p>
        </div>
    </div>

    <div class="invoice-details">
        <div class="invoice-details-left">
            <h3>Bill To:</h3>
            <p>{{ $customerName ?? 'Customer Name' }}<br>
            {{ $customerAddress ?? 'Customer Address' }}<br>
            {{ $customerEmail ?? 'customer@email.com' }}</p>
        </div>
        <div class="invoice-details-right">
            <p><strong>Invoice #:</strong> {{ $invoiceNumber ?? 'INV-'.date('Ymd').'-'.rand(100, 999) }}</p>
            <p><strong>Date:</strong> {{ $invoiceDate ?? date('m/d/Y') }}</p>
            <p><strong>Due Date:</strong> {{ $dueDate ?? date('m/d/Y', strtotime('+30 days')) }}</p>
            <p><strong>Prepared By:</strong> {{ $preparedBy ?? 'Alex' }}</p>
        </div>
    </div>

    <h3>Product Details</h3>
    <table>
        <tr>
            <th>Part Name</th>
            <th>Revision</th>
            <th>Material</th>
            <th>Total Printing Time (hr)</th>
        </tr>
        <tr>
            <td>Acoustic panel</td>
            <td>V1</td>
            <td>ABS</td>
            <td>143.5</td>
        </tr>
    </table>

    <h3>Materials & Components</h3>
    <table>
        <tr>
            <th>Item</th>
            <th>Quantity</th>
            <th>Unit Cost</th>
            <th>Total Cost</th>
        </tr>
        <tr>
            <td>Acoustic panel HighFr</td>
            <td>4</td>
            <td>${{ number_format(12.60, 2) }}</td>
            <td>${{ number_format(50.40, 2) }}</td>
        </tr>
        <tr>
            <td>Acoustic panel LowFr</td>
            <td>4</td>
            <td>${{ number_format(14.70, 2) }}</td>
            <td>${{ number_format(58.80, 2) }}</td>
        </tr>
        <tr>
            <td>Acoustic panel MidFr</td>
            <td>4</td>
            <td>${{ number_format(12.60, 2) }}</td>
            <td>${{ number_format(50.40, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="3">Total Materials Cost</td>
            <td>${{ number_format(160.00, 2) }}</td>
        </tr>
    </table>

    <h3>Labor & Machine Costs</h3>
    <table>
        <tr>
            <th>Item</th>
            <th>Amount</th>
            <th>Rate</th>
            <th>Total Cost</th>
        </tr>
        <tr>
            <td>Labor</td>
            <td>240 minutes</td>
            <td>${{ number_format(30.00, 2) }}/hr</td>
            <td>${{ number_format(120.00, 2) }}</td>
        </tr>
        <tr>
            <td>Machine Cost</td>
            <td>143.5 hours</td>
            <td>${{ number_format(0.19, 2) }}/hr</td>
            <td>${{ number_format(27.27, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="3">Total Labor & Machine Costs</td>
            <td>${{ number_format(147.27, 2) }}</td>
        </tr>
    </table>

    <h3>Summary</h3>
    <table class="price-table">
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Materials Cost</td>
            <td>${{ number_format(160.00, 2) }}</td>
        </tr>
        <tr>
            <td>Labor Cost</td>
            <td>${{ number_format(120.00, 2) }}</td>
        </tr>
        <tr>
            <td>Machine Cost</td>
            <td>${{ number_format(27.27, 2) }}</td>
        </tr>
        <tr>
            <td>Packaging & Shipping</td>
            <td>${{ number_format(0.00, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Total Landed Cost</td>
            <td>${{ number_format(307.27, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Invoice Total ({{ $marginRate ?? '30' }}% Margin)</strong></td>
            <td><strong>${{ number_format(438.39, 2) }}</strong></td>
        </tr>
    </table>

    <div class="notes">
        <h3>Notes</h3>
        <p>{{ $notes ?? 'Thank you for your business! Payment is due within 30 days.' }}</p>
        <p>Material Details: ABS filament at $23/kg. Total filament used: 4728g.</p>
        <p>For questions about this invoice, please contact us at {{ $contactEmail ?? 'billing@your3dprintingbusiness.com' }}.</p>
    </div>

    <div class="footer">
        <p>Your 3D Printing Business &copy; {{ date('Y') }} | Terms and Conditions Apply</p>
    </div>
</body>
</html>
