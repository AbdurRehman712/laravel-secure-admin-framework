<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Welcome to AI Development Platform</h1>
                    <p class="mt-2 text-primary-100">
                        Transform your development workflow with role-specific AI workspaces and automated code generation.
                    </p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-primary-200" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        @php
            $stats = $this->getStats();
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Projects</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_projects'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Workspace Content</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_workspace_content'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Generated Modules</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['generated_modules'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Modules</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_modules'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Content by Role -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Content by Role</h3>
                @php
                    $contentByRole = $this->getContentByRole();
                    $roleLabels = $this->getRoleLabels();
                @endphp
                <div class="space-y-3">
                    @foreach($contentByRole as $role => $count)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $roleLabels[$role] ?? ucwords(str_replace('_', ' ', $role)) }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Content by Type -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Content by Type</h3>
                @php
                    $contentByType = $this->getContentByType();
                    $typeLabels = $this->getContentTypeLabels();
                @endphp
                <div class="space-y-3">
                    @foreach($contentByType as $type => $count)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $typeLabels[$type] ?? ucwords(str_replace('_', ' ', $type)) }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Projects -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Projects</h3>
            </div>
            <div class="p-6">
                @php
                    $recentProjects = $this->getRecentProjects();
                @endphp
                @if(empty($recentProjects))
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No projects yet</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first AI development project.</p>
                        <div class="mt-6">
                            <a href="{{ route('filament.admin.resources.projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Project
                            </a>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($recentProjects as $project)
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                        {{ $project['name'] }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $project['description'] }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($project['status'] === 'planning') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @elseif($project['status'] === 'development') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                            @elseif($project['status'] === 'review') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                            @elseif($project['status'] === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                            @endif">
                                            {{ ucfirst($project['status']) }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ count($project['team_members']) }} team members
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('filament.admin.pages.project-workspace', ['project' => $project['id']]) }}" 
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Open Workspace
                                    </a>
                                    <a href="{{ route('filament.admin.resources.projects.view', ['record' => $project['id']]) }}" 
                                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('filament.admin.resources.projects.create') }}" 
                   class="flex items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary-500 dark:hover:border-primary-400 transition-colors group">
                    <div class="flex items-center">
                        <div class="p-2 bg-primary-100 dark:bg-primary-900 rounded-lg group-hover:bg-primary-200 dark:group-hover:bg-primary-800 transition-colors">
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Create New Project</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Start a new AI development project</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('filament.admin.pages.enhanced-module-builder') }}" 
                   class="flex items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary-500 dark:hover:border-primary-400 transition-colors group">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Module Builder</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Create Laravel modules manually</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('filament.admin.resources.projects.index') }}" 
                   class="flex items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary-500 dark:hover:border-primary-400 transition-colors group">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition-colors">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">View All Projects</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Browse and manage projects</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
