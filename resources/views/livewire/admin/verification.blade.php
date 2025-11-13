<div>
    <!-- Verification Management Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Verification Management</h2>
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <span>Total: {{ $verifications->total() }} requests</span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-6">
            <!-- Total Requests -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($totalRequests) }}</div>
                            <div class="text-sm text-gray-500">Total Requests</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($pendingRequests) }}</div>
                            <div class="text-sm text-gray-500">Pending</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Requests -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($approvedRequests) }}</div>
                            <div class="text-sm text-gray-500">Approved</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rejected Requests -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($rejectedRequests) }}</div>
                            <div class="text-sm text-gray-500">Rejected</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Needs Review -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-orange-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($needsReviewRequests) }}</div>
                            <div class="text-sm text-gray-500">Needs Review</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Requests -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($requestsToday) }}</div>
                            <div class="text-sm text-gray-500">Today</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Types Breakdown -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Verification Types</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                @foreach($verificationTypes as $type)
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-xl font-bold text-gray-900">{{ $type->count }}</div>
                        <div class="text-xs text-gray-600 mt-1">
                            {{ $availableTypes[$type->verification_type] ?? ucfirst(str_replace('_', ' ', $type->verification_type)) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Verifications</label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.live.debounce.300ms="search"
                               placeholder="Search by user name, email, or type..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="needs_review">Needs Review</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select wire:model.live="typeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <option value="">All Types</option>
                        @foreach($availableTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Show</label>
                    <select wire:model.live="perPage" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                <button wire:click="clearFilters" class="text-sm text-gray-600 hover:text-gray-800 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    @if($showReviewModal && $selectedRequest)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900">
                            Review {{ $selectedRequest->type_display_name }} Request
                        </h3>
                        <button wire:click="closeReviewModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- User Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center">
                                <span class="text-white font-bold text-xl">
                                    {{ substr($selectedRequest->user->profile?->first_name ?? 'U', 0, 1) }}{{ substr($selectedRequest->user->profile?->last_name ?? 'U', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">
                                    {{ $selectedRequest->user->profile?->first_name . ' ' . $selectedRequest->user->profile?->last_name ?: 'No Name' }}
                                </h4>
                                <p class="text-sm text-gray-600">{{ $selectedRequest->user->email }}</p>
                                <p class="text-sm text-gray-500">Submitted {{ $selectedRequest->submitted_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    @if($selectedRequest->documents)
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Submitted Documents</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($selectedRequest->getDocumentUrls() as $document)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-900">{{ $document['name'] }}</span>
                                            <span class="text-xs text-gray-500">{{ strtoupper($document['type']) }}</span>
                                        </div>
                                        @if(in_array($document['type'], ['jpg', 'jpeg', 'png', 'gif']))
                                            <img src="{{ $document['url'] }}" class="w-full h-48 object-cover rounded" alt="{{ $document['name'] }}">
                                        @else
                                            <div class="w-full h-48 bg-gray-100 rounded flex items-center justify-center">
                                                <div class="text-center">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <p class="text-sm text-gray-500">{{ strtoupper($document['type']) }} Document</p>
                                                </div>
                                            </div>
                                        @endif
                                        <a href="{{ $document['url'] }}" target="_blank" 
                                           class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                            View Document
                                            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- User Notes -->
                    @if($selectedRequest->user_notes)
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-2">User Notes</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-700">{{ $selectedRequest->user_notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Review Form -->
                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Review Decision</h4>
                        
                        <!-- Action Selection -->
                        <div class="flex space-x-4 mb-4">
                            <button wire:click="$set('reviewAction', 'approve')"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                                           {{ $reviewAction === 'approve' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Approve
                            </button>
                            <button wire:click="$set('reviewAction', 'reject')"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                                           {{ $reviewAction === 'reject' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Reject
                            </button>
                            <button wire:click="$set('reviewAction', 'needs_review')"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                                           {{ $reviewAction === 'needs_review' ? 'bg-orange-100 text-orange-800 border-orange-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Needs Review
                            </button>
                        </div>

                        <!-- Feedback Textarea -->
                        @if($reviewAction)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $reviewAction === 'approve' ? 'Feedback (Optional)' : 'Reason' }}
                                </label>
                                <textarea wire:model="reviewFeedback" 
                                          rows="4" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                          placeholder="{{ $reviewAction === 'approve' ? 'Add any feedback for the user...' : 'Please provide a reason for this decision...' }}"></textarea>
                                @error('reviewFeedback') 
                                    <span class="text-red-600 text-sm">{{ $message }}</span> 
                                @enderror
                            </div>
                        @endif
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end space-x-4">
                        <button wire:click="closeReviewModal" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancel
                        </button>
                        @if($reviewAction)
                            <button wire:click="submitReview" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-pink-600 hover:bg-pink-700 rounded-lg transition-colors">
                                Submit Review
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Verifications Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('id')" class="flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                ID
                                @if($sortBy === 'id')
                                    <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('submitted_at')" class="flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Submitted
                                @if($sortBy === 'submitted_at')
                                    <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewed By</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($verifications as $verification)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                #{{ $verification->id }}
                            </td>
                            
                            <!-- User -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-green-600 flex items-center justify-center">
                                            <span class="text-white font-medium">
                                                {{ substr($verification->user->profile?->first_name ?? 'U', 0, 1) }}{{ substr($verification->user->profile?->last_name ?? 'U', 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $verification->user->profile?->first_name . ' ' . $verification->user->profile?->last_name ?: 'No Name' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $verification->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Type -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $verification->type_display_name }}
                                </span>
                            </td>
                            
                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    bg-{{ $verification->status_color }}-100 text-{{ $verification->status_color }}-800">
                                    {{ $verification->status_display_name }}
                                </span>
                            </td>
                            
                            <!-- Submitted -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $verification->submitted_at->format('M j, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $verification->submitted_at->diffForHumans() }}</div>
                            </td>
                            
                            <!-- Reviewed By -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($verification->reviewedBy)
                                    <div class="flex items-center">
                                        <div class="h-6 w-6 rounded-full bg-gray-300 flex items-center justify-center mr-2">
                                            <span class="text-white text-xs font-medium">
                                                {{ substr($verification->reviewedBy->name ?? 'A', 0, 1) }}
                                            </span>
                                        </div>
                                        <span>{{ $verification->reviewedBy->name ?? 'Admin' }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">Not reviewed</span>
                                @endif
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @if($verification->status === 'pending' || $verification->status === 'needs_review')
                                        <!-- Quick Approve -->
                                        <button wire:click="reviewRequest({{ $verification->id }}, 'approve')" 
                                                title="Review Request"
                                                class="text-green-600 hover:text-green-900 p-2 rounded hover:bg-green-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                            </svg>
                                        </button>
                                    @endif
                                    
                                    <!-- View User Profile -->
                                    <a href="{{ route('admin.user.profile', $verification->user->id) }}" 
                                       title="View User Profile"
                                       class="text-pink-600 hover:text-pink-900 p-2 rounded hover:bg-pink-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-900 mb-2">No verification requests found</p>
                                    <p class="text-gray-500">Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($verifications->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $verifications->links() }}
            </div>
        @endif
    </div>

    <!-- Toast Notifications -->
    <script>
        window.addEventListener('verification-reviewed', event => {
            alert(event.detail[0].message);
        });
    </script>
</div>