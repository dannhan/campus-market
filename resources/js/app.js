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

Alpine.data('wishlistToggle', (initialWishlisted = false) => ({
    wishlisted: Boolean(initialWishlisted),
    pending: false,
    statusMessage: '',

    async toggle(event) {
        if (this.pending) {
            return;
        }

        const form = event.currentTarget;
        this.pending = true;
        this.statusMessage = '';

        try {
            const response = await fetch(form.action, {
                cache: 'no-store',
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            if (! response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const data = await response.json();

            this.wishlisted = Boolean(data.wishlisted);
            this.statusMessage = data.message ?? '';
        } catch {
            // Fall back to the normal POST so the server can show its flash message.
            form.submit();
        } finally {
            this.pending = false;
        }
    },
}));

Alpine.start();
