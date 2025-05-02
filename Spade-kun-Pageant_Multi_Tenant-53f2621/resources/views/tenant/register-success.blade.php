<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        <h2 class="text-xl font-semibold mb-4">{{ __('Registration Successful!') }}</h2>
        <p class="mb-2">{{ __('Thank you for registering your pageant.') }}</p>
        
        @if(session('tenant'))
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-4">
            <p class="font-medium text-gray-700">{{ __('Pageant Name') }}:</p>
            <p class="mb-2 text-gray-600">{{ session('tenant')['pageant_name'] }}</p>
            
            <p class="font-medium text-gray-700">{{ __('Pageant URL Slug') }}:</p>
            <p class="mb-2 text-gray-600">{{ session('tenant')['slug'] }}</p>
        </div>
        @endif
        
        <p class="mb-2">{{ __('Your registration is pending approval by our administrators.') }}</p>
        <p class="mb-2">{{ __('Once approved, you will be able to log in and access your pageant dashboard.') }}</p>
    </div>

    <div class="mt-4 flex justify-center">
        <a href="{{ route('tenant.login') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Return to Login') }}
        </a>
    </div>
</x-guest-layout> 