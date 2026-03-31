document.addEventListener('DOMContentLoaded', () => {

  // ── 1. SCROLL PROGRESS BAR ───────────────────────────────────────────────
  const progressBar = document.querySelector('.scroll-progress');
  if (progressBar) {
    window.addEventListener('scroll', () => {
      const scrolled = window.scrollY;
      const total    = document.documentElement.scrollHeight - window.innerHeight;
      progressBar.style.width = `${(scrolled / total) * 100}%`;
    }, { passive: true });
  }

  // ── 3. MAGNETIC CTA BUTTONS ──────────────────────────────────────────────
  document.querySelectorAll('.btn-primary').forEach(btn => {
    btn.addEventListener('mousemove', e => {
      const rect = btn.getBoundingClientRect();
      const x = (e.clientX - rect.left - rect.width  / 2) * 0.14;
      const y = (e.clientY - rect.top  - rect.height / 2) * 0.18;
      btn.style.transform = `translate(${x}px, ${y}px)`;
      btn.classList.add('magnetic-active');
    });
    btn.addEventListener('mouseleave', () => {
      btn.style.transform = '';
      btn.classList.remove('magnetic-active');
    });
  });

  // ── 4. TEXT MASK REVEAL ───────────────────────────────────────────────────
  document.querySelectorAll('.text-reveal-target').forEach(el => {
    el.innerHTML = `<span class="rl-wrap"><span class="rl">${el.innerHTML}</span></span>`;
  });

  // ── 5. GALLERY STAGGER DELAY ─────────────────────────────────────────────
  document.querySelectorAll('.collage-item').forEach((el, i) => {
    el.style.transitionDelay = `${i * 0.14}s`;
  });

  // ── 6. AMENITY CARD STAGGER DELAY ────────────────────────────────────────
  document.querySelectorAll('.amenity-card').forEach((card, i) => {
    card.style.transitionDelay = `${i * 0.07}s`;
  });

  // ── 7. HEADER SCROLL ─────────────────────────────────────────────────────
  const header = document.querySelector('.site-header');
  const onScroll = () => header && header.classList.toggle('scrolled', window.scrollY > 10);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // ── 8. PARALLAX ──────────────────────────────────────────────────────────
  const introImg     = document.querySelector('.intro-image img');
  const reviewBgEl   = document.querySelector('.review-bg-image');

  window.addEventListener('scroll', () => {
    const sy = window.scrollY;
    const vh = window.innerHeight;

    if (introImg) {
      const rect = introImg.closest('section').getBoundingClientRect();
      if (rect.top < vh && rect.bottom > 0) {
        const ratio = (rect.top / vh);
        introImg.style.transform = `translateY(${ratio * 38}px) scale(1.0)`;
      } else if (rect.top >= vh) {
        introImg.style.transform = ''; // let CSS handle scale before entering view
      }
    }

    if (reviewBgEl) {
      const rect = reviewBgEl.closest('section').getBoundingClientRect();
      if (rect.top < vh && rect.bottom > 0) {
        const ratio = ((rect.top + rect.height / 2) / vh - 0.5);
        reviewBgEl.style.transform = `translateY(${ratio * -55}px)`;
      }
    }
  }, { passive: true });

  // ── 9. INTERSECTION OBSERVERS ─────────────────────────────────────────────
  if (!('IntersectionObserver' in window)) {
    // Fallback: show everything
    document.querySelectorAll('.reveal, .amenity-card, .collage-item, .rl, .section-num, .stat, .testimonial, .star-item').forEach(el => {
      el.classList.add('visible', 'card-visible', 'clip-revealed', 'rl-in', 'num-in', 'stat-in', 'testi-in', 'star-in');
    });
    return;
  }

  // General section reveal
  const sectionObs = new IntersectionObserver((entries, obs) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
  }, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });
  document.querySelectorAll('.reveal').forEach(el => sectionObs.observe(el));

  // Text mask reveal
  const textObs = new IntersectionObserver((entries, obs) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.querySelectorAll('.rl').forEach((rl, i) => setTimeout(() => rl.classList.add('rl-in'), i * 100));
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.25 });
  document.querySelectorAll('.text-reveal-target').forEach(el => textObs.observe(el));

  // Gallery image reveal
  const galleryObs = new IntersectionObserver((entries, obs) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('clip-revealed'); obs.unobserve(e.target); } });
  }, { threshold: 0.08 });
  document.querySelectorAll('.collage-item').forEach(el => galleryObs.observe(el));

  // Amenity card stagger (fires on grid container)
  const cardObs = new IntersectionObserver((entries, obs) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.querySelectorAll('.amenity-card').forEach((c, i) => setTimeout(() => c.classList.add('card-visible'), i * 75));
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.08 });
  const amenitiesGrid = document.querySelector('.amenities-grid');
  if (amenitiesGrid) cardObs.observe(amenitiesGrid);

  // Stats: stagger entrance + count-up
  const statBarEl = document.querySelector('.stat-bar');
  if (statBarEl) {
    const statObs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          document.querySelectorAll('.stat').forEach((s, i) => {
            setTimeout(() => {
              s.classList.add('stat-in');
              const numEl = s.querySelector('.stat-num');
              if (numEl) animateCount(numEl, numEl.textContent.trim());
            }, i * 130);
          });
          statObs.disconnect();
        }
      });
    }, { threshold: 0.5 });
    statObs.observe(statBarEl);
  }

  // Testimonial + stars reveal
  const testimonialEl = document.querySelector('.testimonial');
  if (testimonialEl) {
    const testiObs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          // Testimonial text
          setTimeout(() => testimonialEl.classList.add('testi-in'), 100);
          // Stars: pop in one by one
          document.querySelectorAll('.star-item').forEach((s, i) => {
            setTimeout(() => s.classList.add('star-in'), 500 + i * 100);
          });
          testiObs.disconnect();
        }
      });
    }, { threshold: 0.35 });
    testiObs.observe(testimonialEl);
  }

  // ── 10. SCROLLSPY ─────────────────────────────────────────────────────────
  const navSections = ['home','about','features','gallery','partners','reviews','faqs'];
  const navMap = {};
  navSections.forEach(id => {
    const el = document.getElementById(id);
    const link = document.querySelector(`.nav-links a[href="#${id}"]`);
    if (el && link) navMap[id] = { el, link };
  });

  const spyObs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      const id = entry.target.id;
      if (navMap[id]) {
        navMap[id].link.classList.toggle('nav-active', entry.isIntersecting);
      }
    });
  }, { threshold: 0.35 });

  Object.values(navMap).forEach(({ el }) => spyObs.observe(el));

  // ── 11. PARTNER DUO STAGGER ───────────────────────────────────────────────
  const partnerDuoObs = new IntersectionObserver((entries, obs) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.querySelectorAll('.partners-duo-item').forEach((item, i) => {
          setTimeout(() => item.classList.add('partner-duo-in'), i * 160);
        });
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.2 });
  const duoEl = document.querySelector('.partners-duo');
  if (duoEl) partnerDuoObs.observe(duoEl);

  // ── 12. FAQ ANSWER INITIAL STATE ──────────────────────────────────────────
  document.querySelectorAll('.faq-answer').forEach(a => { a.style.maxHeight = '0'; a.style.opacity = '0'; });

  // ── 14. NAV TOGGLE ────────────────────────────────────────────────────────
  const navToggle  = document.querySelector('.nav-toggle');
  const navLinks   = document.querySelector('.nav-links');
  const navAnchors = document.querySelectorAll('.nav-links a');

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      navToggle.classList.toggle('open', open);
      navToggle.setAttribute('aria-expanded', String(open));
    });
    navAnchors.forEach(a => a.addEventListener('click', () => {
      navLinks.classList.remove('open');
      navToggle.classList.remove('open');
      navToggle.setAttribute('aria-expanded', 'false');
    }));
  }

  // ── 12. FAQ ACCORDION ─────────────────────────────────────────────────────
  document.querySelectorAll('.faq-question').forEach(q => {
    const answer = q.nextElementSibling;
    if (!answer) return;
    q.addEventListener('click', () => {
      const expanded = q.getAttribute('aria-expanded') === 'true';
      // Close all others
      document.querySelectorAll('.faq-question[aria-expanded="true"]').forEach(other => {
        if (other !== q) {
          other.setAttribute('aria-expanded', 'false');
          other.parentElement.classList.remove('open');
          other.nextElementSibling.style.maxHeight = '0';
        }
      });
      q.setAttribute('aria-expanded', String(!expanded));
      q.parentElement.classList.toggle('open', !expanded);
      answer.style.maxHeight = !expanded ? `${answer.scrollHeight}px` : '0';
      answer.style.opacity  = !expanded ? '1' : '0';
    });
  });

  // ── ENQUIRY FORM ──────────────────────────────────────────────────────────
  const enquiryForm = document.getElementById('enquiry-form');
  if (enquiryForm) {
    const submitBtn  = enquiryForm.querySelector('.enquiry-submit');
    const required   = enquiryForm.querySelectorAll('[required]');

    // Inline validation
    required.forEach(field => {
      field.addEventListener('blur', () => validateField(field));
      field.addEventListener('input', () => {
        if (field.classList.contains('field-invalid')) validateField(field);
      });
    });

    function validateField(field) {
      const errEl = field.closest('.field-group')?.querySelector('.field-error');
      const empty  = !field.value.trim();
      const badEmail = field.type === 'email' && field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value);
      let msg = '';
      if (empty)    msg = 'This field is required.';
      if (badEmail) msg = 'Please enter a valid email address.';
      field.classList.toggle('field-invalid', !!(empty || badEmail));
      if (errEl) errEl.textContent = msg;
      return !(empty || badEmail);
    }

    function validateAll() {
      return [...required].map(validateField).every(Boolean);
    }

    enquiryForm.addEventListener('submit', async e => {
      e.preventDefault();
      if (!validateAll()) return;

      submitBtn.classList.add('loading');
      enquiryForm.classList.remove('form-error');

      try {
        const res  = await fetch('https://api.web3forms.com/submit', {
          method:  'POST',
          headers: { Accept: 'application/json' },
          body:    new FormData(enquiryForm),
        });
        const data = await res.json();

        submitBtn.classList.remove('loading');

        if (data.success) {
          const successEl = enquiryForm.querySelector('.enquiry-success');
          // Hide form fields, show success
          Array.from(enquiryForm.children).forEach(el => {
            if (!el.classList.contains('enquiry-success') && !el.classList.contains('enquiry-error')) {
              el.style.display = 'none';
            }
          });
          if (successEl) successEl.classList.add('visible');
          enquiryForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
          enquiryForm.classList.add('form-error');
        }
      } catch {
        submitBtn.classList.remove('loading');
        enquiryForm.classList.add('form-error');
      }
    });
  }

  // ── QUOTE MODAL ───────────────────────────────────────────────────────────
  const quoteModal    = document.getElementById('quote-modal');
  const modalClose    = document.getElementById('quote-modal-close');
  const modalBackdrop = document.getElementById('quote-modal-backdrop');

  function openQuoteModal() {
    if (!quoteModal) return;
    quoteModal.hidden = false;
    requestAnimationFrame(() => quoteModal.classList.add('is-open'));
    document.body.style.overflow = 'hidden';
    if (modalClose) modalClose.focus();
  }

  function closeQuoteModal() {
    if (!quoteModal) return;
    quoteModal.classList.remove('is-open');
    setTimeout(() => { quoteModal.hidden = true; }, 420);
    document.body.style.overflow = '';
  }

  document.querySelectorAll('[data-open-quote]').forEach(el => {
    el.addEventListener('click', e => { e.preventDefault(); openQuoteModal(); });
  });

  if (modalClose)    modalClose.addEventListener('click', closeQuoteModal);
  if (modalBackdrop) modalBackdrop.addEventListener('click', closeQuoteModal);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && quoteModal && !quoteModal.hidden) closeQuoteModal();
  });

  // ── HELPER: COUNT-UP ──────────────────────────────────────────────────────
  function animateCount(el, targetText, duration = 1500) {
    const num = parseInt(targetText.replace(/\D/g, ''));
    if (isNaN(num)) return;
    const suffix = targetText.replace(/[0-9]/g, '');
    const start  = performance.now();
    (function tick(now) {
      const p     = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.floor(eased * num) + suffix;
      if (p < 1) requestAnimationFrame(tick);
      else el.textContent = targetText;
    })(start);
  }

});
