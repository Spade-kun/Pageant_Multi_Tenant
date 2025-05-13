@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Subscription Plans</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.plans.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create New Plan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Interval</th>
                                    <th>Max Events</th>
                                    <th>Max Contestants</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plans as $plan)
                                    <tr>
                                        <td>{{ $plan->name }}</td>
                                        <td>₱{{ number_format($plan->price, 2) }}</td>
                                        <td>
                                            @switch($plan->interval)
                                                @case('3_days')
                                                    3 Days
                                                    @break
                                                @case('15_days')
                                                    15 Days
                                                    @break
                                                @case('monthly')
                                                    Monthly
                                                    @break
                                                @case('yearly')
                                                    Yearly
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>{{ $plan->max_events }}</td>
                                        <td>{{ $plan->max_contestants }}</td>
                                        <td>
                                            <span class="badge badge-{{ $plan->is_active ? 'success' : 'danger' }}">
                                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.plans.show', $plan) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $plan->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal{{ $plan->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $plan->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $plan->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete the plan <strong>{{ $plan->name }}</strong>?</p>
                                                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete Plan</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 

@push('scripts')
<script>
    $(document).ready(function() {
        // Ensure modals work properly
        var deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var targetModal = this.getAttribute('data-bs-target');
                var modalElement = document.querySelector(targetModal);
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement, {
                    backdrop: false
                });
                modal.show();
            });
        });
    });
</script>

<style>
    /* Ensure modals are centered both vertically and horizontally */
    .modal {
        display: flex !important;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
    }
    
    .modal-dialog {
        margin: 0 auto;
        max-width: 500px;
        width: calc(100% - 30px);
    }
    
    @media (max-width: 576px) {
        .modal-dialog {
            max-width: 95%;
            margin: 10px auto;
        }
    }
    
    .modal-content {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        border: none;
    }
    
    /* Animation for modals */
    .modal-content {
        animation: modalFadeIn 0.3s ease;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush 