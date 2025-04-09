<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tenant Details') }}
            </h2>
            <a href="{{ route('admin.tenants.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
                {{ __('Back to List') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tenant Information -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-medium mb-4">Tenant Information</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Pageant Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->pageant_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Slug</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->slug }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Database Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->database_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $tenant->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $tenant->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $tenant->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($tenant->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Owner Information -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-medium mb-4">Owner Information</h3>
                            @if($tenant->owner)
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->owner->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->owner->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Age</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->owner->age }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($tenant->owner->gender) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->owner->address }}</dd>
                                    </div>
                                </dl>
                            @else
                                <p class="text-sm text-gray-500">No owner information available.</p>
                            @endif
                        </div>
                    </div>

                    @if($tenant->status === 'pending')
                        <div class="mt-6 flex space-x-4">
                            <form action="{{ route('admin.tenants.approve', $tenant) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md"
                                        onclick="return confirm('Are you sure you want to approve this tenant?')">
                                    Approve Tenant
                                </button>
                            </form>

                            <form action="{{ route('admin.tenants.reject', $tenant) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md"
                                        onclick="return confirm('Are you sure you want to reject this tenant?')">
                                    Reject Tenant
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 