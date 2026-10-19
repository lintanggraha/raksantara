/**
 * Raksantara UI/UX Pro Max - Framework & Regulation Reader Interaction Engine
 * Standardized across all 13 National Regulations & Global Standards
 */

// 1. Universal Tab Navigation Engine
function initFrameworkTabs() {
    const tabs = document.querySelectorAll('.fw-tab');
    if (!tabs || tabs.length === 0) return;

    tabs.forEach(tab => {
        if (tab.dataset.fwTabBound === 'true') return;
        tab.dataset.fwTabBound = 'true';

        tab.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-tab');
            if (!targetId) return;

            // Update active state on tab buttons in same group/page
            const nav = this.closest('.fw-nav') || this.closest('.fw-nav-container') || document;
            nav.querySelectorAll('.fw-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Hide all tab contents
            document.querySelectorAll('.fw-tab-content').forEach(c => c.classList.add('d-none'));

            // Show selected tab content
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.remove('d-none');

                // If switching to a tab with items, ensure active item is synced
                if (window.activeFrameworkReader && typeof window.activeFrameworkReader.syncActiveItem === 'function') {
                    window.activeFrameworkReader.syncActiveItem();
                }
            }

            // Sync hash in URL history without page jump
            if (history.replaceState) {
                history.replaceState(null, '', '#' + targetId);
            }
        });
    });

    // Check URL hash on initial load
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const matchTab = document.querySelector(`.fw-tab[data-tab="${hash}"]`);
        if (matchTab) {
            matchTab.click();
        }
    }
}

// Auto-initialize tabs when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFrameworkTabs);
} else {
    initFrameworkTabs();
}

// 2. Master-Detail Framework Reader Class
class FrameworkReader {
    constructor(config = {}) {
        this.itemSelector = config.itemSelector || '.fw-item';
        this.searchInputId = config.searchInputId || 'fw-search-input';
        this.filterSelectId = config.filterSelectId || 'fw-filter-select';
        this.counterId = config.counterId || 'fw-counter';
        // Support BOTH onItemSelect and onSelect seamlessly
        this.onSelect = config.onItemSelect || config.onSelect || null;
        this.items = [];
        window.activeFrameworkReader = this;
        this.init();
    }

    init() {
        this.items = Array.from(document.querySelectorAll(this.itemSelector));
        this.searchInput = document.getElementById(this.searchInputId);
        this.filterSelect = document.getElementById(this.filterSelectId);
        this.counter = document.getElementById(this.counterId);

        // Always bind global events (copy, tabs, hotkeys) even if items is empty (e.g. card-only views)
        this.bindEvents();

        if (this.items.length === 0) return;

        // Select initial item (either marked active or first visible)
        const activeItem = this.items.find(i => i.classList.contains('active') && i.style.display !== 'none');
        const firstVisible = activeItem || this.items.find(i => i.style.display !== 'none');
        if (firstVisible) {
            this.selectItem(firstVisible);
        }
    }

    bindEvents() {
        // Master-detail item selection
        this.items.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectItem(item);
            });
        });

        // Search input handling
        if (this.searchInput) {
            this.searchInput.addEventListener('input', () => this.filterItems());
        }

        // Filter select handling
        if (this.filterSelect) {
            this.filterSelect.addEventListener('change', () => this.filterItems());
        }

        // Global hotkeys: '/' to search, 'Escape' to clear, Up/Down arrows to navigate
        document.addEventListener('keydown', (e) => {
            // Ignore if user is currently typing in an input/select/textarea
            const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);

            if (e.key === '/' && !isTyping) {
                if (this.searchInput) {
                    e.preventDefault();
                    this.searchInput.focus();
                    this.searchInput.select();
                }
            } else if (e.key === 'Escape' && document.activeElement === this.searchInput) {
                this.searchInput.value = '';
                this.filterItems();
                this.searchInput.blur();
            } else if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && !isTyping) {
                // Only navigate if list is visible on screen
                const listPanel = document.querySelector('.fw-list-panel');
                if (listPanel && listPanel.offsetParent !== null) {
                    e.preventDefault();
                    this.navigateKeyboard(e.key === 'ArrowDown' ? 1 : -1);
                }
            }
        });

        // Copy button handler (delegated listener)
        if (!window.fwCopyBound) {
            window.fwCopyBound = true;
            document.addEventListener('click', (e) => {
                const copyBtn = e.target.closest('[data-copy-target]');
                if (!copyBtn) return;

                const targetId = copyBtn.getAttribute('data-copy-target');
                const targetEl = document.getElementById(targetId);
                if (targetEl) {
                    const text = targetEl.textContent.trim();
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(() => {
                            this.showToast('Teks berhasil disalin ke clipboard');
                        }).catch(() => {
                            this.fallbackCopyText(text);
                        });
                    } else {
                        this.fallbackCopyText(text);
                    }
                }
            });
        }
    }

    fallbackCopyText(text) {
        try {
            const temp = document.createElement('textarea');
            temp.value = text;
            temp.style.position = 'fixed';
            temp.style.left = '-9999px';
            document.body.appendChild(temp);
            temp.focus();
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            this.showToast('Teks berhasil disalin ke clipboard');
        } catch (err) {
            this.showToast('Gagal menyalin teks', true);
        }
    }

    selectItem(item) {
        if (!item) return;

        this.items.forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        if (typeof this.onSelect === 'function') {
            try {
                this.onSelect(item);
            } catch (err) {
                console.error('[FrameworkReader] Error in onSelect handler:', err);
            }
        }
    }

    syncActiveItem() {
        const active = this.items.find(i => i.classList.contains('active') && i.style.display !== 'none');
        if (active && typeof this.onSelect === 'function') {
            this.onSelect(active);
        } else {
            const first = this.items.find(i => i.style.display !== 'none');
            if (first) this.selectItem(first);
        }
    }

    navigateKeyboard(direction) {
        const visibleItems = this.items.filter(i => i.style.display !== 'none');
        if (visibleItems.length === 0) return;

        const currentIndex = visibleItems.findIndex(i => i.classList.contains('active'));
        let nextIndex = currentIndex + direction;

        if (nextIndex < 0) nextIndex = 0;
        if (nextIndex >= visibleItems.length) nextIndex = visibleItems.length - 1;

        const nextItem = visibleItems[nextIndex];
        if (nextItem) {
            this.selectItem(nextItem);
            nextItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    filterItems() {
        const query = (this.searchInput?.value || '').toLowerCase().trim();
        const filterVal = this.filterSelect?.value || '';

        let visibleCount = 0;
        let firstVisible = null;

        this.items.forEach(item => {
            const searchIndex = (item.getAttribute('data-search-index') || item.textContent).toLowerCase();
            const groupVal = item.getAttribute('data-group') || '';

            const matchQuery = !query || searchIndex.includes(query);
            const matchGroup = !filterVal || groupVal === filterVal;

            if (matchQuery && matchGroup) {
                item.style.display = '';
                visibleCount++;
                if (!firstVisible) firstVisible = item;
            } else {
                item.style.display = 'none';
            }
        });

        if (this.counter) {
            this.counter.textContent = `${visibleCount} dari ${this.items.length} ditampilkan`;
        }

        const currentlyActive = this.items.find(i => i.classList.contains('active'));
        if (!currentlyActive || currentlyActive.style.display === 'none') {
            if (firstVisible) {
                this.selectItem(firstVisible);
            }
        }
    }

    showToast(message, isError = false) {
        let toast = document.getElementById('fw-reader-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'fw-reader-toast';
            toast.className = 'fw-toast';
            document.body.appendChild(toast);
        }

        toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle text-danger' : 'fa-check-circle text-success'} me-2"></i><span>${message}</span>`;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 2200);
    }
}

// Global Exports
window.initFrameworkTabs = initFrameworkTabs;
window.FrameworkReader = FrameworkReader;
