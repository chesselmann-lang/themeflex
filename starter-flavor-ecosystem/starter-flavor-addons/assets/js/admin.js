/**
 * Starter Flavor Addons – Admin JS
 * Demo Importer with AJAX step-by-step progress
 */
(function ($) {
  'use strict';

  // ── Filter tabs ─────────────────────────────────────────────────────────
  $(document).on('click', '.sf-filter-btn', function () {
    const $btn = $(this);
    const cat  = $btn.data('cat');

    $('.sf-filter-btn').removeClass('active');
    $btn.addClass('active');

    if (cat === 'all') {
      $('.sf-demo-card').show();
    } else {
      $('.sf-demo-card').each(function () {
        $(this).toggle($(this).data('cat') === cat);
      });
    }
  });

  // ── Demo import trigger ──────────────────────────────────────────────────
  $(document).on('click', '.sf-import-btn', function () {
    const demoSlug = $(this).data('demo');
    const demoName = $(this).data('name');

    if (!confirm(sfAddons.i18n.confirmText)) return;

    openModal(demoName);
    runStep(demoSlug, 0);
  });

  function openModal(name) {
    $('#sf-modal-title').text(sfAddons.i18n.installing + ' ' + name);
    $('#sf-modal-status').text('Preparing…');
    $('.sf-progress-fill').css('width', '0%');
    $('.sf-step').removeClass('is-active is-done');
    $('#sf-modal-done').hide();
    $('#sf-import-modal, #sf-modal-overlay').fadeIn(200);
  }

  function closeModal() {
    $('#sf-import-modal, #sf-modal-overlay').fadeOut(200);
  }

  $(document).on('click', '#sf-modal-overlay', closeModal);

  function runStep(demo, step) {
    $.post(sfAddons.ajaxUrl, {
      action: 'sf_demo_install',
      nonce:  sfAddons.nonce,
      demo:   demo,
      step:   step,
    })
    .done(function (res) {
      if (!res.success) {
        $('#sf-modal-status').text(sfAddons.i18n.error + ' ' + (res.data?.message || ''));
        return;
      }

      const data = res.data;

      // Update progress bar
      $('.sf-progress-fill').css('width', data.percent + '%');
      $('#sf-modal-status').text(data.message);

      // Mark steps
      for (let i = 0; i < step; i++) {
        $('[data-step="' + i + '"]').removeClass('is-active').addClass('is-done');
      }
      if (step < 5) {
        $('[data-step="' + step + '"]').addClass('is-active');
      }

      if (data.step === 'done') {
        $('.sf-progress-fill').css('width', '100%');
        $('#sf-modal-done').show();
        if (data.url) {
          $('#sf-view-site').attr('href', data.url);
        }
        return;
      }

      // Continue to next step
      setTimeout(function () {
        runStep(demo, data.step);
      }, 600);
    })
    .fail(function () {
      $('#sf-modal-status').text(sfAddons.i18n.error);
    });
  }

  // ── Copy shortcode ───────────────────────────────────────────────────────
  $(document).on('click', '.sf-copy-shortcode', function () {
    const code = $(this).data('shortcode');
    navigator.clipboard.writeText(code).then(function () {
      const $btn = $(this);
      $btn.text('Copied!');
      setTimeout(() => $btn.text('Copy'), 2000);
    }.bind(this));
  });

})(jQuery);
