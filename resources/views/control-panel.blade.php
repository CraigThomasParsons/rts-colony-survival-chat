<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Control Panel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-8">
                <p class="text-center text-gray-600 dark:text-gray-300 mb-8">
                    Quick links to developer tooling.
                </p>

                <div class="flex flex-col items-center gap-4">
                    <form action="{{ route('map.index') }}" method="GET" class="w-full sm:w-2/3">
                        <button
                            type="submit"
                            class="w-full text-center inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Map Generator
                        </button>
                    </form>

                    <form action="{{ route('profile') }}" method="GET" class="w-full sm:w-2/3">
                        <button
                            type="submit"
                            class="w-full text-center inline-flex items-center justify-center px-6 py-3 border border-indigo-600 text-base font-medium rounded-md text-indigo-600 hover:bg-indigo-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Change My Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
