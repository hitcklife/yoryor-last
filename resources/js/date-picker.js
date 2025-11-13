export default function datePicker() {
    return {
        date: '',
        isOpen: false,
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
        selectedDate: null,
        maxDate: null,
        minDate: new Date('1900-01-01'),
        showYearPicker: false,

        init() {
            // Calculate max date (18 years ago)
            const today = new Date();
            this.maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

            // Set calendar to display 18 years ago when opened (but don't prefill)
            const defaultCalendarDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
            this.currentMonth = defaultCalendarDate.getMonth();
            this.currentYear = defaultCalendarDate.getFullYear();

            // Check for existing value only
            this.$nextTick(() => {
                if (this.$wire) {
                    const initialValue = this.$wire.get('dateOfBirth');
                    if (initialValue) {
                        this.date = initialValue;
                        this.selectedDate = new Date(initialValue);
                        // Update calendar display to show the selected date's month/year
                        this.currentMonth = this.selectedDate.getMonth();
                        this.currentYear = this.selectedDate.getFullYear();
                    }
                    // Don't set any default date in Livewire
                }
            });
        },

        get daysInMonth() {
            return new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
        },

        get firstDayOfMonth() {
            return new Date(this.currentYear, this.currentMonth, 1).getDay();
        },

        get monthName() {
            return new Date(this.currentYear, this.currentMonth).toLocaleDateString('en-US', { month: 'long' });
        },

        get year() {
            return this.currentYear;
        },

        get availableYears() {
            const years = [];
            const minYear = this.minDate.getFullYear();
            const maxYear = this.maxDate.getFullYear();
            
            for (let year = maxYear; year >= minYear; year--) {
                years.push(year);
            }
            return years;
        },

        get days() {
            const days = [];
            const daysInMonth = this.daysInMonth;
            const firstDay = this.firstDayOfMonth;

            // Add empty cells for days before the first day of the month
            for (let i = 0; i < firstDay; i++) {
                days.push(null);
            }

            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(this.currentYear, this.currentMonth, day);
                const isSelected = this.selectedDate && this.isSameDate(date, this.selectedDate);
                const isToday = this.isSameDate(date, new Date());
                const isDisabled = this.isDateDisabled(date);
                
                days.push({
                    day,
                    date,
                    isSelected,
                    isToday,
                    isDisabled
                });
            }

            return days;
        },

        isSameDate(date1, date2) {
            return date1.getFullYear() === date2.getFullYear() &&
                   date1.getMonth() === date2.getMonth() &&
                   date1.getDate() === date2.getDate();
        },

        isDateDisabled(date) {
            return date > this.maxDate || date < this.minDate;
        },

        selectDate(dayObj) {
            if (dayObj.isDisabled) return;

            this.selectedDate = dayObj.date;
            this.date = this.formatDate(dayObj.date);
            
            // Update Livewire property
            if (this.$wire) {
                this.$wire.set('dateOfBirth', this.date);
                this.$wire.call('updatedDateOfBirth');
            }
            
            this.isOpen = false;
        },

        formatDate(date) {
            return date.toISOString().split('T')[0];
        },

        formatDisplayDate(date) {
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        },

        openCalendar() {
            // Navigate to the selected date or default date (18 years ago) when opening
            if (this.selectedDate) {
                this.currentMonth = this.selectedDate.getMonth();
                this.currentYear = this.selectedDate.getFullYear();
            } else {
                // Navigate to 18 years ago as default
                const defaultDate = new Date(new Date().getFullYear() - 18, new Date().getMonth(), new Date().getDate());
                this.currentMonth = defaultDate.getMonth();
                this.currentYear = defaultDate.getFullYear();
            }
            this.isOpen = true;
        },

        closeCalendar() {
            this.isOpen = false;
        },

        clearDate() {
            this.date = '';
            this.selectedDate = null;
            if (this.$wire) {
                this.$wire.set('dateOfBirth', '');
                this.$wire.call('updatedDateOfBirth');
            }
        },

        previousMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },

        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
        },

        selectYear(year) {
            this.currentYear = year;
            this.showYearPicker = false;
        },

        get displayValue() {
            if (this.selectedDate) {
                return this.formatDisplayDate(this.selectedDate);
            }
            return '';
        }
    }
}
