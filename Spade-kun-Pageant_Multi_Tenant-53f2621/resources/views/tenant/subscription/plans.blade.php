@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Subscription Plans</h4>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="row">
        @foreach($plans as $plan)
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary">
                    <h4 class="card-title text-white">{{ $plan->name }}</h4>
                </div>
                <div class="card-body">
                    <div class="price-tag">
                        <h2 class="text-center mb-4">₱{{ number_format($plan->price, 2) }}<small>/{{ $plan->interval }}</small></h2>
                    </div>
                    <p class="card-text">{{ $plan->description }}</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Max Events: {{ $plan->max_events }}</li>
                        <li><i class="fas fa-check text-success"></i> Max Contestants: {{ $plan->max_contestants }}</li>
                        <li><i class="fas fa-check text-success"></i> Max Categories: {{ $plan->max_categories }}</li>
                        <li><i class="fas fa-check text-success"></i> Max Judges: {{ $plan->max_judges }}</li>
                        @if($plan->analytics)
                            <li><i class="fas fa-check text-success"></i> Analytics</li>
                        @endif
                        @if($plan->support_priority)
                            <li><i class="fas fa-check text-success"></i> Priority Support</li>
                        @endif
                    </ul>

                    @if($currentPlan && $currentPlan->id === $plan->id)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This is your current plan
                        </div>
                    @elseif($pendingRequest && $pendingRequest->plan_id === $plan->id)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> Plan request pending approval
                        </div>
                    @else
                        <form action="{{ route('tenant.subscription.request', ['slug' => $slug]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <div class="form-group">
                                <label for="notes">Notes (Optional)</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Request Plan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection 