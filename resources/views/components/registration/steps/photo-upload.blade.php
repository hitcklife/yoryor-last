@props([
    'title' => 'Show Your Best Self',
    'subtitle' => 'Add photos to make a great first impression'
])

<div x-data="photoUploadStep()" class="space-y-8">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
        <p class="text-gray-600">{{ $subtitle }}</p>
    </div>

    <!-- Success/Error Messages -->
    <div x-show="$store.registration.successMessage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="$store.registration.successMessage"></span>
    </div>

    <div x-show="$store.registration.errorMessage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span x-text="$store.registration.errorMessage"></span>
    </div>

    <!-- Photo Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <!-- Photo Slots -->
        <template x-for="(photo, index) in photoSlots" :key="`photo-${index}`">
            <div class="relative aspect-square group">
                <!-- Photo Display -->
                <div x-show="photo" class="relative w-full h-full">
                    <img :src="getPhotoUrl(photo)" 
                         :alt="`Photo ${index + 1}`"
                         class="w-full h-full object-cover rounded-xl shadow-md transition-all duration-300 group-hover:shadow-lg">
                    
                    <!-- Main Photo Badge -->
                    <div x-show="index === $store.registration.formData.mainPhotoIndex"
                         class="absolute top-2 left-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white text-xs font-semibold px-2 py-1 rounded-full shadow-lg">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                        Main
                    </div>
                    
                    <!-- Photo Actions -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <div class="flex space-x-2">
                            <!-- Set as Main -->
                            <button x-show="index !== $store.registration.formData.mainPhotoIndex"
                                    @click="setMainPhoto(index)"
                                    class="p-2 bg-white/90 backdrop-blur-sm text-gray-700 rounded-full shadow-lg hover:bg-white transition-colors"
                                    title="Set as main photo">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            
                            <!-- Remove -->
                            <button @click="removePhoto(index)"
                                    class="p-2 bg-red-500/90 backdrop-blur-sm text-white rounded-full shadow-lg hover:bg-red-600 transition-colors"
                                    title="Remove photo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Upload Progress -->
                    <div x-show="$store.registration.uploadProgress[index] !== undefined && $store.registration.uploadProgress[index] < 100"
                         class="absolute inset-0 bg-black bg-opacity-50 rounded-xl flex items-center justify-center">
                        <div class="text-center text-white">
                            <x-ui.progress-ring 
                                :progress="$store.registration.uploadProgress[index] || 0" 
                                size="sm" 
                                color="accent"
                                show-percentage="true"
                            />
                            <p class="text-sm mt-2">Uploading...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Empty Slot -->
                <x-ui.glass-card 
                    x-show="!photo" 
                    @click="triggerFileInput(index)"
                    @drop="handleDrop($event, index)" 
                    @dragover.prevent 
                    @dragenter.prevent
                    class="w-full h-full flex flex-col items-center justify-center cursor-pointer hover:border-pink-300 transition-all duration-300 group-hover:scale-105"
                    hover="true"
                    interactive="true"
                    padding="base"
                >
                    <div class="text-center">
                        <div class="w-12 h-12 mx-auto mb-3 bg-gradient-to-r from-pink-100 to-purple-100 rounded-full flex items-center justify-center group-hover:from-pink-200 group-hover:to-purple-200 transition-all">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-700">Add Photo</p>
                        <p class="text-xs text-gray-500 mt-1">
                            <span x-show="index === 0">Main photo</span>
                            <span x-show="index > 0">Optional</span>
                        </p>
                    </div>
                </x-ui.glass-card>
            </div>
        </template>
    </div>

    <!-- Photo Tips -->
    <x-ui.glass-card padding="base" class="bg-blue-50/30">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-blue-900 mb-2">Photo Tips</h4>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li>• Use high-quality, well-lit photos</li>
                    <li>• Include a clear face shot as your main photo</li>
                    <li>• Show your personality and interests</li>
                    <li>• Avoid group photos where you're hard to identify</li>
                </ul>
            </div>
        </div>
    </x-ui.glass-card>

    <!-- File Input (Hidden) -->
    <input type="file" 
           x-ref="fileInput"
           @change="handleFileSelect($event)"
           accept="image/*"
           multiple
           class="hidden">

    <!-- Navigation Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 pt-8">
        <x-ui.gradient-button
            variant="outline"
            size="lg"
            type="button"
            @click="$store.registration.previousStep()"
            class="flex-1"
            :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M15 19l-7-7 7-7&quot;/></svg>'"
            icon-position="left"
        >
            Previous
        </x-ui.gradient-button>
        
        <x-ui.gradient-button
            variant="primary"
            size="lg"
            type="button"
            @click="handleNext()"
            :loading="$store.registration.isLoading"
            :disabled="!canProceed"
            class="flex-1"
            :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M13 7l5 5m0 0l-5 5m5-5H6&quot;/></svg>'"
            icon-position="right"
        >
            Continue
        </x-ui.gradient-button>
    </div>
</div>

<script>
function photoUploadStep() {
    return {
        photoSlots: new Array(6).fill(null),
        selectedSlotIndex: 0,
        
        // Computed properties
        get canProceed() {
            return this.$store.registration.formData.photos.length > 0;
        },
        
        init() {
            // Initialize photo slots with existing photos
            this.syncPhotosWithStore();
            
            // Watch for changes in store photos
            this.$watch('$store.registration.formData.photos', () => {
                this.syncPhotosWithStore();
            }, { deep: true });
        },
        
        syncPhotosWithStore() {
            // Clear all slots
            this.photoSlots.fill(null);
            
            // Fill slots with photos from store
            this.$store.registration.formData.photos.forEach((photo, index) => {
                if (index < 6) {
                    this.photoSlots[index] = photo;
                }
            });
        },
        
        triggerFileInput(index) {
            this.selectedSlotIndex = index;
            this.$refs.fileInput.click();
        },
        
        async handleFileSelect(event) {
            const files = Array.from(event.target.files);
            await this.processFiles(files);
            
            // Clear the input for future selections
            event.target.value = '';
        },
        
        async handleDrop(event, index) {
            event.preventDefault();
            this.selectedSlotIndex = index;
            
            const files = Array.from(event.dataTransfer.files);
            await this.processFiles(files);
        },
        
        async processFiles(files) {
            const imageFiles = files.filter(file => file.type.startsWith('image/'));
            
            if (imageFiles.length === 0) {
                this.$store.registration.setError('Please select valid image files');
                return;
            }
            
            for (const file of imageFiles) {
                if (!await this.validateFile(file)) continue;
                
                // Find next available slot
                let slotIndex = this.selectedSlotIndex;
                while (slotIndex < 6 && this.photoSlots[slotIndex] !== null) {
                    slotIndex++;
                }
                
                if (slotIndex >= 6) {
                    this.$store.registration.setError('Maximum 6 photos allowed');
                    break;
                }
                
                await this.addPhotoToSlot(file, slotIndex);
            }
        },
        
        async validateFile(file) {
            // Check file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                this.$store.registration.setError('File size must be less than 10MB');
                return false;
            }
            
            // Check file type
            if (!['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(file.type)) {
                this.$store.registration.setError('Only JPEG, PNG, and WebP images are allowed');
                return false;
            }
            
            return true;
        },
        
        async addPhotoToSlot(file, slotIndex) {
            try {
                // Create preview URL
                const previewUrl = URL.createObjectURL(file);
                
                // Compress image if needed
                const processedFile = await this.compressImage(file);
                
                // Add to slot immediately for UI feedback
                this.photoSlots[slotIndex] = {
                    file: processedFile,
                    previewUrl: previewUrl,
                    uploaded: false
                };
                
                // Update store
                this.$store.registration.formData.photos[slotIndex] = processedFile;
                
                // If this is the first photo, make it the main photo
                if (this.$store.registration.formData.photos.filter(p => p).length === 1) {
                    this.$store.registration.formData.mainPhotoIndex = slotIndex;
                }
                
                // Simulate upload progress (in real app, this would be actual upload)
                await this.simulateUpload(slotIndex);
                
            } catch (error) {
                console.error('Error processing image:', error);
                this.$store.registration.setError('Failed to process image');
                this.photoSlots[slotIndex] = null;
            }
        },
        
        async compressImage(file) {
            return new Promise((resolve) => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const img = new Image();
                
                img.onload = () => {
                    // Calculate new dimensions (max 1200px)
                    const maxSize = 1200;
                    let { width, height } = img;
                    
                    if (width > height && width > maxSize) {
                        height = (height * maxSize) / width;
                        width = maxSize;
                    } else if (height > maxSize) {
                        width = (width * maxSize) / height;
                        height = maxSize;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    // Draw and compress
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob(resolve, 'image/jpeg', 0.85);
                };
                
                img.src = URL.createObjectURL(file);
            });
        },
        
        async simulateUpload(slotIndex) {
            // Simulate upload progress
            for (let progress = 0; progress <= 100; progress += 10) {
                this.$store.registration.updatePhotoProgress(slotIndex, progress);
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            
            // Mark as uploaded
            if (this.photoSlots[slotIndex]) {
                this.photoSlots[slotIndex].uploaded = true;
            }
            
            // Clear progress
            delete this.$store.registration.uploadProgress[slotIndex];
        },
        
        removePhoto(index) {
            // Remove from slots
            this.photoSlots[index] = null;
            
            // Clean up URL
            if (this.photoSlots[index]?.previewUrl) {
                URL.revokeObjectURL(this.photoSlots[index].previewUrl);
            }
            
            // Remove from store and compact array
            this.$store.registration.formData.photos.splice(index, 1);
            
            // Adjust main photo index if needed
            if (this.$store.registration.formData.mainPhotoIndex >= index) {
                this.$store.registration.formData.mainPhotoIndex = Math.max(0, 
                    this.$store.registration.formData.mainPhotoIndex - 1);
            }
            
            // Re-sync with store
            this.syncPhotosWithStore();
        },
        
        setMainPhoto(index) {
            if (this.photoSlots[index]) {
                this.$store.registration.formData.mainPhotoIndex = index;
            }
        },
        
        getPhotoUrl(photo) {
            if (!photo) return '';
            return photo.previewUrl || URL.createObjectURL(photo.file || photo);
        },
        
        async handleNext() {
            if (!this.canProceed) {
                this.$store.registration.setError('Please add at least one photo');
                return;
            }
            
            // Check if all uploads are complete
            const incompleteUploads = Object.keys(this.$store.registration.uploadProgress).length > 0;
            if (incompleteUploads) {
                this.$store.registration.setError('Please wait for all photos to finish uploading');
                return;
            }
            
            await this.$store.registration.nextStep();
        }
    }
}
</script>