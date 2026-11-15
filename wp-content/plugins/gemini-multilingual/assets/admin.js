(function ($) {
  'use strict';

  function postAjax(action, data) {
    return $.post(GM_Admin_Vars.ajaxUrl, $.extend({ action: action, nonce: GM_Admin_Vars.nonce }, data));
  }

  // Toggle custom model input.
  $('#gm_model').on('change', function () {
    if ($(this).val() === 'custom') {
      $('#gm-custom-model-wrap').show();
    } else {
      $('#gm-custom-model-wrap').hide();
    }
  });

  // Test Gemini API connection.
  $('#gm-test-connection').on('click', function () {
    const $btn = $(this);
    const $res = $('#gm-test-result');
    $btn.prop('disabled', true);
    $res.removeClass('ok err').text(GM_Admin_Vars.i18n.testing);

    postAjax('gm_test_connection', {}).done(function (resp) {
      if (resp.success) {
        $res.addClass('ok').text('✓ Connected (' + resp.data.model + '): ' + resp.data.sample);
      } else {
        $res.addClass('err').text('✗ ' + resp.data);
      }
    }).fail(function () {
      $res.addClass('err').text('✗ Request failed.');
    }).always(function () {
      $btn.prop('disabled', false);
    });
  });

  // Refresh Gemini models from API.
  $('#gm-refresh-models').on('click', function () {
    const $btn = $(this);
    const $res = $('#gm-refresh-result');
    $btn.prop('disabled', true);
    $res.removeClass('ok err').text('Fetching...');

    postAjax('gm_refresh_models', {}).done(function (resp) {
      if (resp.success && resp.data.models) {
        const $sel = $('#gm_model');
        const current = $sel.val();
        $.each(resp.data.models, function (id, label) {
          if ($sel.find('option[value="' + id + '"]').length === 0) {
            $('<option>', { value: id, text: label }).insertBefore($sel.find('option[value="custom"]'));
          }
        });
        $sel.val(current);
        $res.addClass('ok').text('✓ Models updated!');
      } else {
        $res.addClass('err').text('✗ ' + resp.data);
      }
    }).always(function () {
      $btn.prop('disabled', false);
    });
  });

  // 1-Click Fix Mismatched Post Language.
  $(document).on('click', '.gm-fix-post-lang', function () {
    const $btn = $(this);
    const postId = $btn.data('post-id');
    const lang = $btn.data('lang');
    const origText = $btn.text();

    $btn.prop('disabled', true).text('...');
    postAjax('gm_fix_post_language', { post_id: postId, lang: lang }).done(function (resp) {
      if (resp.success) {
        location.reload();
      } else {
        $btn.prop('disabled', false).text(origText);
        alert(resp.data || 'Error');
      }
    }).fail(function () {
      $btn.prop('disabled', false).text(origText);
    });
  });

  // 1-Click AI Translate Post / Page / Product.
  $(document).on('click', '.gm-one-click-translate', function () {
    const $btn = $(this);
    const postId = $btn.data('post-id');
    const lang = $btn.data('lang');
    const origText = $btn.text();
    const $status = $btn.closest('.gm-metabox-translations').find('.gm-metabox-status');

    $btn.prop('disabled', true).text('...');
    if ($status.length) {
      $status.text(GM_Admin_Vars.i18n.translating);
    }

    postAjax('gm_translate_post', { post_id: postId, lang: lang }).done(function (resp) {
      if (resp.success) {
        if ($btn.hasClass('gm-col-add')) {
          $btn.replaceWith('<a href="' + resp.data.edit_url + '" class="gm-col-flag gm-col-done">✓</a>');
        } else if (resp.data.same_post) {
          $btn.text('✓ Done');
          if ($status.length) {
            $status.html('<span style="color:#007017;">✓ Checked &amp; updated! Reloading...</span>');
          }
          setTimeout(function () { location.reload(); }, 700);
        } else {
          $btn.text('✓ Done');
          if ($status.length) {
            $status.html('<span style="color:#007017;">✓ Translated! <a href="' + resp.data.edit_url + '">Open translation</a></span>');
          }
        }
      } else {
        $btn.prop('disabled', false).text(origText);
        if ($status.length) {
          $status.html('<span style="color:#b32d2e;">✗ ' + resp.data + '</span>');
        } else {
          alert(resp.data);
        }
      }
    }).fail(function () {
      $btn.prop('disabled', false).text(origText);
    });
  });

  // 1-Click Translate to ALL Languages in Metabox.
  $(document).on('click', '.gm-translate-all-btn', async function () {
    const $btn = $(this);
    const $buttons = $btn.closest('.gm-metabox-translations').find('.gm-one-click-translate:not([data-is-current="1"])');
    const $status = $btn.closest('.gm-metabox-translations').find('.gm-metabox-status');

    $btn.prop('disabled', true);
    let done = 0;
    const total = $buttons.length;

    for (let i = 0; i < total; i++) {
      const $b = $($buttons[i]);
      $status.text(GM_Admin_Vars.i18n.translating + ' (' + (i + 1) + '/' + total + ')');
      try {
        await postAjax('gm_translate_post', { post_id: $b.data('post-id'), lang: $b.data('lang') });
        done++;
      } catch (e) {
        // continue next language
      }
    }

    $btn.prop('disabled', false);
    $status.html('<span style="color:#007017;">✓ Translated ' + done + '/' + total + ' languages. Reloading...</span>');
    setTimeout(function () { location.reload(); }, 900);
  });

  // 1-Click Translate Taxonomy Term.
  $(document).on('click', '.gm-one-click-translate-term', function () {
    const $btn = $(this);
    const $status = $btn.closest('td').find('.gm-term-status');
    $btn.prop('disabled', true);
    $status.removeClass('ok err').text(GM_Admin_Vars.i18n.translating);

    postAjax('gm_translate_term', {
      term_id: $btn.data('term-id'),
      taxonomy: $btn.data('taxonomy'),
      lang: $btn.data('lang')
    }).done(function (resp) {
      if (resp.success) {
        $status.addClass('ok').text('✓ Term translated (ID #' + resp.data.target_term_id + ')');
      } else {
        $status.addClass('err').text('✗ ' + resp.data);
      }
    }).always(function () {
      $btn.prop('disabled', false);
    });
  });

  // Scan Theme / Plugin Strings.
  $('#gm-scan-package-btn').on('click', function () {
    const $btn = $(this);
    const $opt = $('#gm-package-select option:selected');
    const packageId = $opt.val();
    const domain = $opt.data('domain');
    const $status = $('#gm-scan-status');

    $btn.prop('disabled', true);
    $status.removeClass('ok err').text(GM_Admin_Vars.i18n.scanning);

    postAjax('gm_scan_package', { package_id: packageId }).done(function (resp) {
      if (resp.success) {
        $status.addClass('ok').text('✓ Found ' + resp.data.found + ' strings (' + resp.data.inserted + ' new). Reloading...');
        setTimeout(function () {
          const url = new URL(window.location.href);
          url.searchParams.set('domain', domain);
          window.location.href = url.toString();
        }, 800);
      } else {
        $status.addClass('err').text('✗ ' + resp.data);
      }
    }).always(function () {
      $btn.prop('disabled', false);
    });
  });

  // Save individual string translation.
  $(document).on('click', '.gm-save-string-btn', function () {
    const $btn = $(this);
    const $tr = $btn.closest('tr');
    const id = $tr.data('string-id');
    const translation = $tr.find('.gm-string-input').val();

    $btn.prop('disabled', true).text('Saving...');
    postAjax('gm_save_string', { id: id, translation: translation }).done(function () {
      $btn.text('✓ Saved');
      setTimeout(function () { $btn.prop('disabled', false).text('Save'); }, 1200);
    });
  });

  // Batch AI Translate all untranslated strings in a domain.
  $('#gm-ai-translate-domain-btn').on('click', async function () {
    const $btn = $(this);
    const domain = $btn.data('domain');
    const lang = $btn.data('lang');
    const $wrap = $('#gm-string-progress');
    const $fill = $wrap.find('.gm-progress-fill');
    const $text = $wrap.find('.gm-progress-text');

    $btn.prop('disabled', true);
    $wrap.show();
    let totalTranslated = 0;

    while (true) {
      $text.text('Translating batch with Gemini AI... (' + totalTranslated + ' translated so far)');
      const resp = await postAjax('gm_ai_translate_strings_batch', { domain: domain, lang: lang });
      if (!resp.success) {
        $text.text('Error: ' + resp.data);
        $btn.prop('disabled', false);
        return;
      }
      totalTranslated += resp.data.translated;
      const remaining = resp.data.remaining;
      const pct = remaining === 0 ? 100 : Math.min(95, Math.round((totalTranslated / (totalTranslated + remaining)) * 100));
      $fill.css('width', pct + '%');

      if (remaining === 0 || resp.data.translated === 0) {
        break;
      }
    }

    $text.text('✓ Completed! Translated ' + totalTranslated + ' strings. Reloading...');
    setTimeout(function () { location.reload(); }, 900);
  });

  // Bulk Content Translator.
  let bulkItems = [];
  $('#gm-load-untranslated-btn').on('click', function () {
    const postType = $('#gm-bulk-post-type').val();
    const lang = $('#gm-bulk-target-lang').val();
    const $list = $('#gm-bulk-list');

    $list.html('<p>Scanning...</p>');
    postAjax('gm_get_untranslated_posts', { post_type: postType, lang: lang }).done(function (resp) {
      if (!resp.success) {
        $list.html('<p>' + resp.data + '</p>');
        return;
      }
      bulkItems = resp.data.items || [];
      if (bulkItems.length === 0) {
        $list.html('<p>All items of this type are already translated into ' + lang + '!</p>');
        $('#gm-run-bulk-btn').hide();
        return;
      }
      let html = '<p><strong>' + bulkItems.length + ' untranslated items found:</strong></p><ul>';
      bulkItems.slice(0, 50).forEach(function (it) {
        html += '<li id="gm-bulk-item-' + it.id + '">#' + it.id + ' — ' + $('<div>').text(it.title).html() + ' <span class="gm-bulk-st"></span></li>';
      });
      if (bulkItems.length > 50) {
        html += '<li>...and ' + (bulkItems.length - 50) + ' more</li>';
      }
      html += '</ul>';
      $list.html(html);
      $('#gm-run-bulk-btn').show();
    });
  });

  $('#gm-run-bulk-btn').on('click', async function () {
    const $btn = $(this);
    const lang = $('#gm-bulk-target-lang').val();
    const $wrap = $('#gm-bulk-progress');
    const $fill = $wrap.find('.gm-progress-fill');
    const $text = $wrap.find('.gm-progress-text');

    $btn.prop('disabled', true);
    $wrap.show();

    for (let i = 0; i < bulkItems.length; i++) {
      const it = bulkItems[i];
      $text.text('Translating ' + (i + 1) + ' / ' + bulkItems.length + ': ' + it.title);
      const resp = await postAjax('gm_translate_post', { post_id: it.id, lang: lang });
      const $itemEl = $('#gm-bulk-item-' + it.id + ' .gm-bulk-st');
      if (resp.success) {
        $itemEl.html('<span style="color:#007017;">✓ Done</span>');
      } else {
        $itemEl.html('<span style="color:#b32d2e;">✗ ' + resp.data + '</span>');
      }
      $fill.css('width', Math.round(((i + 1) / bulkItems.length) * 100) + '%');
    }

    $text.text('✓ Bulk translation finished!');
    $btn.prop('disabled', false);
  });

})(jQuery);
