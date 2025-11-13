<!-- Multi-Currency Display Component -->
<div class="fixed bottom-4 left-4 z-40" x-data="{
    selectedCurrency: 'USD',
    currencies: {
        'USD': { symbol: '$', name: 'US Dollar', rate: 1.0, flag: '🇺🇸' },
        'EUR': { symbol: '€', name: 'Euro', rate: 0.85, flag: '🇪🇺' },
        'GBP': { symbol: '£', name: 'British Pound', rate: 0.73, flag: '🇬🇧' },
        'UZS': { symbol: 'so\'m', name: 'Uzbek Som', rate: 12000, flag: '🇺🇿' }
    },
    isOpen: false,
    formatPrice(price) {
        const currency = this.currencies[this.selectedCurrency];
        const convertedPrice = price * currency.rate;
        
        if (this.selectedCurrency === 'UZS') {
            return new Intl.NumberFormat('uz-UZ').format(Math.round(convertedPrice)) + ' ' + currency.symbol;
        }
        
        return currency.symbol + convertedPrice.toFixed(2);
    },
    selectCurrency(currency) {
        this.selectedCurrency = currency;
        this.isOpen = false;
        // Trigger custom event for other components
        window.dispatchEvent(new CustomEvent('currency-changed', { detail: currency }));
    }
}" @click.away="isOpen = false">
    <!-- Currency Selector Button -->
    <button @click="isOpen = !isOpen" 
            class="flex items-center space-x-2 bg-white/95 backdrop-blur-xl border border-gray-200/40 rounded-xl px-3 py-2 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 group">
        <span class="text-lg" x-text="currencies[selectedCurrency].flag"></span>
        <span class="text-sm font-semibold text-gray-700" x-text="selectedCurrency"></span>
        <svg class="w-3 h-3 text-gray-400 transform transition-transform duration-300" 
             :class="{ 'rotate-180': isOpen }" 
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Currency Dropdown -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
         x-cloak
         class="absolute bottom-full left-0 mb-2 w-48 bg-white/95 backdrop-blur-xl rounded-xl shadow-2xl border border-gray-200/40 py-2 z-50">
        
        <template x-for="(currency, code) in currencies" :key="code">
            <button @click="selectCurrency(code)"
                    class="w-full flex items-center space-x-3 px-4 py-2 text-left hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group"
                    :class="{ 'bg-gradient-to-r from-blue-50 to-indigo-50': selectedCurrency === code }">
                <span class="text-lg" x-text="currency.flag"></span>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200" 
                         x-text="code + ' - ' + currency.name"></div>
                    <div class="text-xs text-gray-500 group-hover:text-blue-600 transition-colors duration-200" 
                         x-text="'Rate: ' + currency.rate"></div>
                </div>
                <div class="w-2 h-2 rounded-full bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                     :class="{ 'opacity-100': selectedCurrency === code }"></div>
            </button>
        </template>
    </div>
</div>

<!-- Currency Conversion Display -->
<div class="fixed bottom-4 right-4 z-40" x-data="{
    showConversion: false,
    currentRate: 1.0,
    currentSymbol: '$',
    init() {
        window.addEventListener('currency-changed', (e) => {
            const currency = e.detail;
            this.currentRate = this.currencies[currency].rate;
            this.currentSymbol = this.currencies[currency].symbol;
            this.showConversion = true;
            setTimeout(() => this.showConversion = false, 2000);
        });
    },
    currencies: {
        'USD': { symbol: '$', rate: 1.0 },
        'EUR': { symbol: '€', rate: 0.85 },
        'GBP': { symbol: '£', rate: 0.73 },
        'UZS': { symbol: 'so\'m', rate: 12000 }
    }
}" 
     x-show="showConversion"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="transform opacity-0 translate-y-4"
     x-transition:enter-end="transform opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="transform opacity-100 translate-y-0"
     x-transition:leave-end="transform opacity-0 translate-y-4"
     x-cloak>
    <div class="bg-white/95 backdrop-blur-xl border border-gray-200/40 rounded-xl p-3 shadow-2xl">
        <div class="text-sm font-semibold text-gray-900 mb-1">Currency Updated</div>
        <div class="text-xs text-gray-600">
            All prices now displayed in <span x-text="currentSymbol"></span>
        </div>
    </div>
</div>

<!-- Price Display Helper -->
<script>
// Global currency conversion function
window.convertPrice = function(price, fromCurrency = 'USD', toCurrency = 'USD') {
    const rates = {
        'USD': 1.0,
        'EUR': 0.85,
        'GBP': 0.73,
        'UZS': 12000
    };
    
    const convertedPrice = price * (rates[toCurrency] / rates[fromCurrency]);
    
    if (toCurrency === 'UZS') {
        return new Intl.NumberFormat('uz-UZ').format(Math.round(convertedPrice)) + ' so\'m';
    }
    
    const symbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'UZS': 'so\'m'
    };
    
    return symbols[toCurrency] + convertedPrice.toFixed(2);
};

// Auto-detect user's currency based on location
document.addEventListener('DOMContentLoaded', function() {
    // This would typically use a geolocation API or IP-based detection
    // For now, we'll default to USD
    const userCurrency = 'USD';
    
    // Dispatch currency change event
    window.dispatchEvent(new CustomEvent('currency-changed', { detail: userCurrency }));
});
</script>
