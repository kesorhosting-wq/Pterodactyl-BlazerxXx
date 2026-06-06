document.addEventListener('DOMContentLoaded', function() {
    // Number of Easter elements to display initially
    const EasterCount = 20;

    // The emojis to randomly choose from
    const icons = ['🥚', '🐰'];

    // Function to create a single Easter element
    function createEaster() {
        const Easter = document.createElement('div');
        Easter.classList.add('Easter');

        // Randomly pick an emoji (egg or bunny)
        Easter.innerText = icons[Math.floor(Math.random() * icons.length)];

        // Random horizontal position
        Easter.style.left = Math.random() * 100 + 'vw';

        // Random animation duration (between 5s and 15s)
        const duration = Math.random() * 10 + 5;
        Easter.style.animationDuration = duration + 's';

        // Random size (fontSize)
        const size = Math.random() * 1.5 + 0.5;
        Easter.style.fontSize = size + 'em';

        // Random end positions for the animation
        Easter.style.setProperty('--Easter-end-x', (Math.random() * 100 - 50) + 'vw');
        Easter.style.setProperty('--Easter-end-y', '100vh');

        document.body.appendChild(Easter);

        // Remove the Easter element after it finishes falling
        setTimeout(() => {
            Easter.remove();
        }, duration * 1000);
    }

    // Create Easter elements at intervals (one every 200ms)
    setInterval(createEaster, 200);

    // Create initial Easter elements
    for (let i = 0; i < EasterCount; i++) {
        setTimeout(createEaster, i * 200);
    }
});