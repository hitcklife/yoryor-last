<!-- Instagram-Style Dating Dashboard -->
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

                    <!-- Stories Section -->
                    <div class="mb-8">
                        <livewire:dashboard.stories-bar />
                    </div>

                    <!-- Main Feed Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                        <!-- Main Feed - Center Column (Instagram-like) -->
                        <div class="lg:col-span-8">

                            <!-- Discover Section -->
                            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden mb-6 transition-colors duration-300">
                                <!-- Header -->
                                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700 transition-colors duration-300">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center transition-colors duration-300">
                                            <div class="w-8 h-8 mr-3 bg-gradient-to-r from-pink-500 to-purple-500 rounded-full flex items-center justify-center">
                                                <x-lucide-heart class="w-5 h-5 text-white" />
                                            </div>
                                            {{ __('dashboard.discover') }}
                                        </h2>

                                        <!-- Filter Options -->
                                        <div class="flex items-center space-x-2">
                                            <button @click="$dispatch('open-filters')" class="p-2 text-gray-500 dark:text-zinc-400 hover:text-gray-700 dark:hover:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-600 rounded-lg transition-colors duration-300">
                                                <x-lucide-filter class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Discovery Grid Content -->
                                <div class="p-6">
                                    <livewire:dashboard.discovery-grid />
                                </div>
                            </div>

                            <!-- Recent Activity Feed -->
                            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden transition-colors duration-300">
                                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700 transition-colors duration-300">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white transition-colors duration-300">Recent Activity</h3>
                                </div>
                                <div class="divide-y divide-gray-200 dark:divide-zinc-700">
                                    <!-- Activity items would go here -->
                                    <div class="p-6 text-center text-gray-500 dark:text-zinc-400 transition-colors duration-300">
                                        <x-lucide-zap class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-zinc-600" />
                                        <p>No recent activity</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Sidebar - Activity & Stats -->
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

    <!-- Profile Modal -->
    <livewire:dashboard.profile-modal />
</div>
