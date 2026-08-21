import './bootstrap';
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

                feedback?.classList.remove('hidden');
                label?.classList.add('hidden');

                window.setTimeout(() => {
                    feedback?.classList.add('hidden');
                    label?.classList.remove('hidden');
                }, 1500);
            } catch (error) {
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

        if (backToTop) {
            const isVisible = window.scrollY > 500;

            backToTop.classList.toggle('pointer-events-none', !isVisible);
            backToTop.classList.toggle('translate-y-2.5', !isVisible);
            backToTop.classList.toggle('opacity-0', !isVisible);
            backToTop.classList.toggle('pointer-events-auto', isVisible);
            backToTop.classList.toggle('translate-y-0', isVisible);
            backToTop.classList.toggle('opacity-100', isVisible);
        }
    };

    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress, { passive: true });
    updateProgress();

    backToTop?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
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
        link.className = `block border-l-2 border-navy/8 py-1.5 leading-[1.4] text-navy/50 no-underline transition-colors duration-200 hover:border-gold hover:text-gold ${heading.tagName === 'H3' ? 'pl-8 text-xs' : 'pl-4 text-[0.8rem]'}`;
        link.addEventListener('click', (event) => {
            event.preventDefault();
            heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
            link.classList.toggle('text-navy/50', !isActive);
            link.classList.toggle('border-gold', isActive);
            link.classList.toggle('text-gold', isActive);
            link.classList.toggle('font-semibold', isActive);
        });
    };

    window.addEventListener('scroll', updateTableOfContents, { passive: true });
    updateTableOfContents();
}

initializeCopyLinks();
initializeBlogArticle();
