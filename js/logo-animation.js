/**
 * Logo Animation Script
 * Simple hover animation for VbN logo
 */

document.addEventListener('DOMContentLoaded', function() {
    const logoLink = document.querySelector('.logo-link');
    const logoSvg = document.querySelector('.logo-svg');
    
    if (!logoLink || !logoSvg) {
        console.log('Logo elements not found');
        return;
    }
    
    // Get animated elements
    const border = logoSvg.querySelector('.logo-border');
    const text = logoSvg.querySelector('.logo-text');
    
    console.log('Logo animation initialized', {
        border: !!border,
        text: !!text
    });
    
    // Mouse enter - activate animations
    logoLink.addEventListener('mouseenter', function() {
        console.log('Logo hover START');
        
        // Scale and glow entire SVG
        logoSvg.style.transform = 'scale(1.1)';
        logoSvg.style.filter = 'drop-shadow(0 0 20px rgba(139, 0, 0, 0.9))';
        
        // Animate border
        if (border) {
            border.setAttribute('stroke', '#ff0000');
            border.style.filter = 'drop-shadow(0 0 10px rgba(255, 0, 0, 0.8))';
        }
        
        // Animate text
        if (text) {
            text.setAttribute('fill', '#ff0000');
            text.style.filter = 'drop-shadow(0 0 12px rgba(255, 0, 0, 1))';
        }
    });
    
    // Mouse leave - reset animations
    logoLink.addEventListener('mouseleave', function() {
        console.log('Logo hover END');
        
        // Reset SVG
        logoSvg.style.transform = 'scale(1)';
        logoSvg.style.filter = 'none';
        
        // Reset border
        if (border) {
            border.setAttribute('stroke', '#8B0000');
            border.style.filter = 'none';
        }
        
        // Reset text
        if (text) {
            text.setAttribute('fill', '#8B0000');
            text.style.filter = 'url(#shadow)';
        }
    });
});

