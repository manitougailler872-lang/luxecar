/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './templates/**/*.html.twig',
        './assets/**/*.{js,css}',
        './src/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                'bg-A': '#0B8A3D',
                'bg-B': '#4CAF39',
                'bg-C': '#9DC81F',
                'bg-D': '#F7E000',
                'bg-E': '#F5A623',
                'bg-F': '#E8590C',
                'bg-G': '#D32F2F',
                'luxe-black':     '#0b0b0c',
                'luxe-black-alt': '#121214',
                'luxe-card':      '#17171a',
                'luxe-border':    '#2a2a2e',
                'luxe-orange':      '#E5AD3D',   // NEW brand accent (per client)
                'luxe-orange-dark': '#C98800',
                'luxe-orange-soft': 'rgba(236, 90, 19, 0.12)',
                // Aliases kept for backward-compat during transition
                'luxe-gold':      '#E5AD3D',
                'luxe-gold-dark': '#C98800',
                'luxe-text':      '#f3f3f2',
                'luxe-muted':     '#9a9a9e',
                'luxe-faint':     '#6b6b70',
            },
            fontFamily: {
                sans:    ['Inter', 'system-ui', 'sans-serif'],
                display: ['"Space Grotesk"', 'Inter', 'system-ui', 'sans-serif'],
                mono:    ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
            },
            borderRadius: {
                'xl': '0.9rem',
                '2xl': '1.25rem',
            },
            keyframes: {
                fadeIn:   { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
                fadeInUp: { '0%': { opacity: 0, transform: 'translateY(20px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
                confettiFall: {
                    '0%':   { transform: 'translateY(-10vh) rotate(0deg)', opacity: 1 },
                    '100%': { transform: 'translateY(110vh) rotate(720deg)', opacity: 0 },
                },
                marquee: {
                  '0%': { transform: 'translateX(0%)' },
                  '100%': { transform: 'translateX(-50%)' },
                },
                'cta-pulse': {
                  '0%, 100%': { boxShadow: '0 0 0 0 rgba(229, 173, 61, 0.5)' },
                  '50%': { boxShadow: '0 0 0 12px rgba(229, 173, 61, 0)' },
                },
            },
            animation: {
                marquee: 'marquee 22s linear infinite',
                'cta-pulse': 'cta-pulse 2.2s ease-out infinite',
                'fade-in':      'fadeIn .6s ease-out both',
                'fade-in-up':   'fadeInUp .8s ease-out both',
                'confetti-fall': 'confettiFall 2.5s linear infinite',
            },
        },
    },
    plugins: [],
}
