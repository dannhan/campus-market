import * as Turbo from '@hotwired/turbo';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.Turbo = Turbo;

const scrollPositions = new Map();

document.addEventListener('turbo:before-render', () => {
    // Clean up old Alpine state before Turbo swaps DOM
    if (window.Alpine && document.body) {
        Alpine.destroyTree(document.body);
    }

    // Save horizontal scroll positions so category navigation doesn't snap to 0
    scrollPositions.clear();
    document.querySelectorAll('.overflow-x-auto, [data-scroll-container]').forEach((el, index) => {
        const key = el.id || ('scroller_' + index);
        scrollPositions.set(key, el.scrollLeft);
    });
});

document.addEventListener('turbo:render', () => {
    // Restore horizontal scroll positions
    document.querySelectorAll('.overflow-x-auto, [data-scroll-container]').forEach((el, index) => {
        const key = el.id || ('scroller_' + index);
        if (scrollPositions.has(key)) {
            el.scrollLeft = scrollPositions.get(key);
        }
    });
});

document.addEventListener('turbo:load', () => {
    // Initialize Alpine on the freshly rendered DOM
    if (window.Alpine && document.body) {
        Alpine.initTree(document.body);
    }
});

Alpine.start();
