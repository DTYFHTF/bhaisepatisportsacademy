import type { Config } from 'tailwindcss'
import defaultTheme from 'tailwindcss/defaultTheme'

export default <Config>{
  content: [
    './app/**/*.{vue,ts}',
    './components/**/*.{vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './composables/**/*.ts',
    './plugins/**/*.ts',
  ],
  theme: {
    screens: {
      sm: '480px',
      md: '768px',
      lg: '1024px',
      xl: '1280px',
      '2xl': '1440px',
    },
    extend: {
      colors: {
        // --- BSA Sports Academy Palette ---
        // White energetic base
        canvas: '#FFFFFF',        // Pure white background
        surface: '#F7F7F9',       // Card/section surface
        'surface-2': '#EFEFF3',   // Elevated surface
        'surface-3': '#E7E7EE',   // Hover surface
        border: '#DCDCE6',        // Subtle borders
        'border-light': '#EAEAF0',

        // Text
        ink: {
          DEFAULT: '#0A0A0F',     // Primary text (near-black)
          muted: '#4A4A65',       // Secondary text
          faint: '#8A8AA5',       // Tertiary text
        },

        // BSA Red — energetic primary brand color
        accent: {
          DEFAULT: '#E8001E',     // Crimson red
          hover: '#CC0019',
          light: '#FF4D5E',
          dark: '#A80016',
          50: '#FFF0F2',
          100: '#FFD6DC',
          200: '#FFB3BC',
          300: '#FF8090',
          400: '#FF4D5E',
          500: '#E8001E',
          600: '#CC0019',
          700: '#A80016',
          800: '#840012',
          900: '#60000D',
        },

        // Secondary red — warmer tone for variation
        energy: {
          DEFAULT: '#FF3B3B',
          hover: '#E52E2E',
          light: '#FF6B6B',
          dark: '#CC2E2E',
        },

        // Court blue — badminton court color
        court: {
          DEFAULT: '#1E90FF',
          light: '#4DA6FF',
          dark: '#0066CC',
        },

        // Utility
        success: '#22C55E',
        error: '#EF4444',
        warning: '#F59E0B',
        overlay: 'rgba(0, 0, 0, 0.7)',
        'overlay-light': 'rgba(0, 0, 0, 0.4)',
      },
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
        display: ['Bebas Neue', 'Inter', ...defaultTheme.fontFamily.sans],
      },
      fontSize: {
        '2xs': ['0.6875rem', { lineHeight: '1' }],
        xs: ['0.75rem', { lineHeight: '1.4' }],
        sm: ['0.875rem', { lineHeight: '1.5' }],
        base: ['1rem', { lineHeight: '1.5' }],
        lg: ['1.125rem', { lineHeight: '1.5' }],
        xl: ['1.25rem', { lineHeight: '1.3' }],
        '2xl': ['1.5rem', { lineHeight: '1.3' }],
        '3xl': ['2rem', { lineHeight: '1.2' }],
        '4xl': ['3rem', { lineHeight: '1.1' }],
        '5xl': ['4rem', { lineHeight: '1.05' }],
        '6xl': ['5.25rem', { lineHeight: '1' }],
        '7xl': ['6rem', { lineHeight: '1' }],
        '8xl': ['8rem', { lineHeight: '0.9' }],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
      },
      borderRadius: {
        sm: '4px',
        DEFAULT: '8px',
        md: '12px',
        lg: '16px',
        xl: '20px',
        '2xl': '28px',
      },
      transitionDuration: {
        fast: '120ms',
        base: '200ms',
        slow: '320ms',
        enter: '280ms',
        exit: '180ms',
      },
      transitionTimingFunction: {
        'ease-out': 'cubic-bezier(0.16, 1, 0.3, 1)',
        'ease-in': 'cubic-bezier(0.55, 0, 1, 0.45)',
        spring: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
      },
      aspectRatio: {
        '4/5': '4 / 5',
        '3/4': '3 / 4',
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'grid-pattern': 'linear-gradient(rgba(232,0,30,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(232,0,30,0.04) 1px, transparent 1px)',
      },
      backgroundSize: {
        'grid': '40px 40px',
      },
      keyframes: {
        'shuttle-fly': {
          '0%': { transform: 'translateX(-100%) translateY(50%) rotate(-30deg)', opacity: '0' },
          '20%': { opacity: '1' },
          '100%': { transform: 'translateX(100vw) translateY(-100%) rotate(-30deg)', opacity: '0' },
        },
        'pulse-glow': {
          '0%, 100%': { boxShadow: '0 0 20px rgba(232, 0, 30, 0.15)' },
          '50%': { boxShadow: '0 0 40px rgba(232, 0, 30, 0.3)' },
        },
        'float': {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        'slide-up': {
          '0%': { transform: 'translateY(20px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        'count-up': {
          '0%': { transform: 'translateY(100%)' },
          '100%': { transform: 'translateY(0)' },
        },
        'scroll-x': {
          '0%': { transform: 'translateX(0)' },
          '100%': { transform: 'translateX(-50%)' },
        },
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        'fade-in-up': {
          '0%': { opacity: '0', transform: 'translateY(30px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
      },
      animation: {
        'shuttle-fly': 'shuttle-fly 3s ease-in-out infinite',
        'pulse-glow': 'pulse-glow 3s ease-in-out infinite',
        'float': 'float 3s ease-in-out infinite',
        'slide-up': 'slide-up 0.5s ease-out',
        'count-up': 'count-up 0.8s ease-out',
        'scroll-x': 'scroll-x 30s linear infinite',
      },
    },
  },
  plugins: [],
}
