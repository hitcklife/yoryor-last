<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Header Section -->
    <div class="relative bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white shadow-2xl">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('profile.enhance') }}" class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold">🎓 Career & Education</h1>
                        <p class="text-white/80 text-sm">Share your professional achievements and goals</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form wire:submit.prevent="save" class="space-y-8">
            
            <!-- Education Section -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">🎓</span>
                    Education
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="education_level" class="block text-sm font-medium text-gray-900 mb-2">Education Level</label>
                        <select wire:model="education_level" id="education_level" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose education level</option>
                            <option value="high_school">🎓 High School</option>
                            <option value="associate_degree">📄 Associate Degree</option>
                            <option value="bachelors_degree">🎓 Bachelor's Degree</option>
                            <option value="masters_degree">🎓 Master's Degree</option>
                            <option value="doctorate_phd">👨‍🎓 Doctorate/PhD</option>
                            <option value="professional_degree">🎨 Professional Degree</option>
                            <option value="trade_school">🔨 Trade School</option>
                            <option value="other">🎆 Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Field of Study</label>
                        <input type="text" wire:model="field_of_study" 
                               placeholder="e.g., Computer Science, Medicine..."
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                    </div>
                </div>
            </div>

            <!-- Work Status Section -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">💼</span>
                    Work Status
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="work_status" class="block text-sm font-medium text-gray-900 mb-2">Work Status</label>
                        <select wire:model="work_status" id="work_status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose status</option>
                            <option value="full_time">💼 Full-Time</option>
                            <option value="part_time">🕰️ Part-Time</option>
                            <option value="self_employed">🚀 Self-Employed</option>
                            <option value="freelancer">🎨 Freelancer</option>
                            <option value="student">🎓 Student</option>
                            <option value="between_jobs">🔍 Between Jobs</option>
                            <option value="retired">🌴 Retired</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Occupation/Job Title</label>
                        <input type="text" wire:model="occupation" 
                               placeholder="e.g., Software Engineer, Teacher..."
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company/Employer (Optional)</label>
                        <input type="text" wire:model="employer" 
                               placeholder="e.g., Google, Government..."
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                    </div>
                </div>
            </div>

            <!-- Career Goals & Income -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="text-2xl mr-3">🚀</span>
                        Career Goals
                    </h3>
                    
                    <textarea wire:model="career_goals" 
                              placeholder="Share your professional aspirations and goals..."
                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100"
                              rows="6"></textarea>
                </div>

                <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="text-2xl mr-3">💰</span>
                        Income Range
                    </h3>
                    
                    <div>
                        <label for="income_range" class="block text-sm font-medium text-gray-900 mb-2">Income Range (Optional)</label>
                        <select wire:model="income_range" id="income_range" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose range</option>
                            <option value="under_25k">🌱 Under $25,000</option>
                            <option value="25k_50k">🌿 $25,000 - $50,000</option>
                            <option value="50k_75k">🌲 $50,000 - $75,000</option>
                            <option value="75k_100k">🌳 $75,000 - $100,000</option>
                            <option value="100k_150k">🌄 $100,000 - $150,000</option>
                            <option value="over_150k">💰 Over $150,000</option>
                            <option value="prefer_not_to_say">🤐 Prefer Not to Say</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-center pt-8">
                <button type="submit" 
                        class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 px-12 rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-2xl flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Save Career Information</span>
                </button>
            </div>

        </form>
    </div>
</div>
