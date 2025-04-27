@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Event Assignment</h1>
        <a href="{{ route('tenant.event-assignments.index', ['slug' => $slug]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('tenant.event-assignments.update', ['slug' => $slug, 'id' => $assignment->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="event_id">Event <span class="text-danger">*</span></label>
                    <select name="event_id" id="event_id" class="form-control @error('event_id') is-invalid @enderror" required>
                        <option value="">Select Event</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ old('event_id', $assignment->event_id) == $event->id ? 'selected' : '' }}>
                                {{ $event->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('event_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Contestants <span class="text-danger">*</span></label>
                    <div class="card">
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <div class="row">
                                @foreach($contestants as $contestant)
                                    <div class="col-md-4 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" 
                                                class="custom-control-input" 
                                                id="contestant_{{ $contestant->id }}" 
                                                name="contestant_ids[]" 
                                                value="{{ $contestant->id }}"
                                                {{ in_array($contestant->id, old('contestant_ids', [$assignment->contestant_id])) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="contestant_{{ $contestant->id }}">
                                                {{ $contestant->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @error('contestant_ids')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Categories <span class="text-danger">*</span></label>
                    <div class="card">
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <div class="row">
                                @foreach($categories as $category)
                                    <div class="col-md-4 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" 
                                                class="custom-control-input" 
                                                id="category_{{ $category->id }}" 
                                                name="category_ids[]" 
                                                value="{{ $category->id }}"
                                                {{ in_array($category->id, old('category_ids', [$assignment->category_id])) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="category_{{ $category->id }}">
                                                {{ $category->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @error('category_ids')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="registered" {{ old('status', $assignment->status) == 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="confirmed" {{ old('status', $assignment->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="withdrawn" {{ old('status', $assignment->status) == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $assignment->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize select2 for event dropdown only
        $('#event_id').select2({
            theme: 'bootstrap4'
        });
    });
</script>
@endsection 