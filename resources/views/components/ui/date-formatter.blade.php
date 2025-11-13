@props([
    'date' => null,
    'format' => 'relative',
    'timezone' => null,
    'locale' => null
])

@php
$date = $date ?? now();
$timezone = $timezone ?? config('app.timezone');
$locale = $locale ?? app()->getLocale();

// Format options
$formats = [
    'relative' => 'relative',
    'short' => 'short',
    'medium' => 'medium',
    'long' => 'long',
    'time' => 'time',
    'date' => 'date',
    'datetime' => 'datetime'
];
@endphp

<span x-data="dateFormatter()" 
      x-init="init('{{ $date->toISOString() }}', '{{ $format }}', '{{ $timezone }}', '{{ $locale }}')"
      x-text="formattedDate"
      {{ $attributes }}>
    {{ $date->format('M j, Y') }}
</span>

@push('scripts')
<script>
    function dateFormatter() {
        return {
            formattedDate: '',
            date: null,
            format: 'relative',
            timezone: 'UTC',
            locale: 'en',
            
            init(dateString, format, timezone, locale) {
                this.date = new Date(dateString);
                this.format = format;
                this.timezone = timezone;
                this.locale = locale;
                
                this.updateDisplay();
                
                // Update relative times every minute
                if (format === 'relative') {
                    setInterval(() => this.updateDisplay(), 60000);
                }
            },
            
            updateDisplay() {
                try {
                    switch (this.format) {
                        case 'relative':
                            this.formattedDate = this.getRelativeTime();
                            break;
                        case 'short':
                            this.formattedDate = this.date.toLocaleDateString(this.locale, {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                            break;
                        case 'medium':
                            this.formattedDate = this.date.toLocaleDateString(this.locale, {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                            break;
                        case 'long':
                            this.formattedDate = this.date.toLocaleDateString(this.locale, {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                weekday: 'long'
                            });
                            break;
                        case 'time':
                            this.formattedDate = this.date.toLocaleTimeString(this.locale, {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            break;
                        case 'date':
                            this.formattedDate = this.date.toLocaleDateString(this.locale);
                            break;
                        case 'datetime':
                            this.formattedDate = this.date.toLocaleString(this.locale, {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            break;
                        default:
                            this.formattedDate = this.date.toLocaleDateString(this.locale);
                    }
                } catch (error) {
                    console.error('Date formatting error:', error);
                    this.formattedDate = this.date.toLocaleDateString();
                }
            },
            
            getRelativeTime() {
                const now = new Date();
                const diffInSeconds = Math.floor((now - this.date) / 1000);
                
                if (diffInSeconds < 60) {
                    return this.locale === 'ar' ? 'الآن' : 'Just now';
                }
                
                const diffInMinutes = Math.floor(diffInSeconds / 60);
                if (diffInMinutes < 60) {
                    return this.getRelativeText(diffInMinutes, 'minute');
                }
                
                const diffInHours = Math.floor(diffInMinutes / 60);
                if (diffInHours < 24) {
                    return this.getRelativeText(diffInHours, 'hour');
                }
                
                const diffInDays = Math.floor(diffInHours / 24);
                if (diffInDays < 7) {
                    return this.getRelativeText(diffInDays, 'day');
                }
                
                const diffInWeeks = Math.floor(diffInDays / 7);
                if (diffInWeeks < 4) {
                    return this.getRelativeText(diffInWeeks, 'week');
                }
                
                const diffInMonths = Math.floor(diffInDays / 30);
                if (diffInMonths < 12) {
                    return this.getRelativeText(diffInMonths, 'month');
                }
                
                const diffInYears = Math.floor(diffInDays / 365);
                return this.getRelativeText(diffInYears, 'year');
            },
            
            getRelativeText(value, unit) {
                const translations = {
                    'en': {
                        minute: ['minute ago', 'minutes ago'],
                        hour: ['hour ago', 'hours ago'],
                        day: ['day ago', 'days ago'],
                        week: ['week ago', 'weeks ago'],
                        month: ['month ago', 'months ago'],
                        year: ['year ago', 'years ago']
                    },
                    'ru': {
                        minute: ['минуту назад', 'минуты назад', 'минут назад'],
                        hour: ['час назад', 'часа назад', 'часов назад'],
                        day: ['день назад', 'дня назад', 'дней назад'],
                        week: ['неделю назад', 'недели назад', 'недель назад'],
                        month: ['месяц назад', 'месяца назад', 'месяцев назад'],
                        year: ['год назад', 'года назад', 'лет назад']
                    },
                    'uz': {
                        minute: ['daqiqa oldin', 'daqiqa oldin'],
                        hour: ['soat oldin', 'soat oldin'],
                        day: ['kun oldin', 'kun oldin'],
                        week: ['hafta oldin', 'hafta oldin'],
                        month: ['oy oldin', 'oy oldin'],
                        year: ['yil oldin', 'yil oldin']
                    }
                };
                
                const localeTranslations = translations[this.locale] || translations['en'];
                const unitTranslations = localeTranslations[unit];
                
                if (!unitTranslations) {
                    return `${value} ${unit} ago`;
                }
                
                // Handle different plural forms for Russian
                if (this.locale === 'ru' && unitTranslations.length === 3) {
                    if (value === 1) return unitTranslations[0];
                    if (value >= 2 && value <= 4) return unitTranslations[1];
                    return unitTranslations[2];
                }
                
                // Handle English and other languages
                return value === 1 ? unitTranslations[0] : unitTranslations[1];
            }
        }
    }
</script>
@endpush
