<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Account Verification</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Verify your identity to increase trust and get more matches</p>
                </div>
                
                <!-- Verification Score -->
                <div class="text-right">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $this->getOverallVerificationScore() }}%
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Verification Score</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Verification Overview -->
        <div class="mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Verification Status</h2>
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" 
                                     style="width: {{ $this->getOverallVerificationScore() }}%"></div>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $this->getOverallVerificationScore() }}% Complete
                            </span>
                        </div>
                    </div>

                    <!-- Verification Badges -->
                    @if(count($this->getVerificationBadges()) > 0)
                        <div class="flex flex-wrap gap-3 mb-6">
                            @foreach($this->getVerificationBadges() as $badge)
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                           {{ $badge['color'] === 'green' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' }}">
                                    <i data-lucide="{{ $badge['icon'] }}" class="w-4 h-4 mr-2"></i>
                                    {{ $badge['name'] }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Benefits -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">Benefits of Verification</h3>
                        <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                            <li>• Increased trust and credibility</li>
                            <li>• Higher match rates</li>
                            <li>• Priority in search results</li>
                            <li>• Access to premium features</li>
                            <li>• Enhanced safety and security</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Steps -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Photo Verification -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mr-3">
                                <i data-lucide="camera" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Photo Verification</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Take a selfie to verify your identity</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                   {{ $this->getVerificationColor($verificationStatus['photo']['status']) }}">
                            {{ $this->getVerificationStatusText($verificationStatus['photo']['status']) }}
                        </span>
                    </div>

                    @if($verificationStatus['photo']['status'] === 'not_started')
                        <div class="space-y-4">
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                <i data-lucide="upload" class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2"></i>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Upload a clear selfie for verification
                                </p>
                                <input type="file" 
                                       wire:model="photoVerification" 
                                       accept="image/*" 
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300">
                            </div>
                            
                            @if($uploadProgress > 0)
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $uploadProgress }}%"></div>
                                </div>
                            @endif
                        </div>
                    @elseif($verificationStatus['photo']['status'] === 'pending')
                        <div class="text-center py-4">
                            <i data-lucide="clock" class="w-8 h-8 text-yellow-500 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Your photo is being reviewed. This usually takes 24 hours.
                            </p>
                        </div>
                    @elseif($verificationStatus['photo']['status'] === 'verified')
                        <div class="text-center py-4">
                            <i data-lucide="check-circle" class="w-8 h-8 text-green-500 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Photo verified successfully!
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ID Verification -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center mr-3">
                                <i data-lucide="id-card" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">ID Verification</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Upload your government ID</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                   {{ $this->getVerificationColor($verificationStatus['id']['status']) }}">
                            {{ $this->getVerificationStatusText($verificationStatus['id']['status']) }}
                        </span>
                    </div>

                    @if($verificationStatus['id']['status'] === 'not_started')
                        <div class="space-y-4">
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                <i data-lucide="upload" class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2"></i>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Upload a clear photo of your government-issued ID
                                </p>
                                <input type="file" 
                                       wire:model="idVerification" 
                                       accept="image/*" 
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:file:bg-green-900 dark:file:text-green-300">
                            </div>
                            
                            @if($uploadProgress > 0)
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $uploadProgress }}%"></div>
                                </div>
                            @endif
                        </div>
                    @elseif($verificationStatus['id']['status'] === 'pending')
                        <div class="text-center py-4">
                            <i data-lucide="clock" class="w-8 h-8 text-yellow-500 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Your ID is being reviewed. This usually takes 24-48 hours.
                            </p>
                        </div>
                    @elseif($verificationStatus['id']['status'] === 'verified')
                        <div class="text-center py-4">
                            <i data-lucide="check-circle" class="w-8 h-8 text-green-500 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                ID verified successfully!
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Phone Verification -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center mr-3">
                                <i data-lucide="phone" class="w-5 h-5 text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Phone Verification</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Verify your phone number</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                   {{ $this->getVerificationColor($verificationStatus['phone']['status']) }}">
                            {{ $this->getVerificationStatusText($verificationStatus['phone']['status']) }}
                        </span>
                    </div>

                    @if($verificationStatus['phone']['status'] === 'not_started')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Phone Number
                                </label>
                                <input type="tel" 
                                       wire:model="phoneNumber" 
                                       placeholder="+1234567890"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <button wire:click="sendPhoneVerification"
                                    class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
                                Send Verification Code
                            </button>
                        </div>
                    @elseif($verificationStatus['phone']['status'] === 'verified')
                        <div class="text-center py-4">
                            <i data-lucide="check-circle" class="w-8 h-8 text-green-500 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Phone number verified: {{ $phoneNumber }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Email Verification -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center mr-3">
                                <i data-lucide="mail" class="w-5 h-5 text-orange-600 dark:text-orange-400"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Email Verification</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Verify your email address</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                   {{ $this->getVerificationColor($verificationStatus['email']['status']) }}">
                            {{ $this->getVerificationStatusText($verificationStatus['email']['status']) }}
                        </span>
                    </div>

                    @if($verificationStatus['email']['status'] === 'not_started')
                        <div class="text-center py-4">
                            <button wire:click="resendEmailVerification"
                                    class="bg-orange-600 hover:bg-orange-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
                                Send Verification Email
                            </button>
                        </div>
                    @elseif($verificationStatus['email']['status'] === 'verified')
                        <div class="text-center py-4">
                            <i data-lucide="check-circle" class="w-8 h-8 text-green-500 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Email address verified successfully!
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-8 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i data-lucide="shield" class="w-5 h-5 text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Your Privacy & Security
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>We take your privacy seriously. All verification documents are encrypted and stored securely. 
                        We only use this information to verify your identity and will never share it with third parties.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>
@endpush