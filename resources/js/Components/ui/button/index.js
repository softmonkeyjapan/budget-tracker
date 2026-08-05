import { cva } from 'class-variance-authority';

export { default as Button } from './Button.vue';

export const buttonVariants = cva(
    'inline-flex items-center rounded-control px-5 py-2.5 text-sm font-semibold transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-40 disabled:pointer-events-none',
    {
        variants: {
            variant: {
                primary: 'bg-nav text-white shadow-primary hover:bg-nav/90 focus:ring-nav',
                secondary:
                    'border border-line bg-surface text-ink shadow-pill hover:bg-app focus:ring-nav',
                danger: 'bg-expense text-white hover:bg-expense/90 focus:ring-expense',
            },
        },
        defaultVariants: {
            variant: 'primary',
        },
    },
);
