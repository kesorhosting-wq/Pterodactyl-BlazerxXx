document.addEventListener('DOMContentLoaded', function() {
    // Number of elements to display
    const elementCount = 20;

    // Unicode characters for bats and pumpkins
    const halloweenCharacters = ['🦇', '🎃'];

    // Function to create a falling element
    function createFallingElement() {
        const element = document.createElement('div');
        element.classList.add('falling-element');
        element.innerText = halloweenCharacters[Math.floor(Math.random() * halloweenCharacters.length)];

        // Random horizontal position
        element.style.left = Math.random() * 100 + 'vw';

        // Random animation duration
        const duration = Math.random() * 5 + 5; // Duration between 5 and 10 seconds
        element.style.animationDuration = duration + 's';

        // Random size
        const size = Math.random() * 0.5 + 0.5; // Size between 0.5em and 1em
        element.style.fontSize = size + 'em';

        // Random end positions for animation
        element.style.setProperty('--end-x', (Math.random() * 50 - 25) + 'vw'); // Drift left or right
        element.style.setProperty('--end-y', '100vh'); // Fall to the bottom of the viewport

        document.body.appendChild(element);

        // Remove the element after it falls
        setTimeout(() => {
            element.remove();
        }, duration * 1000);
    }

    // Create falling elements at intervals
    setInterval(createFallingElement, 200);

    // Create initial falling elements
    for (let i = 0; i < elementCount; i++) {
        setTimeout(createFallingElement, i * 200);
    }
});