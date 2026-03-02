document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('.site-header');
  const navToggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');
  const navAnchors = document.querySelectorAll('.nav-links a');
  const revealElements = document.querySelectorAll('.reveal');
  const faqQuestions = document.querySelectorAll('.faq-question');

  // ── Parallax: hero background drifts slowly on scroll ──
  const heroSection = document.querySelector('.hero');
  if (heroSection) {
    window.addEventListener('scroll', () => {
      const scrolled = window.scrollY;
      if (scrolled < window.innerHeight * 1.5) {
        heroSection.style.backgroundPositionY = `calc(50% + ${scrolled * 0.28}px)`;
      }
    }, { passive: true });
  }

  // ── Stat counter: counts up when .stat-bar scrolls into view ──
  function animateCount(el, targetText, duration = 1400) {
    const num = parseInt(targetText.replace(/\D/g, ''));
    if (isNaN(num)) return;
    const suffix = targetText.replace(/[0-9]/g, '');
    const start = performance.now();
    const tick = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(eased * num) + suffix;
      if (progress < 1) requestAnimationFrame(tick);
      else el.textContent = targetText;
    };
    requestAnimationFrame(tick);
  }

  const statBar = document.querySelector('.stat-bar');
  if (statBar && 'IntersectionObserver' in window) {
    const statObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          document.querySelectorAll('.stat-num').forEach(el => {
            animateCount(el, el.textContent.trim());
          });
          statObserver.disconnect();
        }
      });
    }, { threshold: 0.6 });
    statObserver.observe(statBar);
  }

  const handleScroll = () => {
    if (!header) return;
    if (window.scrollY > 10) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      const isOpen = navLinks.classList.toggle('open');
      navToggle.classList.toggle('open', isOpen);
      navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    navAnchors.forEach(anchor => {
      anchor.addEventListener('click', () => {
        navLinks.classList.remove('open');
        navToggle.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          obs.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.2,
      rootMargin: '0px 0px -50px 0px'
    });

    revealElements.forEach(el => observer.observe(el));
  } else {
    revealElements.forEach(el => el.classList.add('visible'));
  }

  faqQuestions.forEach(question => {
    const answer = question.nextElementSibling;
    if (!answer) return;

    question.addEventListener('click', () => {
      const isExpanded = question.getAttribute('aria-expanded') === 'true';
      question.setAttribute('aria-expanded', String(!isExpanded));
      question.parentElement.classList.toggle('open', !isExpanded);

      if (!isExpanded) {
        answer.style.maxHeight = `${answer.scrollHeight}px`;
      } else {
        answer.style.maxHeight = '0';
      }
    });
  });
});
