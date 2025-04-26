@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-header">
    <h4 class="page-title">UI Customization</h4>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Customize Your Dashboard</div>
            </div>
            <div class="card-body">
                <form action="{{ route('tenant.ui-settings.update', ['slug' => session('tenant_slug')]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Colors Section -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Logo Header Color</label>
                                <select name="logo_header_color" class="form-control">
                                    <option value="dark" {{ $settings->logo_header_color == 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="blue" {{ $settings->logo_header_color == 'blue' ? 'selected' : '' }}>Blue</option>
                                    <option value="purple" {{ $settings->logo_header_color == 'purple' ? 'selected' : '' }}>Purple</option>
                                    <option value="light-blue" {{ $settings->logo_header_color == 'light-blue' ? 'selected' : '' }}>Light Blue</option>
                                    <option value="green" {{ $settings->logo_header_color == 'green' ? 'selected' : '' }}>Green</option>
                                    <option value="orange" {{ $settings->logo_header_color == 'orange' ? 'selected' : '' }}>Orange</option>
                                    <option value="red" {{ $settings->logo_header_color == 'red' ? 'selected' : '' }}>Red</option>
                                    <option value="white" {{ $settings->logo_header_color == 'white' ? 'selected' : '' }}>White</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Navbar Color</label>
                                <select name="navbar_color" class="form-control">
                                    <option value="dark" {{ $settings->navbar_color == 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="blue" {{ $settings->navbar_color == 'blue' ? 'selected' : '' }}>Blue</option>
                                    <option value="purple" {{ $settings->navbar_color == 'purple' ? 'selected' : '' }}>Purple</option>
                                    <option value="light-blue" {{ $settings->navbar_color == 'light-blue' ? 'selected' : '' }}>Light Blue</option>
                                    <option value="green" {{ $settings->navbar_color == 'green' ? 'selected' : '' }}>Green</option>
                                    <option value="orange" {{ $settings->navbar_color == 'orange' ? 'selected' : '' }}>Orange</option>
                                    <option value="red" {{ $settings->navbar_color == 'red' ? 'selected' : '' }}>Red</option>
                                    <option value="white" {{ $settings->navbar_color == 'white' ? 'selected' : '' }}>White</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sidebar Color</label>
                                <select name="sidebar_color" class="form-control">
                                    <option value="dark" {{ $settings->sidebar_color == 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="blue" {{ $settings->sidebar_color == 'blue' ? 'selected' : '' }}>Blue</option>
                                    <option value="purple" {{ $settings->sidebar_color == 'purple' ? 'selected' : '' }}>Purple</option>
                                    <option value="light-blue" {{ $settings->sidebar_color == 'light-blue' ? 'selected' : '' }}>Light Blue</option>
                                    <option value="green" {{ $settings->sidebar_color == 'green' ? 'selected' : '' }}>Green</option>
                                    <option value="orange" {{ $settings->sidebar_color == 'orange' ? 'selected' : '' }}>Orange</option>
                                    <option value="red" {{ $settings->sidebar_color == 'red' ? 'selected' : '' }}>Red</option>
                                    <option value="white" {{ $settings->sidebar_color == 'white' ? 'selected' : '' }}>White</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Layout Section -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Navbar Position</label>
                                <select name="navbar_position" class="form-control">
                                    <option value="top" {{ $settings->navbar_position == 'top' ? 'selected' : '' }}>Top</option>
                                    <option value="bottom" {{ $settings->navbar_position == 'bottom' ? 'selected' : '' }}>Bottom</option>
                                    <option value="left" {{ $settings->navbar_position == 'left' ? 'selected' : '' }}>Left</option>
                                    <option value="right" {{ $settings->navbar_position == 'right' ? 'selected' : '' }}>Right</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sidebar Position</label>
                                <select name="sidebar_position" class="form-control">
                                    <option value="left" {{ $settings->sidebar_position == 'left' ? 'selected' : '' }}>Left</option>
                                    <option value="right" {{ $settings->sidebar_position == 'right' ? 'selected' : '' }}>Right</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Toggle Options -->
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_sidebar_collapsed" name="is_sidebar_collapsed" {{ $settings->is_sidebar_collapsed ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_sidebar_collapsed">Collapse Sidebar by Default</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_navbar_fixed" name="is_navbar_fixed" {{ $settings->is_navbar_fixed ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_navbar_fixed">Fixed Navbar</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_sidebar_fixed" name="is_sidebar_fixed" {{ $settings->is_sidebar_fixed ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_sidebar_fixed">Fixed Sidebar</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 