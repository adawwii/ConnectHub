@if (session('success'))
    <div x-data="{ show: true }" 
    x-show="show" 
    x-init="setTimeout(() => show = false, 4000)"
    x-transition class="fixed top-0 left-1/2 transform -translate-x-1/2 text-white px-48 py-3">
        <div  class="bg-green-100 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" 
    x-show="show" 
    x-init="setTimeout(() => show = false, 4000)"
    x-transition class="fixed top-0 left-1/2 transform -translate-x-1/2 text-white px-48 py-3">
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    </div>
@endif

@if (session('info'))
    <div x-data="{ show: true }" 
    x-show="show" 
    x-init="setTimeout(() => show = false, 4000)"
    x-transition class="fixed top-0 left-1/2 transform -translate-x-1/2 text-white px-48 py-3">
        <div class="bg-yellow-100 text-yellow-700 px-4 py-3 rounded-lg">
            {{ session('info') }}
        </div>
    </div>
@endif
