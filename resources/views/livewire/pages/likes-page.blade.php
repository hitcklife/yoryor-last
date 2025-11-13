<!-- Instagram-Style Likes Page -->
<div>
    <!-- Breadcrumb Navigation -->
    <x-navigation.breadcrumb />
    
    <div class="h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 flex transition-colors duration-300">

        <!-- Left Navigation Sidebar - Full Mode -->
        <x-navigation-sidebar mode="full" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 p-6 overflow-y-auto">
                <div class="max-w-6xl mx-auto">

                    <!-- Header Section -->
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden mb-6 transition-colors duration-300">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700 transition-colors duration-300">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center transition-colors duration-300">
                                    <div class="w-8 h-8 mr-3 bg-gradient-to-r from-pink-500 to-purple-500 rounded-full flex items-center justify-center">
                                        <x-lucide-heart class="w-5 h-5 text-white" />
                                    </div>
                                    Your Likes
                                </h2>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <!-- Likes Content -->
                        <div class="lg:col-span-8">
                            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-700 p-12 text-center transition-colors duration-300">
                                <div class="mx-auto w-32 h-32 bg-gradient-to-br from-pink-100 to-purple-100 dark:from-pink-900/20 dark:to-purple-900/20 rounded-full flex items-center justify-center mb-6">
                                    <x-lucide-heart class="w-16 h-16 text-pink-500 dark:text-pink-400" />
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Your Likes</h3>
                                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto mb-8">
                                    People you've liked will appear here. Start discovering to see your likes!
                                </p>
                                <a href="{{ route('discover') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold rounded-full hover:from-pink-600 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                                    <x-lucide-search class="w-5 h-5 mr-2" />
                                    Start Discovering
                                </a>
                            </div>
                        </div>

                        <!-- Right Sidebar -->
                        <div class="lg:col-span-4">
                            <div class="sticky top-8 space-y-6">
                                <livewire:dashboard.activity-sidebar />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
