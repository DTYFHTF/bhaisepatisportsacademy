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
        canvas: '#FFF8F5',
        surface: '#FFF2EC',
        border: '#F2D9CC',
        ink: {
          DEFAULT: '#1A0F0A',
          muted: '#7A5C52',
          faint: '#B09A94',
        },
        accent: {
          DEFAULT: '#E8967A',
          hover: '#D4805F',
        },
        peach: {
          50: '#FFF8F5',
          100: '#FFE8DC',
          200: '#FFD0B8',
          300: '#FFAC89',
          400: '#F5845A',
          500: '#E8967A',
          600: '#D4805F',
          700: '#B86040',
          800: '#8F4A2E',
          900: '#6B3621',
        },
        blush: {
          light: '#FDE8E8',
          DEFAULT: '#F9CACA',
          dark: '#F0A0A0',
        },
        gold: {
          light: '#FFF3CD',
          DEFAULT: '#F5C842',
          dark: '#D4A017',
        },
        success: '#4A6741',
        error: '#C0392B',
        warning: '#D4A843',
        overlay: 'rgba(26, 15, 10, 0.5)',
        'overlay-light': 'rgba(26, 15, 10, 0.2)',
      },
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
        serif: ['Playfair Display', ...defaultTheme.fontFamily.serif],
      },
      fontSize: {
        '2xs': ['0.6875rem', { lineHeight: '1' }],        // 11px
        xs: ['0.75rem', { lineHeight: '1.4' }],            // 12px
        sm: ['0.875rem', { lineHeight: '1.5' }],           // 14px
        base: ['1rem', { lineHeight: '1.5' }],             // 16px
        lg: ['1.125rem', { lineHeight: '1.5' }],           // 18px
        xl: ['1.25rem', { lineHeight: '1.3' }],            // 20px
        '2xl': ['1.5rem', { lineHeight: '1.3' }],          // 24px
        '3xl': ['2rem', { lineHeight: '1.2' }],            // 32px
        '4xl': ['3rem', { lineHeight: '1.1' }],            // 48px
        '5xl': ['4rem', { lineHeight: '1.1' }],            // 64px
        '6xl': ['5.25rem', { lineHeight: '1.05' }],        // 84px
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
    },
  },
  plugins: [],
}
