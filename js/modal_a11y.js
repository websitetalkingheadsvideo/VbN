/**
 * Modal A11y Helper (lightweight, reusable)
 * - Focus trap inside modal
 * - ESC to close
 * - Inert/aria-hidden background
 * - Focus modal container first (better for SRs), then first focusable
 * - Data API: [data-modal-open="#id"], [data-modal-close]
 */
(function () {
  function getFocusable(container) {
    const selectors = [
      'a[href]', 'button:not([disabled])', 'input:not([disabled])', 'select:not([disabled])',
      'textarea:not([disabled])', '[tabindex]:not([tabindex="-1"])'
    ];
    return Array.from(container.querySelectorAll(selectors.join(',')))
      .filter(el => el.offsetParent !== null);
  }

  function setSiblingsInert(modal, inert) {
    const parent = modal.parentElement;
    if (!parent) return;
    const siblings = Array.from(parent.children).filter(ch => ch !== modal);
    siblings.forEach(el => {
      if (inert) {
        el.setAttribute('aria-hidden', 'true');
        try { el.inert = true; } catch (_) {}
      } else {
        el.removeAttribute('aria-hidden');
        try { el.inert = false; } catch (_) {}
      }
    });
  }

  function trapFocus(modal) {
    function onKeyDown(e) {
      if (e.key === 'Tab') {
        const nodes = getFocusable(modal);
        if (!nodes.length) return;
        const first = nodes[0];
        const last = nodes[nodes.length - 1];
        if (e.shiftKey && document.activeElement === first) {
          e.preventDefault();
          last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      } else if (e.key === 'Escape') {
        const id = modal.getAttribute('id');
        if (id) closeModalA11y(id);
      }
    }
    modal.__trapHandler = onKeyDown;
    document.addEventListener('keydown', onKeyDown);
  }

  function releaseFocus(modal) {
    if (modal && modal.__trapHandler) {
      document.removeEventListener('keydown', modal.__trapHandler);
      delete modal.__trapHandler;
    }
  }

  let previouslyFocused = null;

  function openModalA11y(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    previouslyFocused = document.activeElement;
    // Set required attributes
    if (!modal.hasAttribute('role')) modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-hidden', 'false');
    modal.removeAttribute('inert');

    // Show visually
    modal.classList.add('active');

    // Focus modal container for SRs first
    if (!modal.hasAttribute('tabindex')) modal.setAttribute('tabindex', '-1');
    try { modal.focus(); } catch (_) {}

    // Then move to first focusable if present for keyboard users
    const nodes = getFocusable(modal);
    if (nodes.length) {
      try { nodes[0].focus(); } catch (_) {}
    }

    // Inert background and trap focus
    setSiblingsInert(modal, true);
    trapFocus(modal);
  }

  function closeModalA11y(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    setSiblingsInert(modal, false);
    releaseFocus(modal);
    if (previouslyFocused && typeof previouslyFocused.focus === 'function') {
      try { previouslyFocused.focus(); } catch (_) {}
    }
  }

  function closeAnyOpenModal() {
    const open = document.querySelector('[role="dialog"].active');
    if (open && open.id) closeModalA11y(open.id);
  }

  // Data API
  document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('[data-modal-open]');
    if (openBtn) {
      const id = openBtn.getAttribute('data-modal-open').replace('#', '');
      openModalA11y(id);
      e.preventDefault();
      return;
    }
    const closeBtn = e.target.closest('[data-modal-close]');
    if (closeBtn) {
      const modal = closeBtn.closest('[role="dialog"]');
      if (modal && modal.id) closeModalA11y(modal.id);
      e.preventDefault();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAnyOpenModal();
  });

  // Expose API
  window.ModalA11y = { open: openModalA11y, close: closeModalA11y };
})();

