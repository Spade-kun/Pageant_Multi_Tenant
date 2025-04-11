@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Contestants</h4>
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Contestants List</h4>
                        <a href="{{ route('tenant.contestants.create', ['slug' => $slug]) }}" class="btn btn-primary btn-round ml-auto">
                            <i class="fa fa-plus"></i>
                            Add Contestant
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="contestants-table" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contestants as $contestant)
                                <tr>
                                    <td>
                                        <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                             alt="Contestant Photo" 
                                             class="rounded-circle"
                                             width="50" 
                                             height="50"
                                             style="object-fit: cover;">
                                    </td>
                                    <td>{{ $contestant->name }}</td>
                                    <td>{{ $contestant->age }}</td>
                                    <td>{{ $contestant->gender }}</td>
                                    <td>{{ $contestant->score ?? 'N/A' }}</td>
                                    <td>
                                        <div class="form-button-action">
                                            <a href="{{ route('tenant.contestants.show', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                               class="btn btn-link btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tenant.contestants.edit', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                               class="btn btn-link btn-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('tenant.contestants.destroy', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-link btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this contestant?')">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#contestants-table').DataTable({
            "pageLength": 10,
            "responsive": true,
            "order": [[1, "asc"]], // Sort by name by default
            "columnDefs": [
                { "orderable": false, "targets": [0, 5] } // Disable sorting for photo and actions columns
            ]
        });
    });
</script>
@endpush
@endsection 