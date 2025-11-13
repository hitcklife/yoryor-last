<div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen flex flex-col justify-center">
    <!-- Mobile Progress Only -->
    <div class="lg:hidden mb-8">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-gray-700">Step 6 of 9</span>
            <span class="text-sm font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">67%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
            <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 h-3 rounded-full transition-all duration-700 shadow-sm" style="width: 67%"></div>
        </div>
    </div>

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50/90 border border-red-200 rounded-xl">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if ($errorMessage)
        <div class="mb-6 p-4 bg-red-50/90 border border-red-200 rounded-xl">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-800 font-medium">{{ $errorMessage }}</p>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-8">
        <!-- Photo Upload Section -->
        <div class="animate-fade-in" style="animation-delay: 100ms">
            <label class="block text-sm font-semibold text-gray-700 mb-4">Add Your Photos</label>
            <p class="text-xs text-gray-500 mb-4">Upload 1 main photo and up to 5 extra photos</p>
            
            <!-- Photo Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @for ($i = 0; $i < 6; $i++)
                    <div class="relative aspect-square">
                        @if (isset($photos[$i]))
                            <!-- Uploaded Photo -->
                            <div class="relative w-full h-full rounded-2xl overflow-hidden bg-gray-100 border-2 {{ $profilePhotoIndex === $i ? 'border-purple-500' : 'border-gray-200' }}">
                                @if (is_string($photos[$i]) && filter_var($photos[$i], FILTER_VALIDATE_URL))
                                    <!-- Existing photo URL -->
                                    <img src="{{ $photos[$i] }}" 
                                         alt="Photo {{ $i + 1 }}" 
                                         class="w-full h-full object-cover {{ $isPrivate ? 'blur-md' : '' }} transition-all duration-300">
                                @else
                                    <!-- New upload preview -->
                                    <img src="{{ $photos[$i]->temporaryUrl() }}" 
                                         alt="Photo {{ $i + 1 }}" 
                                         class="w-full h-full object-cover {{ $isPrivate ? 'blur-md' : '' }} transition-all duration-300">
                                @endif
                                
                                <!-- Privacy Overlay Icon -->
                                @if ($isPrivate)
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="bg-white/90 rounded-full p-3 shadow-lg">
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Profile Photo Badge -->
                                @if ($profilePhotoIndex === $i)
                                    <div class="absolute top-2 left-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs px-2 py-1 rounded-full font-medium shadow-lg z-10">
                                        Main
                                    </div>
                                @endif
                                
                                <!-- Photo Actions -->
                                <div class="absolute bottom-2 right-2 flex space-x-1">
                                    @if ($profilePhotoIndex !== $i)
                                        <button type="button" 
                                                wire:click="setProfilePhoto({{ $i }})"
                                                class="bg-white/90 hover:bg-white p-2 rounded-full shadow-lg transition-all duration-300 hover:scale-110"
                                                title="Set as main photo">
                                            <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </button>
                                    @endif
                                    
                                    <button type="button" 
                                            wire:click="removePhoto({{ $i }})"
                                            class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-lg transition-all duration-300 hover:scale-110"
                                            title="Remove photo">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- Upload Area -->
                            <label for="photo-{{ $i }}" class="block w-full h-full">
                                <div class="w-full h-full border-2 border-dashed border-gray-200 rounded-2xl hover:border-purple-300 hover:bg-purple-50 transition-all duration-300 cursor-pointer flex flex-col items-center justify-center p-4 bg-white/60">
                                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center mb-2">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600 text-center font-medium">
                                        @if ($i === 0)
                                            Add main
                                        @else
                                            Add photo
                                        @endif
                                    </span>
                                </div>
                                <input type="file" 
                                       id="photo-{{ $i }}"
                                       wire:model="photos.{{ $i }}"
                                       accept="image/*"
                                       class="hidden">
                            </label>
                        @endif
                    </div>
                @endfor
            </div>

            @error('photos')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @foreach($errors->get('photos.*') as $photoErrors)
                @foreach($photoErrors as $error)
                    <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
                @endforeach
            @endforeach
        </div>

        <!-- Privacy Toggle -->
        <div class="animate-fade-in" style="animation-delay: 200ms">
            <div class="flex items-center justify-between p-4 {{ $isPrivate ? 'bg-gradient-to-r from-purple-50 to-pink-50 border-purple-400' : 'bg-purple-50 border-purple-200' }} rounded-2xl border transition-all duration-300">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mr-3 {{ $isPrivate ? 'animate-pulse' : '' }}">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold {{ $isPrivate ? 'text-purple-900' : 'text-gray-900' }}">
                            Private Photos {{ $isPrivate ? '(Enabled)' : '' }}
                        </h3>
                        <p class="text-xs {{ $isPrivate ? 'text-purple-700' : 'text-gray-600' }}">
                            {{ $isPrivate ? 'Photos are blurred & only visible to matches' : 'Only visible to matched users' }}
                        </p>
                    </div>
                </div>
                
                <!-- Toggle Switch -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="isPrivate" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-purple-500 peer-checked:to-pink-500"></div>
                </label>
            </div>
        </div>

        <!-- Upload Progress -->
        <div wire:loading wire:target="photos" class="text-center py-4 animate-fade-in">
            <div class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-700 rounded-full">
                <svg class="animate-spin mr-2 h-4 w-4 text-purple-700" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium">Uploading photo...</span>
            </div>
        </div>

        <!-- Photo Tips -->
        <div class="animate-fade-in" style="animation-delay: 300ms">
            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
                <p class="text-sm text-purple-800 font-medium flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Tap the star to set your main photo • Max 5MB per photo • Recent photos work best
                </p>
            </div>
        </div>

        <!-- Continue Button -->
        <div class="pt-6 animate-fade-in" style="animation-delay: 400ms">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 text-white py-4 px-8 rounded-2xl font-bold text-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:-translate-y-1 disabled:hover:translate-y-0"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    @if(!$this->canContinue) disabled @endif>
                <span wire:loading.remove wire:target="submit" class="flex items-center justify-center">
                    Continue to Location
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </span>
                <span wire:loading wire:target="submit" class="inline-flex items-center justify-center">
                    <svg class="animate-spin mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Saving Photos...</span>
                </span>
            </button>

            @if(!$this->canContinue)
                <p class="mt-3 text-center text-xs text-gray-500">
                    Upload at least 1 main photo to continue
                </p>
            @endif
        </div>
    </form>

    <!-- Back Link -->
    <div class="mt-6 text-center">
        <a href="{{ route('onboard.interests') }}" 
           class="text-sm text-gray-600 hover:text-gray-700 font-medium">
            ← Back to Interests
        </a>
    </div>
</div>