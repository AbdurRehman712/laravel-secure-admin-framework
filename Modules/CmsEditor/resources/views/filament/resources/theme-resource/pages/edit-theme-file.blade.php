<x-filament::page>

    <div class="flex h-[calc(100vh-120px)] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <!-- File Tree Sidebar -->
        <div class="w-64 bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
            <div class="p-3">
                <div class="mb-3 pb-2 border-b border-gray-200 dark:border-gray-600">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        Theme: {{ $theme }}
                    </h3>
                </div>

                <div class="space-y-1">
                    @if(count($themeStructure) > 0)
                        @include('cmseditor::filament.resources.theme-resource.partials.file-tree', [
                            'files' => $themeStructure,
                            'path' => '',
                            'level' => 0
                        ])
                    @else
                        <p class="text-sm text-gray-500">No files found</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Editor Area -->
        <div class="flex-1 flex flex-col">
            <!-- File Tabs -->
            @if(count($openFiles) > 0)
                <div class="flex bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    @foreach($openFiles as $openFile)
                        <div class="flex items-center px-4 py-2 border-r border-gray-200 dark:border-gray-700 {{ $activeFile === $openFile ? 'bg-white dark:bg-gray-900 border-b-2 border-blue-500' : 'hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            @php
                                $extension = pathinfo($openFile, PATHINFO_EXTENSION);
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
                            <button
                                wire:click="loadFile('{{ $openFile }}')"
                                class="text-sm {{ $activeFile === $openFile ? 'text-gray-900 dark:text-gray-100 font-medium' : 'text-gray-600 dark:text-gray-400' }}"
                            >
                                {{ basename($openFile) }}
                            </button>
                            <button
                                wire:click="closeFile('{{ $openFile }}')"
                                class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Editor Content -->
            <div class="flex-1 flex flex-col">
                @if($filePath)
                    <!-- Editor Header with Markup/Code tabs -->
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2">
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Editing: <span class="font-mono text-blue-600 dark:text-blue-400">{{ $filePath }}</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button
                                    wire:click="setActiveTab('markup')"
                                    class="px-3 py-1 text-xs font-medium rounded {{ $activeTab === 'markup' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 border-b-2 border-blue-500' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                >
                                    <x-heroicon-o-code-bracket class="w-4 h-4 inline mr-1" />
                                    Markup
                                </button>
                                <button
                                    wire:click="setActiveTab('code')"
                                    class="px-3 py-1 text-xs font-medium rounded {{ $activeTab === 'code' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 border-b-2 border-blue-500' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                >
                                    <x-heroicon-o-eye class="w-4 h-4 inline mr-1" />
                                    Code
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-medium text-gray-500 bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded">
                                {{ strtoupper($fileExtension) }}
                            </span>
                        </div>
                    </div>

                    <!-- Editor Content -->
                    <div class="flex-1 overflow-hidden">
                        @if($activeTab === 'markup' && $this->isPageFile())
                            <!-- Page Metadata Form -->
                            <div class="flex h-full">
                                <!-- Metadata Panel -->
                                <div class="w-80 bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 p-4 overflow-y-auto">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Page Settings</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                                            <input type="text" wire:model.live="pageTitle" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" placeholder="Page Title">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                                            <input type="text" wire:model.live="pageUrl" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" placeholder="/page-url">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Layout</label>
                                            <select wire:model.live="pageLayout" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                <option value="">Select layout</option>
                                                <option value="default">default</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                            <textarea wire:model.live="pageDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" placeholder="Page description"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hidden</label>
                                            <select wire:model.live="pageHidden" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- Code Editor -->
                                <div class="flex-1 bg-gray-900 text-gray-100">
                                    {{ $this->form }}
                                </div>
                            </div>
                        @else
                            <!-- Full Code Editor -->
                            <div class="h-full bg-gray-900 text-gray-100">
                                {{ $this->form }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <x-heroicon-o-document-text class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                No File Selected
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Select a file from the tree to start editing
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Enhanced Code Editor Styles -->
    <style>
        .code-editor-container {
            position: relative;
            background: #1f2937;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .code-editor-textarea {
            background: #1f2937 !important;
            color: #f9fafb !important;
            border: none !important;
            outline: none !important;
            resize: none !important;
            font-family: 'Fira Code', 'Monaco', 'Consolas', 'SF Mono', monospace !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            padding: 16px !important;
            tab-size: 4;
            white-space: pre;
            overflow-wrap: normal;
            overflow-x: auto;
        }

        /* Force white text color for all textarea content */
        textarea[wire\:model="content"] {
            background: #1f2937 !important;
            color: #ffffff !important;
        }

        /* Override any Filament styles */
        .fi-fo-textarea textarea {
            background: #1f2937 !important;
            color: #ffffff !important;
        }

        .code-editor-textarea:focus {
            box-shadow: none !important;
            border-color: transparent !important;
        }

        /* File tree improvements */
        .file-tree-item {
            transition: all 0.2s ease;
        }

        .file-tree-item:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .file-tree-item.active {
            background-color: rgba(59, 130, 246, 0.2);
            border-left: 3px solid #3b82f6;
        }
    </style>

    <!-- Keyboard Shortcuts and Enhanced Editor -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Apply code editor styling
            const textareas = document.querySelectorAll('textarea[wire\\:model="content"]');
            textareas.forEach(textarea => {
                textarea.classList.add('code-editor-textarea');
                textarea.setAttribute('spellcheck', 'false');
                textarea.setAttribute('autocomplete', 'off');
                textarea.setAttribute('autocorrect', 'off');
                textarea.setAttribute('autocapitalize', 'off');

                // Add tab support
                textarea.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab') {
                        e.preventDefault();
                        const start = this.selectionStart;
                        const end = this.selectionEnd;
                        this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                        this.selectionStart = this.selectionEnd = start + 4;
                        this.dispatchEvent(new Event('input'));
                    }
                });
            });
        });

        document.addEventListener('keydown', function(e) {
            // Ctrl+S or Cmd+S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                @this.call('saveFile');
            }
        });
    </script>
</x-filament::page>
