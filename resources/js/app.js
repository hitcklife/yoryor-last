/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

/**
 * Lucide Icons
 */
import { createIcons,
    ShieldCheck,
    Check,
    Info,
    Users,
    AlertTriangle,
    Shield,
    Phone,
    Download,
    ArrowRight,
    Sun,
    Moon,
    Monitor,
    Heart,
    ChevronDown,
    Menu,
    X,
    ArrowRight as ArrowRightIcon,
    Lock,
    Globe,
    Video,
    ChevronRight,
    Home,
    MessageCircle,
    Search,
    Plus,
    Bell,
    BarChart3,
    Settings,
    ShieldAlert,
    Eye,
    User,
    Star,
    Filter,
    LayoutGrid,
    AlignJustify,
    FolderGit2,
    BookOpenText,
    LogOut,
    ChevronLeft,
    Clock,
    MapPin,
    Languages,
    CreditCard,
    Plane,
    Send,
    Quote,
    ArrowLeft,
    Calendar,
    Zap,
    Book,
    List
} from 'lucide';

/**
 * Alpine.js initialization
 */
import Alpine from 'alpinejs';
import 'flowbite';
import './flowbite-init';

// Import registration store and country data
import './registration-store';
import './country-data';

// Import date picker component
import datePicker from './date-picker';

// Import messages functionality
import initializeMessages from './messages';

// Import theme system
import './theme';

// Import VideoSDK service
import './videosdk';

// Import video call component
import videoCall from './video-call';

// Register date picker component
Alpine.data('datePicker', datePicker);

// Register video call component
Alpine.data('videoCall', videoCall);

// Start Alpine.js only if not already started
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

// Create icons object
const iconsObject = {
    ShieldCheck,
    Check,
    Info,
    Users,
    AlertTriangle,
    Shield,
    Phone,
    Download,
    ArrowRight,
    Sun,
    Moon,
    Monitor,
    Heart,
    ChevronDown,
    Menu,
    X,
    ArrowRightIcon,
    Lock,
    Globe,
    Video,
    ChevronRight,
    Home,
    MessageCircle,
    Search,
    Plus,
    Bell,
    BarChart3,
    Settings,
    ShieldAlert,
    Eye,
    User,
    Star,
    Filter,
    LayoutGrid,
    AlignJustify,
    FolderGit2,
    BookOpenText,
    LogOut,
    ChevronLeft,
    Clock,
    MapPin,
    Languages,
    CreditCard,
    Plane,
    Send,
    Quote,
    ArrowLeft,
    Calendar,
    Zap,
    Book,
    List
};

// Initialize messages functionality after DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing Lucide icons...');

    try {
        // Initialize Lucide icons with specific icons
        createIcons({ icons: iconsObject });
        console.log('Lucide icons initialized successfully');
    } catch (error) {
        console.error('Failed to initialize Lucide icons:', error);
    }

    // Make Lucide available globally for Livewire updates
    window.lucide = {
        createIcons,
        icons: iconsObject
    };

    // Initialize icons immediately for any existing elements
    setTimeout(() => {
        try {
            createIcons({ icons: iconsObject });
            console.log('Lucide icons re-initialized');
        } catch (error) {
            console.error('Failed to re-initialize Lucide icons:', error);
        }
    }, 100);

    // Wait a bit for Echo to be fully initialized
    setTimeout(() => {
        if (window.Echo) {
            console.log('Echo is ready, initializing messages');
            initializeMessages();
        } else {
            console.error('Echo failed to initialize');
        }
    }, 500);
});

// Re-initialize Lucide icons when Livewire updates the DOM
document.addEventListener('livewire:navigated', () => {
    console.log('Livewire navigated, re-initializing icons');
    try {
        createIcons({ icons: iconsObject });
    } catch (error) {
        console.error('Failed to initialize icons on navigation:', error);
    }
});

document.addEventListener('livewire:updated', () => {
    console.log('Livewire updated, re-initializing icons');
    try {
        createIcons({ icons: iconsObject });
    } catch (error) {
        console.error('Failed to initialize icons on update:', error);
    }
});
