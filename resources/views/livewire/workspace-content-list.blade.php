<div>
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
        Your Content
    </h3>
    
    @if($content->isEmpty())
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No content yet</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Start by using the AI prompt templates above and pasting the responses.
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($content as $item)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 dark:text-white">
                                {{ $item->title }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ ucwords(str_replace('_', ' ', $item->content_type)) }}
                            </p>
                            
                            <!-- Content Preview -->
                            @if($item->parsed_data && is_array($item->parsed_data))
                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    @if(isset($item->parsed_data['stories']))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ count($item->parsed_data['stories']) }} stories
                                        </span>
                                    @elseif(isset($item->parsed_data['criteria']))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            {{ count($item->parsed_data['criteria']) }} criteria
                                        </span>
                                    @elseif(isset($item->parsed_data['wireframes']))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            {{ count($item->parsed_data['wireframes']) }} wireframes
                                        </span>
                                    @elseif(isset($item->parsed_data['tables']))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                            {{ count($item->parsed_data['tables']) }} tables
                                        </span>
                                    @elseif(isset($item->parsed_data['endpoints']))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                            {{ count($item->parsed_data['endpoints']) }} endpoints
                                        </span>
                                    @elseif(isset($item->parsed_data['components']))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200">
                                            {{ count($item->parsed_data['components']) }} components
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            Parsed content available
                                        </span>
                                    @endif
                                </div>
                            @endif
                            
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Created {{ $item->created_at->diffForHumans() }}
                                by {{ $item->admin->name }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($item->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                @elseif($item->status === 'review') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                @elseif($item->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                @endif">
                                {{ ucfirst($item->status) }}
                            </span>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-1">
                                <button 
                                    title="View Details"
                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button 
                                    title="Edit"
                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <button 
                                    title="Generate Code"
                                    class="p-1 text-primary-500 hover:text-primary-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Expandable Content Preview -->
                    @if($item->parsed_data && is_array($item->parsed_data))
                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600" x-data="{ expanded: false }">
                            <button 
                                @click="expanded = !expanded"
                                class="flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                                <span x-text="expanded ? 'Hide' : 'Show'"></span>
                                <span class="ml-1">parsed content</span>
                                <svg class="w-4 h-4 ml-1 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <div x-show="expanded" x-collapse class="mt-2">
                                <div class="bg-gray-50 dark:bg-gray-800 rounded p-3 text-sm">
                                    <pre class="whitespace-pre-wrap text-gray-700 dark:text-gray-300">{{ json_encode($item->parsed_data, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
