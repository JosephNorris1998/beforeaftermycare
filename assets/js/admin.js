/**
 * Before After My Care – Admin JS
 * Handles confirmation dialogs for delete / toggle actions.
 */
(function ($) {
  'use strict';

  $(function () {
    // ── Delete confirmation ───────────────────────────────────
    $(document).on('click', '.bam-confirm-delete', function (e) {
      if (!window.confirm(bamAdmin.i18n.confirmDelete)) {
        e.preventDefault();
      }
    });

    // ── Toggle confirmation ───────────────────────────────────
    $(document).on('click', '.bam-confirm-toggle', function (e) {
      if (!window.confirm(bamAdmin.i18n.confirmToggle)) {
        e.preventDefault();
      }
    });

    // ── Manual reminder – confirmation checkbox unlock ────────
    var $confirmCheck  = $('#bam-reminder-confirm-check');
    var $reminderFields = $('#bam-reminder-fields');
    var $submitBtn     = $('#bam-reminder-submit-btn');

    function toggleReminderFields() {
      var checked = $confirmCheck.is(':checked');
      $reminderFields.css({ opacity: checked ? '1' : '0.4', 'pointer-events': checked ? 'auto' : 'none' });
      $reminderFields.find('input').prop('disabled', !checked);
      $submitBtn.prop('disabled', !checked);
    }

    if ($confirmCheck.length) {
      $confirmCheck.on('change', toggleReminderFields);
      toggleReminderFields(); // init state
    }

    // ── Auto-dismiss notices ──────────────────────────────────
    var notices = document.querySelectorAll('.bam-notice');
    if (notices.length) {
      setTimeout(function () {
        notices.forEach(function (el) {
          el.style.transition = 'opacity 0.5s';
          el.style.opacity = '0';
          setTimeout(function () { el.remove(); }, 500);
        });
      }, 4000);
    }
  });
}(jQuery));
