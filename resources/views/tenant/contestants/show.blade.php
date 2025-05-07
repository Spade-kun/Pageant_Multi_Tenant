@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Contestant Details</h4>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">{{ $contestant->name }}</h4>
                        <div class="ml-auto">
                            <a href="{{ route('tenant.contestants.edit', ['slug' => $slug, 'id' => $contestant->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('tenant.contestants.destroy', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                  method="POST" 
                                  style="display: inline-block;"
                                  onsubmit="return confirm('Are you sure you want to delete this contestant?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                @if($contestant->photo)
                                    <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                         alt="{{ $contestant->name }}" 
                                         class="img-fluid rounded shadow"
                                         style="max-height: 300px; width: auto;">
                                @else
                                    <div class="text-center p-5 bg-light rounded shadow-sm">
                                        <i class="fa fa-user fa-5x text-secondary"></i>
                                        <p class="mt-3 text-muted">No photo available</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-8">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th style="width: 150px;">Name</th>
                                        <td>{{ $contestant->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Age</th>
                                        <td>{{ $contestant->age }}</td>
                                    </tr>
                                    <tr>
                                        <th>Gender</th>
                                        <td>{{ ucfirst($contestant->gender) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Representing</th>
                                        <td>{{ $contestant->representing }}</td>
                                    </tr>
                                    <tr>
                                        <th>Bio</th>
                                        <td>{{ $contestant->bio ?? 'No bio available' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Registration Date</th>
                                        <td>{{ date('F d, Y', strtotime($contestant->registration_date)) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($contestant->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Score</th>
                                        <td>{{ $contestant->score ?? 'Not scored yet' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection