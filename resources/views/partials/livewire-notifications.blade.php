@if(session('success'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg"
         role="alert">
        <strong class="font-bold">Success! </strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <button class="absolute top-0 right-0 mt-2 mr-2 text-green-600 hover:text-green-800"
                @click="show = false">
            &times;
        </button>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg"
         role="alert">
        <strong class="font-bold">Error! </strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <button class="absolute top-0 right-0 mt-2 mr-2 text-red-600 hover:text-red-800"
                @click="show = false">
            &times;
        </button>
    </div>
@endif

@if(session('warning'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded shadow-lg"
         role="alert">
        <strong class="font-bold">Warning! </strong>
        <span class="block sm:inline">{{ session('warning') }}</span>
        <button class="absolute top-0 right-0 mt-2 mr-2 text-yellow-600 hover:text-yellow-800"
                @click="show = false">
            &times;
        </button>
    </div>
@endif
