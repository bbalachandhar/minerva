// Profile V2 — shared presentation-layer JS for the redesigned Student & Staff
// profile pages. Pure UI helpers only (tab switching, toast, copy-to-clipboard,
// password visibility, modal open/close). Business logic stays in each view's
// own existing AJAX/links — this file does not talk to the server.
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    var root = document.querySelector('.mn-profile-v2');
    if (!root) return;

    // ---- Tab switching, with location.hash support for existing deep links ----
    var tabLinks = root.querySelectorAll('.tab-link');
    var tabPanes = root.querySelectorAll('.tab-pane');
    var tabActions = root.querySelectorAll('[data-tab-actions]');

    function activateTab(tabId) {
      var found = false;
      tabPanes.forEach(function (pane) {
        if (pane.id === tabId) {
          pane.classList.add('active');
          found = true;
        } else {
          pane.classList.remove('active');
        }
      });
      tabLinks.forEach(function (link) {
        link.classList.toggle('active', link.getAttribute('data-tab-target') === tabId);
      });
      tabActions.forEach(function (el) {
        el.classList.toggle('active', el.getAttribute('data-tab-actions') === tabId);
      });
      return found;
    }

    tabLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        var targetId = link.getAttribute('data-tab-target');
        if (activateTab(targetId)) {
          history.replaceState(null, '', '#' + targetId);
        }
      });
    });

    var initialHash = window.location.hash ? window.location.hash.substring(1) : '';
    if (!initialHash || !activateTab(initialHash)) {
      if (tabLinks.length) {
        activateTab(tabLinks[0].getAttribute('data-tab-target'));
      }
    }

    window.addEventListener('hashchange', function () {
      var hash = window.location.hash ? window.location.hash.substring(1) : '';
      if (hash) activateTab(hash);
    });

    // ---- Toast notifications ----
    var toastTimeout;
    window.mnpShowToast = function (message) {
      var toast = document.getElementById('mnpToastNotification');
      var toastMsg = document.getElementById('mnpToastMessage');
      if (!toast || !toastMsg) return;
      toastMsg.innerText = message;
      toast.classList.add('active');
      clearTimeout(toastTimeout);
      toastTimeout = setTimeout(function () {
        toast.classList.remove('active');
      }, 3000);
    };

    // ---- Copy to clipboard ----
    window.mnpCopyText = function (text, label) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () {
          window.mnpShowToast(label + ' copied to clipboard!');
        }).catch(function () {
          window.mnpShowToast('Failed to copy text.');
        });
      }
    };

    // ---- Password show/hide ----
    window.mnpTogglePassVisibility = function (inputId, btnElement) {
      var input = document.getElementById(inputId);
      if (!input) return;
      if (input.type === 'password') {
        input.type = 'text';
        btnElement.classList.add('is-visible');
      } else {
        input.type = 'password';
        btnElement.classList.remove('is-visible');
      }
    };

    // ---- Modal open/close ----
    window.mnpOpenModal = function (modalId) {
      var modal = document.getElementById(modalId);
      if (modal) modal.classList.add('active');
    };

    window.mnpCloseModal = function (modalId) {
      var modal = document.getElementById(modalId);
      if (modal) modal.classList.remove('active');
    };

    root.querySelectorAll('.mnp-modal-overlay').forEach(function (overlay) {
      overlay.addEventListener('click', function (event) {
        if (event.target === overlay) {
          overlay.classList.remove('active');
        }
      });
    });
  });
})();
