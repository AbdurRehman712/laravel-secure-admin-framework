@foreach($files as $file)
    <div class="ml-{{ $level * 4 }}">
        @if($file['type'] === 'directory')
            <!-- Directory -->
            <div
                x-data="{ expanded: true }"
                class="select-none"
            >
                <div
                    @click="expanded = !expanded"
                    class="flex items-center py-2 px-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md cursor-pointer transition-all duration-200 font-medium"
                >
                    <x-heroicon-o-chevron-right
                        x-show="!expanded"
                        class="w-3 h-3 mr-1 text-gray-400"
                    />
                    <x-heroicon-o-chevron-down
                        x-show="expanded"
                        class="w-3 h-3 mr-1 text-gray-400"
                    />
                    <x-heroicon-o-folder class="w-4 h-4 mr-2 text-blue-500" />
                    <span>{{ $file['name'] }}</span>
                </div>

                <!-- Recursively render subdirectories -->
                @if(isset($file['children']) && count($file['children']) > 0)
                    <div x-show="expanded" x-transition>
                        @include('cmseditor::filament.resources.theme-resource.partials.file-tree', [
                            'files' => $file['children'],
                            'path' => $file['path'],
                            'level' => $level + 1
                        ])
                    </div>
                @endif
            </div>
        @else
            <!-- File -->
            <div
                wire:click="loadFile('{{ $file['path'] }}')"
                class="file-tree-item flex items-center py-2 px-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md cursor-pointer transition-all duration-200 {{ $activeFile === $file['path'] ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-3 border-blue-500 font-medium' : '' }}"
            >
                @php
                    $extension = $file['extension'] ?? pathinfo($file['name'], PATHINFO_EXTENSION);
                    $iconClass = match($extension) {
                        'php' => 'text-purple-500',
                        'js' => 'text-yellow-500',
                        'css' => 'text-blue-500',
                        'html', 'htm' => 'text-orange-500',
                        'json' => 'text-green-500',
                        'md' => 'text-gray-500',
                        'yml', 'yaml' => 'text-red-500',
                        'twig' => 'text-green-600',
                        default => 'text-gray-400'
                    };
                @endphp

                <x-heroicon-o-document-text class="w-4 h-4 mr-2 {{ $iconClass }}" />
                <span>{{ $file['name'] }}</span>
            </div>
        @endif
    </div>
@endforeach
