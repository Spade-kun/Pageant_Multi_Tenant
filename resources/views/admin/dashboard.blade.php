<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __("Welcome to the Admin Dashboard") }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Manage Tenants') }}</h4>
                            <p class="text-sm mb-4">{{ __('View, approve, or reject tenant registrations.') }}</p>
                            <a href="{{ route('admin.tenants.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('View Tenants') }}
                            </a>
                        </div>
                        
                        <div class="bg-green-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Manage Users') }}</h4>
                            <p class="text-sm mb-4">{{ __('Manage admin users and their permissions.') }}</p>
                            <a href="#" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('View Users') }}
                            </a>
                        </div>
                        
                        <div class="bg-purple-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('System Settings') }}</h4>
                            <p class="text-sm mb-4">{{ __('Configure system-wide settings and preferences.') }}</p>
                            <a href="#" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Settings') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 