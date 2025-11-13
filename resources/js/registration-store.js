/**
 * YorYor Registration Store
 * Alpine.js store for managing registration state and flow
 */

document.addEventListener('alpine:init', () => {
    console.log('Initializing registration store...');
    Alpine.store('registration', {
        // === STATE ===
        
        // Current registration state
        currentStep: 1,
        totalSteps: 7,
        isLoading: false,
        isSubmitting: false,
        
        // User authentication
        user: null,
        token: localStorage.getItem('auth_token'),
        isAuthenticated: false,
        
        // Form data for each step
        formData: {
            // Step 1: Phone verification
            phone: '',
            countryCode: '+1',
            otp: '',
            otpSent: false,
            otpExpires: null,
            
            // Step 2: Basic information
            firstName: '',
            lastName: '',
            dateOfBirth: '',
            gender: '',
            age: null,
            
            // Step 3: Location & preferences
            country: '',
            countryCode: '',
            state: '',
            city: '',
            
            // Step 4: Photos
            photos: [], // Array of File objects or photo data
            mainPhotoIndex: 0,
            uploadProgress: {}, // Photo upload progress tracking
            
            // Step 5: Profile details
            email: '',
            bio: '',
            profession: '',
            occupation: '',
            status: 'single',
            interests: [],
            lookingFor: 'all',
            
            // Step 6: Cultural & family preferences
            religion: '',
            ethnicity: '',
            languages: [],
            familyValues: '',
            
            // Step 7: Review & settings
            isPrivate: false,
            termsAccepted: false,
            privacyAccepted: false,
        },
        
        // Validation state
        validationErrors: {},
        stepValidation: {
            1: false, // Phone verification
            2: false, // Basic info
            3: false, // Location
            4: false, // Photos
            5: false, // Profile details
            6: false, // Cultural preferences
            7: false  // Review
        },
        
        // UI state
        showErrors: false,
        successMessage: '',
        errorMessage: '',
        
        // === INITIALIZATION ===
        
        init() {
            this.loadPersistedData();
            this.checkAuthenticationStatus();
            this.startAutoSave();
            
            console.log('Registration store initialized', {
                currentStep: this.currentStep,
                isAuthenticated: this.isAuthenticated,
                hasToken: !!this.token
            });
        },
        
        // === DATA PERSISTENCE ===
        
        loadPersistedData() {
            try {
                const savedData = localStorage.getItem('registration_data');
                if (savedData) {
                    const parsed = JSON.parse(savedData);
                    this.formData = { ...this.formData, ...parsed };
                }
                
                const savedStep = localStorage.getItem('registration_step');
                if (savedStep) {
                    this.currentStep = parseInt(savedStep);
                }
            } catch (error) {
                console.warn('Failed to load persisted registration data:', error);
            }
        },
        
        saveFormData() {
            try {
                localStorage.setItem('registration_data', JSON.stringify(this.formData));
                localStorage.setItem('registration_step', this.currentStep.toString());
            } catch (error) {
                console.warn('Failed to save registration data:', error);
            }
        },
        
        clearPersistedData() {
            localStorage.removeItem('registration_data');
            localStorage.removeItem('registration_step');
            localStorage.removeItem('auth_token');
        },
        
        startAutoSave() {
            // Auto-save every 30 seconds
            setInterval(() => {
                this.saveFormData();
            }, 30000);
        },
        
        // === AUTHENTICATION ===
        
        checkAuthenticationStatus() {
            if (this.token) {
                this.isAuthenticated = true;
                // TODO: Validate token with server
            }
        },
        
        setAuthToken(token) {
            this.token = token;
            this.isAuthenticated = true;
            localStorage.setItem('auth_token', token);
        },
        
        clearAuthToken() {
            this.token = null;
            this.isAuthenticated = false;
            localStorage.removeItem('auth_token');
        },
        
        // === STEP VALIDATION ===
        
        validateStep(step = null) {
            const stepToValidate = step || this.currentStep;
            
            switch(stepToValidate) {
                case 1:
                    return this.validatePhoneStep();
                case 2:
                    return this.validateBasicInfoStep();
                case 3:
                    return this.validateLocationStep();
                case 4:
                    return this.validatePhotosStep();
                case 5:
                    return this.validateProfileStep();
                case 6:
                    return this.validatePreferencesStep();
                case 7:
                    return this.validateReviewStep();
                default:
                    return false;
            }
        },
        
        validatePhoneStep() {
            const isValid = this.isAuthenticated && 
                           this.formData.phone && 
                           this.user?.phone_verified_at;
            
            this.stepValidation[1] = isValid;
            return isValid;
        },
        
        validateBasicInfoStep() {
            const isValid = !!(
                this.formData.firstName?.trim() &&
                this.formData.lastName?.trim() &&
                this.formData.dateOfBirth &&
                this.formData.gender &&
                this.isAgeValid()
            );
            
            this.stepValidation[2] = isValid;
            return isValid;
        },
        
        validateLocationStep() {
            const isValid = !!(this.formData.country?.trim());
            this.stepValidation[3] = isValid;
            return isValid;
        },
        
        validatePhotosStep() {
            const isValid = this.formData.photos.length > 0;
            this.stepValidation[4] = isValid;
            return isValid;
        },
        
        validateProfileStep() {
            const isValid = !!(
                this.formData.bio?.trim() &&
                this.formData.profession?.trim()
            );
            this.stepValidation[5] = isValid;
            return isValid;
        },
        
        validatePreferencesStep() {
            // This step is optional
            this.stepValidation[6] = true;
            return true;
        },
        
        validateReviewStep() {
            const isValid = this.formData.termsAccepted && 
                           this.formData.privacyAccepted &&
                           this.validateAllPreviousSteps();
            
            this.stepValidation[7] = isValid;
            return isValid;
        },
        
        validateAllPreviousSteps() {
            for (let i = 1; i <= 6; i++) {
                if (!this.validateStep(i)) {
                    return false;
                }
            }
            return true;
        },
        
        isAgeValid() {
            if (!this.formData.dateOfBirth) return false;
            
            const birthDate = new Date(this.formData.dateOfBirth);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            this.formData.age = age;
            return age >= 18;
        },
        
        // === STEP NAVIGATION ===
        
        async nextStep() {
            if (this.isLoading) return;
            
            // Validate current step
            if (!this.validateStep(this.currentStep)) {
                this.showValidationErrors();
                return false;
            }
            
            this.clearMessages();
            
            if (this.currentStep < this.totalSteps) {
                // Move to next step
                this.currentStep++;
                this.saveFormData();
                this.scrollToTop();
                return true;
            } else {
                // Complete registration
                return await this.completeRegistration();
            }
        },
        
        previousStep() {
            if (this.isLoading || this.currentStep <= 1) return;
            
            this.currentStep--;
            this.clearMessages();
            this.saveFormData();
            this.scrollToTop();
        },
        
        goToStep(step) {
            if (step < 1 || step > this.totalSteps || this.isLoading) return;
            
            // Can only go to completed steps or next step
            if (step > this.currentStep + 1) return;
            
            this.currentStep = step;
            this.clearMessages();
            this.saveFormData();
            this.scrollToTop();
        },
        
        // === API INTEGRATION ===
        
        async sendOTP(phone = null) {
            const phoneNumber = phone || this.formData.phone;
            if (!phoneNumber) {
                this.setError('Please enter your phone number');
                return false;
            }
            
            this.setLoading(true);
            this.clearMessages();
            
            try {
                const response = await fetch('/api/v1/auth/authenticate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ phone: phoneNumber })
                });
                
                const data = await response.json();
                
                if (data.status === 'success' && data.data.otp_sent) {
                    this.formData.phone = phoneNumber;
                    this.formData.otpSent = true;
                    this.formData.otpExpires = Date.now() + (data.data.expires_in * 1000);
                    this.setSuccess('OTP sent successfully');
                    return true;
                } else {
                    this.setError(data.message || 'Failed to send OTP');
                    return false;
                }
            } catch (error) {
                console.error('OTP sending failed:', error);
                this.setError('Failed to send OTP. Please try again.');
                return false;
            } finally {
                this.setLoading(false);
            }
        },
        
        async verifyOTP(otp = null) {
            const otpCode = otp || this.formData.otp;
            if (!otpCode || otpCode.length !== 4) {
                this.setError('Please enter the 4-digit code');
                return false;
            }
            
            this.setLoading(true);
            this.clearMessages();
            
            try {
                const response = await fetch('/api/v1/auth/authenticate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        phone: this.formData.phone,
                        otp: otpCode
                    })
                });
                
                const data = await response.json();
                
                if (data.status === 'success' && data.data.authenticated) {
                    this.user = data.data.user;
                    this.setAuthToken(data.data.token);
                    this.formData.otp = otpCode;
                    this.setSuccess('Phone verified successfully');
                    
                    // Check if registration is already completed
                    if (data.data.registration_completed) {
                        window.location.href = data.data.redirect_url || '/dashboard';
                        return true;
                    }
                    
                    return true;
                } else {
                    this.setError(data.message || 'Invalid verification code');
                    return false;
                }
            } catch (error) {
                console.error('OTP verification failed:', error);
                this.setError('Failed to verify code. Please try again.');
                return false;
            } finally {
                this.setLoading(false);
            }
        },
        
        async completeRegistration() {
            if (!this.validateAllPreviousSteps()) {
                this.setError('Please complete all required fields');
                return false;
            }
            
            this.setLoading(true);
            this.isSubmitting = true;
            this.clearMessages();
            
            try {
                const formData = new FormData();
                
                // Basic information
                formData.append('firstName', this.formData.firstName);
                formData.append('lastName', this.formData.lastName);
                formData.append('dateOfBirth', this.formData.dateOfBirth);
                formData.append('gender', this.formData.gender);
                
                // Optional fields
                if (this.formData.email) formData.append('email', this.formData.email);
                if (this.formData.bio) formData.append('bio', this.formData.bio);
                if (this.formData.profession) formData.append('profession', this.formData.profession);
                if (this.formData.occupation) formData.append('occupation', this.formData.occupation);
                if (this.formData.status) formData.append('status', this.formData.status);
                if (this.formData.country) formData.append('country', this.formData.country);
                if (this.formData.state) formData.append('state', this.formData.state);
                if (this.formData.city) formData.append('city', this.formData.city);
                if (this.formData.lookingFor) formData.append('lookingFor', this.formData.lookingFor);
                
                // Interests array
                if (this.formData.interests && this.formData.interests.length > 0) {
                    this.formData.interests.forEach((interest, index) => {
                        formData.append(`interests[${index}]`, interest);
                    });
                }
                
                // Photos
                if (this.formData.photos && this.formData.photos.length > 0) {
                    this.formData.photos.forEach((photo, index) => {
                        formData.append(`photos[${index}]`, photo);
                    });
                    
                    if (this.formData.mainPhotoIndex !== undefined) {
                        formData.append('mainPhotoIndex', this.formData.mainPhotoIndex);
                    }
                }
                
                // Privacy settings
                formData.append('profile_private', this.formData.isPrivate ? '1' : '0');
                
                const response = await fetch('/api/v1/auth/complete-registration', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.setSuccess('Registration completed successfully!');
                    this.clearPersistedData();
                    
                    // Redirect after a short delay to show success message
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 2000);
                    
                    return true;
                } else {
                    this.setError(data.message || 'Registration failed');
                    return false;
                }
            } catch (error) {
                console.error('Registration completion failed:', error);
                this.setError('Registration failed. Please try again.');
                return false;
            } finally {
                this.setLoading(false);
                this.isSubmitting = false;
            }
        },
        
        // === PHOTO MANAGEMENT ===
        
        addPhoto(file) {
            if (this.formData.photos.length >= 6) {
                this.setError('You can upload up to 6 photos');
                return false;
            }
            
            this.formData.photos.push(file);
            this.saveFormData();
            return true;
        },
        
        removePhoto(index) {
            if (index >= 0 && index < this.formData.photos.length) {
                this.formData.photos.splice(index, 1);
                
                // Adjust main photo index if needed
                if (this.formData.mainPhotoIndex >= index) {
                    this.formData.mainPhotoIndex = Math.max(0, this.formData.mainPhotoIndex - 1);
                }
                
                this.saveFormData();
            }
        },
        
        setMainPhoto(index) {
            if (index >= 0 && index < this.formData.photos.length) {
                this.formData.mainPhotoIndex = index;
                this.saveFormData();
            }
        },
        
        updatePhotoProgress(index, progress) {
            this.uploadProgress[index] = progress;
        },
        
        // === UI HELPERS ===
        
        setLoading(loading) {
            this.isLoading = loading;
        },
        
        setSuccess(message) {
            this.successMessage = message;
            this.errorMessage = '';
            this.showErrors = false;
        },
        
        setError(message) {
            this.errorMessage = message;
            this.successMessage = '';
            this.showErrors = true;
        },
        
        clearMessages() {
            this.successMessage = '';
            this.errorMessage = '';
            this.showErrors = false;
        },
        
        showValidationErrors() {
            this.showErrors = true;
            this.setError('Please complete all required fields');
        },
        
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        
        // === UTILITIES ===
        
        getProgressPercentage() {
            return Math.round((this.currentStep / this.totalSteps) * 100);
        },
        
        canGoToNextStep() {
            return this.validateStep(this.currentStep) && !this.isLoading;
        },
        
        canGoToPreviousStep() {
            return this.currentStep > 1 && !this.isLoading;
        },
        
        getStepTitle(step = null) {
            const stepTitles = {
                1: 'Verify Your Phone',
                2: 'Tell Us About Yourself',
                3: 'Where Are You Based?',
                4: 'Show Your Best Self',
                5: 'Share Your Story',
                6: 'Your Preferences',
                7: 'Complete Your Profile'
            };
            
            return stepTitles[step || this.currentStep] || 'Registration';
        }
    });
    console.log('Registration store initialized successfully');
});