<x-filament-panels::page>
    <div class="space-y-6">
        @if(empty($themes))
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a4 4 0 004-4V5z"></path>
                    </svg>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No themes</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new theme.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($themes as $theme)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 bg-primary-500 rounded-lg flex items-center justify-center">
                                        <x-heroicon-o-swatch class="h-6 w-6 text-white" />
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ $theme['name'] }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $theme['code'] }}
                                    </p>
                                </div>
                            </div>
                            
                            @if(!empty($theme['description']))
                                <div class="mt-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ Str::limit($theme['description'], 100) }}
                                    </p>
                                </div>
                            @endif
                            
                            <div class="mt-4 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <x-heroicon-o-user class="h-4 w-4 mr-1" />
                                {{ $theme['author'] ?? 'Unknown' }}
                            </div>
                            
                            <div class="mt-6 flex space-x-3">
                                <a href="{{ \Modules\CmsEditor\app\Filament\Resources\ThemeResource::getUrl('browse', ['record' => $theme['code']]) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <x-heroicon-o-folder-open class="h-4 w-4 mr-1" />
                                    Browse
                                </a>
                                
                                <a href="{{ \Modules\CmsEditor\app\Filament\Resources\ThemeResource::getUrl('edit', ['record' => $theme['code']]) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <x-heroicon-o-pencil class="h-4 w-4 mr-1" />
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
