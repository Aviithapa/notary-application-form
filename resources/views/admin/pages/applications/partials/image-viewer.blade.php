{{--
    Full-screen image viewer. Any link with data-gallery-item (href = image URL, optional data-caption)
    opens here instead of a new tab. Prev/next steps through the other images in the same area
    (the open modal, or the page outside modals). Zoom with the buttons, mouse wheel, +/- keys or
    double-click; drag to move a zoomed image; swipe or arrow keys to change image; Esc closes.
--}}
@once
    @push('styles')
        <style>
            .image-viewer { position: fixed; inset: 0; z-index: 2000; display: flex; flex-direction: column; background: rgba(10, 14, 26, .94); color: #fff; }
            .image-viewer[hidden] { display: none; }
            .image-viewer-bar { display: flex; align-items: center; gap: .5rem; padding: .6rem .9rem; background: rgba(0, 0, 0, .35); }
            .image-viewer-caption { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; }
            .image-viewer-count { opacity: .75; font-size: .85rem; white-space: nowrap; }
            .image-viewer-btn {
                display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 .5rem;
                border: 0; border-radius: 8px; background: rgba(255, 255, 255, .1); color: #fff; font-size: 1.15rem; text-decoration: none;
            }
            .image-viewer-btn:hover, .image-viewer-btn:focus-visible { background: rgba(255, 255, 255, .25); color: #fff; }
            .image-viewer-btn:disabled { opacity: .35; }
            .image-viewer-zoom { min-width: 3.5rem; text-align: center; font-size: .85rem; opacity: .85; }
            .image-viewer-stage { position: relative; flex: 1; overflow: hidden; display: flex; align-items: center; justify-content: center; touch-action: none; user-select: none; }
            .image-viewer-stage img { max-width: 92%; max-height: 92%; object-fit: contain; transition: transform .12s ease; will-change: transform; background: #fff; border-radius: 4px; }
            .image-viewer-stage.is-zoomed { cursor: grab; }
            .image-viewer-stage.is-dragging { cursor: grabbing; }
            .image-viewer-stage.is-dragging img { transition: none; }
            .image-viewer-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 48px; height: 48px; border-radius: 50%; font-size: 1.5rem; z-index: 1; }
            .image-viewer-prev { left: 1rem; }
            .image-viewer-next { right: 1rem; }
            [data-gallery-item] { cursor: zoom-in; }
            @media (max-width: 575.98px) {
                .image-viewer-nav { width: 40px; height: 40px; }
                .image-viewer-hide-sm { display: none !important; }
            }
        </style>
    @endpush

    @push('scripts')
        <div class="image-viewer" id="image-viewer" role="dialog" aria-modal="true" aria-label="{{ __('Image viewer') }}" hidden>
            <div class="image-viewer-bar">
                <span class="image-viewer-caption" data-viewer-caption></span>
                <span class="image-viewer-count" data-viewer-count></span>
                <button type="button" class="image-viewer-btn" data-viewer-action="zoom-out" title="{{ __('Zoom out') }} (−)" aria-label="{{ __('Zoom out') }}"><i class="bi bi-zoom-out"></i></button>
                <span class="image-viewer-zoom image-viewer-hide-sm" data-viewer-zoom>100%</span>
                <button type="button" class="image-viewer-btn" data-viewer-action="zoom-in" title="{{ __('Zoom in') }} (+)" aria-label="{{ __('Zoom in') }}"><i class="bi bi-zoom-in"></i></button>
                <button type="button" class="image-viewer-btn" data-viewer-action="reset" title="{{ __('Reset zoom') }} (0)" aria-label="{{ __('Reset zoom') }}"><i class="bi bi-arrows-angle-contract"></i></button>
                <a class="image-viewer-btn image-viewer-hide-sm" data-viewer-original target="_blank" rel="noopener" title="{{ __('Open original') }}" aria-label="{{ __('Open original') }}"><i class="bi bi-box-arrow-up-right"></i></a>
                <button type="button" class="image-viewer-btn" data-viewer-action="close" title="{{ __('Close') }} (Esc)" aria-label="{{ __('Close') }}"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="image-viewer-stage" data-viewer-stage>
                <button type="button" class="image-viewer-btn image-viewer-nav image-viewer-prev" data-viewer-action="prev" aria-label="{{ __('Previous image') }}"><i class="bi bi-chevron-left"></i></button>
                <img alt="" data-viewer-image draggable="false">
                <button type="button" class="image-viewer-btn image-viewer-nav image-viewer-next" data-viewer-action="next" aria-label="{{ __('Next image') }}"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>

        <script>
            (function () {
                const viewer = document.getElementById('image-viewer');
                const stage = viewer.querySelector('[data-viewer-stage]');
                const image = viewer.querySelector('[data-viewer-image]');
                const caption = viewer.querySelector('[data-viewer-caption]');
                const count = viewer.querySelector('[data-viewer-count]');
                const zoomLabel = viewer.querySelector('[data-viewer-zoom]');
                const original = viewer.querySelector('[data-viewer-original]');
                const prevButton = viewer.querySelector('[data-viewer-action="prev"]');
                const nextButton = viewer.querySelector('[data-viewer-action="next"]');
                const MIN = 1, MAX = 5, STEP = 0.25;

                let items = [], index = 0, scale = 1, x = 0, y = 0, returnFocus = null;

                function apply() {
                    image.style.transform = `translate(${x}px, ${y}px) scale(${scale})`;
                    zoomLabel.textContent = Math.round(scale * 100) + '%';
                    stage.classList.toggle('is-zoomed', scale > 1);
                }

                function zoomTo(next) {
                    scale = Math.min(MAX, Math.max(MIN, next));
                    if (scale === 1) { x = 0; y = 0; }
                    apply();
                }

                function show(i) {
                    index = (i + items.length) % items.length;
                    const item = items[index];
                    image.src = item.href;
                    image.alt = item.dataset.caption || '';
                    caption.textContent = item.dataset.caption || '';
                    original.href = item.href;
                    count.textContent = items.length > 1 ? `${index + 1} / ${items.length}` : '';
                    prevButton.hidden = nextButton.hidden = items.length < 2;
                    scale = 1; x = 0; y = 0;
                    apply();
                }

                function open(link) {
                    // Gallery = images in the same open modal, or on the page outside any modal
                    const scope = link.closest('.modal');
                    items = [...(scope || document).querySelectorAll('[data-gallery-item]')]
                        .filter((el) => scope || !el.closest('.modal'));
                    // Inside a Bootstrap modal so its focus trap keeps focus in the viewer
                    (scope || document.body).appendChild(viewer);
                    returnFocus = document.activeElement;
                    viewer.hidden = false;
                    document.body.style.overflow = 'hidden';
                    show(items.indexOf(link));
                    viewer.querySelector('[data-viewer-action="close"]').focus();
                }

                function close() {
                    viewer.hidden = true;
                    image.removeAttribute('src');
                    if (!document.querySelector('.modal.show')) document.body.style.overflow = '';
                    returnFocus?.focus();
                }

                document.addEventListener('click', (event) => {
                    const link = event.target.closest('[data-gallery-item]');
                    if (!link || event.ctrlKey || event.metaKey || event.shiftKey) return;
                    event.preventDefault();
                    open(link);
                });

                viewer.addEventListener('click', (event) => {
                    const action = event.target.closest('[data-viewer-action]')?.dataset.viewerAction;
                    if (action === 'close') close();
                    else if (action === 'prev') show(index - 1);
                    else if (action === 'next') show(index + 1);
                    else if (action === 'zoom-in') zoomTo(scale + STEP);
                    else if (action === 'zoom-out') zoomTo(scale - STEP);
                    else if (action === 'reset') zoomTo(1);
                    else if (event.target === stage) close();
                });

                document.addEventListener('keydown', (event) => {
                    if (viewer.hidden) return;
                    const keys = {
                        Escape: close, ArrowLeft: () => show(index - 1), ArrowRight: () => show(index + 1),
                        '+': () => zoomTo(scale + STEP), '=': () => zoomTo(scale + STEP), '-': () => zoomTo(scale - STEP), '0': () => zoomTo(1),
                    };
                    if (keys[event.key]) {
                        event.preventDefault();
                        event.stopPropagation(); // keep Esc from also closing the Bootstrap modal underneath
                        keys[event.key]();
                    }
                }, true);

                stage.addEventListener('wheel', (event) => {
                    event.preventDefault();
                    zoomTo(scale + (event.deltaY < 0 ? STEP : -STEP));
                }, { passive: false });

                image.addEventListener('dblclick', () => zoomTo(scale > 1 ? 1 : 2));

                // Drag to move when zoomed; swipe left/right to change image when not
                let start = null;
                image.addEventListener('pointerdown', (event) => {
                    start = { px: event.clientX, py: event.clientY, x, y };
                    image.setPointerCapture(event.pointerId);
                    if (scale > 1) stage.classList.add('is-dragging');
                });
                image.addEventListener('pointermove', (event) => {
                    if (!start || scale === 1) return;
                    x = start.x + event.clientX - start.px;
                    y = start.y + event.clientY - start.py;
                    apply();
                });
                image.addEventListener('pointerup', (event) => {
                    if (start && scale === 1 && items.length > 1) {
                        const dx = event.clientX - start.px;
                        if (Math.abs(dx) > 50) show(index + (dx < 0 ? 1 : -1));
                    }
                    start = null;
                    stage.classList.remove('is-dragging');
                });
            })();
        </script>
    @endpush
@endonce
