<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Project Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $project->name }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        {{ $project->description }}
                    </p>
                    <div class="flex items-center gap-4 mt-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($project->status === 'planning') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @elseif($project->status === 'development') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                            @elseif($project->status === 'review') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                            @elseif($project->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                            @endif">
                            {{ ucfirst($project->status) }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Owner: {{ $project->creator->name }}
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Current Role</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $this->getRoleDisplayName($currentRole) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Switcher -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Choose Your Role
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->getAvailableRoles() as $role => $label)
                    <button 
                        wire:click="switchRole('{{ $role }}')"
                        class="p-4 rounded-lg border-2 transition-all duration-200 text-left
                            @if($currentRole === $role) 
                                border-primary-500 bg-primary-50 dark:bg-primary-900/20 
                            @else 
                                border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 
                            @endif">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ $label }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ $this->getRoleDescription($role) }}
                                </p>
                            </div>
                            @if(isset($roleProgress[$role]) && $roleProgress[$role]['completed'])
                                <div class="ml-2">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Current Role Workspace -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $this->getRoleDisplayName($currentRole) }} Workspace
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            {{ $this->getRoleDescription($currentRole) }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Content
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- AI Content Creator Component -->
                @livewire('ai-content-creator', ['project' => $project, 'role' => $currentRole], $currentRole)

                <!-- Existing Content List -->
                <div class="mt-8">
                    @livewire('workspace-content-list', ['project' => $project, 'role' => $currentRole], "{$currentRole}-content")
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
