@extends('layouts.DashboardTemplate')

@section('content')
<div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __("Welcome to the Admin Dashboard") }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Manage Tenants') }}</h4>
                            <p class="text-sm mb-4">{{ __('View, approve, or reject tenant registrations.') }}</p>
                            <a href="{{ route('admin.tenants.index') }}" class="btn btn-info btn-round">
                                <span class="btn-label">
                                    <i class="fa fa-building"></i>
                                </span>
                                {{ __('View Tenants') }}
                            </a>
                        </div>
                        
                        <div class="bg-green-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Manage Users') }}</h4>
                            <p class="text-sm mb-4">{{ __('Manage admin users and their permissions.') }}</p>
                            <a href="#" class="btn btn-success btn-round">
                                <span class="btn-label">
                                    <i class="fa fa-users"></i>
                                </span>
                                {{ __('View Users') }}
                            </a>
                        </div>
                        
                        <div class="bg-purple-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('System Settings') }}</h4>
                            <p class="text-sm mb-4">{{ __('Configure system-wide settings and preferences.') }}</p>
                            <a href="#" class="btn btn-primary btn-round">
                                <span class="btn-label">
                                    <i class="fa fa-cog"></i>
                                </span>
                                {{ __('Settings') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection