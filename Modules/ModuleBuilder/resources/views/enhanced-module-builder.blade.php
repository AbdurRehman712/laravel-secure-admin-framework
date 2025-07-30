<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">⚡</span>
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Enhanced Module Builder</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Create sophisticated Laravel modules with relationships, advanced field types, and complete Filament integration.
                        Perfect for building complex applications with proper architecture.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form wire:submit="generateModule">
                {{ $this->form }}

                <div class="mt-6 flex gap-3 flex-wrap">
                    <x-filament::button
                        type="submit"
                        color="primary"
                        size="lg"
                        icon="heroicon-o-cog-6-tooth"
                    >
                        Generate Enhanced Module
                    </x-filament::button>

                    @if($this->hasProjectsWithSchemas())
                        <x-filament::button
                            type="button"
                            color="info"
                            wire:click="selectProjectAndFill"
                            icon="heroicon-o-document-text"
                        >
                            Auto Fill from Project
                        </x-filament::button>
                    @endif

                    <x-filament::button
                        type="button"
                        color="warning"
                        wire:click="downloadExcelTemplate"
                        icon="heroicon-o-arrow-down-tray"
                    >
                        Download Excel Template
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="orange"
                        wire:click="downloadCSVTemplates"
                        icon="heroicon-o-document-arrow-down"
                    >
                        Download CSV Templates
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="secondary"
                        wire:click="showExcelImportModal"
                        icon="heroicon-o-arrow-up-tray"
                    >
                        Import Excel/CSV
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="info"
                        wire:click="loadFromERD"
                        icon="heroicon-o-squares-2x2"
                    >
                        Load from ERD Designer
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="success"
                        wire:click="fillDemoData"
                        icon="heroicon-o-sparkles"
                    >
                        Fill Demo Data (Shop)
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        color="gray"
                        wire:click="clearForm"
                    >
                        Clear Form
                    </x-filament::button>
                </div>
            </form>
        </div>

        <!-- Project Selection Modal -->
        @if($showProjectSelection)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Select Project to Auto-Fill
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Choose a project with AI-generated database schemas to auto-populate the module builder form.
                                        </p>
                                    </div>
                                    <div class="mt-4 space-y-2">
                                        @foreach($availableProjectsForSelection as $projectId => $projectName)
                                            <button
                                                wire:click="fillFromSelectedProject({{ $projectId }})"
                                                class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-blue-50 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors duration-200"
                                            >
                                                <div class="font-medium text-gray-900">{{ $projectName }}</div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                wire:click="closeProjectSelection"
                                type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Excel Import Modal -->
        @if($showExcelImport)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Import Database Schema from Excel or CSV</h3>
                            <button wire:click="hideExcelImportModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="font-medium text-blue-900 mb-2">📋 Instructions</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h5 class="font-medium text-blue-900 mb-1">📊 Excel Format:</h5>
                                        <ol class="text-sm text-blue-800 space-y-1">
                                            <li>1. Download Excel template</li>
                                            <li>2. Fill in all sheets (Tables, Fields, Seeder)</li>
                                            <li>3. Upload the completed Excel file</li>
                                        </ol>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-blue-900 mb-1">📄 CSV Format:</h5>
                                        <ol class="text-sm text-blue-800 space-y-1">
                                            <li>1. Download CSV templates (ZIP file)</li>
                                            <li>2. Fill in the CSV files</li>
                                            <li>3. Upload any single CSV file</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="mt-4">
                                        <label for="excel-upload" class="cursor-pointer">
                                            <span class="mt-2 block text-sm font-medium text-gray-900">
                                                Upload Excel or CSV File
                                            </span>
                                            <input
                                                id="excel-upload"
                                                type="file"
                                                wire:model.live="excelFile"
                                                accept=".xlsx,.xls,.csv"
                                                class="sr-only"
                                            >
                                        </label>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Excel (.xlsx, .xls) or CSV (.csv) files
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading State -->
                            <div wire:loading wire:target="excelFile" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-center">
                                    <svg class="animate-spin w-5 h-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm text-blue-800">Uploading file...</span>
                                </div>
                            </div>

                            <!-- File Selected State -->
                            @if($excelFile)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3" wire:loading.remove wire:target="excelFile">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm text-green-800">
                                            File selected: {{ is_object($excelFile) ? $excelFile->getClientOriginalName() : $excelFile }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                            <button
                                wire:click="hideExcelImportModal"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                wire:click="importFromExcel"
                                wire:loading.attr="disabled"
                                wire:target="importFromExcel"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
                                @if(!$excelFile) disabled @endif
                            >
                                <span wire:loading.remove wire:target="importFromExcel">Import Schema</span>
                                <span wire:loading wire:target="importFromExcel" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Importing...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
