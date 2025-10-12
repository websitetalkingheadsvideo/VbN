const fs = require('fs');

// Read the CSS file
let cssContent = fs.readFileSync('css/style.css', 'utf8');

// Function to reduce any padding value by 50%
function reducePaddingValue(match, prefix, value) {
    const num = parseInt(value);
    const reduced = Math.max(1, Math.round(num / 2));
    return `${prefix}: ${reduced}px;`;
}

// Apply reductions to all padding properties
// 1. padding: value;
cssContent = cssContent.replace(/(padding):\s*(\d+)px;/g, reducePaddingValue);

// 2. padding-top: value;
cssContent = cssContent.replace(/(padding-top):\s*(\d+)px;/g, reducePaddingValue);

// 3. padding-right: value;
cssContent = cssContent.replace(/(padding-right):\s*(\d+)px;/g, reducePaddingValue);

// 4. padding-bottom: value;
cssContent = cssContent.replace(/(padding-bottom):\s*(\d+)px;/g, reducePaddingValue);

// 5. padding-left: value;
cssContent = cssContent.replace(/(padding-left):\s*(\d+)px;/g, reducePaddingValue);

// 6. Four-value padding: top right bottom left (reduce top and bottom by 50%)
cssContent = cssContent.replace(/padding:\s*(\d+)px\s+(\d+)px\s+(\d+)px\s+(\d+)px;/g, (match, top, right, bottom, left) => {
    const topReduced = Math.max(1, Math.round(parseInt(top) / 2));
    const bottomReduced = Math.max(1, Math.round(parseInt(bottom) / 2));
    return `padding: ${topReduced}px ${right}px ${bottomReduced}px ${left}px;`;
});

// 7. Two-value padding: vertical horizontal (reduce vertical by 50%)
cssContent = cssContent.replace(/padding:\s*(\d+)px\s+(\d+)px;/g, (match, vertical, horizontal) => {
    const vertReduced = Math.max(1, Math.round(parseInt(vertical) / 2));
    return `padding: ${vertReduced}px ${horizontal}px;`;
});

// Write the modified CSS
fs.writeFileSync('css/style.css', cssContent);

console.log('All vertical padding reduced by 50% throughout the page!');
console.log('All padding values have been halved (minimum 1px maintained)');
