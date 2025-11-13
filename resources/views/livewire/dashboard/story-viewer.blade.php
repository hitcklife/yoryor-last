<!-- Story Viewer Modal -->
<!-- Debug: isOpen = {{ $isOpen ? 'true' : 'false' }}, stories count = {{ count($stories) }} -->
@if($isOpen)
<div x-data="storyViewer()"
     class="fixed inset-0 z-[9999] flex items-center justify-center"
     x-show="true"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <!-- Background Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-90"
         wire:click="closeViewer"></div>

    <!-- Story Container -->
    @if($currentStory)
    <div class="relative w-full max-w-md mx-auto h-full md:h-[90vh] bg-black rounded-none md:rounded-2xl overflow-hidden"
         @keydown.escape.window="$wire.closeViewer()"
         @keydown.arrow-left.window="$wire.previousStory()"
         @keydown.arrow-right.window="$wire.nextStory()">

        <!-- Progress Bars -->
        <div class="absolute top-0 left-0 right-0 z-20 p-4">
            <div class="flex space-x-1">
                @foreach($stories as $index => $story)
                    <div class="flex-1 h-1 bg-white bg-opacity-30 rounded-full overflow-hidden">
                        <div class="h-full bg-white rounded-full transition-all duration-100"
                             style="width: {{ $index < $currentStoryIndex ? '100%' : ($index === $currentStoryIndex ? $progress . '%' : '0%') }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Story Header -->
        <div class="absolute top-0 left-0 right-0 z-20 pt-8 pb-4 px-4 bg-gradient-to-b from-black/50 to-transparent">
            <div class="flex items-center justify-between">
                <!-- User Info -->
                <div class="flex items-center space-x-3">
                    @if($currentStory['user']['profile_photo'] ?? null)
                        <img src="{{ $currentStory['user']['profile_photo']['thumbnail_url'] }}"
                             alt="{{ $currentStory['user']['profile']['first_name'] ?? $currentStory['user']['name'] }}"
                             class="w-10 h-10 rounded-full object-cover border-2 border-white">
                    @else
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center border-2 border-white">
                            <span class="text-white font-bold text-sm">
                                {{ substr($currentStory['user']['profile']['first_name'] ?? $currentStory['user']['name'], 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <p class="text-white font-semibold text-sm">
                            {{ $currentStory['user']['profile']['first_name'] ?? $currentStory['user']['name'] }}
                        </p>
                        <p class="text-white text-xs opacity-70">
                            {{ \Carbon\Carbon::parse($currentStory['created_at'])->diffForHumans() }}
                        </p>
                    </div>
                </div>

                <!-- Close Button -->
                <button wire:click="closeViewer"
                        class="w-8 h-8 rounded-full bg-black bg-opacity-30 flex items-center justify-center text-white hover:bg-opacity-50 transition-colors">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Story Content -->
        <div class="relative w-full h-full flex items-center justify-center"
             @mousedown="pauseStory()"
             @mouseup="resumeStory()"
             @touchstart="pauseStory()"
             @touchend="resumeStory()">

            <!-- Navigation Areas -->
            <div class="absolute inset-0 flex">
                <!-- Previous Story Area -->
                <div class="flex-1 cursor-pointer" wire:click="previousStory"></div>
                <!-- Next Story Area -->
                <div class="flex-1 cursor-pointer" wire:click="nextStory"></div>
            </div>

            @if($currentStory['type'] === 'image')
                <!-- Image Story -->
                <img src="{{ $currentStory['media_url'] }}"
                     alt="Story"
                     class="w-full h-full object-cover"
                     loading="lazy">
            @else
                <!-- Video Story -->
                <video class="w-full h-full object-cover"
                       autoplay
                       muted
                       playsinline
                       @loadedmetadata="setVideoDuration($event.target.duration)"
                       @timeupdate="updateVideoProgress($event.target.currentTime)">
                    <source src="{{ $currentStory['media_url'] }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            @endif
        </div>

        <!-- Story Footer -->
        @if($currentStory['caption'])
        <div class="absolute bottom-0 left-0 right-0 z-20 p-4 bg-gradient-to-t from-black/70 to-transparent">
            <p class="text-white text-sm leading-relaxed">
                {{ $currentStory['caption'] }}
            </p>
        </div>
        @endif

        <!-- Navigation Arrows (Desktop) -->
        <div class="hidden md:block">
            @if($currentStoryIndex > 0)
                <button wire:click="previousStory"
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-black bg-opacity-30 rounded-full flex items-center justify-center text-white hover:bg-opacity-50 transition-colors">
                    <x-lucide-chevron-left class="w-6 h-6" />
                </button>
            @endif

            @if($currentStoryIndex < count($stories) - 1)
                <button wire:click="nextStory"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-black bg-opacity-30 rounded-full flex items-center justify-center text-white hover:bg-opacity-50 transition-colors">
                    <x-lucide-chevron-right class="w-6 h-6" />
                </button>
            @endif
        </div>
    </div>
    @endif

</div>
@endif

<script>
function storyViewer() {
    let progressInterval;
    let videoDuration = 0;
    let isPaused = false;

    return {
        isOpen: false,
        
        init() {
            // Wait for Livewire to be available
            this.waitForLivewire(() => {
                // Listen for Livewire events
                Livewire.on('start-story-progress', () => {
                    this.startProgress();
                });

                Livewire.on('pause-story-progress', () => {
                    this.pauseProgress();
                });

                Livewire.on('resume-story-progress', () => {
                    this.resumeProgress();
                });

                Livewire.on('close-story-viewer', () => {
                    this.cleanup();
                });
            });
        },

        waitForLivewire(callback) {
            if (typeof Livewire !== 'undefined') {
                callback();
            } else {
                setTimeout(() => this.waitForLivewire(callback), 100);
            }
        },

        startProgress() {
            this.cleanup();
            let progress = 0;
            const duration = 5000; // 5 seconds for images
            const interval = 50; // Update every 50ms
            const increment = (100 / duration) * interval;

            progressInterval = setInterval(() => {
                if (!isPaused) {
                    progress += increment;
                    @this.updateProgress(progress);

                    if (progress >= 100) {
                        this.cleanup();
                    }
                }
            }, interval);
        },

        pauseStory() {
            isPaused = true;
            @this.pauseStory();
        },

        resumeStory() {
            isPaused = false;
            @this.resumeStory();
        },

        pauseProgress() {
            isPaused = true;
        },

        resumeProgress() {
            isPaused = false;
        },

        setVideoDuration(duration) {
            videoDuration = duration;
        },

        updateVideoProgress(currentTime) {
            if (videoDuration > 0) {
                const progress = (currentTime / videoDuration) * 100;
                @this.updateProgress(progress);
            }
        },

        cleanup() {
            if (progressInterval) {
                clearInterval(progressInterval);
                progressInterval = null;
            }
        }
    }
}
</script>
