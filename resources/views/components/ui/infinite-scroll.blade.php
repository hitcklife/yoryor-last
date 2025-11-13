@props([
    'loadMoreUrl' => null,
    'hasMore' => true,
    'loading' => false,
    'threshold' => 100
])

<div x-data="infiniteScroll()" 
     x-init="init('{{ $loadMoreUrl }}', {{ $hasMore ? 'true' : 'false' }}, {{ $loading ? 'true' : 'false' }}, {{ $threshold }})"
     {{ $attributes }}>
    
    <!-- Content -->
    <div x-ref="content">
        {{ $slot }}
    </div>
    
    <!-- Loading Indicator -->
    <div x-show="loading" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="flex justify-center py-8">
        <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
            <div class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-blue-600"></div>
            <span class="text-sm">Loading more...</span>
        </div>
    </div>
    
    <!-- End of Content -->
    <div x-show="!hasMore && !loading" 
         class="flex justify-center py-8">
        <div class="text-center">
            <i data-lucide="check-circle" class="w-8 h-8 text-green-500 mx-auto mb-2"></i>
            <p class="text-sm text-gray-500 dark:text-gray-400">You've reached the end</p>
        </div>
    </div>
    
    <!-- Load More Button (Fallback) -->
    <div x-show="!loading && hasMore" 
         class="flex justify-center py-4">
        <button @click="loadMore()"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
            Load More
        </button>
    </div>
</div>

@push('scripts')
<script>
    function infiniteScroll() {
        return {
            loading: false,
            hasMore: true,
            loadMoreUrl: null,
            threshold: 100,
            
            init(url, hasMore, loading, threshold) {
                this.loadMoreUrl = url;
                this.hasMore = hasMore;
                this.loading = loading;
                this.threshold = threshold;
                
                if (this.loadMoreUrl) {
                    this.setupScrollListener();
                }
            },
            
            setupScrollListener() {
                window.addEventListener('scroll', () => {
                    if (this.loading || !this.hasMore) return;
                    
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const windowHeight = window.innerHeight;
                    const documentHeight = document.documentElement.scrollHeight;
                    
                    if (scrollTop + windowHeight >= documentHeight - this.threshold) {
                        this.loadMore();
                    }
                });
            },
            
            async loadMore() {
                if (this.loading || !this.hasMore || !this.loadMoreUrl) return;
                
                this.loading = true;
                
                try {
                    const response = await fetch(this.loadMoreUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to load more content');
                    }
                    
                    const data = await response.json();
                    
                    if (data.html) {
                        // Append new content
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;
                        
                        while (tempDiv.firstChild) {
                            this.$refs.content.appendChild(tempDiv.firstChild);
                        }
                    }
                    
                    this.hasMore = data.hasMore || false;
                    this.loadMoreUrl = data.nextUrl || null;
                    
                } catch (error) {
                    console.error('Error loading more content:', error);
                    this.showError('Failed to load more content. Please try again.');
                } finally {
                    this.loading = false;
                }
            },
            
            showError(message) {
                // You can implement a toast notification here
                console.error(message);
            }
        }
    }
</script>
@endpush
