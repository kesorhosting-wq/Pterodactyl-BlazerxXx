document.addEventListener('DOMContentLoaded', function() {
    // Number of snowflakes to display
    const snowflakeCount = 20;

    // Function to create a snowflake element
    function createSnowflake() {
        const snowflake = document.createElement('div');
        snowflake.classList.add('snowflake');
        snowflake.innerText = '❄'; // You can use other snowflake characters or images

        // Random horizontal position
        snowflake.style.left = Math.random() * 100 + 'vw';

        // Random animation duration
        const duration = Math.random() * 10 + 5;
        snowflake.style.animationDuration = duration + 's';

        // Random size
        const size = Math.random() * 1.5 + 0.5;
        snowflake.style.fontSize = size + 'em';

        // Random end positions for animation
        snowflake.style.setProperty('--snowflake-end-x', (Math.random() * 100 - 50) + 'vw');
        snowflake.style.setProperty('--snowflake-end-y', '100vh');

        document.body.appendChild(snowflake);

        // Remove the snowflake after it falls
        setTimeout(() => {
            snowflake.remove();
        }, duration * 1000);
    }

    // Create snowflakes at intervals
    setInterval(createSnowflake, 200);

    // Create initial snowflakes
    for (let i = 0; i < snowflakeCount; i++) {
        setTimeout(createSnowflake, i * 200);
    }
});