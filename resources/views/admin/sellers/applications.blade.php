@extends('admin.layouts.app')
@section('title', 'Seller Applications')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="page-title">Pending Seller Applications</h4>
                    <p class="text-muted mb-0">Review and approve or reject seller applications</p>
                </div>
                <a href="{{ route('admin.sellers.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to Sellers
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i data-lucide="check-circle" class="me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i data-lucide="alert-circle" class="me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Pending Applications ({{ $applications->total() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Seller</th>
                                <th>Shop Name</th>
                                <th>Business Type</th>
                                <th>Phone</th>
                                <th>Applied</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $seller)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded-circle">
                                                {{ strtoupper(substr($seller->user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $seller->user->name }}</h6>
                                            <small class="text-muted">{{ $seller->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $seller->shop->shop_name ?? 'N/A' }}</td>
                                <td>{{ ucfirst($seller->business_type ?? 'N/A') }}</td>
                                <td>{{ $seller->phone_number ?? 'N/A' }}</td>
                                <td>
                                    {{ $seller->created_at->format('d M, Y') }}<br>
                                    <small class="text-muted">{{ $seller->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.sellers.show', $seller) }}"
                                           class="btn btn-sm btn-primary">
                                            <i data-lucide="eye" class="me-1"></i>Review
                                        </a>
                                        <!-- Quick Approve -->
                                        <form action="{{ route('admin.sellers.approve', $seller) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Approve {{ addslashes($seller->user->name) }}\'s application?')">
                                                <i data-lucide="check"></i>
                                            </button>
                                        </form>
                                        <!-- Quick Reject -->
                                        <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal{{ $seller->id }}">
                                            <i data-lucide="x"></i>
                                        </button>
                                    </div>

                                    <!-- Quick Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $seller->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Application</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.sellers.reject', $seller) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Rejecting <strong>{{ $seller->user->name }}</strong>'s application. They will be notified by email.</p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Reason <span class="text-muted">(optional, sent to seller)</span></label>
                                                            <textarea name="reason" class="form-control" rows="3"
                                                                      placeholder="Reason for rejection..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i data-lucide="check-circle" class="text-success" style="width:48px;height:48px;"></i>
                                    <p class="text-muted mt-2 mb-0">No pending applications — all caught up!</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($applications->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} of {{ $applications->total() }}</div>
                    {{ $applications->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>lucide.createIcons();</script>
@endpush