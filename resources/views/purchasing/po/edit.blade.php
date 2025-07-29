@extends('layouts.app')

@section('title', 'Purchasing | Edit PO')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Edit Purchase Order</h4>
    </div>
    <form action="{{ route('pur-pos.update', $purPo->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">
                    <label>Vendor</label>
                    <select name="vendor_id" class="form-control" required>
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ $purPo->vendor_id == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $purPo->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Order Date</label>
                    <input type="date" name="order_date" class="form-control" value="{{ \Carbon\Carbon::parse($purPo->order_date)->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-2">
                    <label>Order By</label>
                    <input type="text" name="order_by" class="form-control" value="{{ $purPo->order_by }}">
                </div>
                <div class="col-md-3">
                    <label>Remarks</label>
                    <textarea class="form-control" name="remarks">{{ $purPo->remarks }}</textarea>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="itemTable">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 20%;">Item</th>
                            <th style="width: 20%;">Description</th>
                            <th style="width: 10%;">Width</th> <!-- Added -->
                            <th style="width: 15%;">Rate</th>
                            <th style="width: 15%;">Qty</th>
                            <th style="width: 15%;">Total</th>
                            <th style="width: 10%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purPo->details as $index => $detail)
                        <tr>
                            <td>
                                <select name="details[{{ $index }}][item_id]" class="form-control" required>
                                    <option value="">Select Item</option>
                                    @foreach($products as $item)
                                        <option value="{{ $item->id }}" {{ $detail->item_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="details[{{ $index }}][width]" class="form-control" value="{{ $detail->width ?? '' }}">
                            </td>
                            <td>
                                <input type="text" name="details[{{ $index }}][description]" class="form-control" value="{{ $detail->description ?? ''  }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control item_rate" name="details[{{ $index }}][item_rate]" value="{{ $detail->item_rate }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control item_qty" name="details[{{ $index }}][item_qty]" value="{{ $detail->item_qty }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control item_total" value="{{ $detail->item_qty * $detail->item_rate  }}" readonly>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger removeRowBtn">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary mb-3" id="addRowBtn"><i class="fas fa-plus"></i> Add Item</button>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Attachments (if any)</label>
                    <input type="file" name="att[]" class="form-control" multiple>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row mb-2">
                <div class="col-md-3 offset-md-6">
                    <label><strong>Total Quantity</strong></label>
                    <input type="number" step="0.01" class="form-control" id="total_qty" readonly>
                </div>
                <div class="col-md-3">
                    <label><strong>Total Bill</strong></label>
                    <input type="number" step="0.01" class="form-control" id="total_bill" readonly>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">Update PO</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rowIdx = {{ $purPo->details->count() }};

        function updateRowTotals(row) {
            let rate = parseFloat(row.querySelector('.item_rate').value) || 0;
            let qty = parseFloat(row.querySelector('.item_qty').value) || 0;
            let total = rate * qty;
            row.querySelector('.item_total').value = total.toFixed(2);
            updateSummary();
        }

        function updateSummary() {
            let totalQty = 0;
            let totalBill = 0;

            document.querySelectorAll('#itemTable tbody tr').forEach(row => {
                let qty = parseFloat(row.querySelector('.item_qty')?.value) || 0;
                let rate = parseFloat(row.querySelector('.item_rate')?.value) || 0;
                totalQty += qty;
                totalBill += qty * rate;
            });

            document.getElementById('total_qty').value = totalQty.toFixed(2);
            document.getElementById('total_bill').value = totalBill.toFixed(2);
        }

        // Bind initial input listeners
        document.querySelectorAll('.item_rate, .item_qty').forEach(input => {
            input.addEventListener('input', function () {
                updateRowTotals(this.closest('tr'));
            });
        });

        // Row remove logic
        document.querySelectorAll('.removeRowBtn').forEach(btn => {
            btn.addEventListener('click', function () {
                this.closest('tr').remove();
                updateSummary();
            });
        });

        // Add row button
        document.getElementById('addRowBtn').addEventListener('click', function () {
            const table = document.querySelector('#itemTable tbody');
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td>
                    <select name="details[${rowIdx}][item_id]" class="form-control" required>
                        <option value="">Select Item</option>
                        @foreach($products as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" name="details[${rowIdx}][width]" class="form-control">
                </td>
                <td>
                    <input type="text" name="details[${rowIdx}][description]" class="form-control">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control item_rate" name="details[${rowIdx}][item_rate]" value="0">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control item_qty" name="details[${rowIdx}][item_qty]" value="0">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control item_total" name="details[${rowIdx}][item_total]" value="0" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger removeRowBtn"><i class="fa fa-trash"></i></button>
                </td>
            `;
            table.appendChild(newRow);
            rowIdx++;

            newRow.querySelectorAll('.item_rate, .item_qty').forEach(input => {
                input.addEventListener('input', function () {
                    updateRowTotals(this.closest('tr'));
                });
            });

            newRow.querySelector('.removeRowBtn').addEventListener('click', function () {
                this.closest('tr').remove();
                updateSummary();
            });

            updateSummary();
        });

        // Initial total
        updateSummary();
    });
</script>

@endsection
