<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pageant Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Welcome to your Pageant Dashboard') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Contestants') }}</h4>
                            <p class="text-sm mb-4">{{ __('Manage contestants participating in your pageant.') }}</p>
                            <a href="#" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('View Contestants') }}
                            </a>
                        </div>
                        
                        <div class="bg-green-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Events') }}</h4>
                            <p class="text-sm mb-4">{{ __('Schedule and manage pageant events.') }}</p>
                            <a href="#" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('View Events') }}
                            </a>
                        </div>
                        
                        <div class="bg-purple-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Judges') }}</h4>
                            <p class="text-sm mb-4">{{ __('Manage judges and scoring for your pageant.') }}</p>
                            <a href="#" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('View Judges') }}
                            </a>
                        </div>
                        
                        <div class="bg-yellow-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Scoring Criteria') }}</h4>
                            <p class="text-sm mb-4">{{ __('Define the criteria and weights for judging.') }}</p>
                            <a href="#" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Manage Criteria') }}
                            </a>
                        </div>
                        
                        <div class="bg-red-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Scores') }}</h4>
                            <p class="text-sm mb-4">{{ __('View and manage scores from judges.') }}</p>
                            <a href="#" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('View Scores') }}
                            </a>
                        </div>
                        
                        <div class="bg-indigo-100 p-4 rounded-lg shadow">
                            <h4 class="font-semibold mb-2">{{ __('Reports') }}</h4>
                            <p class="text-sm mb-4">{{ __('Generate reports and insights for your pageant.') }}</p>
                            <a href="#" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('View Reports') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 