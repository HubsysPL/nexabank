import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
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
                primary: '#1D4ED8',
                secondary: '#22C55E',
                accent: '#FBBF24',
                background: '#F9FAFB',
                surface: '#FFFFFF',
                error: '#EF4444',
                info: '#3B82F6',
                warning: '#F59E0B',
                'text-primary': '#111827',
                'text-secondary': '#6B7280',
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
