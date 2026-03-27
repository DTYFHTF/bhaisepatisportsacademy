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
        // Dark athletic base
        canvas: '#0A0A0F',        // Near-black background
        surface: '#12121A',       // Card/section surface
        'surface-2': '#1A1A26',   // Elevated surface
        'surface-3': '#22222E',   // Hover surface
        border: '#2A2A3A',        // Subtle borders
        'border-light': '#3A3A4A',

        // Text
        ink: {
          DEFAULT: '#F5F5F7',     // Primary text (white-ish)
          muted: '#9A9AB0',       // Secondary text
          faint: '#5A5A70',       // Tertiary text
        },

        // BSA Yellow — from the logo
        accent: {
          DEFAULT: '#FFB800',     // Golden yellow
          hover: '#E5A600',
          light: '#FFD54F',
          dark: '#CC9200',
          50: '#FFF8E1',
          100: '#FFECB3',
          200: '#FFE082',
          300: '#FFD54F',
          400: '#FFC107',
          500: '#FFB800',
          600: '#E5A600',
          700: '#CC9200',
          800: '#997000',
          900: '#664A00',
        },

        // Energetic red for CTAs and emphasis
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
        'grid-pattern': 'linear-gradient(rgba(255,184,0,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,184,0,0.03) 1px, transparent 1px)',
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
          '0%, 100%': { boxShadow: '0 0 20px rgba(255, 184, 0, 0.15)' },
          '50%': { boxShadow: '0 0 40px rgba(255, 184, 0, 0.3)' },
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
      },
      animation: {
        'shuttle-fly': 'shuttle-fly 3s ease-in-out infinite',
        'pulse-glow': 'pulse-glow 3s ease-in-out infinite',
        'float': 'float 3s ease-in-out infinite',
        'slide-up': 'slide-up 0.5s ease-out',
        'count-up': 'count-up 0.8s ease-out',
      },
    },
  },
  plugins: [],
}
