import styled from 'styled-components/macro';
import tw from 'twin.macro';

export default styled.div<{ $hoverable?: boolean }>`
    ${tw`flex rounded no-underline text-neutral-200 items-center p-4 border transition-colors duration-150 overflow-hidden`};
    background: linear-gradient(135deg, rgba(17, 24, 39, 0.86), rgba(5, 8, 14, 0.74));
    border-color: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.22);
    backdrop-filter: blur(16px);

    ${(props) => props.$hoverable !== false && tw`hover:border-neutral-500`};
    ${(props) =>
        props.$hoverable !== false &&
        `
            &:hover {
                border-color: color-mix(in srgb, var(--primary-color) 48%, rgba(255, 255, 255, 0.18));
                box-shadow: 0 22px 55px rgba(0, 0, 0, 0.28), 0 0 0 1px color-mix(in srgb, var(--primary-color) 20%, transparent);
            }
        `};

    & .icon {
        ${tw`rounded-full w-16 flex items-center justify-center p-3`};
        background: color-mix(in srgb, var(--primary-color) 22%, rgba(255, 255, 255, 0.08));
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
    }
`;
