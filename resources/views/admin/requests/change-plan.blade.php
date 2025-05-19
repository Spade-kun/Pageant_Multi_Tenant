@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Change Plan for {{ $tenant->pageant_name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Requests
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Current Plan</h4>
                                </div>
                                <div class="card-body">
                                    @if($tenant->plan)
                                        <h5>{{ $tenant->plan->name }}</h5>
                                        <p>₱{{ number_format($tenant->plan->price, 2) }}/{{ $tenant->plan->interval }}</p>
                                    @else
                                        <p>No active plan</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.requests.update-plan', $tenant->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label>Select New Plan</label>
                            <select name="plan_id" class="form-control">
                               
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ $tenant->plan_id == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} - ₱{{ number_format($plan->price, 2) }}/{{ $plan->interval }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Plan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 