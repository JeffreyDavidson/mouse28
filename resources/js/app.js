function initializeCopyLinks() {
    document.querySelectorAll('[data-copy-link]').forEach((button) => {
        button.addEventListener('click', async () => {
            const feedback = button.querySelector('[data-copy-feedback], .copy-feedback');
            const label = button.querySelector('[data-copy-label]');

            try {
                await navigator.clipboard.writeText(window.location.href);

                if (feedback) {
                    feedback.textContent = 'Copied!';
                }
                feedback?.classList.remove('hidden');
                label?.classList.add('hidden');

                window.setTimeout(() => {
                    feedback?.classList.add('hidden');
                    label?.classList.remove('hidden');
                }, 1500);
            } catch (error) {
                if (feedback) {
                    feedback.textContent = "Couldn't copy. Use your browser's address bar.";
                }
                feedback?.classList.remove('hidden');
                label?.classList.add('hidden');

                window.setTimeout(() => {
                    feedback?.classList.add('hidden');
                    label?.classList.remove('hidden');
                }, 4000);

                console.error('Unable to copy the page link.', error);
            }
        });
    });
}

function initializeBlogArticle() {
    const article = document.getElementById('article-body');

    if (!article) {
        return;
    }

    const progressBar = document.getElementById('reading-progress');
    const backToTop = document.getElementById('back-to-top');
    const preferredScrollBehavior = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ? 'auto'
        : 'smooth';

    const updateProgress = () => {
        const articleBounds = article.getBoundingClientRect();
        const articleTop = window.scrollY + articleBounds.top;
        const denominator = articleBounds.height - window.innerHeight * 0.5;
        const percentage = denominator > 0
            ? Math.max(0, Math.min(100, ((window.scrollY - articleTop) / denominator) * 100))
            : 0;

        if (progressBar) {
            progressBar.style.width = `${percentage}%`;
        }

        if (backToTop) {
            const isVisible = window.scrollY > 500;

            backToTop.classList.toggle('pointer-events-none', !isVisible);
            backToTop.classList.toggle('invisible', !isVisible);
            backToTop.classList.toggle('translate-y-2.5', !isVisible);
            backToTop.classList.toggle('opacity-0', !isVisible);
            backToTop.classList.toggle('pointer-events-auto', isVisible);
            backToTop.classList.toggle('visible', isVisible);
            backToTop.classList.toggle('translate-y-0', isVisible);
            backToTop.classList.toggle('opacity-100', isVisible);
            backToTop.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
            backToTop.tabIndex = isVisible ? 0 : -1;
        }
    };

    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress, { passive: true });
    updateProgress();

    backToTop?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: preferredScrollBehavior() });
    });

    const content = article.querySelector('.blog-article-content');
    const tocCard = document.getElementById('toc-card');
    const tocNav = document.getElementById('toc-nav');

    if (!content || !tocCard || !tocNav) {
        return;
    }

    const headings = Array.from(content.querySelectorAll('h1, h2, h3'));

    if (headings.length < 2) {
        return;
    }

    tocCard.classList.remove('hidden');

    headings.forEach((heading, index) => {
        const id = `section-${index}`;
        const link = document.createElement('a');

        heading.id = id;
        link.href = `#${id}`;
        link.textContent = heading.textContent;
        link.dataset.blogTocLink = '';
        link.className = `flex min-h-12 items-center border-l-2 border-navy/8 py-2 leading-[1.4] text-navy/65 no-underline transition-colors duration-200 hover:border-gold hover:text-gold-ink ${heading.tagName === 'H3' ? 'pl-8 text-xs' : 'pl-4 text-[0.8rem]'}`;
        link.addEventListener('click', (event) => {
            event.preventDefault();
            heading.scrollIntoView({
                behavior: preferredScrollBehavior(),
                block: 'start',
            });
        });
        tocNav.appendChild(link);
    });

    const tocLinks = Array.from(tocNav.querySelectorAll('[data-blog-toc-link]'));
    const updateTableOfContents = () => {
        let currentHeading = 0;

        headings.forEach((heading, index) => {
            if (heading.getBoundingClientRect().top < 150) {
                currentHeading = index;
            }
        });

        tocLinks.forEach((link, index) => {
            const isActive = index === currentHeading;

            link.classList.toggle('border-navy/8', !isActive);
            link.classList.toggle('text-navy/65', !isActive);
            link.classList.toggle('border-gold', isActive);
            link.classList.toggle('text-gold-ink', isActive);
            link.classList.toggle('font-semibold', isActive);
        });
    };

    window.addEventListener('scroll', updateTableOfContents, { passive: true });
    updateTableOfContents();
}

function focusFirstInvalidField() {
    window.addEventListener('pageshow', () => {
        document.querySelector('[aria-invalid="true"]')?.focus();
    });
}

function initializeBlogMetadata() {
    window.addEventListener('blog-metadata-updated', ({ detail }) => {
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
    });
}

function initializeBlogFiltering(Livewire) {
    let pendingFilterTransition = false;
    let filterTransition = null;

    const storyCards = () => Array.from(document.querySelectorAll('[data-blog-post]'));

    const stopCardAnimations = () => {
        storyCards().forEach((card) => {
            card.getAnimations().forEach((animation) => animation.cancel());
            card.style.removeProperty('will-change');
        });
    };

    const cardPositions = () => {
        stopCardAnimations();

        return new Map(storyCards().map((card) => {
            const bounds = card.getBoundingClientRect();

            return [card.dataset.blogPost, { left: bounds.left, top: bounds.top }];
        }));
    };

    const animateCardReflow = (previousPositions) => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        storyCards().forEach((card) => {
            const previousPosition = previousPositions.get(card.dataset.blogPost);

            if (!previousPosition) {
                card.animate([
                    { opacity: 0.35 },
                    { opacity: 1 },
                ], {
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

            const animation = card.animate([
                { transform: `translate(${horizontalDistance}px, ${verticalDistance}px)` },
                { transform: 'translate(0, 0)' },
            ], {
                duration: 440,
                easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
            });

            animation.addEventListener('finish', () => {
                card.style.removeProperty('will-change');
            }, { once: true });
        });
    };

    const queueFilterTransition = (event) => {
        if (!(event.target instanceof Element) || !event.target.closest('[data-preserve-blog-filter-position]')) {
            return;
        }

        pendingFilterTransition = true;
    };

    ['click', 'input', 'change', 'submit'].forEach((eventName) => {
        document.addEventListener(eventName, queueFilterTransition, true);
    });

    Livewire.hook('commit', ({ component, fail }) => {
        if (!component.el.matches('[data-blog-browser]') || !pendingFilterTransition) {
            return;
        }

        pendingFilterTransition = false;

        const filters = document.querySelector('[data-blog-filters]');

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

    Livewire.hook('morphed', ({ el }) => {
        if (!el.matches('[data-blog-browser]') || filterTransition === null) {
            return;
        }

        const transition = filterTransition;

        window.requestAnimationFrame(() => {
            if (filterTransition !== transition) {
                return;
            }

            const filters = document.querySelector('[data-blog-filters]');

            if (filters) {
                window.scrollBy(0, filters.getBoundingClientRect().top - transition.viewportTop);
            }

            animateCardReflow(transition.cardPositions);
            filterTransition = null;
        });
    });
}

initializeCopyLinks();
initializeBlogArticle();
focusFirstInvalidField();
initializeBlogMetadata();

if (document.querySelector('[data-editorial-blog]')) {
    const { Alpine, Livewire } = await import('../../vendor/livewire/livewire/dist/livewire.esm');

    window.Alpine = Alpine;
    initializeBlogFiltering(Livewire);
    Livewire.start();
} else {
    const { default: Alpine } = await import('alpinejs');

    window.Alpine = Alpine;
    Alpine.start();
}
