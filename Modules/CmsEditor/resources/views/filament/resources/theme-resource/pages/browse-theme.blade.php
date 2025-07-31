<x-filament::page>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="text-sm breadcrumbs">
                <ul>
                    <li>
                        <a wire:click="navigateTo('')" class="text-primary-600 hover:underline cursor-pointer">
                            {{ $theme }}
                        </a>
                    </li>
                    @if ($currentPath)
                        @php
                            $paths = explode('/', $currentPath);
                            $buildPath = '';
                        @endphp
                        @foreach ($paths as $index => $segment)
                            @php
                                $buildPath .= ($index > 0 ? '/' : '') . $segment;
                            @endphp
                            <li>
                                <a wire:click="navigateTo('{{ $buildPath }}')" class="text-primary-600 hover:underline cursor-pointer">
                                    {{ $segment }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
            
            <div>
                <x-filament::button
                    icon="heroicon-o-plus"
                    wire:click="$dispatch('open-modal', { id: 'create-file' })"
                >
                    Create File
                </x-filament::button>
                
                <x-filament::button
                    icon="heroicon-o-folder-plus"
                    wire:click="$dispatch('open-modal', { id: 'create-folder' })"
                >
                    Create Folder
                </x-filament::button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="p-3">Name</th>
                        <th class="p-3">Type</th>
                        <th class="p-3">Size</th>
                        <th class="p-3">Modified</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($currentPath)
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="p-3">
                                <a wire:click="navigateTo('{{ dirname($currentPath) }}')" class="text-primary-600 hover:underline cursor-pointer flex items-center">
                                    <span class="mr-1">
                                        <x-heroicon-o-arrow-up class="w-4 h-4" />
                                    </span>
                                    ..
                                </a>
                            </td>
                            <td class="p-3">Directory</td>
                            <td class="p-3">-</td>
                            <td class="p-3">-</td>
                            <td class="p-3">-</td>
                        </tr>
                    @endif
                    
                    @forelse ($files as $file)
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="p-3">
                                @if ($file['type'] === 'dir')
                                    <a wire:click="navigateTo('{{ $currentPath ? $currentPath . '/' . $file['name'] : $file['name'] }}')" class="text-primary-600 hover:underline cursor-pointer flex items-center">
                                        <span class="mr-1">
                                            <x-heroicon-o-folder class="w-4 h-4" />
                                        </span>
                                        {{ $file['name'] }}
                                    </a>
                                @else
                                    <div class="flex items-center">
                                        <span class="mr-1">
                                            <x-heroicon-o-document-text class="w-4 h-4" />
                                        </span>
                                        {{ $file['name'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-3">{{ ucfirst($file['type']) }}</td>
                            <td class="p-3">{{ $file['size'] ?? '-' }}</td>
                            <td class="p-3">{{ $file['modified'] ?? '-' }}</td>
                            <td class="p-3">
                                <div class="flex space-x-2">
                                    @if ($file['type'] === 'file')
                                        <x-filament::icon-button
                                            icon="heroicon-o-pencil"
                                            wire:click="editFile('{{ $file['name'] }}')"
                                            label="Edit"
                                        />
                                    @endif
                                    <x-filament::icon-button
                                        icon="heroicon-o-trash"
                                        color="danger"
                                        wire:click="deleteItem('{{ $file['name'] }}', '{{ $file['type'] }}')"
                                        label="Delete"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-3 text-center text-gray-500">
                                No files or folders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
