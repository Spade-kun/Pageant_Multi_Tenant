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

    @php
        $tenant = \App\Models\Tenant::where('slug', request()->route('slug'))->first();
        $currentPlan = $tenant->subscription_plan ?? 'trial';
        $trialEndsAt = \Carbon\Carbon::parse($tenant->created_at)->addDays(3);
        $isTrialActive = now()->lt($trialEndsAt);
    @endphp

    <div class="row justify-content-center">
        <!-- Trial Plan -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="fw-bold">Free Trial</h3>
                    <div class="price-tag">
                        <span class="price">Free</span>
                        <span class="period">/3 days</span>
                    </div>
                    <div class="my-4">
                        <ul class="list-unstyled">
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Full Access</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> All Features</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> 3 Days Trial</li>
                        </ul>
                    </div>
                    @if($isTrialActive && !$currentPlan)
                        <button class="btn btn-secondary btn-round" disabled>
                            Current Plan
                        </button>
                    @else
                        <button class="btn btn-secondary btn-round" disabled>
                            Trial Expired
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- 30 Days Plan -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="fw-bold">Basic</h3>
                    <div class="price-tag">
                        <span class="price">₱999</span>
                        <span class="period">/30 days</span>
                    </div>
                    <div class="my-4">
                        <ul class="list-unstyled">
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Full Access</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> All Features</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> 30 Days Access</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Priority Support</li>
                        </ul>
                    </div>
                    @if($currentPlan === '30_days')
                        <button class="btn btn-secondary btn-round" disabled>
                            Current Plan
                            <br>
                            <small>Expires: {{ $tenant->subscription_ends_at ? \Carbon\Carbon::parse($tenant->subscription_ends_at)->format('M d, Y') : 'N/A' }}</small>
                        </button>
                    @else
                        <form action="{{ route('tenant.subscription.update', ['slug' => request()->route('slug')]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="plan" value="30_days">
                            <button type="submit" class="btn btn-primary btn-round">
                                Choose Plan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Monthly Plan -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="ribbon ribbon-top-right"><span>Popular</span></div>
                    <h3 class="fw-bold">Standard</h3>
                    <div class="price-tag">
                        <span class="price">₱2,499</span>
                        <span class="period">/month</span>
                    </div>
                    <div class="my-4">
                        <ul class="list-unstyled">
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Full Access</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> All Features</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Monthly Access</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Priority Support</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> 20% Savings</li>
                        </ul>
                    </div>
                    @if($currentPlan === 'monthly')
                        <button class="btn btn-secondary btn-round" disabled>
                            Current Plan
                            <br>
                            <small>Expires: {{ $tenant->subscription_ends_at ? \Carbon\Carbon::parse($tenant->subscription_ends_at)->format('M d, Y') : 'N/A' }}</small>
                        </button>
                    @else
                        <form action="{{ route('tenant.subscription.update', ['slug' => request()->route('slug')]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="plan" value="monthly">
                            <button type="submit" class="btn btn-primary btn-round">
                                Choose Plan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Yearly Plan -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="fw-bold">Premium</h3>
                    <div class="price-tag">
                        <span class="price">₱24,999</span>
                        <span class="period">/year</span>
                    </div>
                    <div class="my-4">
                        <ul class="list-unstyled">
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Full Access</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> All Features</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Yearly Access</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Priority Support</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> 40% Savings</li>
                            <li class="mb-3"><i class="fas fa-check text-success"></i> Free Updates</li>
                        </ul>
                    </div>
                    @if($currentPlan === 'yearly')
                        <button class="btn btn-secondary btn-round" disabled>
                            Current Plan
                            <br>
                            <small>Expires: {{ $tenant->subscription_ends_at ? \Carbon\Carbon::parse($tenant->subscription_ends_at)->format('M d, Y') : 'N/A' }}</small>
                        </button>
                    @else
                        <form action="{{ route('tenant.subscription.update', ['slug' => request()->route('slug')]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="plan" value="yearly">
                            <button type="submit" class="btn btn-primary btn-round">
                                Choose Plan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.price-tag {
    margin: 20px 0;
}

.price {
    font-size: 2.5rem;
    font-weight: bold;
    color: #1a2035;
}

.period {
    font-size: 1rem;
    color: #8d9498;
}

.ribbon {
    width: 150px;
    height: 150px;
    overflow: hidden;
    position: absolute;
}

.ribbon span {
    position: absolute;
    display: block;
    width: 225px;
    padding: 8px 0;
    background-color: #3498db;
    box-shadow: 0 5px 10px rgba(0,0,0,.1);
    color: #fff;
    text-shadow: 0 1px 1px rgba(0,0,0,.2);
    text-align: center;
}

.ribbon-top-right {
    top: -10px;
    right: -10px;
}

.ribbon-top-right span {
    left: -25px;
    top: 30px;
    transform: rotate(45deg);
}

.btn-round small {
    display: block;
    font-size: 0.75rem;
    margin-top: 5px;
    opacity: 0.8;
}
</style>
@endsection 