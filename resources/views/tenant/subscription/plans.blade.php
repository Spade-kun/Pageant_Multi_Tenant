@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-inner">
    <!-- IMPROVED HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="page-title fw-bold">{{ __('Subscription Plans') }}</h3>
            <p class="text-muted">{{ __('Choose the right plan for your pageant needs') }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4">
        <i class="fas fa-check-circle text-success me-3 fa-2x"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4">
        <i class="fas fa-exclamation-circle text-danger me-3 fa-2x"></i>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    <div class="row">
        @foreach($plans as $plan)
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100 plan-card {{ $currentPlan && $currentPlan->id === $plan->id ? 'current-plan' : '' }}">
                @if($currentPlan && $currentPlan->id === $plan->id)
                <div class="ribbon ribbon-top-right"><span>Current Plan</span></div>
                @endif
                
                <div class="card-header bg-gradient-{{ $plan->pageant_management ? ($plan->reports_module ? 'primary' : 'info') : 'secondary' }} text-white py-4">
                    <h4 class="card-title text-white mb-0 text-center fw-bold">{{ $plan->name }}</h4>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <div class="price-tag text-center mb-4">
                        <span class="currency">₱</span>
                        <span class="amount">{{ number_format($plan->price, 0) }}</span>
                        <span class="period">/{{ $plan->interval }}</span>
                    </div>
                    
                    <p class="card-text text-center mb-4">{{ $plan->description }}</p>
                    
                    <div class="divider-dashed my-3"></div>
                    
                    <h5 class="feature-title">
                        <i class="fas fa-sliders-h me-2"></i> {{ __('Resource Limits') }}
                    </h5>
                    <ul class="feature-list mb-4">
                        <li>
                            <i class="fas fa-calendar-check text-primary"></i>
                            <span>{{ __('Max Events') }}: <strong>{{ $plan->max_events }}</strong></span>
                        </li>
                        <li>
                            <i class="fas fa-users text-primary"></i>
                            <span>{{ __('Max Contestants') }}: <strong>{{ $plan->max_contestants }}</strong></span>
                        </li>
                        <li>
                            <i class="fas fa-tags text-primary"></i>
                            <span>{{ __('Max Categories') }}: <strong>{{ $plan->max_categories }}</strong></span>
                        </li>
                        <li>
                            <i class="fas fa-gavel text-primary"></i>
                            <span>{{ __('Max Judges') }}: <strong>{{ $plan->max_judges }}</strong></span>
                        </li>
                    </ul>
                    
                    <h5 class="feature-title">
                        <i class="fas fa-star me-2"></i> {{ __('Features') }}
                    </h5>
                    <ul class="feature-list">
                        <!-- Default features -->
                        <li class="included">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>{{ __('Dashboard Access') }}</span>
                        </li>
                        <li class="included">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>{{ __('User Management') }}</span>
                        </li>
                        <li class="included">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>{{ __('Subscription Management') }}</span>
                        </li>
                        
                        <!-- Premium features -->
                        <li class="{{ $plan->pageant_management ? 'included' : 'excluded' }}">
                            <i class="fas {{ $plan->pageant_management ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }}"></i>
                            <span>{{ __('Pageant Management') }}</span>
                        </li>
                        <li class="{{ $plan->reports_module ? 'included' : 'excluded' }}">
                            <i class="fas {{ $plan->reports_module ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }}"></i>
                            <span>{{ __('Reports Module') }}</span>
                        </li>
                        
                        <!-- Additional features -->
                        <li class="{{ $plan->analytics ? 'included' : 'excluded' }}">
                            <i class="fas {{ $plan->analytics ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }}"></i>
                            <span>{{ __('Analytics') }}</span>
                        </li>
                        <li class="{{ $plan->support_priority ? 'included' : 'excluded' }}">
                            <i class="fas {{ $plan->support_priority ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }}"></i>
                            <span>{{ __('Priority Support') }}</span>
                        </li>
                    </ul>

                    <div class="mt-auto pt-4">
                        @if($currentPlan && $currentPlan->id === $plan->id)
                            <div class="plan-status current">
                                <i class="fas fa-check-circle me-2"></i> {{ __('This is your current plan') }}
                            </div>
                        @elseif($pendingRequest && $pendingRequest->plan_id === $plan->id)
                            <div class="plan-status pending">
                                <i class="fas fa-clock me-2"></i> {{ __('Plan request pending approval') }}
                            </div>
                        @else
                            <button type="button" class="btn btn-{{ $plan->pageant_management ? ($plan->reports_module ? 'primary' : 'info') : 'secondary' }} btn-block btn-lg" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                <i class="fas fa-paper-plane me-2"></i> {{ __('Request Plan') }}
                            </button>
                            
                            <!-- Modal for plan request -->
                            <div class="modal fade" id="planModal{{ $plan->id }}" tabindex="-1" aria-labelledby="planModalLabel{{ $plan->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="planModalLabel{{ $plan->id }}">Request {{ $plan->name }} Plan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('tenant.subscription.request', ['slug' => $slug]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                            <div class="modal-body">
                                                <p class="mb-3">You are requesting to subscribe to the <strong>{{ $plan->name }}</strong> plan at <strong>₱{{ number_format($plan->price, 2) }}/{{ $plan->interval }}</strong>.</p>
                                                
                                                <div class="form-group">
                                                    <label for="notes{{ $plan->id }}">Additional Notes (Optional)</label>
                                                    <textarea name="notes" id="notes{{ $plan->id }}" class="form-control" rows="3" placeholder="Any questions or special requirements..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Submit Request</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
    /* Plan Card Styling */
    .plan-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .current-plan {
        border: 2px solid #4CAF50 !important;
        position: relative;
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }
    
    /* Price Tag Styling */
    .price-tag {
        position: relative;
        padding: 10px 0;
    }
    
    .price-tag .currency {
        font-size: 1.5rem;
        vertical-align: top;
        position: relative;
        top: 10px;
    }
    
    .price-tag .amount {
        font-size: 3.5rem;
        font-weight: 700;
    }
    
    .price-tag .period {
        font-size: 1rem;
        color: #6c757d;
    }
    
    /* Feature List Styling */
    .feature-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: #495057;
    }
    
    .feature-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 20px;
    }
    
    .feature-list li {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .feature-list li:last-child {
        border-bottom: none;
    }
    
    .feature-list li i {
        font-size: 1.1rem;
        width: 24px;
        margin-right: 10px;
    }
    
    .feature-list li span {
        flex-grow: 1;
    }
    
    .excluded {
        color: #6c757d;
    }
    
    /* Plan Status Styling */
    .plan-status {
        text-align: center;
        padding: 10px;
        border-radius: 5px;
        font-weight: 500;
    }
    
    .plan-status.current {
        background-color: rgba(76, 175, 80, 0.1);
        color: #4CAF50;
    }
    
    .plan-status.pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: #FFC107;
    }
    
    /* Divider */
    .divider-dashed {
        border-top: 1px dashed rgba(0,0,0,0.1);
        margin: 15px 0;
    }
    
    /* Background Gradients */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
    }
    
    .bg-gradient-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }
    
    /* Ribbon */
    .ribbon {
        position: absolute;
        z-index: 1;
        overflow: hidden;
        width: 150px;
        height: 150px;
        pointer-events: none;
    }
    
    .ribbon-top-right {
        top: -10px;
        right: -10px;
    }
    
    .ribbon-top-right::before,
    .ribbon-top-right::after {
        border-top-color: transparent;
        border-right-color: transparent;
    }
    
    .ribbon-top-right::before {
        top: 0;
        left: 0;
    }
    
    .ribbon-top-right::after {
        bottom: 0;
        right: 0;
    }
    
    .ribbon-top-right span {
        position: absolute;
        top: 30px;
        right: -25px;
        transform: rotate(45deg);
        background-color: #4CAF50;
        color: white;
        padding: 5px 35px;
        font-size: 0.8rem;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
</style>
@endsection