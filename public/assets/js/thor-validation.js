/**
 * Client-side form validation for the whole admin panel (Parsley.js 2.9 + jQuery 3.7, self-hosted in
 * public/assets/vendor/parsley). It gives instant feedback only — the server still validates everything.
 *
 * Every POST form with editable fields is validated automatically. Rules come from HTML attributes:
 *   required, type="email|url|number", min / max, minlength / maxlength, pattern,
 *   data-parsley-equalto="#f-password", data-parsley-type="integer", and the custom rules below:
 *   data-parsley-max-file-size="3"          each chosen file ≤ 3 MB
 *   data-parsley-file-types="jpg,png,webp"  allowed file extensions
 *   data-parsley-https-url                  full https:// address
 *   data-parsley-youtube                    YouTube link or 11-character video ID
 *   data-parsley-required-when="#f-status:published" (+ data-parsley-validate-if-empty) required only in that case
 *   data-parsley-min-photos="input[name='images[]']" (+ data-parsley-validate-if-empty) keep or add at least one photo
 * Add data-parsley-skip to a <form> to leave it out. Fields added later by Alpine (sizes, offices) are picked up on submit.
 */
(function ($) {
  'use strict';
  if (!$ || !window.Parsley) return;

  var P = window.Parsley;

  P.addMessages('en', {
    defaultMessage: 'This value doesn’t look right.',
    type: {
      email: 'Please enter a valid email address.',
      url: 'Please enter a full web address, starting with https://',
      number: 'Please enter a number.',
      integer: 'Please enter a whole number (no commas or decimals).',
      digits: 'Please use digits only.',
      alphanum: 'Please use letters and numbers only.',
    },
    required: 'This field is required.',
    pattern: 'This value doesn’t look right.',
    min: 'Please enter %s or more.',
    max: 'Please enter %s or less.',
    range: 'Please enter a value between %s and %s.',
    minlength: 'Please use at least %s characters.',
    maxlength: 'Please keep this to %s characters or fewer.',
    length: 'Please use between %s and %s characters.',
    check: 'Please choose between %s and %s options.',
    equalto: 'This doesn’t match.',
  });
  P.setLocale('en');

  var files = function (instance) { return Array.prototype.slice.call(instance.$element[0].files || []); };

  P.addValidator('maxFileSize', {
    requirementType: 'number',
    validateString: function (_value, maxMb, instance) {
      return files(instance).every(function (f) { return f.size <= maxMb * 1024 * 1024; });
    },
    messages: { en: 'Each file must be %s MB or smaller.' },
  });

  P.addValidator('fileTypes', {
    requirementType: 'string',
    validateString: function (_value, types, instance) {
      var allowed = types.toLowerCase().split(',').map(function (t) { return t.trim(); });
      return files(instance).every(function (f) { return allowed.indexOf(f.name.split('.').pop().toLowerCase()) !== -1; });
    },
    messages: { en: 'Please choose a %s file.' },
  });

  P.addValidator('httpsUrl', {
    validateString: function (value) { return /^https:\/\/[^\s/$.?#][^\s]*$/i.test(value); },
    messages: { en: 'Please enter a full web address starting with https://' },
  });

  // Same rule as App\Http\Controllers\Admin\MediaController::youtubeId().
  P.addValidator('youtube', {
    validateString: function (value) {
      return /^[A-Za-z0-9_-]{11}$/.test(value) ||
        /^https?:\/\/(?:www\.|m\.)?(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/|youtube-nocookie\.com\/embed\/)[A-Za-z0-9_-]{11}/i.test(value);
    },
    messages: { en: 'Paste a YouTube link (https://www.youtube.com/watch?v=…) or the 11-character video ID.' },
  });

  P.addValidator('requiredWhen', {
    requirementType: 'string',
    validateString: function (value, requirement) {
      var parts = requirement.split(':');
      return $(parts[0]).val() !== parts[1] || $.trim(value) !== '';
    },
    messages: { en: 'This field is required.' },
  });

  P.addValidator('minPhotos', {
    requirementType: 'string',
    validateString: function (_value, keptSelector, instance) {
      return $(keptSelector).length > 0 || files(instance).length > 0;
    },
    messages: { en: 'Add at least one photo.' },
  });

  var config = {
    errorClass: 'is-invalid',
    successClass: 'is-valid-parsley',
    // CKEditor hides its textarea, so validate it anyway; skip other hidden/disabled fields.
    excluded: 'input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled], :hidden:not([data-ckeditor])',
    focus: 'first',
    triggerAfterFailure: 'input change',
    errorsWrapper: '<span class="t-error parsley-errors" role="alert"></span>',
    errorTemplate: '<span></span>',
    // Put the red outline on the visible box (the editor for CKEditor fields).
    classHandler: function (field) {
      var $el = field.$element;
      return $el.is('[data-ckeditor]') ? $el.closest('.t-editor') : $el;
    },
    // Messages go under the whole field (not inside icon / password wrappers).
    errorsContainer: function (field) {
      var $field = field.$element.closest('.t-field');
      return $field.length ? $field : field.$element.parent();
    },
  };

  function bind(form) {
    var $form = $(form);
    if ($form.data('parsleyBound') || form.hasAttribute('data-parsley-skip')) return;
    if (!form.querySelector('input:not([type=hidden]):not([type=submit]), textarea, select')) return; // e.g. delete / sign-out buttons
    $form.data('parsleyBound', true).attr('novalidate', 'novalidate');

    // Number boxes without step="any" (display order, specs, prices) only take whole numbers, as on the server.
    $form.find('input[type=number]').not('[step=any]').not('[data-parsley-type]').attr('data-parsley-type', 'integer');

    var parsley = $form.parsley(config);

    // CKEditor writes its HTML into the textarea only on submit — sync it before validating.
    parsley.on('form:validate', function () {
      (window.thorEditors || []).forEach(function (editor) { editor.updateSourceElement(); });
    });

    // Once a field is fixed, drop the server's old message for it too.
    parsley.on('field:success', function (field) {
      field.$element.closest('.t-field').find('.t-error').not('.parsley-errors').remove();
    });

    // Prevent double submits while the page is posting.
    parsley.on('form:success', function () {
      $form.find('button[type=submit]').prop('disabled', true).addClass('is-busy');
    });
  }

  // Coming "back" to a page restored from the browser cache: re-enable its buttons.
  window.addEventListener('pageshow', function () {
    $('button.is-busy').prop('disabled', false).removeClass('is-busy');
  });

  $(function () {
    $('form').filter(function () { return (this.getAttribute('method') || 'get').toLowerCase() === 'post'; }).each(function () { bind(this); });
  });

  // Fields edited by CKEditor: re-check when the editor changes after a failed submit.
  window.thorValidateEditor = function (editor) {
    var $ta = $(editor.sourceElement);
    editor.model.document.on('change:data', function () {
      if ($ta.closest('.t-editor').hasClass('is-invalid')) {
        editor.updateSourceElement();
        $ta.parsley().validate();
      }
    });
  };
})(window.jQuery);
