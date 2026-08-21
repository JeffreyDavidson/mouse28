import './bootstrap';

function initializeBlogArticle() {
    const article = document.getElementById('article-body');

    if (!article) {
        return;
    }

    const progressBar = document.getElementById('reading-progress');
    const readingIndicator = document.getElementById('reading-indicator');
    const backToTop = document.getElementById('back-to-top');
    const readingMinutes = Number.parseInt(article.dataset.readingMinutes ?? '0', 10);

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

        if (readingIndicator && readingMinutes > 0) {
            const currentMinute = Math.max(1, Math.ceil((percentage / 100) * readingMinutes));
            readingIndicator.textContent = `${currentMinute} of ${readingMinutes} min read`;
        }

        backToTop?.classList.toggle('is-visible', window.scrollY > 500);
    };

    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress, { passive: true });
    updateProgress();

    backToTop?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.querySelectorAll('[data-copy-link]').forEach((button) => {
        button.addEventListener('click', async () => {
            const feedback = button.querySelector('[data-copy-feedback], .copy-feedback');
            const label = button.querySelector('[data-copy-label]');

            try {
                await navigator.clipboard.writeText(window.location.href);

                feedback?.classList.remove('hidden');
                label?.classList.add('hidden');

                window.setTimeout(() => {
                    feedback?.classList.add('hidden');
                    label?.classList.remove('hidden');
                }, 1500);
            } catch (error) {
                console.error('Unable to copy the article link.', error);
            }
        });
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
        link.className = `blog-toc-link${heading.tagName === 'H3' ? ' is-level-three' : ''}`;
        link.addEventListener('click', (event) => {
            event.preventDefault();
            heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        tocNav.appendChild(link);
    });

    const tocLinks = Array.from(tocNav.querySelectorAll('.blog-toc-link'));
    const updateTableOfContents = () => {
        let currentHeading = 0;

        headings.forEach((heading, index) => {
            if (heading.getBoundingClientRect().top < 150) {
                currentHeading = index;
            }
        });

        tocLinks.forEach((link, index) => {
            link.classList.toggle('active', index === currentHeading);
        });
    };

    window.addEventListener('scroll', updateTableOfContents, { passive: true });
    updateTableOfContents();
}

initializeBlogArticle();
