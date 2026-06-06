const fs = require('fs');

function cleanCSS(filePath) {
    let css = fs.readFileSync(filePath, 'utf8');

    // 1. Replace standalone hashes that the theme author hardcoded without the parent class
    const replacements = {
        '.LWXmF': '[class*="ServerRow___StyledP-sc-"]',
        '.iWCdMr': '[class*="ServerRow___StyledDiv3-sc-"] .icon',
        '.QNQDy': '[class*="ServerRow___StyledFontAwesomeIcon-sc-"]',
        '.kVwOgb': '[class*="ServerRow___StyledP2-sc-"]',
        '.IQTcj': '[class*="ServerRow___StyledP3-sc-"]',
        '.iUikAX': '[class*="ServerRow___StyledP4-sc-"]',
        '.fujSji': '[class*="ServerRow___StyledP5-sc-"]',
        '.iOYhVG': '[class*="ServerRow___StyledP6-sc-"]',
        '.evVMzW': '[class*="ServerRow___StyledFontAwesomeIcon-sc-"]',
        '.juhRZD': '[class*="ServerRow___StyledDiv8-sc-"]',
        '.hUgrQh': '[class*="ServerRow___StyledDiv10-sc-"]',
        '.cNvVYR': '[class*="ServerRow___StyledDiv12-sc-"]',
        '.kVijQB': '[class*="ServerRow__StatusIndicatorBox-sc-"]',
        '.dyLna-D': '[class*="ServerRow___StyledDiv-sc-"]',
        '.ecJXa-d': '[class*="ServerRow___StyledDiv-sc-"]',
        '.iqtzEC': '[class*="ServerRow___StyledDiv2-sc-"]',
        '.gQExFz': '[class*="ServerRow___StyledDiv4-sc-"]',
        '.kncYZx': '[class*="ServerRow___StyledDiv7-sc-"]',
        '.fZEUwy': '[class*="ServerRow___StyledDiv9-sc-"]'
    };

    for (const [oldClass, newClass] of Object.entries(replacements)) {
        const regex = new RegExp(`\\${oldClass}(?![A-Za-z0-9_-])`, 'g');
        css = css.replace(regex, newClass);
    }

    // 2. Convert all .ClassName-sc-hash-num to [class*="ClassName-sc-"]
    css = css.replace(/\.([A-Za-z0-9_-]+)-sc-[a-z0-9]+-[0-9]+/g, '[class*="$1-sc-"]');

    // 3. Strip all trailing classes that followed the styled components (e.g. .fnfeQw or .fade-appear-done)
    let previousCss = '';
    while (css !== previousCss) {
        previousCss = css;
        css = css.replace(/(\[class\*="[^"]+"\])\.[A-Za-z0-9_-]+/g, '$1');
    }

    // 4. Fix the DOM structural change (#app > div > ... > a) to point to StatusIndicatorBox
    css = css.replace(/#app\s*>\s*div\[?[^>]+>\s*div\[?[^>]+>\s*section\s*>\s*div\[?[^>]+>\s*a/g, '[class*="ServerRow__StatusIndicatorBox-sc-"]');
    
    // Also catch the specific ServerList___StyledDiv fallback just in case
    css = css.replace(/\[class\*="ServerList___StyledDiv-sc-"\]/g, '[class*="ServerRow__StatusIndicatorBox-sc-"]');

    fs.writeFileSync(filePath, css);
    console.log(`Successfully cleaned ${filePath}`);
}

cleanCSS('.tmp/extract6/client/client.css');
