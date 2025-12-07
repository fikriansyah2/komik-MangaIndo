import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Load cover images via proxy using data-cover-url attribute
// This avoids HTML line-wrapping issues with long URLs in Blade templates
document.addEventListener('DOMContentLoaded', () => {
    const coverImages = document.querySelectorAll('img.cover-img[data-cover-url]');
    
    coverImages.forEach(img => {
        const coverUrl = img.getAttribute('data-cover-url');
        const coverSize = img.getAttribute('data-cover-size') || '256'; // default to 256 if not specified
        
        if (!coverUrl) return;
        
        // Remove file extension and add size suffix
        const cleanUrl = coverUrl.replace(/\.(jpg|jpeg|png|gif)$/i, '') + '.' + coverSize + '.jpg';
        
        // Build proxy URL
        const proxyUrl = '/comics/cover-proxy?url=' + encodeURIComponent(cleanUrl);
        
        // Set src
        img.src = proxyUrl;
    });
});

// Chatbot frontend behavior
import './chatbot';
