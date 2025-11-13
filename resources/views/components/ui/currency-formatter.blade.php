@props([
    'amount' => 0,
    'currency' => null,
    'locale' => null,
    'showSymbol' => true,
    'precision' => 2
])

@php
$currency = $currency ?? config('app.currency', 'USD');
$locale = $locale ?? app()->getLocale();

// Currency configurations
$currencyConfigs = [
    'USD' => ['symbol' => '$', 'code' => 'USD', 'name' => 'US Dollar'],
    'EUR' => ['symbol' => '€', 'code' => 'EUR', 'name' => 'Euro'],
    'GBP' => ['symbol' => '£', 'code' => 'GBP', 'name' => 'British Pound'],
    'UZS' => ['symbol' => 'so\'m', 'code' => 'UZS', 'name' => 'Uzbek Som'],
    'RUB' => ['symbol' => '₽', 'code' => 'RUB', 'name' => 'Russian Ruble']
];

$currencyConfig = $currencyConfigs[$currency] ?? $currencyConfigs['USD'];
@endphp

<span x-data="currencyFormatter()" 
      x-init="init({{ $amount }}, '{{ $currency }}', '{{ $locale }}', {{ $showSymbol ? 'true' : 'false' }}, {{ $precision }})"
      x-text="formattedAmount"
      {{ $attributes }}>
    {{ $showSymbol ? $currencyConfig['symbol'] : '' }}{{ number_format($amount, $precision) }}
</span>

@push('scripts')
<script>
    function currencyFormatter() {
        return {
            formattedAmount: '',
            amount: 0,
            currency: 'USD',
            locale: 'en',
            showSymbol: true,
            precision: 2,
            
            init(amount, currency, locale, showSymbol, precision) {
                this.amount = amount;
                this.currency = currency;
                this.locale = locale;
                this.showSymbol = showSymbol;
                this.precision = precision;
                
                this.updateDisplay();
            },
            
            updateDisplay() {
                try {
                    if (this.showSymbol) {
                        // Use Intl.NumberFormat for proper currency formatting
                        this.formattedAmount = new Intl.NumberFormat(this.locale, {
                            style: 'currency',
                            currency: this.currency,
                            minimumFractionDigits: this.precision,
                            maximumFractionDigits: this.precision
                        }).format(this.amount);
                    } else {
                        // Format as number only
                        this.formattedAmount = new Intl.NumberFormat(this.locale, {
                            minimumFractionDigits: this.precision,
                            maximumFractionDigits: this.precision
                        }).format(this.amount);
                    }
                } catch (error) {
                    console.error('Currency formatting error:', error);
                    // Fallback formatting
                    this.formattedAmount = this.getFallbackFormat();
                }
            },
            
            getFallbackFormat() {
                const formattedNumber = this.amount.toFixed(this.precision);
                
                if (this.showSymbol) {
                    const symbols = {
                        'USD': '$',
                        'EUR': '€',
                        'GBP': '£',
                        'UZS': 'so\'m',
                        'RUB': '₽'
                    };
                    
                    const symbol = symbols[this.currency] || this.currency;
                    
                    // Handle different symbol positions based on locale
                    if (this.locale === 'ar' || this.locale === 'he') {
                        return `${formattedNumber} ${symbol}`;
                    } else {
                        return `${symbol}${formattedNumber}`;
                    }
                } else {
                    return formattedNumber;
                }
            }
        }
    }
</script>
@endpush
