import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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

function initializeBlogDiscovery() {
    if (!document.querySelector('[data-blog-browser]')) {
        return;
    }

    let activeRequest;
    let searchTimer;
    const status = document.createElement('p');

    status.className = 'sr-only';
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    document.body.appendChild(status);

    const updateMetadata = (blogBrowser) => {
        document.title = blogBrowser.dataset.pageTitle;

        [
            ['meta[name="description"]', 'content', blogBrowser.dataset.pageDescription],
            ['meta[name="robots"]', 'content', blogBrowser.dataset.robots],
            ['meta[property="og:title"]', 'content', blogBrowser.dataset.pageTitle],
            ['meta[property="og:description"]', 'content', blogBrowser.dataset.pageDescription],
            ['meta[property="og:url"]', 'content', blogBrowser.dataset.canonicalUrl],
            ['meta[name="twitter:title"]', 'content', blogBrowser.dataset.pageTitle],
            ['meta[name="twitter:description"]', 'content', blogBrowser.dataset.pageDescription],
            ['link[rel="canonical"]', 'href', blogBrowser.dataset.canonicalUrl],
        ].forEach(([selector, attribute, value]) => {
            if (value) {
                document.querySelector(selector)?.setAttribute(attribute, value);
            }
        });
    };

    const navigate = async (destination, { history = 'push', focus = 'filter' } = {}) => {
        const currentBlogBrowser = document.querySelector('[data-blog-browser]');

        if (!currentBlogBrowser) {
            window.location.assign(destination);

            return;
        }

        activeRequest?.abort();

        const request = new AbortController();
        const currentFilterTop = currentBlogBrowser.querySelector('[data-blog-filters]')?.getBoundingClientRect().top;

        activeRequest = request;
        currentBlogBrowser.setAttribute('aria-busy', 'true');
        currentBlogBrowser.classList.add('pointer-events-none', 'opacity-60');
        status.textContent = 'Updating stories…';

        try {
            const response = await fetch(destination, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: request.signal,
            });

            if (!response.ok) {
                throw new Error(`Blog request failed with status ${response.status}.`);
            }

            const template = document.createElement('template');
            template.innerHTML = (await response.text()).trim();

            const nextBlogBrowser = template.content.querySelector('[data-blog-browser]');

            if (!nextBlogBrowser) {
                throw new Error('Blog response did not include the discovery region.');
            }

            currentBlogBrowser.replaceWith(nextBlogBrowser);
            updateMetadata(nextBlogBrowser);

            const nextFilterTop = nextBlogBrowser.querySelector('[data-blog-filters]')?.getBoundingClientRect().top;

            if (currentFilterTop !== undefined && nextFilterTop !== undefined) {
                window.scrollBy(0, nextFilterTop - currentFilterTop);
            }

            if (history === 'push') {
                window.history.pushState({ blog: true }, '', destination);
            }

            if (focus === 'search') {
                const search = nextBlogBrowser.querySelector('[data-blog-live-search]');

                search?.focus({ preventScroll: true });
                search?.setSelectionRange(search.value.length, search.value.length);
            }

            if (focus === 'filter') {
                const activeFilter = nextBlogBrowser.querySelector('[data-blog-filter-link][aria-current="page"]');

                activeFilter?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                activeFilter?.focus({ preventScroll: true });
            }

            status.textContent = nextBlogBrowser.dataset.blogAnnouncement || 'Stories updated.';
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            window.location.assign(destination);
        } finally {
            if (activeRequest === request) {
                const blogBrowser = document.querySelector('[data-blog-browser]');

                blogBrowser?.setAttribute('aria-busy', 'false');
                blogBrowser?.classList.remove('pointer-events-none', 'opacity-60');
                activeRequest = undefined;
            }
        }
    };

    const searchUrl = (form) => {
        const url = new URL(form.action, window.location.href);
        const parameters = new URLSearchParams(new FormData(form));

        for (const [key, value] of [...parameters]) {
            if (String(value).trim() === '') {
                parameters.delete(key);
            }
        }

        url.search = parameters.toString();

        return url;
    };

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const link = event.target.closest('[data-blog-navigation-link]');

        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        window.clearTimeout(searchTimer);
        navigate(link.href);
    });

    document.addEventListener('submit', (event) => {
        if (!(event.target instanceof HTMLFormElement) || !event.target.matches('[data-blog-search-form]')) {
            return;
        }

        event.preventDefault();
        window.clearTimeout(searchTimer);
        navigate(searchUrl(event.target), { focus: 'search' });
    });

    document.addEventListener('input', (event) => {
        if (!(event.target instanceof HTMLInputElement) || !event.target.matches('[data-blog-live-search]')) {
            return;
        }

        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            navigate(searchUrl(event.target.form), { focus: 'search' });
        }, 300);
    });

    window.addEventListener('popstate', () => {
        if (document.querySelector('[data-blog-browser]')) {
            navigate(window.location.href, { history: 'none', focus: null });
        }
    });
}

initializeCopyLinks();
initializeBlogArticle();
focusFirstInvalidField();
initializeBlogDiscovery();
