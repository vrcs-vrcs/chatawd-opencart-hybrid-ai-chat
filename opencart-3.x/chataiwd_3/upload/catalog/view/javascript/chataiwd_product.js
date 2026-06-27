/* chataiwd_product.js */
const cfg = window.config || {};

$(document).on('submit', '.form-chataiwd-cart', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('.btn-cart');

    $.ajax({
        url: cfg.add_to_cart_url,
        type: 'post',
        data: $form.serialize(),
        dataType: 'json',
        beforeSend: function() {
            if (!$btn.data('original-content')) {
                $btn.data('original-content', $btn.html());
            }

            $btn.prop('disabled', true).addClass('loading');
            $btn.html('<i class="fa-solid fa-circle-notch fa-spin"></i>');
        },
        complete: function() {
            $btn.prop('disabled', false).removeClass('loading');
        },
        success: function(json) {

            if (json['error']) {
                if (json['redirect']) {
                    const productId = $form.find('input[name="product_id"]').val();

                    if (productId) {
                        openProductModal(productId);
                    } else {
                        // Végső mentőöv, ha valamiért nem lenne meg az ID
                        location = json['redirect'];
                    }

                    // A gombot alaphelyzetbe állítjuk a kártyán
                    $btn.removeClass('loading').prop('disabled', false).html($btn.data('original-content'));
                } else if (json['error']['warning']) {
                    alert(json['error']['warning']);
                }
            }

            if (json['success']) {
                $('#cart').load('index.php?route=common/cart'+cfg.method_separator+'info');

                if (json['total']) {
                    $('.cart-total').html(json['total']);
                }

                $btn.addClass('btn-success').html('<i class="fa fa-check"></i>');

                setTimeout(() => {
                    $btn.removeClass('btn-success loading')
                        .prop('disabled', false)
                        .html($btn.data('original-content'));
                }, 2000);
            }
        },
        error: function (e) {
            debugger;
        }
    });
});

function openProductModal(productId) {
    $('.iwd-overlay').remove();
    $('.iwd-modal-wrapper').remove();



    const skeletonHtml = `
                    <div class="iwd-overlay"></div>
                    <div class="iwd-modal-wrapper" id="chataiwd-modal">
                        <div class="iwd-modal-card">
                            <div id="iwd-modal-content">
                                <div class="loader-overlay">
                                    <img src="${cfg.loader_gif}" alt="Loading..." class="loader-gif">
                                    <p>Loading product ...</p>
                                </div>
                            </div>
                        </div>
                    </div>`;

    $('body').append(skeletonHtml).addClass('iwd-modal-open');

    $.ajax({
        url: cfg.product_info_url,
        type: 'post',
        data: { product_id: productId },
        dataType: 'html',
        beforeSend: function () {
            $('.iwd-overlay').fadeIn(250);
            $('.iwd-modal-wrapper').css('display', 'flex').hide().fadeIn(250);
        },
        success: function(html) {
            $('.iwd-modal-wrapper, .iwd-overlay').remove();

            $('body').append(html).addClass('iwd-modal-open');

            $('.iwd-overlay').show();
            $('.iwd-modal-wrapper').css('display', 'flex');

        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

$(document).off('click', '.chataiwd-quickview-btn').on('click', '.chataiwd-quickview-btn', function(e) {
    e.preventDefault();
    const productId = $(this).closest('.chataiwd-card').find('input[name=product_id]').val();
    openProductModal(productId);
});

const iwdClose = () => {
    $('.iwd-overlay, .iwd-modal-wrapper').fadeOut(250, function() {
        $(this).remove();
        $('body').removeClass('iwd-modal-open');
        $('#iwd-dynamic-css').remove();
    });
};

// Kattintás a kártya MELLÉ (a wrapperre) bezárja
$(document).on('click', '.iwd-modal-wrapper', function(e) {
    if (e.target === this) iwdClose();
});

$(document).on('click', '.iwd-modal-close', function(e) {
    e.preventDefault();
    iwdClose();
});

$(document).on('click', '.iwd-tabs a', function(e) {
    e.preventDefault();

    const targetId = $(this).attr('href'); // Ez most már pl. #tabs-specification lesz

    // 1. Gombok: csak a MODALON belüli tab-gombokat bántjuk
    $(this).closest('.iwd-tabs').find('a').removeClass('active');
    $(this).addClass('active');

    // 2. Panelek: csak a MODALON belüli paneleket kapcsoljuk
    $(this).closest('.iwd-modal-card').find('.iwd-tab-pane').removeClass('active');
    $(targetId).addClass('active');
});

// Kiskép csere (Thumb Swap)
$(document).on('click', '.thumb-swap', function() {
    const $this = $(this);
    const newImageSrc = $this.data('full'); // A nagy kép elérése

    // 1. Kicseréljük a főképet
    $('#main-product-image').attr('src', newImageSrc);

    // 2. Vizuális visszajelzés (aktív keret a kisképnek)
    $('.thumb-swap').css('border-color', '#ddd'); // Alaphelyzet
    $this.css('border-color', '#2f3eb1');         // Kiemelés
});

$(document).on('submit', '#form-product-qv', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('.btn-buy');

    $.ajax({
        url: cfg.add_to_cart_url,
        type: 'post',
        data: $('#form-product-qv').serialize(),
        dataType: 'json',
        beforeSend: function() {
            $btn.prop('disabled', true).addClass('loading').find('i').attr('class', 'fa fa-spinner fa-spin');
        },
        complete: function() {
            $btn.prop('disabled', false).removeClass('loading').find('i').attr('class', 'fa fa-shopping-cart');
        },
        success: function(json) {
            // 1. Tisztítás
            $('.iwd-invalid-feedback').hide().html('');
            $('.iwd-is-invalid').removeClass('iwd-is-invalid');
            $('#modal-alert-container').slideUp().html('');

            if (json['error']) {
                if (json['error']['option']) {
                    for (let i in json['error']['option']) {
                        let cleanId = i.replace('_', '-');
                        let $errorDiv = $('#error-option-' + cleanId);
                        $form.find('#input-option-' + cleanId).addClass('iwd-is-invalid');
                        $errorDiv.html(json['error']['option'][i]).fadeIn();
                    }
                }

                if (json['error']['warning']) {
                    $('#modal-alert-container').html('<div class="iwd-alert iwd-alert-danger">' + json['error']['warning'] + '</div>').slideDown();
                }

                let $firstError = $('.iwd-is-invalid, .iwd-invalid-feedback:visible').first();

                if ($firstError.length) {
                    // Megkeressük a szülőt is (qv-form-group), hogy ne csak a piros betűt,
                    // hanem az egész beviteli mezőt lássuk
                    let $container = $firstError.closest('.qv-form-group');
                    let target = $container.length ? $container : $firstError;

                    let scrollPos = target.offset().top - $('.iwd-modal-wrapper').offset().top + $('.iwd-modal-wrapper').scrollTop() - 40;

                    $('.iwd-modal-wrapper').animate({
                        scrollTop: scrollPos
                    }, 'slow');
                } else {
                    // Ha nincs konkrét mezőhiba (csak warning), akkor megyünk a tetejére
                    $('.iwd-modal-wrapper').animate({ scrollTop: 0 }, 'slow');
                }
            }

            if (json['success']) {
                // Siker üzenet a modalon belül
                $('#modal-alert-container').html('<div class="iwd-alert iwd-alert-success">' + json['success'] + '</div>').slideDown();


                setTimeout(function() {
                    $('#modal-alert-container .iwd-alert-success')
                        .fadeTo(800, 0.1, function() {
                            // Miután láthatatlan lett (fade), finoman felhúzzuk a redőnyt (slide)
                            $(this).slideUp(600, function() {
                                $(this).remove();
                            });
                        });
                }, 2500);
                $('#chataiwd-modal').animate({
                    scrollTop: 0
                }, 'slow');

                // Frissítjük a fejlécben a kosár számlálót (OpenCart specifikus)
                setTimeout(function () {
                    $('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
                }, 100);

                // A kosár dropdown frissítése (ha van ilyen az oldalon)
                $('#cart').load('index.php?route=common/cart'+cfg.method_separator+'info');

                // Opcionális: Modal bezárása 2 másodperc múlva
                 setTimeout(iwdClose, 3500);
            }
        },
        error: function (e) {
            debugger;
        }
    });
});

$(document).on('click', 'button[id^="button-upload"]', function() {
        const $node = $(this);
        const $input = $node.parent().find('input[type="hidden"]');
        const optionId = $node.attr('id').replace('button-upload-', '');
        const $infoDiv = $('#iwd-upload-filename-' + optionId);

        // Létrehozunk egy láthatatlan file inputot
        $('#form-upload').remove();
    $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');

    $('#form-upload input[name=\'file\']').trigger('click');

    if (typeof timer !== 'undefined') {
        clearInterval(timer);
    }

    // Figyeljük, mikor választott ki fájlt
    var timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
            clearInterval(timer);

            $.ajax({
                url: cfg.file_upload,
                type: 'post',
                data: new FormData($('#form-upload')[0]),
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $node.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
                },
                complete: function() {
                    $node.prop('disabled', false).html('<i class="fa-solid fa-upload"></i> Upload File');
                },
                success: function(json) {
                    $('.iwd-invalid-feedback').hide();

                    if (json['error']) {
                        alert(json['error']); // Vagy használd a saját iwd-alertedet
                    }

                    if (json['success']) {
                        // Elmentjük a kódot a rejtett inputba
                        $input.val(json['code']);
                        // Megjelenítjük a fájl nevét a felhasználónak
                        $infoDiv.html('<i class="fa fa-check text-success"></i> ' + json['success']).fadeIn();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});