# Yoryor Dating App - Design System

## Overview
This document outlines the comprehensive design system for the Yoryor Dating App, ensuring consistent UI/UX across all components and pages.

## Color Palette

### Primary Colors
- **Purple**: `#7C3AED` (Primary brand color)
- **Pink**: `#EC4899` (Secondary accent)
- **Blue**: `#3B82F6` (Info/links)
- **Green**: `#10B981` (Success)
- **Yellow**: `#F59E0B` (Warning)
- **Red**: `#EF4444` (Danger/Error)

### Neutral Colors
- **Gray Scale**: `#F9FAFB` to `#111827`
- **Zinc Scale**: `#FAFAFA` to `#09090B` (Dark mode)

## Typography

### Font Families
- **Primary**: Inter (Sans-serif)
- **Fallback**: system-ui, -apple-system, sans-serif

### Font Sizes
- **xs**: 0.75rem (12px)
- **sm**: 0.875rem (14px)
- **base**: 1rem (16px)
- **lg**: 1.125rem (18px)
- **xl**: 1.25rem (20px)
- **2xl**: 1.5rem (24px)
- **3xl**: 1.875rem (30px)
- **4xl**: 2.25rem (36px)

### Font Weights
- **Light**: 300
- **Normal**: 400
- **Medium**: 500
- **Semibold**: 600
- **Bold**: 700

## Spacing System

### Base Unit: 4px
- **xs**: 0.25rem (4px)
- **sm**: 0.5rem (8px)
- **md**: 1rem (16px)
- **lg**: 1.5rem (24px)
- **xl**: 2rem (32px)
- **2xl**: 3rem (48px)
- **3xl**: 4rem (64px)

## Component Library

### Buttons

#### Variants
- **Primary**: Purple background, white text
- **Secondary**: Gray background, white text
- **Success**: Green background, white text
- **Danger**: Red background, white text
- **Warning**: Yellow background, white text
- **Info**: Blue background, white text
- **Outline**: Transparent background, colored border
- **Ghost**: Transparent background, colored text
- **Link**: Underlined text, no background

#### Sizes
- **xs**: `px-2.5 py-1.5 text-xs`
- **sm**: `px-3 py-2 text-sm`
- **md**: `px-4 py-2 text-sm` (default)
- **lg**: `px-4 py-2 text-base`
- **xl**: `px-6 py-3 text-base`

#### Usage
```blade
<x-ui.button variant="primary" size="md">Click me</x-ui.button>
<x-ui.button variant="outline" size="sm" icon="M12 4v16m8-8H4">Save</x-ui.button>
```

### Inputs

#### Types
- **Text**: Standard text input
- **Email**: Email validation
- **Password**: Hidden text
- **Number**: Numeric input
- **Tel**: Phone number
- **Search**: Search input with icon

#### States
- **Default**: Gray border
- **Focus**: Purple border with ring
- **Error**: Red border with error message
- **Disabled**: Grayed out, non-interactive

#### Usage
```blade
<x-ui.input 
    type="email" 
    label="Email Address" 
    placeholder="Enter your email"
    required 
/>
```

### Cards

#### Variants
- **Default**: White background, subtle shadow
- **Elevated**: Stronger shadow
- **Flat**: No shadow
- **Gradient**: Purple gradient background
- **Success**: Green accent
- **Warning**: Yellow accent
- **Danger**: Red accent
- **Info**: Blue accent

#### Padding Options
- **none**: No padding
- **sm**: `p-4`
- **md**: `p-6` (default)
- **lg**: `p-8`
- **xl**: `p-10`

#### Usage
```blade
<x-ui.card variant="gradient" padding="lg">
    <h3>Card Title</h3>
    <p>Card content goes here.</p>
</x-ui.card>
```

### Badges

#### Variants
- **Default**: Gray background
- **Primary**: Purple background
- **Success**: Green background
- **Warning**: Yellow background
- **Danger**: Red background
- **Info**: Blue background
- **Outline**: Transparent background, colored border

#### Sizes
- **xs**: `px-2 py-0.5 text-xs`
- **sm**: `px-2.5 py-1 text-xs`
- **md**: `px-3 py-1 text-sm` (default)
- **lg**: `px-4 py-1.5 text-sm`

#### Usage
```blade
<x-ui.badge variant="success" size="sm">Active</x-ui.badge>
<x-ui.badge variant="primary" removable>Tag</x-ui.badge>
```

### Alerts

#### Variants
- **Success**: Green background with check icon
- **Warning**: Yellow background with warning icon
- **Danger**: Red background with error icon
- **Info**: Blue background with info icon

#### Features
- **Dismissible**: Close button
- **Icon**: Optional icon display
- **Custom content**: Flexible content area

#### Usage
```blade
<x-ui.alert variant="success" dismissible>
    <strong>Success!</strong> Your profile has been updated.
</x-ui.alert>
```

## Layout System

### Grid System
- **Mobile**: 1 column
- **Tablet**: 2 columns
- **Desktop**: 3-4 columns
- **Large**: 4-6 columns

### Breakpoints
- **sm**: 640px
- **md**: 768px
- **lg**: 1024px
- **xl**: 1280px
- **2xl**: 1536px

### Container Sizes
- **sm**: `max-w-2xl`
- **md**: `max-w-4xl`
- **lg**: `max-w-6xl`
- **xl**: `max-w-7xl`
- **full**: `max-w-full`

## Dark Mode Support

### Implementation
- Uses `dark:` prefix for dark mode styles
- Automatic switching based on user preference
- Consistent color mapping between light and dark themes

### Color Mapping
- **Light Gray** → **Dark Zinc**
- **White** → **Zinc-800**
- **Black** → **White**
- **Purple** → **Purple with opacity**

## Animation & Transitions

### Standard Transitions
- **Duration**: 200ms (fast), 300ms (normal), 500ms (slow)
- **Easing**: `ease-in-out` for most transitions
- **Hover**: Scale transforms (1.02x)
- **Active**: Scale transforms (0.98x)

### Loading States
- **Spinner**: Rotating circle animation
- **Skeleton**: Shimmer effect for loading content
- **Progress**: Linear progress bars

## Accessibility

### Color Contrast
- **AA Standard**: 4.5:1 for normal text
- **AAA Standard**: 7:1 for large text
- **Focus States**: High contrast focus rings

### Keyboard Navigation
- **Tab Order**: Logical tab sequence
- **Focus Indicators**: Visible focus states
- **Skip Links**: Navigation shortcuts

### Screen Reader Support
- **ARIA Labels**: Descriptive labels
- **Semantic HTML**: Proper heading hierarchy
- **Alt Text**: Image descriptions

## Usage Guidelines

### Do's
- ✅ Use consistent spacing (4px grid)
- ✅ Maintain color hierarchy
- ✅ Include loading states
- ✅ Provide error feedback
- ✅ Use semantic HTML
- ✅ Test with keyboard navigation

### Don'ts
- ❌ Mix different button styles unnecessarily
- ❌ Use low contrast color combinations
- ❌ Skip loading states for async operations
- ❌ Ignore accessibility requirements
- ❌ Use inline styles
- ❌ Forget dark mode support

## Implementation Status

### ✅ Completed Components
- [x] Button component with all variants
- [x] Input component with validation states
- [x] Card component with multiple variants
- [x] Badge component with removable option
- [x] Alert component with dismissible option

### 🔄 In Progress
- [ ] Modal component
- [ ] Dropdown component
- [ ] Tabs component
- [ ] Progress component
- [ ] Tooltip component

### 📋 Planned Components
- [ ] Data table component
- [ ] Form components
- [ ] Navigation components
- [ ] Media components
- [ ] Layout components

## File Structure

```
resources/views/components/ui/
├── button.blade.php
├── input.blade.php
├── card.blade.php
├── badge.blade.php
├── alert.blade.php
├── modal.blade.php (planned)
├── dropdown.blade.php (planned)
├── tabs.blade.php (planned)
└── progress.blade.php (planned)
```

## Best Practices

### Component Usage
1. Always use the design system components
2. Prefer composition over customization
3. Use semantic HTML elements
4. Include proper ARIA attributes
5. Test across different screen sizes

### Performance
1. Minimize CSS bundle size
2. Use CSS custom properties for theming
3. Implement lazy loading for heavy components
4. Optimize images and assets

### Maintenance
1. Keep components focused and single-purpose
2. Document component APIs
3. Version control design system changes
4. Regular accessibility audits

---

This design system ensures consistency, accessibility, and maintainability across the entire Yoryor Dating App. All components follow the established patterns and can be easily customized while maintaining design coherence.
