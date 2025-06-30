<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">🚀</span>
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Simple Module Builder</h1>
                    <p class="text-gray-600 dark:text-gray-400">Create complete modules with CRUD operations in seconds</p>
                </div>
            </div>
        </div>

        <!-- Quick Start Guide -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">🚀 Quick Start</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="flex items-start space-x-2">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                    <div>
                        <p class="font-medium text-blue-900 dark:text-blue-100">Enter Module Name</p>
                        <p class="text-blue-700 dark:text-blue-300">e.g., "Blog", "Shop", "Users"</p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                    <div>
                        <p class="font-medium text-blue-900 dark:text-blue-100">Define Tables & Fields</p>
                        <p class="text-blue-700 dark:text-blue-300">Add your data structure</p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                    <div>
                        <p class="font-medium text-blue-900 dark:text-blue-100">Click Generate</p>
                        <p class="text-blue-700 dark:text-blue-300">Module appears in sidebar instantly</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{ $this->form }}
        </div>

        <!-- Features -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">✨ What Gets Generated</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-green-100 text-green-600 rounded mx-auto mb-2 flex items-center justify-center">
                        <span class="text-lg font-bold">DB</span>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Database</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Migrations & Models</p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded mx-auto mb-2 flex items-center justify-center">
                        <span class="text-lg font-bold">UI</span>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Admin Interface</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Filament Resources</p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-purple-100 text-purple-600 rounded mx-auto mb-2 flex items-center justify-center">
                        <span class="text-lg font-bold">⚙</span>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Auto-Registration</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Service Providers</p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-green-100 text-green-600 rounded mx-auto mb-2 flex items-center justify-center">
                        <span class="text-lg font-bold">✓</span>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Ready to Use</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">No manual setup</p>
                </div>
            </div>
        </div>

        <!-- Examples -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">💡 Example Modules</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">📝 Blog Module</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Posts, Categories, Tags</p>
                    <div class="text-xs text-gray-500 dark:text-gray-500">
                        <p>• Posts: title, content, status</p>
                        <p>• Categories: name, description</p>
                    </div>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">🛒 Shop Module</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Products, Orders, Customers</p>
                    <div class="text-xs text-gray-500 dark:text-gray-500">
                        <p>• Products: name, price, stock</p>
                        <p>• Orders: customer, total, status</p>
                    </div>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">👥 CRM Module</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Contacts, Companies, Deals</p>
                    <div class="text-xs text-gray-500 dark:text-gray-500">
                        <p>• Contacts: name, email, phone</p>
                        <p>• Companies: name, industry</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
