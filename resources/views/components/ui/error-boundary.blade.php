@props([
    'fallback' => null,
    'showDetails' => false
])

<div x-data="errorBoundary()" x-init="init()">
    <template x-if="hasError">
        <div class="min-h-64 flex items-center justify-center p-6">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="alert-triangle" class="w-8 h-8 text-red-600 dark:text-red-400"></i>
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Something went wrong
                </h3>
                
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    We're sorry, but something unexpected happened. Please try refreshing the page.
                </p>
                
                @if($showDetails)
                    <details class="text-left mb-4">
                        <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            Show error details
                        </summary>
                        <div class="mt-2 p-3 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono text-gray-700 dark:text-gray-300">
                            <div x-text="errorMessage"></div>
                        </div>
                    </details>
                @endif
                
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button @click="retry()"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                        Try Again
                    </button>
                    
                    <button @click="goHome()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i data-lucide="home" class="w-4 h-4 mr-2"></i>
                        Go Home
                    </button>
                </div>
            </div>
        </div>
    </template>
    
    <template x-if="!hasError">
        <div>
            {{ $slot }}
        </div>
    </template>
</div>

@push('scripts')
<script>
    function errorBoundary() {
        return {
            hasError: false,
            errorMessage: '',
            
            init() {
                // Listen for Livewire errors
                document.addEventListener('livewire:error', (event) => {
                    this.handleError(event.detail);
                });
                
                // Listen for general JavaScript errors
                window.addEventListener('error', (event) => {
                    this.handleError({
                        message: event.message,
                        filename: event.filename,
                        lineno: event.lineno,
                        colno: event.colno,
                        error: event.error
                    });
                });
                
                // Listen for unhandled promise rejections
                window.addEventListener('unhandledrejection', (event) => {
                    this.handleError({
                        message: event.reason?.message || 'Unhandled promise rejection',
                        error: event.reason
                    });
                });
            },
            
            handleError(error) {
                this.hasError = true;
                this.errorMessage = this.formatError(error);
                console.error('Error caught by boundary:', error);
            },
            
            formatError(error) {
                if (typeof error === 'string') {
                    return error;
                }
                
                if (error.message) {
                    return error.message;
                }
                
                if (error.error && error.error.message) {
                    return error.error.message;
                }
                
                return 'An unknown error occurred';
            },
            
            retry() {
                this.hasError = false;
                this.errorMessage = '';
                
                // Reload the current page
                window.location.reload();
            },
            
            goHome() {
                window.location.href = '/dashboard';
            }
        }
    }
</script>
@endpush
