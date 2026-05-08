@extends('admin.layouts.app')

@section('title', 'Rider Applications')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to All Riders
                </a>
            </div>
            <h4 class="page-title">Pending Rider Applications</h4>
            <p class="text-muted">Review and approve new rider registrations</p>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                                <i data-lucide="clock" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Pending Applications</p>
                        <h4 class="mb-0">{{ $applications->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                                <i data-lucide="calendar" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Today's Applications</p>
                        <h4 class="mb-0">{{ $applications->where('created_at', '>=', now()->startOfDay())->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                                <i data-lucide="users" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">This Week</p>
                        <h4 class="mb-0">{{ $applications->where('created_at', '>=', now()->startOfWeek())->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Applications List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Applications Awaiting Review</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Applicant</th>
                                <th>Contact</th>
                                <th>Vehicle Details</th>
                                <th>License</th>
                                <th>Applied Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $rider)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            @if($rider->profile_photo)
                                                <img src="{{ asset('storage/' . $rider->profile_photo) }}" 
                                                     class="rounded-circle" alt="{{ $rider->full_name }}">
                                            @else
                                                <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle">
                                                    {{ strtoupper(substr($rider->full_name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $rider->full_name }}</h6>
                                            <small class="text-muted">{{ $rider->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i data-lucide="phone" class="me-1 text-muted" style="width: 14px; height: 14px;"></i>
                                        {{ $rider->phone_number }}
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge bg-secondary mb-1">{{ ucfirst($rider->vehicle_type) }}</span>
                                        <br>
                                        <small class="text-muted">{{ $rider->vehicle_registration }}</small>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $rider->license_number }}</small>
                                </td>
                                <td>
                                    <div>
                                        {{ $rider->created_at->format('d M, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $rider->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-soft-info"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#reviewModal{{ $rider->id }}">
                                            <i data-lucide="eye" class="me-1"></i>Review
                                        </button>
                                        <button type="button" class="btn btn-sm btn-soft-success"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#approveModal{{ $rider->id }}">
                                            <i data-lucide="check-circle" class="me-1"></i>Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-soft-danger"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectModal{{ $rider->id }}">
                                            <i data-lucide="x-circle" class="me-1"></i>Reject
                                        </button>
                                    </div>

                                    <!-- Review Modal -->
                                    <div class="modal fade" id="reviewModal{{ $rider->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Application Review - {{ $rider->full_name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="text-muted small">Full Name</label>
                                                            <p class="mb-0 fw-medium">{{ $rider->full_name }}</p>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="text-muted small">Email</label>
                                                            <p class="mb-0">{{ $rider->user->email }}</p>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="text-muted small">Phone Number</label>
                                                            <p class="mb-0">{{ $rider->phone_number }}</p>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="text-muted small">Vehicle Type</label>
                                                            <p class="mb-0">{{ ucfirst($rider->vehicle_type) }}</p>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="text-muted small">Vehicle Registration</label>
                                                            <p class="mb-0">{{ $rider->vehicle_registration }}</p>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="text-muted small">License Number</label>
                                                            <p class="mb-0">{{ $rider->license_number }}</p>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="text-muted small">Application Date</label>
                                                            <p class="mb-0">{{ $rider->created_at->format('F d, Y h:i A') }}</p>
                                                        </div>
                                                    </div>

                                                    @if($rider->profile_photo)
                                                    <div class="mt-3">
                                                        <label class="text-muted small">Profile Photo</label>
                                                        <div class="mt-2">
                                                            <img src="{{ asset('storage/' . $rider->profile_photo) }}" 
                                                                 class="img-fluid rounded" 
                                                                 style="max-height: 300px;"
                                                                 alt="Profile Photo">
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-success" 
                                                            data-bs-dismiss="modal"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approveModal{{ $rider->id }}">
                                                        <i data-lucide="check-circle" class="me-1"></i>Approve
                                                    </button>
                                                    <button type="button" class="btn btn-danger"
                                                            data-bs-dismiss="modal"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal{{ $rider->id }}">
                                                        <i data-lucide="x-circle" class="me-1"></i>Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Approve Modal -->
                                    <div class="modal fade" id="approveModal{{ $rider->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">Approve Rider Application</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.riders.approve', $rider) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="alert alert-success">
                                                            <i data-lucide="check-circle" class="me-2"></i>
                                                            Approve <strong>{{ $rider->full_name }}</strong> as a verified rider?
                                                        </div>
                                                        <p>Once approved, the rider will be able to:</p>
                                                        <ul>
                                                            <li>Accept delivery requests</li>
                                                            <li>Access the rider dashboard</li>
                                                            <li>Start earning</li>
                                                        </ul>
                                                        <div class="mb-3">
                                                            <label class="form-label">Notes (Optional)</label>
                                                            <textarea name="notes" class="form-control" rows="2" 
                                                                      placeholder="Add any notes..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i data-lucide="check-circle" class="me-1"></i>Approve Application
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $rider->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Reject Application</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.riders.reject', $rider) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i data-lucide="alert-triangle" class="me-2"></i>
                                                            You are about to reject this application.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Reason for Rejection *</label>
                                                            <textarea name="reason" class="form-control" rows="3" 
                                                                      placeholder="Explain why this application is being rejected..."
                                                                      required></textarea>
                                                            <small class="text-muted">This reason will be sent to the applicant.</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i data-lucide="x-circle" class="me-1"></i>Reject Application
                                                        </button>
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
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted">No pending applications</p>
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
                    <div class="text-muted">
                        Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} of {{ $applications->total() }}
                    </div>
                    {{ $applications->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
    
    // Reinitialize icons when modals are shown
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function () {
            lucide.createIcons();
        });
    });
</script>
@endpush