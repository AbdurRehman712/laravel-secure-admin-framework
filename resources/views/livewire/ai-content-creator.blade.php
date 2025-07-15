<div>
    <!-- AI Prompt Templates -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            AI Prompt Templates
        </h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($promptTemplates as $templateKey => $template)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">
                        {{ $template['title'] }}
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Prompt Template:
                            </label>
                            <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded text-sm font-mono text-gray-800 dark:text-gray-200">
                                {{ $template['prompt'] }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Example:
                            </label>
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded text-sm text-blue-800 dark:text-blue-200">
                                {{ $template['example'] }}
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                wire:click="copyPrompt"
                                class="flex-1 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm">
                                Copy Prompt
                            </button>
                            <button 
                                wire:click="openForm('{{ $templateKey }}', {{ json_encode($template) }})"
                                class="flex-1 px-3 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors text-sm">
                                Use Template
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Content Creation Modal -->
    @if($showForm)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button 
                            wire:click="closeForm"
                            type="button" 
                            class="bg-white dark:bg-gray-800 rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Create {{ $this->getContentTypeLabel() }}
                            </h3>
                            
                            <div class="mt-6 space-y-6">
                                <!-- Template Info -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                    <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">
                                        {{ $promptTemplate['title'] ?? '' }}
                                    </h4>
                                    <p class="text-sm text-blue-800 dark:text-blue-200 mb-3">
                                        Copy this prompt to your AI tool (ChatGPT, Claude, etc.), get the response, and paste it below:
                                    </p>
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded border text-sm font-mono">
                                        {{ $promptTemplate['prompt'] ?? '' }}
                                    </div>
                                    <button 
                                        wire:click="copyPrompt"
                                        class="mt-2 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                        📋 Copy prompt to clipboard
                                    </button>
                                </div>

                                <!-- Form -->
                                <form wire:submit="saveContent" class="space-y-4">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Content Title
                                        </label>
                                        <input 
                                            type="text" 
                                            id="title"
                                            wire:model="title"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                            placeholder="Enter a descriptive title for this content">
                                        @error('title') 
                                            <span class="text-red-500 text-sm">{{ $message }}</span> 
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="aiResponse" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            AI Response
                                        </label>
                                        <textarea 
                                            id="aiResponse"
                                            wire:model="aiResponse"
                                            rows="12" 
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                            placeholder="Paste your AI tool response here..."></textarea>
                                        @error('aiResponse') 
                                            <span class="text-red-500 text-sm">{{ $message }}</span> 
                                        @enderror
                                    </div>

                                    <div class="flex justify-end space-x-3 pt-4">
                                        <button 
                                            type="button"
                                            wire:click="closeForm"
                                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                            Cancel
                                        </button>
                                        <button 
                                            type="submit"
                                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                            Save Content
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Copy to Clipboard Script -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('copy-to-clipboard', (event) => {
                navigator.clipboard.writeText(event.text).then(() => {
                    console.log('Text copied to clipboard');
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                });
            });
        });
    </script>
</div>
