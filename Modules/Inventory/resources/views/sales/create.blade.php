@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('New Inventory Sale') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><div class="card-title">{{ _lang('Find Student') }}</div></div>
                <div class="card-body">
                    <form method="GET" action="{{ route('inventory-sales.create') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="{{ _lang('Name or roll') }}" value="{{ $search }}">
                            <button class="btn btn-primary">{{ _lang('Search') }}</button>
                        </div>
                    </form>

                    @if ($students->isNotEmpty())
                        <div class="list-group mt-3">
                            @foreach ($students as $student)
                                <a href="{{ route('inventory-sales.create', ['search' => $search, 'student_id' => $student->id]) }}"
                                    class="list-group-item list-group-item-action {{ request('student_id') == $student->id ? 'active' : '' }}">
                                    {{ $student->name }} — {{ $student->class_name }} {{ $student->section_name }} ({{ _lang('Roll') }}: {{ $student->roll }})
                                </a>
                            @endforeach
                        </div>
                    @elseif ($search !== '')
                        <p class="text-danger mt-3">{{ _lang('No student found.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if (request('student_id'))
                <div class="card">
                    <div class="card-header"><div class="card-title">{{ _lang('Sale Details') }}</div></div>
                    <div class="card-body">
                        <form action="{{ route('inventory-sales.store') }}" method="post" id="sale-form">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ request('student_id') }}">

                            <table class="table" id="items-table">
                                <thead>
                                    <tr>
                                        <th>{{ _lang('Item') }}</th>
                                        <th style="width:120px">{{ _lang('Quantity') }}</th>
                                        <th style="width:140px">{{ _lang('Unit Price') }}</th>
                                        <th style="width:140px">{{ _lang('Line Total') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[0][item_id]" class="form-control item-select" required>
                                                <option value="">{{ _lang('Select Item') }}</option>
                                                @foreach ($items as $item)
                                                    <option value="{{ $item->id }}" data-price="{{ $item->selling_price }}" data-stock="{{ $item->stock_quantity }}">
                                                        {{ $item->name }} ({{ $item->category?->name }}) — {{ _lang('Stock') }}: {{ $item->stock_quantity }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="items[0][quantity]" class="form-control item-qty" min="1" value="1" required></td>
                                        <td><input type="text" class="form-control item-price" readonly></td>
                                        <td><input type="text" class="form-control item-line-total" readonly></td>
                                        <td><button type="button" class="btn btn-sm btn-danger remove-row">&times;</button></td>
                                    </tr>
                                </tbody>
                            </table>

                            <button type="button" class="btn btn-sm btn-secondary mb-3" id="add-row">{{ _lang('+ Add Item') }}</button>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">{{ _lang('Total Payable') }}</label>
                                    <input type="text" id="total-payable" class="form-control" readonly value="0.00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ _lang('Paid Now') }}</label>
                                    <input type="number" step="0.01" name="paid_now" id="paid-now" class="form-control" min="0" value="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ _lang('Receive Ledger') }}</label>
                                    <select name="receive_ledger_id" class="form-control">
                                        <option value="">{{ _lang('None (do not post to accounting)') }}</option>
                                        @foreach ($ledgers as $ledger)
                                            <option value="{{ $ledger->id }}">{{ $ledger->ledger_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ _lang('Note') }}</label>
                                    <textarea name="note" class="form-control"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-4">{{ _lang('Record Sale') }}</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-muted">{{ _lang('Search and select a student to begin.') }}</div>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function () {
            const table = document.getElementById('items-table');
            if (!table) return;
            const tbody = table.querySelector('tbody');
            let rowIndex = 1;

            function recalcRow(row) {
                const select = row.querySelector('.item-select');
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const priceField = row.querySelector('.item-price');
                const lineTotalField = row.querySelector('.item-line-total');
                const opt = select.options[select.selectedIndex];
                const price = opt ? parseFloat(opt.dataset.price || 0) : 0;
                priceField.value = price.toFixed(2);
                lineTotalField.value = (price * qty).toFixed(2);
            }

            function recalcTotal() {
                let total = 0;
                tbody.querySelectorAll('.item-line-total').forEach(function (el) {
                    total += parseFloat(el.value) || 0;
                });
                document.getElementById('total-payable').value = total.toFixed(2);
                const paidNow = document.getElementById('paid-now');
                if (parseFloat(paidNow.value) > total) {
                    paidNow.value = total.toFixed(2);
                }
            }

            tbody.addEventListener('change', function (e) {
                if (e.target.classList.contains('item-select') || e.target.classList.contains('item-qty')) {
                    recalcRow(e.target.closest('tr'));
                    recalcTotal();
                }
            });

            tbody.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-row')) {
                    if (tbody.querySelectorAll('.item-row').length > 1) {
                        e.target.closest('tr').remove();
                        recalcTotal();
                    }
                }
            });

            document.getElementById('add-row').addEventListener('click', function () {
                const first = tbody.querySelector('.item-row');
                const clone = first.cloneNode(true);
                clone.querySelectorAll('select, input').forEach(function (el) {
                    if (el.name) {
                        el.name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
                    }
                    if (el.classList.contains('item-select')) {
                        el.selectedIndex = 0;
                    } else if (el.classList.contains('item-qty')) {
                        el.value = 1;
                    } else {
                        el.value = '';
                    }
                });
                rowIndex++;
                tbody.appendChild(clone);
            });
        })();
    </script>
@endsection
