import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // Umożliwia kontrolowanie ciemnego motywu poprzez klasę 'dark'
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './Modules/**/resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                // ... existing light and dark semantic colors ...
                'light-primary': '#1D4ED8',
                'light-secondary': '#22C55E',
                'light-accent': '#FBBF24',
                'light-background': '#F9FAFB',
                'light-surface': '#FFFFFF',
                'light-error': '#EF4444',
                'light-info': '#3B82F6',
                'light-warning': '#F59E0B',
                'light-text-primary': '#111827',
                'light-text-secondary': '#6B7280',

                // Dark theme base colors (examples, adjust as needed for aesthetic)
                'dark-primary': '#60A5FA', // A lighter blue for dark mode
                'dark-secondary': '#4ADE80', // A lighter green
                'dark-accent': '#FCD34D', // A lighter yellow
                'dark-background': '#111827', // Dark background
                'dark-surface': '#1F2937', // Slightly lighter dark surface
                'dark-error': '#FCA5A5', // Lighter red
                'dark-info': '#93C5FD', // Lighter blue
                'dark-warning': '#FDBA74', // Lighter orange
                'dark-text-primary': '#F9FAFB', // Light text
                'dark-text-secondary': '#9CA3AF', // Lighter grey text

                // Semantic colors that switch based on dark mode
                // These will be used in components, e.g., `bg-background`
                primary: 'var(--color-primary)',
                secondary: 'var(--color-secondary)',
                accent: 'var(--color-accent)',
                background: 'var(--color-background)',
                surface: 'var(--color-surface)',
                error: 'var(--color-error)',
                info: 'var(--color-info)',
                warning: 'var(--color-warning)',
                'text-primary': 'var(--color-text-primary)',
                'text-secondary': 'var(--color-text-secondary)',

                'alert-error': {
                    light: {
                        bg: '#FEE2E2', // red-100 equivalent
                        border: '#FCA5A5', // red-400 equivalent
                        text: '#B91C1C', // red-700 equivalent
                    },
                    dark: {
                        bg: '#450A0A', // dark-red-900 equivalent
                        border: '#7F1D1D', // dark-red-700 equivalent
                        text: '#FCA5A5', // dark-red-300 equivalent
                    }
                },
                'alert-success': {
                    light: {
                        bg: '#DCFCE7', // green-100 equivalent
                        border: '#86EFAC', // green-400 equivalent
                        text: '#16A34A', // green-700 equivalent
                    },
                    dark: {
                        bg: '#064E3B', // dark-green-900 equivalent
                        border: '#047857', // dark-green-700 equivalent
                        text: '#A7F3D0', // dark-green-300 equivalent
                    }
                },
                'alert-warning': {
                    light: {
                        bg: '#FFFBEB', // yellow-100 equivalent
                        border: '#FDE68A', // yellow-400 equivalent
                        text: '#B45309', // yellow-700 equivalent
                    },
                    dark: {
                        bg: '#78350F', // dark-yellow-900 equivalent
                        border: '#B45309', // dark-yellow-700 equivalent
                        text: '#FDE68A', // dark-yellow-300 equivalent
                    }
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                lg: '12px',
                md: '8px'
            }
        },
    },
    plugins: [],
};
