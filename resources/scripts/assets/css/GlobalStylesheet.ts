import tw from 'twin.macro';
import { createGlobalStyle } from 'styled-components/macro';

export default createGlobalStyle`
    body {
        ${tw`font-sans bg-neutral-800 text-neutral-200`};
        letter-spacing: 0;
        min-height: 100vh;
        background:
            radial-gradient(circle at 18% 8%, color-mix(in srgb, var(--primary-color, #c2185b) 24%, transparent) 0, transparent 28rem),
            linear-gradient(135deg, rgba(5, 8, 14, 0.78), rgba(8, 10, 18, 0.9));
        background-attachment: fixed;
        text-rendering: geometricPrecision;
    }

    body::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
        background-size: 48px 48px;
        mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.55), transparent 72%);
    }

    h1, h2, h3, h4, h5, h6 {
        ${tw`font-medium tracking-normal font-header`};
    }

    p {
        ${tw`text-neutral-200 leading-snug font-sans`};
    }

    form {
        ${tw`m-0`};
    }

    textarea, select, input, button, button:focus, button:focus-visible {
        ${tw`outline-none`};
    }

    a, button {
        -webkit-tap-highlight-color: transparent;
    }

    ::selection {
        background: color-mix(in srgb, var(--primary-color, #c2185b) 55%, rgba(255, 255, 255, 0.14));
        color: #fff;
    }

    .fade-appear,
    .fade-enter {
        opacity: 0;
        transform: translateY(8px);
    }

    .fade-appear-active,
    .fade-enter-active {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 180ms ease, transform 220ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .fade-exit {
        opacity: 1;
    }

    .fade-exit-active {
        opacity: 0;
        transition: opacity 120ms ease;
    }

    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield !important;
    }

    /* Scroll Bar Style */
    ::-webkit-scrollbar {
        background: none;
        width: 16px;
        height: 16px;
    }

    ::-webkit-scrollbar-thumb {
        border: solid 0 rgb(0 0 0 / 0%);
        border-right-width: 4px;
        border-left-width: 4px;
        -webkit-border-radius: 9px 4px;
        -webkit-box-shadow: inset 0 0 0 1px hsl(211, 10%, 53%), inset 0 0 0 4px hsl(209deg 18% 30%);
    }

    ::-webkit-scrollbar-track-piece {
        margin: 4px 0;
    }

    ::-webkit-scrollbar-thumb:horizontal {
        border-right-width: 0;
        border-left-width: 0;
        border-top-width: 4px;
        border-bottom-width: 4px;
        -webkit-border-radius: 4px 9px;
    }

    ::-webkit-scrollbar-corner {
        background: transparent;
    }
`;
