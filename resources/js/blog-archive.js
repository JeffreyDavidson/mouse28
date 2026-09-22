export function initializeBlogArchive(Livewire) {
    Livewire.hook('component.init', ({ component, cleanup }) => {
        if (!component.el.matches('[data-blog-browser]')) {
            return;
        }

        const root = component.el;
        const events = new AbortController();
        let pendingFilterTransition = false;
        let filterTransition = null;

        const storyCards = () => Array.from(root.querySelectorAll('[data-blog-post]'));

        const stopCardAnimations = () => {
            storyCards().forEach((card) => {
                card.getAnimations().forEach((animation) => animation.cancel());
                card.style.removeProperty('will-change');
            });
        };

        const cardPositions = () => {
            stopCardAnimations();

            return new Map(
                storyCards().map((card) => {
                    const bounds = card.getBoundingClientRect();

                    return [card.dataset.blogPost, { left: bounds.left, top: bounds.top }];
                }),
            );
        };

        const animateCardReflow = (previousPositions) => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            storyCards().forEach((card) => {
                const previousPosition = previousPositions.get(card.dataset.blogPost);

                if (!previousPosition) {
                    card.animate([{ opacity: 0.35 }, { opacity: 1 }], {
                        duration: 260,
                        easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
                    });

                    return;
                }

                const bounds = card.getBoundingClientRect();
                const horizontalDistance = previousPosition.left - bounds.left;
                const verticalDistance = previousPosition.top - bounds.top;

                if (Math.abs(horizontalDistance) < 1 && Math.abs(verticalDistance) < 1) {
                    return;
                }

                card.style.willChange = 'transform';

                const animation = card.animate(
                    [
                        { transform: `translate(${horizontalDistance}px, ${verticalDistance}px)` },
                        { transform: 'translate(0, 0)' },
                    ],
                    {
                        duration: 440,
                        easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
                    },
                );

                animation.addEventListener(
                    'finish',
                    () => {
                        card.style.removeProperty('will-change');
                    },
                    { once: true },
                );
            });
        };

        const queueFilterTransition = (event) => {
            if (!(event.target instanceof Element) || !event.target.closest('[data-preserve-blog-filter-position]')) {
                return;
            }

            pendingFilterTransition = true;
        };

        root.addEventListener(
            'blog-metadata-updated',
            ({ detail }) => {
                document.title = detail.pageTitle;

                [
                    ['meta[name="description"]', 'content', detail.pageDescription],
                    ['meta[name="robots"]', 'content', detail.robots],
                    ['meta[property="og:title"]', 'content', detail.pageTitle],
                    ['meta[property="og:description"]', 'content', detail.pageDescription],
                    ['meta[property="og:url"]', 'content', detail.canonicalUrl],
                    ['meta[name="twitter:title"]', 'content', detail.pageTitle],
                    ['meta[name="twitter:description"]', 'content', detail.pageDescription],
                    ['link[rel="canonical"]', 'href', detail.canonicalUrl],
                ].forEach(([selector, attribute, value]) => {
                    document.querySelector(selector)?.setAttribute(attribute, value);
                });
            },
            { signal: events.signal },
        );

        ['click', 'input', 'change', 'submit'].forEach((eventName) => {
            root.addEventListener(eventName, queueFilterTransition, { capture: true, signal: events.signal });
        });

        const removeCommitHook = Livewire.hook('commit', ({ component, fail }) => {
            if (component.el !== root || !pendingFilterTransition) {
                return;
            }

            pendingFilterTransition = false;

            const filters = root.querySelector('[data-blog-filters]');

            if (!filters) {
                return;
            }

            const transition = {
                viewportTop: filters.getBoundingClientRect().top,
                cardPositions: cardPositions(),
            };

            filterTransition = transition;

            fail(() => {
                if (filterTransition === transition) {
                    filterTransition = null;
                }
            });
        });

        const removeMorphHook = Livewire.hook('morphed', ({ el }) => {
            if (el !== root || filterTransition === null) {
                return;
            }

            const transition = filterTransition;

            window.requestAnimationFrame(() => {
                if (!root.isConnected || filterTransition !== transition) {
                    return;
                }

                const filters = root.querySelector('[data-blog-filters]');

                if (filters) {
                    window.scrollBy(0, filters.getBoundingClientRect().top - transition.viewportTop);
                }

                animateCardReflow(transition.cardPositions);
                filterTransition = null;
            });
        });

        cleanup(() => {
            events.abort();
            removeCommitHook();
            removeMorphHook();
            stopCardAnimations();
            filterTransition = null;
        });
    });
}
