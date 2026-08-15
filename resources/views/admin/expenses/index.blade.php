@extends("layouts.admin")
@section("content")
<div class="d-flex flex-column flex-md-row justify-content-between mb-2 mt-2 gap-3 gap-md-0">
    <div>
        <h2 class="mb-0 fw-bold"><i class="fas fa-wallet text-danger me-2"></i> Operational Expenses</h2>
        <p class="text-muted small">Monitor your store expenditures and raw material costs.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        @if($tab !== 'archived')
        <div class="btn-group shadow-sm border rounded-pill overflow-hidden bg-white p-1 me-2" style="background: #f8fafc;">
            <button class="btn btn-sm btn-light border-0 px-3 active-view" id="btnGrid" onclick="setViewMode('grid', '')"><i class="fas fa-th-large"></i></button>
            <button class="btn btn-sm btn-light border-0 px-3" id="btnList" onclick="setViewMode('list', '')"><i class="fas fa-list"></i></button>
        </div>
        <button class="btn btn-outline-danger px-4 rounded-pill shadow-sm bg-white" onclick="alert('Feature coming soon: Export to PDF/Excel')"><i class="fas fa-download small me-2"></i> Export</button>
        <button class="btn btn-primary px-4 rounded-pill shadow-sm" onclick="openAddExpense()"><i class="fas fa-plus small me-2"></i> Log Expenditure</button>
        @endif
    </div>
</div>

<ul class="nav nav-tabs border-0 mb-4 px-2" style="gap: 15px;">
    <li class="nav-item">
        <a class="nav-link rounded-pill px-4 py-2 {{ $tab !== 'archived' ? 'active bg-white border fw-bold shadow-sm' : 'border-0 text-muted' }}" 
           href="{{ route('expenses.index') }}"><i class="fas fa-check-circle me-1"></i> Active Ledger</a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded-pill px-4 py-2 {{ $tab === 'archived' ? 'active bg-white border fw-bold shadow-sm' : 'border-0 text-muted' }}" 
           href="{{ route('expenses.index', ['tab' => 'archived']) }}"><i class="fas fa-archive me-1"></i> Deleted Archive</a>
    </li>
</ul>

@if($tab !== 'archived')
<div class="row" id="gridView">
    @foreach($expenses->groupBy('category') as $cat => $group)
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <span class="badge border text-dark fw-bold rounded-pill px-3 py-2 bg-light"><i class="fas fa-tag me-1 text-primary"></i> {{ $cat }}</span>
                <span class="fw-bold text-danger">-&#8377;{{ number_format($group->sum('amount'), 2) }}</span>
            </div>
            <div class="card-body px-0">
                <div class="list-group list-group-flush">
                    @foreach($group->take(3) as $e)
                    <div class="list-group-item bg-transparent py-3 px-4 border-light d-flex justify-content-between">
                        <div>
                            <p class="mb-0 fw-bold small">{{ $e->description ?: "Unspecified Item" }}</p>
                            <small class="text-muted">{{ date("d M, Y", strtotime($e->date)) }}</small>
                        </div>
                        <span class="text-secondary small fw-bold">-&#8377;{{ number_format($e->amount, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-3 px-4 text-center">
                <a href="javascript:void(0)" onclick="setViewMode('list', '{{ $cat }}')" class="small text-decoration-none fw-bold text-primary">View Full Category History <i class="fas fa-chevron-right small ms-1"></i></a>
            </div>
        </div>
    </div>
    @endforeach
    
    @if($expenses->isEmpty())
    <div class="col-12 text-center py-5">
        <div class="bg-white rounded-5 p-5 shadow-sm border">
            <i class="fas fa-receipt fa-4x text-muted mb-3 opacity-25"></i>
            <h5 class="text-muted fw-bold">No expenses recorded yet.</h5>
            <p class="small text-muted mb-0">Start tracking your business expenditures here.</p>
        </div>
    </div>
    @endif
</div>
@endif

<div class="card border-0 shadow-sm rounded-4 {{ $tab === 'archived' ? '' : 'd-none' }} mb-4" id="listView">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">{{ $tab === 'archived' ? 'Deleted Expense Records' : 'Complete Expense Ledger' }} <span id="categoryFilterLabel" class="text-primary fs-6 ms-2"></span></h5>
        <button id="clearFilterBtn" class="btn btn-sm btn-outline-secondary rounded-pill d-none" onclick="setViewMode('list', '')">Clear Filter X</button>
    </div>
    <div class="card-body p-0 mt-3">
        @if($expenses->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-archive fa-3x text-muted mb-3 opacity-25"></i>
            <h5 class="text-muted fw-bold">No records found.</h5>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="expenseTable">
                <thead class="bg-light text-muted small text-uppercase" style="font-size: 11px;">
                    <tr>
                        <th class="ps-4 py-3">Date</th>
                        <th class="py-3">Category</th>
                        <th class="py-3">Description</th>
                        <th class="py-3 text-end">Amount</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $e)
                        <tr data-category="{{ $e->category }}">
                            <td class="ps-4"><span class="fw-bold text-dark">{{ date("d M, Y", strtotime($e->date)) }}</span></td>
                            <td><span class="badge bg-light text-dark fw-normal border">{{ $e->category }}</span></td>
                            <td>
                                <div class="small fw-600 text-dark">{{ $e->description ?: 'Unspecified Payment' }}</div>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-danger">-&#8377;{{ number_format($e->amount, 2) }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    @if($tab !== 'archived')
                                    <button class="btn btn-sm btn-light border shadow-sm rounded-pill px-3" title="Edit"
                                        onclick='editExpense({{ $e->id }}, {!! json_encode($e->category) !!}, {!! json_encode($e->amount) !!}, {!! json_encode($e->date) !!}, {!! json_encode($e->description) !!})'>
                                        <i class="fas fa-edit text-primary small me-1"></i> <small class="fw-bold">Edit</small>
                                    </button>
                                    <form action="{{ route('expenses.destroy', $e->id) }}" method="POST">
                                        @csrf @method("DELETE")
                                        <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3" title="Delete"
                                            onclick="confirmAction('Delete Expense', 'Are you sure you want to remove this record?', () => this.closest('form').submit())">
                                            <i class="fas fa-trash text-danger small me-1"></i> <small class="fw-bold">Del</small>
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('expenses.restore', $e->id) }}" method="POST">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3" title="Restore"
                                            onclick="confirmAction('Restore Expense', 'Are you sure you want to restore this record?', () => this.closest('form').submit())">
                                            <i class="fas fa-undo text-success small me-1"></i> <small class="fw-bold">Restore</small>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- EXPENSE MODAL (ADD/EDIT) -->
<div class="modal fade" id="expenseModal">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form id="expenseForm" method="POST">
                @csrf <span id="method_field"></span>
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="expenseModalTitle">Log New Expenditure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Expense Type</label>
                    <select name="category" id="f_category" class="form-select mb-3 shadow-none bg-light border-0" required>
                        <option value="Rent">Rent</option>
                        <option value="Salary">Salary Staff</option>
                        <option value="Raw Material">Inventory / Raw Materials</option>
                        <option value="Electricity">Bills / Electricity / Internet</option>
                        <option value="Repair">Maintenance & Repairs</option>
                        <option value="Marketing">Marketing & Ads</option>
                        <option value="Other">Other Expenses</option>
                    </select>
                    
                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Expended Amount</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white border-0 fw-bold">&#8377;</span>
                        <input type="number" step="0.01" name="amount" id="f_amount" class="form-control bg-light border-0 shadow-none text-danger fw-bold" placeholder="0.00" required>
                    </div>

                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Date of Payment</label>
                    <input type="date" name="date" id="f_date" class="form-control mb-3 shadow-none bg-light border-0" required value="{{ date("Y-m-d") }}">
                    
                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Expense Information</label>
                    <textarea name="description" id="f_description" class="form-control shadow-none bg-light border-0" rows="3" placeholder="Additional details (Vendor name, Invoice #)..."></textarea>
                </div>
                <div class="modal-footer border-0 pb-4 pt-0 px-4">
                    <button type="submit" class="btn btn-danger w-100 py-3 rounded-pill fw-bold shadow-lg"><i class="fas fa-save me-2"></i> Save Expenditure</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script>
    function setViewMode(mode, filterCategory = '') {
        localStorage.setItem('expenses_view_mode', mode);
        
        if (mode === 'list') {
            $('#gridView').addClass('d-none');
            $('#listView').removeClass('d-none');
            $('#btnList').addClass('active-view bg-primary text-white').removeClass('btn-light');
            $('#btnGrid').addClass('btn-light').removeClass('active-view bg-primary text-white');
            
            if(filterCategory !== '') {
                $('#categoryFilterLabel').text('— ' + filterCategory + ' Only');
                $('#clearFilterBtn').removeClass('d-none');
                
                $('#expenseTable tbody tr').each(function() {
                    if($(this).data('category') === filterCategory) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            } else {
                $('#categoryFilterLabel').text('');
                $('#clearFilterBtn').addClass('d-none');
                $('#expenseTable tbody tr').removeClass('d-none');
            }
        } else {
            $('#listView').addClass('d-none');
            $('#gridView').removeClass('row').addClass('row').removeClass('d-none');
            $('#btnGrid').addClass('active-view bg-primary text-white').removeClass('btn-light');
            $('#btnList').addClass('btn-light').removeClass('active-view bg-primary text-white');
            
            // clear tables to reset
            $('#categoryFilterLabel').text('');
            $('#clearFilterBtn').addClass('d-none');
            $('#expenseTable tbody tr').removeClass('d-none');
        }
    }

    $(document).ready(function () {
        let mode = localStorage.getItem('expenses_view_mode') || 'grid';
        setViewMode(mode, '');
    });

    function openAddExpense() {
        $("#expenseForm")[0].reset();
        $("#expenseForm").attr("action", "{{ route('expenses.store') }}");
        $("#method_field").html('');
        $("#expenseModalTitle").text("Log New Expenditure");
        $("#f_date").val("{{ date('Y-m-d') }}");
        new bootstrap.Modal(document.getElementById("expenseModal")).show();
    }

    function editExpense(id, category, amount, date, description) {
        $("#expenseForm")[0].reset();
        $("#expenseForm").attr("action", "/cp/expenses/" + id);
        $("#method_field").html('<input type="hidden" name="_method" value="PUT">');
        $("#expenseModalTitle").text("Edit Expense Details");
        
        $("#f_category").val(category);
        $("#f_amount").val(amount);
        $("#f_date").val(date.split(' ')[0]);
        $("#f_description").val(description);

        new bootstrap.Modal(document.getElementById("expenseModal")).show();
    }
</script>
@endsection

