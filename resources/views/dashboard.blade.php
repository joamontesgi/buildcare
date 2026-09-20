<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Dashboard — Schedule
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bc-card">
                <div class="bc-card-header">
                    <h3 class="text-xl font-bold">Welcome to BuildCare</h3>
                    <p class="text-sky-100 text-sm mt-1">Daily schedule and work-order management</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-sky-500 uppercase">Area</p>
                            <p class="text-lg font-bold text-slate-800 mt-1">Repairs</p>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-emerald-600 uppercase">Schedule</p>
                            <p class="text-lg font-bold text-slate-800 mt-1">Daily jobs</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-500 uppercase">Buildings</p>
                            <p class="text-lg font-bold text-slate-800 mt-1">In catalog</p>
                        </div>
                    </div>
                    <a href="{{ route('schedule.export.pdf') }}" target="_blank" class="bc-btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Download schedule PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
