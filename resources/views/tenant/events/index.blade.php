<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Events') }}
            </h2>
            <a href="{{ route('tenant.events.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Create New Event
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($events as $event)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $event->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $event->location }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $event->start_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $event->end_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('tenant.events.show', $event) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <a href="{{ route('tenant.events.edit', $event) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('tenant.events.destroy', $event) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this event?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center">No events found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 