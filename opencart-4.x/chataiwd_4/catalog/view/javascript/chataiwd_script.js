(function($) {
    "use strict";

    const cfg = window.config || {};
    let responseCheckInterval = true; // null helyett true
    let intervalTime = 5000;

    let messageIdToCheck = null;
    const registrationId = cfg.registration_id;
    let lastMessageId = 0;
    let isChatLoading = false;
    let currentChatRequest = null;
    let showMessageInterval = null;
    let showMessageTimeout = null;
    let welcomeLogicActive = localStorage.getItem('vrcs_welcome_muted') !== 'true';
    let needsScrollToBottom = true;
    let anonymousHistoryCache = null;
    let cartLogicActive = localStorage.getItem('vrcs_teaser_muted') !== 'true';
    let lastHasCart = false;
    let lastRenderedStatus = null;
    let lastIsOnlineStatus = null;

    function generateUUID() {
        if (crypto.randomUUID) {
            return crypto.randomUUID().replace(/-/g, '');
        }
        return 'xxxxxxxxxxxx4xxxyxxxxxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    let sessionId = localStorage.getItem('chat_session_token');
    if (!sessionId) {
        sessionId = generateUUID();
        localStorage.setItem('chat_session_token', sessionId);
    }

    let notMergeHistory = localStorage.getItem('not_merge_history');

    let oldestId = null;
    let isLoadingMore = false;
    let limit = 10;


    const chatControl = {
        _isLoading: true,
        isFatalError: false,

        get isLoading() {
            return this._isLoading;
        },

        set isLoading(value) {
            if (this.isFatalError) return;

            this._isLoading = value;
            if (value === true) {
                // ZÁROLÁS: Csak a specifikus ikonokat és funkciókat
                $('.chataiwd-footer i, .chataiwd-footer button, #chataiwd-input').addClass('disabled-icon').css({
                    'pointer-events': 'none',
                    'opacity': '0.5'
                });

            } else {
                // FELOLDÁS
                $('.chataiwd-footer i, .chataiwd-footer button, #chataiwd-input').removeClass('disabled-icon').css({
                    'pointer-events': 'auto',
                    'opacity': '1'
                });
            }
        }
    };

    // Teaser buborékra kattintás -> Chat megnyitása
    $(document).on('click', '#dispatcher-teaser-bubble', function(e) {
        // Biztonsági ellenőrzés: ha valamiért az X gombra kattintott, de átcsúszott volna az esemény, ne nyisson meg semmit
        if ($(e.target).closest('.teaser-close').length) {
            return;
        }

        // Ha magára a buborékra kattintott, megnyitjuk a chatet
        if (typeof openChataiwd === 'function') {
            openChataiwd();
        }
    });

    function openChataiwd(mode='') {
        welcomeLogicActive = false;
        intervalTime = 1000;
        cartLogicActive = false;

        localStorage.setItem('vrcs_welcome_muted', 'true');

        $('#cart-share-teaser-bubble').addClass('hidden');
        $('#cart-share-teaser-bubble').removeClass('show');

        clearInterval(showMessageInterval);
        showMessageInterval = -1;

        const bubble = document.querySelector('#chat-welcome-bubble');
        const dispatcher_bubble = document.querySelector('#dispatcher-teaser-bubble');
        const chatBtn = document.querySelector('.chataiwd-open-btn');

        bubble.classList.remove('show');
        dispatcher_bubble.classList.remove('show');
        //chatBtn.classList.remove('chat-btn-shake');



        const chatBody = $('#chataiwd-body');

        document.getElementById('chataiwd-container').style.display = 'flex';
        document.querySelector('.chataiwd-open-btn').style.display = 'none';

        if (needsScrollToBottom) {
            setTimeout(function() {
                chatBody.scrollTop(chatBody[0].scrollHeight);
                needsScrollToBottom = false; // Elvégeztük, legközelebb nem bántjuk
            }, 50);
        }

        chatInput.focus();
        responseCheckInterval = true;

        bindchataiwdEsc();
        checkChatConsent();

        const shareForm = $('#share-cart-form');

        if (mode === 'share_cart') {
            // Megnyitjuk az űrlapot, ha rejtve volt
            shareForm.removeClass('hidden').fadeIn(200);

            // Fókusz az e-mail mezőre, hogy azonnal gépelhessen
            setTimeout(() => {
                $('#share-recipient-email').focus();
            }, 400);
        } else if (lastHasCart) {
            const isMuted = localStorage.getItem('vrcs_teaser_muted') === 'true';
            if (!isMuted) {
                // Egy kis késleltetéssel hívjuk meg, hogy a chat ablak már látszódjon
                setTimeout(appendShareCartCard, 500);
            }
        }

    }

    function loadChatHistory() {
        if (currentChatRequest) currentChatRequest.abort();
        $('.chataiwd-message:not(:first)').remove();
        $('#faq-container').remove();

        // Chat előzmények betöltése
        currentChatRequest =$.ajax({
            url: cfg.load_history_url,
            method: 'POST',
            data: {
                registration_id: registrationId,
                session_id: sessionId,
                limit: 20, // Kezdeti limit (újabbak)
                oldest_id: null,
                nonce: cfg.nonce
            },
            dataType: 'json',
            beforeSend: function() {
                chatControl.isLoading = true;
                $('#chat-history-loader').fadeIn(200);
            },
            complete: function() {
                chatControl.isLoading = false;
                $('#chat-history-loader').fadeOut(200);
                currentChatRequest = null;
            },
            success: function (json) {
                if (json['last_message_id']) updateLastMessageId(json['last_message_id']);
                if (!json['is_logged']) {
                    $('#anonymous-history-merge').addClass('hidden');
                }

                if ('is_human_mode' in json) {
                    welcomeLogicActive = json.is_human_mode == 0;
                }

                if (json.success && json.settings) {
                    if (json.history && json.history.length > 0) {

                        oldestId = json.history[json.history.length - 1].id;
                        displayHistory(json);

                        setTimeout(function () {
                            const chatBody = $('#chataiwd-body');

                            chatBody.scrollTop(chatBody[0].scrollHeight);
                            initScrollListener(true);

                        }, 500);

                    } else if (json['show_faq'] && json['faqs'].length > 0) {
                        if ($('#faq-container').length == 0) {

                            let faq_html = '<div id="faq-container">';
                            faq_html += '  <div class="faq-grid">'; // Itt a grid konténer

                            json['faqs'].forEach(function (faq) {
                                let visual = faq.type === 'image' ?
                                    `<img src="${faq.image}" class="faq-img">` :
                                    `<div class="faq-icon-wrapper"><i class="${faq.icon}"></i></div>`;

                                faq_html += `
                                <div class="faq-card pulse-ai-faq" onclick="sendFaq('${faq.question.replace(/'/g, "\\'")}', '${faq.answer.replace(/'/g, "\\'")}')">
                                    <div class="faq-visual-area">${visual}</div>
                                    <div class="faq-label">${faq.question}</div>
                                </div>`;
                            });

                            faq_html += '  </div></div>';
                            $('#chataiwd-body .chataiwd-message.bot:first').after(faq_html);
                        }

                    } else {
                        updateChatStatus(false);
                    }
                }

            },
            error: function (e) {
                debugger;
            }
        });
    }

    function loadMoreHistory(scroll=false) {
        if (isLoadingMore || oldestId === null || chatControl.isFatalError) return;
        isLoadingMore = true;

        const chatBody = $('#chataiwd-body');
        const oldScrollHeight = chatBody[0].scrollHeight;

        $.ajax({
            url: cfg.load_history_url,
            method: 'POST',
            data: {
                registration_id: registrationId,
                session_id: sessionId,
                limit: limit,
                oldest_id: oldestId,
                nonce: cfg.nonce
            },
            dataType: 'json',
            success: function(json) {
                if (json.success && json.history && json.history.length > 0) {
                    updateLastMessageId(json['last_message_id'])

                    oldestId = json.history[json.history.length - 1].id;
                    displayHistory(json, scroll, oldScrollHeight);
                    isLoadingMore = false;

                } else if (json.success && json.history && json.history.length === 0) {
                    oldestId = null;
                    isLoadingMore = false;

                } else {
                    isLoadingMore = false;
                }

            },
            error: function(e) {
                isLoadingMore = false;
                debugger;
            }
        });
    }

    function displayHistory(json, scroll = true, oldScrollHeight = null) {
        const chatBody = $('#chataiwd-body');
        $('#faq-container').hide();
        let tempHtml = '';
        needsScrollToBottom = true;

        // 1. PHP DESC -> JS ASC (időrendben: régitől az új felé)
        const historyToDisplay = [...json.history].reverse();

        // 2. Stringbe gyűjtjük az összes üzenetet
        historyToDisplay.forEach(function (entry) {
            // Kérdés blokk
            if (entry.question) {
                tempHtml += `<div class="chataiwd-message user" data-message-id="${entry.id}"><div class="message-content">${entry.question}</div></div>`;
            }

            // Kép/Csatolmány blokk
            if (entry.attachment_filename) {
                if (entry.attachment_thumb) {
                    tempHtml += '<div class="chataiwd-message image">';
                    tempHtml += '<div class="thumbnail_image"><img src="' + entry.attachment_thumb + '" class="thumbnail"></div>';
                    tempHtml += '<div class="message-content">' + entry.attachment_filename + '</div></div>';
                } else {
                    const docUploadedText = cfg.text_document_uploaded || 'The document has been uploaded.';
                    tempHtml += '<div class="chataiwd-message user" style="margin-bottom: 0"><div class="message-content">' + entry.attachment_filename + '</div></div>';
                    tempHtml += '<div class="chataiwd-message attachment"><div class="message-content">' + docUploadedText + '</div></div>';
                }
            }

            // Válasz blokk
            if (entry.answer) {
                const isHuman = entry.responder_type === 1;
                const answerClass = isHuman ? 'chataiwd-message bot dispacher' : 'chataiwd-message bot';
                const dispatcherAttr = (isHuman && entry.dispatcher) ? ' data-name="' + entry.dispatcher + '"' : '';

                let productCardsHtml = '';
                if (entry.product && entry.product.length > 0) {
                    productCardsHtml = '<div class="chataiwd-product-holder" style="margin-top:10px;">';
                    entry.product.forEach(function(cardHtml) {
                        productCardsHtml += cardHtml;
                    });
                    productCardsHtml += '</div>';
                }

                const messageUniqueId = entry.id; // verziótól függően

                tempHtml += `
                <div class="${answerClass}" ${dispatcherAttr} data-message-id="${messageUniqueId}">
                    <div class="message-content">
                        <div>${entry.answer}</div> 
                        ${productCardsHtml}
                    </div>
                </div>`;


                //tempHtml += '<div class="chataiwd-message ' + answerClass + '" ' + dispatcherAttr + '><div class="message-content">' + entry.answer + '</div></div>';
            }
        });

        // 3. A horgony után (üdvözlő üzenet) szúrjuk be az egészet egyszerre
        $('#chataiwd-body .chataiwd-message:first').after(tempHtml);

        // 4. Görgetés kezelése
        if (oldScrollHeight !== null) {
            // Load More esetén
            chatBody[0].scrollTop = chatBody[0].scrollHeight - oldScrollHeight;
        } else if (scroll) {
            const newImages = chatBody.find('img');
            if (newImages.length > 0) {
                // Miután az összes kép betöltődött (vagy hiba történt), görgessünk le
                Promise.all(Array.from(newImages).filter(img => !img.complete).map(img => {
                    return new Promise(resolve => { img.onload = img.onerror = resolve; });
                })).then(() => {
                    chatBody.animate({ scrollTop: chatBody[0].scrollHeight }, 200);
                });
            }

            // Biztonsági görgetés (ha nincsenek képek, vagy túl lassúak)
            setTimeout(function () {
                chatBody.animate({ scrollTop: chatBody[0].scrollHeight }, 200);
            }, 150);
        }

        // 5. Státusz ikon frissítése (a PHP eredeti listájának [0] eleme a legfrissebb)
        if (json.history.length > 0) {
            updateChatStatus(json.history[0].status === 1);
        }
    }


    function displayImage(attachment_filename,attachment_thumb) {

        if (attachment_filename) {
            let userMessageDiv = '';
            if (attachment_thumb) {

                if (attachment_thumb.toLowerCase().endsWith('.pdf')) {
                    attachment_thumb = cfg.pdf_icon || 'image/pdf_icon.png';
                }

                userMessageDiv +=  '<div class="chataiwd-message image">';
                userMessageDiv += '<div class="thumbnail_image"><img src="' + attachment_thumb + '" class="thumbnail" alt="Thumbnail"></div>';
                userMessageDiv += '<div class="message-content">' + attachment_filename + '</div>';

            } else {
                userMessageDiv +=  '<div class="chataiwd-message user" style="margin-bottom: 0">';
                userMessageDiv += '<div class="message-content">' + attachment_filename + '</div>';
            }
            $('#chataiwd-body').append(userMessageDiv);
            //$('#chataiwd-body .chataiwd-message:first').after(userMessageDiv);

            if (!attachment_thumb) {
                const botAttachmentDiv = $('<div>').addClass('chataiwd-message attachment');
                botAttachmentDiv.html('<div class="message-content">'+cfg.text_document_uploaded+'</div>');
                $('#chataiwd-body').append(botAttachmentDiv);
            }
        }
    }

    $('#chataiwd-input').on('input', function () {
        updateSendButtonVisibility();
    });


    // Függvény a küldés gomb láthatóságának frissítéséhez
    function updateSendButtonVisibility(setting) {
        const input = $('#chataiwd-input').val().trim();
        const hasFile = (fileInput && fileInput.files) ? fileInput.files.length > 0 : false;
        const sendButton = $('#send-message-button');

        if ((input || hasFile) && !setting) {
            sendButton.css({
                visibility: 'visible',
                opacity: 1,
                'pointer-events': 'auto'
            }).stop().animate({ opacity: 1 }, 'slow');
        } else {
            sendButton.css({
                visibility: 'hidden',
                opacity: 0,
                'pointer-events': 'none'
            }).stop().animate({ opacity: 0 }, 'slow', function() {
                $(this).css('visibility', 'hidden');
            });
        }
    }


    // Fájl kiválasztásának kezelése
    const fileInput = document.getElementById('file-input');
    const fileNameDisplay = document.getElementById('file-name');
    const dropArea = document.querySelector('.drop-area');
    const fileNameContainer = document.getElementById('file-name-container');
    let currentThumbnail = '';


    function uploadFileForThumbnail(file, callback) {
        const MAX_SIZE = 5 * 1024 * 1024;

        if (file.size > MAX_SIZE) {
            const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
            const limitInMb = (MAX_SIZE / (1024 * 1024)).toFixed(0);
            const mbText = cfg.text_mb || 'MB';

            fileImgClear();
            handleGlobalChatError(
                `${cfg.error_upload_size} (${sizeInMb} ${mbText}).`,
                `${cfg.error_max_size} ${limitInMb} ${mbText}.`,
                "",
                false
            );

            // Itt megállítjuk a folyamatot, és ürítjük a file inputot, ha szükséges
            $('.chataiwd-file-input').val('');
            return;
        }



        const formData = new FormData();
        formData.append('attachment', file);
        formData.append('session_id', sessionId);
        formData.append('nonce', cfg.nonce);

        $.ajax({
            url: cfg.thumbnail_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(json) {
                if (json.success && json.thumbnail) {
                    callback(json.thumbnail, json.temp_file_path);

                } else if (json.error) {
                    fileImgClear();
                    handleGlobalChatError(json.error, '', '',false);

                }
            },
            error: function(e) {
                debugger;
            }
        });
    }

    // Fájl törlése a szerverről
    function deleteTempFile(tempFilePath) {
        if (!tempFilePath) return;

        $.ajax({
            url: cfg.delete_temp_url, // Új végpont a fájl törléséhez
            method: 'POST',
            data: {
                temp_file_path: tempFilePath,
                session_id: sessionId,
                nonce: cfg.nonce,
            },
            dataType: 'json',
            success: function(json) {
                if (!json.success) {
                    console.error('Failed to delete temporary file:', json.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error deleting temporary file:', error);
            }
        });
    }

    // Kattintással történő fájl kiválasztás
    $(document).on('click', '.file-upload-btn', function() {
        closeAllChatOverlays();
        const $fileInput = $('#file-input');
        if ($fileInput.length) {
            $fileInput.click();
        }
    });


    $(document).on('change', '#file-input', function() {
        const fileInput = this; // A natív input elem
        const $fileNameDisplay = $('#file-name');
        const $fileNameContainer = $('#file-name-container');

        if (fileInput.files && fileInput.files.length > 0) {
            const file = fileInput.files[0];

            // Alapértelmezett kijelzés, amíg a thumbnail tölt
            $fileNameDisplay.text(file.name);
            $fileNameContainer.css('display', 'inline-flex');

            uploadFileForThumbnail(file, (thumbnailUrl, tempFilePath) => {
                $fileNameDisplay.html(`<span class="file-link">${file.name}</span>`);
                $fileNameDisplay.attr('data-temp-file-path', tempFilePath);

                if (thumbnailUrl) {
                    let html = `<div class="thumbnail_image">
                                    <img src="${thumbnailUrl}" class="thumbnail" alt="Thumbnail">
                                </div>`;
                    $('.file-name-container .thumbnail_image').remove();
                    $fileNameDisplay.before(html);
                }
            });
        } else {
            // Ha kiürült az input (pl. mégse választott semmit)
            const tempPath = $fileNameDisplay.attr('data-temp-file-path');
            if (tempPath) {
                deleteTempFile(tempPath);
            }
            $fileNameDisplay.text('').attr('data-temp-file-path', '');
            $fileNameContainer.hide();
            $('.file-name-container .thumbnail_image').remove();
        }

        // Ez a függvényed is lefut, ha létezik
        if (typeof updateSendButtonVisibility === "function") {
            updateSendButtonVisibility();
        }
    });

    // Törlő gomb eseménykezelője
    $('#file-clear-btn').on('click', function() {
        fileImgClear();
    });

    function fileImgClear() {
        fileInput.value = ''; // Törli a fájlt az inputból
        fileNameDisplay.textContent = ''; // Törli a fájlnév megjelenítését
        fileNameContainer.style.display = 'none'; // Elrejti a feltöltést
        $('.file-name-container .thumbnail_image').remove();
        deleteTempFile(fileNameDisplay.dataset.tempFilePath);
        updateSendButtonVisibility();
    }


    if (fileInput) {
        // Drag-and-Drop események kezelése
        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.classList.add('drag-over');
            closeAllChatOverlays();
        });

        dropArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropArea.classList.remove('drag-over');
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.classList.remove('drag-over');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];

                // Fájl hozzárendelése a file input-hoz
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                fileNameDisplay.textContent = file.name;
                fileNameContainer.style.display = 'inline-flex'; // Gomb megjelenítése


                uploadFileForThumbnail(file, (thumbnailUrl, tempFilePath) => {

                    fileNameDisplay.innerHTML = `<span class="file-link">${file.name}</span>`;
                    fileNameDisplay.dataset.tempFilePath = tempFilePath;
                    if (thumbnailUrl) {
                        let html = '<div class="thumbnail_image">';
                        html += `<img src="${thumbnailUrl}" class="thumbnail" alt="Thumbnail">`;
                        html += '</div>'
                        $('.file-name-container .thumbnail_image').remove();
                        $('.file-name').before(html);
                    }
                });

                // Fájlnév megjelenítése
                updateSendButtonVisibility(); // Frissítjük a gomb láthatóságát
            }
        });
    }

    // Szín sötétítése hoverhez
    function darkenColor(hex, percent) {
        hex = hex.replace('#', '');
        let r = parseInt(hex.substring(0, 2), 16);
        let g = parseInt(hex.substring(2, 4), 16);
        let b = parseInt(hex.substring(4, 6), 16);

        r = Math.floor(r * (1 - percent / 100));
        g = Math.floor(g * (1 - percent / 100));
        b = Math.floor(b * (1 - percent / 100));

        r = r < 0 ? 0 : r;
        g = g < 0 ? 0 : g;
        b = b < 0 ? 0 : b;

        return '#' + (r.toString(16).padStart(2, '0') + g.toString(16).padStart(2, '0') + b.toString(16).padStart(2, '0'));
    }

    const openBtn = document.querySelector('.chataiwd-open-btn');
    const originalColor = cfg.chat_color;
    const hoverColor = darkenColor(originalColor, 20);
    openBtn.addEventListener('mouseover', () => {
        openBtn.style.backgroundColor = hoverColor;
    });
    openBtn.addEventListener('mouseout', () => {
        openBtn.style.backgroundColor = originalColor;
    });

    // Enter billentyű figyelése
    const chatInput = document.getElementById('chataiwd-input');
    chatInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendChataiwdMessage();
        }
    });

    function initScrollListener(iniciale) {
        // Először lecsatlakozunk az esetleges korábbi figyelőről, hogy ne legyen duplikáció
        $('#chataiwd-body').off('scroll');

        $('#chataiwd-body').on('scroll', function() {
            // Ha éppen az első görgetés van (iniciale === true)
            if (iniciale) {
                iniciale = false; // Kikapcsoljuk a védelmet a következő görgetéshez
                return;           // Ebből a futásból kilépünk, nem töltünk be semmit
            }

            // Minden további görgetésnél már ez fut le:
            if (this.scrollTop < 10 && !isLoadingMore) {
                loadMoreHistory();
            }
        });
    }

    function bindchataiwdEsc() {
        // 1. ESC billentyű figyelése (Bezárja a chatet)
        $(document).on('keydown.chataiwd', function(e) {
            if (e.key === 'Escape' || e.which === 27) {
                closeChataiwd();
            }
        });

        // 2. Mellékattintás (outside click) figyelése
        setTimeout(function() {
            $(document).on('click.chataiwdOutside', function(e) {
                // VÉDELEM: Ha a kattintott elem időközben eltűnt a DOM-ból (pl. fájlfeltöltő ablak nyílik)
                if (!e.target || !document.body.contains(e.target)) {
                    return;
                }

                const $container = $('#chataiwd-container');
                const $openBtn = $('.chataiwd-open-btn');

                // Az új modal ablak és az overlay elemei
                const $newModal = $('.iwd-modal-wrapper');
                const $overlay = $('.iwd-overlay');
                const $oldModal = $('#chataiwd-modal');

                // VÉDELEM: Megnézzük, hogy magára az upload gombra, vagy annak belsejére kattintottak-e
                // (Itt feltételezem, hogy van egy 'upload' vagy 'button-upload' osztályod, vagy típusod)
                const $uploadTarget = $(e.target);
                if ($uploadTarget.closest('[type="file"]').length > 0 ||
                    $uploadTarget.closest('.upload, [id*="upload"], [class*="upload"]').length > 0) {
                    return; // Ha feltöltéshez kapcsolódik a kattintás, ne zárjuk be!
                }

                // VÉDELEM: Ha a gyorsnézet modal ablak nyitva van, és a kattintás AZON BELÜL történt, NEM zárhatjuk be a chatet!
                if ($newModal.has(e.target).length > 0 || $newModal.is(e.target) ||
                    $oldModal.has(e.target).length > 0 || $oldModal.is(e.target) ||
                    $overlay.is(e.target)) {
                    return; // Megszakítjuk a futást, a gyorsnézet modal kezeli magát
                }

                // Ha a chat főablaka látható, és NEM a chat ablakra, és NEM a nyitógombra kattintottak
                if ($container.is(':visible') &&
                    !$container.is(e.target) && $container.has(e.target).length === 0 &&
                    !$openBtn.is(e.target) && $openBtn.has(e.target).length === 0) {

                    closeChataiwd();
                }
            });
        }, 0);
    }

    function closeChataiwd() {
        const $oldModal = $('#chataiwd-modal');
        const $newModal = $('.iwd-modal-wrapper'); // Az új gyorsnézet wrapper
        const $overlay = $('.iwd-overlay');

        // Ha a gyorsnézet modal ablak nyitva van (pl. az X-re kattintottak benne), akkor CSAK a modalt zárjuk be, a chat ablak maradjon nyitva!
        if ($newModal.length > 0 || $oldModal.length > 0) {
            $('.iwd-modal-wrapper, .iwd-overlay, #chataiwd-modal').fadeOut(200, function() {
                $(this).remove();
                $('body').removeClass('modal-open iwd-modal-open').css('overflow', '');
                $('.modal-backdrop').remove();
            });
            return; // Itt megállunk, hogy a fő chat ablak nyitva maradjon
        }

        // Ha nincs modal ablak, akkor a fő chat ablakot zárjuk be
        document.getElementById('chataiwd-container').style.display = 'none';
        document.querySelector('.chataiwd-open-btn').style.display = 'flex';
        intervalTime = 5000;

        // Eseményfigyelők precíz lekapcsolása a memóriából
        $(document).off('keydown.chataiwd');
        $(document).off('click.chataiwdOutside');
    }


    function updateChatStatus(isHuman) {
        const headerIcon = document.getElementById('chataiwd-status-icon');
        const headerText = document.getElementById('chataiwd-heading');
        const footerStatus = document.getElementById('chataiwd-footer-status');

        if (isHuman) {
            if (lastRenderedStatus == 'HUMAN') {
                return;
            }
            welcomeLogicActive = false;

            headerIcon.innerHTML = '👩‍';
            headerText.innerHTML  = cfg.heading_dispatcher;
            footerStatus.innerHTML = '<span class="emoji-icon">👩</span>';

            footerStatus.innerHTML += '<span>' + cfg.indicator_dispatcher + '</span>';
            lastRenderedStatus = 'HUMAN';

        } else {
            if(lastRenderedStatus == 'AI') {
                return;
            }

            welcomeLogicActive = localStorage.getItem('vrcs_welcome_muted') !== 'true';
            headerIcon.innerHTML = '<img src="' + cfg.ai_img + '" class="img-ai-human-header">';

            headerText.innerHTML  = cfg.heading_ai;
            footerStatus.innerHTML = '<img src="' + cfg.ai_img + '" class="ai_img img-ai-human">';
            footerStatus.innerHTML += '<span>' + cfg.indicator_ai + '</span>';
            lastRenderedStatus = 'AI';

        }
    }

    function sendChataiwdMessage() {
        if (chatControl.isFatalError) return;

        const input = document.getElementById('chataiwd-input');
        const message = input.value.trim();
        const file = (fileInput && fileInput.files && fileInput.files.length > 0) ? fileInput.files[0] : null;
        if (!message && !file) return;

        const chatBody = document.getElementById('chataiwd-body');

        const userMessageDiv = document.createElement('div');
        userMessageDiv.className = 'chataiwd-message user ideiglenes';

        // A message.replace sor lecseréli a sortöréseket HTML sortörésekre
        const formattedMessage = message.replace(/\n/g, '<br>');
        let messageContent = message ? `<div class="message-content">${formattedMessage}</div>` : '';
        if (file && file) {
            messageContent += messageContent ? '<br>' : '';
            messageContent += `<div class="message-content">${cfg.text_file_attached} ${file.name}</div>`;
        }

        userMessageDiv.innerHTML = messageContent;
        chatBody.appendChild(userMessageDiv);


        // FormData objektum létrehozása
        const formData = new FormData();
        formData.append('message', message);
        formData.append('session_id', sessionId);
        if (file) {
            formData.append('attachment', file);
            formData.append('attachment_thumb', fileNameDisplay.dataset.tempFilePath);
        }
        if (cfg.nonce) {
            formData.append('nonce', cfg.nonce);
        }
        formData.append('nonce', cfg.nonce);


        $.ajax({
            url: cfg.send_message_url,
            method: 'POST',
            data:formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                const typingDiv = document.createElement('div');
                typingDiv.className = 'chataiwd-message typing';
                typingDiv.id = 'typing-indicator';
                typingDiv.innerHTML = `<div class="message-content"><span class="typing-dots"><span></span><span></span><span></span></span></div>`;
                chatBody.appendChild(typingDiv);
                chatBody.scrollTop = chatBody.scrollHeight;
                updateSendButtonVisibility(true);
            },
            complete: function () {
                const existingTyping = document.getElementById('typing-indicator');
                if (existingTyping) existingTyping.remove();
                updateSendButtonVisibility(true);
            },
            success: function(json) {
                if (json.error) {
                    handleGlobalChatError(json.error, json.error_send, json.error_service_unavailable, false);
                }
                chatBody.scrollTop = chatBody.scrollHeight;

                if (fileInput) {
                    fileInput.value = '';
                }
                if (fileNameDisplay) {
                    fileNameDisplay.textContent = '';
                }
                if (fileNameContainer) {
                    fileNameContainer.style.display = 'none';
                }
                startResponseCheckTimer(true);
            },
            error: function(e) {
                //handleGlobalChatError();

                debugger;
            }
        });

        input.value = '';
        input.style.height = 'auto'; // Ezt add hozzá!
        input.focus();
    }

    function handleGlobalChatError(errorMsg, errorMsgPlease, errorServiceUnavailable, errorStopChat) {
        const chatBody = $('#chataiwd-body');

        const displayMsg = (typeof errorMsg !== 'undefined')
            ? errorMsg
            : cfg.error_technical;

        const displayPlease = (typeof errorMsgPlease !== 'undefined')
            ? errorMsgPlease
            : cfg.error_report;

    const displayService = (typeof errorServiceUnavailable !== 'undefined')
        ? errorServiceUnavailable
        : (cfg.error_service_unavailable || 'The service is temporarily unavailable...');

    const stopChat = (typeof errorStopChat !== 'undefined') ? errorStopChat : false;
    const systemMessageTitle = cfg.text_system_message || 'System Message:';

    // 2. Insert system message for the visitor
    const errorHtml = `
<div class="chataiwd-message bot system-error-msg" style="border-left: 4px solid #ff4d4d; margin-top: 10px;">
    <div class="message-content" style="background: #fffafa;">
        <i class="fa fa-exclamation-triangle" style="color: #ff4d4d;"></i> 
        <strong>${systemMessageTitle}</strong><br>
        ${displayMsg}<br><br>
        <span style="font-size: 0.85em;">${displayPlease}</span>
    </div>
</div>`;

        chatBody.append(errorHtml);
        chatBody.animate({ scrollTop: chatBody[0].scrollHeight }, 300);

    if (stopChat) {
        if (responseCheckInterval) {
            clearTimeout(responseCheckInterval);
        }
        responseCheckInterval = null;

            const $input = $('#chataiwd-input');
            $input.val('');
            $input.attr('placeholder', displayService);
            $input.addClass('chat-fatal-error');

            chatControl.isFatalError = true;

            $('.chataiwd-footer i, .chataiwd-footer button, #chataiwd-input').addClass('disabled-icon').css({
                'pointer-events': 'none',
                'opacity': '0.5'
            });
            $('#btn-contact-trigger,  .fa-envelope').removeClass('disabled-icon').css({
                'pointer-events': 'auto',
                'opacity': '1'
            });
        }
    }

    function handleGlobalChatSuccess(successMsg, successMsgDetail) {
        const chatBody = $('#chataiwd-body');

        const displayMsg = (typeof successMsg !== 'undefined') ? successMsg : (cfg.text_success || 'Success!');
        const displayDetail = (typeof successMsgDetail !== 'undefined') ? successMsgDetail : '';

        let contentHtml = `<strong>${displayMsg}</strong>`;
        if (displayDetail !== '') {
            contentHtml += `<br><br><span style="font-size: 0.85em;">${displayDetail}</span>`;
        }

        const successHtml = `
<div class="chataiwd-message bot system-success-msg" style="border-left: 4px solid #28a745; margin-top: 10px;">
    <div class="message-content" style="background: #f8fff9;">
        <i class="fa fa-check-circle" style="color: #28a745;"></i> 
        ${contentHtml}
    </div>
</div>`;

        chatBody.append(successHtml);
        chatBody.animate({ scrollTop: chatBody[0].scrollHeight }, 300);
    }

    function appendShareCartCard() {
        const chatBody = $('#chataiwd-body');

        // Ellenőrizzük, hogy nincs-e már kint egy ilyen kártya (hogy ne halmozzuk fel)
        if ($('.chataiwd-share-card').length > 0) return;

        const shareCardHtml = `
<div class="chataiwd-message bot chataiwd-share-card" style="border-left: 4px solid #007bff; margin: 15px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <div class="message-content" style="background: #f0f7ff; padding: 12px;">
        <div style="display: flex; align-items: center; margin-bottom: 8px;">
            <i class="fa fa-users" style="color: #007bff; font-size: 1.2em; margin-right: 10px;"></i>
            <strong style="color: #333;">${cfg.text_share_cart_title}</strong>
        </div>
        <div style="font-size: 0.9em; color: #555; line-height: 1.4; margin-bottom: 12px;">
            ${cfg.text_share_cart_desc}
        </div>
        <button onclick="openChataiwd('share_cart')" class="btn btn-primary btn-sm" style="width: 100%; background: #007bff; border: none; border-radius: 4px; padding: 6px; font-weight: bold;">
            <i class="fa fa-share-alt"></i> ${cfg.text_share_cart_header}
        </button>
    </div>
</div>`;

        chatBody.append(shareCardHtml);
        chatBody.animate({ scrollTop: chatBody[0].scrollHeight }, 300);
        localStorage.setItem('vrcs_teaser_muted', 'true');

    }

    function showError(message) {
        const errorMessageDiv = document.createElement('div');
        errorMessageDiv.className = 'chataiwd-message error';
        errorMessageDiv.innerHTML = `<div class="message-content">${message}</div>`;
        $('#chataiwd-body').append(errorMessageDiv);
        $('#chataiwd-body').scrollTop = $('#chataiwd-body').scrollHeight;
    }

    let isPollingBusy = false; // Flag a duplikáció elkerülésére

    function startResponseCheckTimer(isManualTrigger = false) {

        if (chatControl.isFatalError || !responseCheckInterval) return;

        if (isPollingBusy) return;

        if (chatControl.isLoading) {
            // Ha nem indítjuk újra, itt megállna a chat frissítése örökre
            responseCheckInterval = setTimeout(startResponseCheckTimer, intervalTime);
            return;
        }

        if (isManualTrigger && responseCheckInterval !== null) {
            clearTimeout(responseCheckInterval);
        }

        isPollingBusy = true; // Zároljuk a folyamatot
        $.ajax({
            url: cfg.check_response_url,
            method: 'POST',
            data: {
                session_id: sessionId,
                registration_id: registrationId,
                last_id: lastMessageId,
                nonce: cfg.nonce
            },
            dataType: 'json',
            async: true,
            complete: function() {
                isPollingBusy = false; // Feloldjuk a zárat
                if (responseCheckInterval !== null) {
                    responseCheckInterval = setTimeout(startResponseCheckTimer, intervalTime);
                }
            },
            success: function(json) {

                if (json.dispatcher_is_typing == 1) {
                    // Csak akkor adjuk hozzá, ha még nincs ott
                    if ($('#disp-typing-indicator').length === 0) {
                        const chatBody = $('#chataiwd-body');

                        const typingHtml = `
                            <div id="disp-typing-indicator" class="chataiwd-message typing-indicator-container">
                                <div class="message-content">
                                    <div class="typing-dots">
                                        <span></span><span></span><span></span>
                                    </div>
                                </div>
                            </div>`;
                        chatBody.append(typingHtml);
                        chatBody.scrollTop(chatBody[0].scrollHeight);
                    }
                } else {
                    // Ha már nem gépel, eltávolítjuk
                    $('#disp-typing-indicator').remove();
                }

                if (json.success) {
                    updateLastMessageId(json['last_message_id'])

                    let isHuman = (lastRenderedStatus === 'HUMAN');

                    if (json.ai_human_status !== undefined && json.ai_human_status !== null) {
                        isHuman = (parseInt(json.ai_human_status) === 1);
                        updateChatStatus(isHuman);
                    }


                    // 2. BUBORÉK LOGIKA (ha zárva van a chat)
                    const isChatClosed = $('#chataiwd-container').css('display') === 'none';

                    if (isChatClosed && parseInt(json.ai_human_status) === 1 && json.responses && json.responses.length > 0) {

                        // Megkeressük az utolsó választ a tömbben
                        const lastResp = json.responses[json.responses.length - 1];

                        if (lastResp && lastResp.answer && lastResp.answer.trim() !== '') {
                            // Ellenőrizzük, hogy létezik-e a showDispatcherTeaser függvény
                            if (typeof showDispatcherTeaser === "function") {


                                const defaultDispatcherName = cfg.text_default_dispatcher || 'Admin';
                                const messageToShow = lastResp.teaser ? lastResp.teaser : stripHtml(lastResp.answer);
                                showDispatcherTeaser(
                                    messageToShow,
                                    lastResp.dispatcher || defaultDispatcherName,
                                    json.dispatcher_image || cfg.human_img
                                );
                            }
                        }
                    }

                    // A polling siker ágában:
                    const cartTeaser = $('#cart-share-teaser-bubble');

                    if (json.has_cart) {
                        lastHasCart = true;
                        const isMuted = localStorage.getItem('vrcs_teaser_muted') === 'true';

                        if (isChatClosed && cartLogicActive && !isMuted) {
                            showCartShareTeaser();
                        }

                        const shareBtn = $('#btn-share-cart');
                        const shareBubble = $('.share-btn-bubble');

                        shareBtn.removeClass('hidden');

                        if (localStorage.getItem('vrcs_bubble_muted') === 'true') {
                            shareBubble.hide();
                            // Hozzáadjuk a title attribútumot a gombhoz (a PHP-ból átvett változóval)
                            shareBtn.attr('title', cfg.text_share_cart_title);
                            //$('.share-btn-bubble').css('display','none');
                        } else {
                            // Ha be van kapcsolva a buborék (felirat látszik)
                            shareBubble.show();
                            // Eltávolítjuk a title attribútumot, hogy ne zavarjon be
                            shareBtn.removeAttr('title');
                        }

                    } else {
                        lastHasCart = false;
                        cartTeaser.addClass('hidden');
                        $('#btn-share-cart').addClass('hidden');
                        localStorage.removeItem('vrcs_teaser_muted');
                        localStorage.removeItem('vrcs_bubble_muted');
                        cartLogicActive = true;
                    }

                    if (json.responses && json.responses.length > 0) {
                        needsScrollToBottom = true;

                        $('.message-content.wait-human').slideUp('slow');
                        const chatBody = $('#chataiwd-body');
                        $('.ideiglenes').remove();

                        json.responses.forEach(function(response) {

                            // 2. KÉP/CSATOLMÁNY kiírása (a meglévő displayImage függvénnyel)
                            if (response.attachment_thumb && response.attachment_thumb.trim() !== '') {
                                // Itt a meglévő displayImage-et hívjuk, ami magától appendel
                                const fileName = response.attachment_filename || 'Attachment';
                                displayImage(fileName, response.attachment_thumb);
                            }

                            // 1. KÉRDÉS kiírása (azonnal)
                            if (response.question && response.question.trim() !== '') {
                                const userHtml = `
                                        <div class="chataiwd-message user" data-message-id="${response.message_id}">
                                            <div class="message-content">${response.question}</div>
                                        </div>`;
                                chatBody.append(userHtml);
                            }

                            // 3. VÁLASZ kiírása
                            if (response.answer && response.answer.trim() !== '') {
                                // Vezérlő üzenetek kezelése (státuszváltás)
                                if (response.answer === '__AI__') {
                                    updateChatStatus(false);
                                } else if (response.answer === '__HUMAN__') {
                                    updateChatStatus(true);
                                } else {
                                    // Normál válasz kiírása
                                    const answerClass = isHuman ? 'chataiwd-message bot dispacher' : 'chataiwd-message bot';
                                    const uniqueId = 'typing-' + response.message_id;
                                    const productId = 'product-' + response.message_id;
                                    const dispatcherAttr = (isHuman && response.dispatcher) ? ' data-name="' + response.dispatcher + '"' : '';

                                    const botHtml = `
                                        <div class="${answerClass}" ${dispatcherAttr} data-message-id="${response.message_id}">
                                            <div class="message-content">
                                                <div id="${uniqueId}"></div>
                                                <div id="${productId}" class="chataiwd-product-holder" style="display:none; margin-top:10px;"></div>
                                            </div>
                                        </div>`;

                                    chatBody.append(botHtml);

                                    // 4. Elindítjuk a gépelést a frissen létrehozott elembe
                                    const targetElement = document.getElementById(uniqueId);
                                    if (targetElement) {

                                        typeWriter(targetElement, response.answer, 10, null, function() {
                                            if (response.product && response.product.length > 0) {
                                                const $productBox = $('#' + productId); // Most már meg fogja találni!

                                                response.product.forEach(function(cardHtml) {
                                                    $productBox.append(cardHtml);
                                                });

                                                $productBox.slideDown(500, function() {
                                                    chatBody.animate({ scrollTop: chatBody[0].scrollHeight }, 200);
                                                });
                                            }
                                        });

                                    }
                                }
                            }
                        });

                        // Gördítés az aljára minden új elem után (vagy a ciklus végén)
                        chatBody.scrollTop(chatBody[0].scrollHeight);
                    }


                } else if (json.error) {
                    handleGlobalChatError(json.error, json.error_send, error_service_unavailable);

                }
            },
            error: function(e) {
                debugger;
            }
        });
    }

    function showCartShareTeaser() {
        const aiWelcome = $('#chat-welcome-bubble');
        const cartTeaser = $('#cart-share-teaser-bubble');
        const dispatcherTeaser = $('#dispatcher-teaser-bubble');

        // 1. Prioritás: Ha a diszpécser épp üzen (vagy már kint van), a kosár ne jöjjön rá
        if (dispatcherTeaser.is(':visible') || !$('#chataiwd-container').is(':hidden')) {
            return;
        }

        // 2. AI welcome elrejtése (hogy ne legyen átfedés)
        aiWelcome.removeClass('show').hide();
        welcomeLogicActive = false;

        // 3. Kosár teaser megjelenítése
        // Előbb a hiddent vesszük le, hogy a display:block meglegyen
        cartTeaser.removeClass('hidden');
        cartTeaser.addClass('show');

    }

    function submitShareCart() {
        const senderNameInput = document.getElementById('share-sender-name');
        const emailInput = document.getElementById('share-recipient-email');
        const messageInput = document.getElementById('share-message');

        const senderName = senderNameInput.value.trim();
        const email = emailInput.value.trim();
        const userMsg = messageInput.value.trim();

        // Reset borders
        senderNameInput.style.border = '';
        emailInput.style.border = '';

        // Validáció: név és email kötelező
        let hasError = false;
        if (!senderName) {
            senderNameInput.style.border = '1px solid red';
            hasError = true;
        }
        if (!email || !email.includes('@')) {
            emailInput.style.border = '1px solid red';
            hasError = true;
        }
        if (hasError) return;

        const formData = new FormData();
        formData.append('sender_name', senderName);
        formData.append('email', email);
        formData.append('message', userMsg);
        formData.append('session_id', sessionId);
        formData.append('registration_id', registrationId);
        formData.append('nonce', cfg.nonce);
        formData.append('mode', 'share_cart');

        $.ajax({
            url: cfg.share_cart_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('.btn-share-send').html('<i class="fa fa-spinner fa-spin"></i> ' + cfg.text_sending).prop('disabled', true);
            },
            success: function(json) {
                if (json.success) {
                    // 1. Bezárjuk az űrlapot
                    $('#share-cart-form').addClass('hidden');

                    const email = emailInput.value.trim();
                    const rawDetail = cfg.text_share_success_detail;
                    const formattedDetail = rawDetail.replace('%s', email);

                    handleGlobalChatSuccess(cfg.text_share_success_title, formattedDetail);

                    emailInput.value = '';
                    messageInput.value = '';

                } else {
                    showError(json.error || cfg.error_sending_share_email);
                }
            },
            complete: function() {
                $('.btn-share-send').html('<i class="fa fa-paper-plane"></i>' + cfg.text_send_share ).prop('disabled', false);
            },
            error: function (e) {
                debugger;
            }
        });
    }

    function closeCartTeaser(e) {
        // Megállítjuk, hogy a szülő div 'onclick' eseménye (openChataiwd) lefusson
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }

        const cartTeaser = $('#cart-share-teaser-bubble');

        // Javított jQuery szintaxis
        cartTeaser.removeClass('show');

        // Megvárjuk az opacity animációt (300ms), mielőtt teljesen elrejtjük (display:none)
        setTimeout(() => {
            cartTeaser.addClass('hidden');
        }, 300);

        // Globális változó némítása
        cartLogicActive = false;
        localStorage.setItem('vrcs_teaser_muted', 'true');
    }


    $('#auth-button-login').on('click', function() {
        $('#auth-form-register').addClass('hidden');


        if ($('#auth-form-login').hasClass('hidden')) {
            $('#auth-form-login').removeClass('hidden');
        } else {
            $('#auth-form-login').addClass('hidden');
        }
    });

    $('#auth-button-register').on('click', function() {
        $('#auth-form-login').addClass('hidden');

        if ($('#auth-form-register').hasClass('hidden')) {
            $('#auth-form-register').removeClass('hidden');
        } else {
            $('#auth-form-register').addClass('hidden');
        }
    });

    function submitAuthFormRegister() {
        const name = $('#auth-form-register input[name="auth_name"]').val().trim();
        const email = $('#auth-form-register input[name="auth_email"]').val().trim();
        const password = $('#auth-form-register input[name="auth_password"]').val().trim();

        if (!name || !email || !password) {
            alert(cfg.text_fill_all_fields);
            return;
        }

        if (!validateEmail(email)) {
            alert(cfg.error_invalid_mail);
            return;
        }

        $.ajax({
            url: cfg.save_auth_url, // PHP endpoint (pl. extension/chataiwd/module/chataiwd.saveAuth)
            method: 'POST',
            data: { name: name,
                email: email,
                password: password,
                type: 'register',
                session_id: sessionId,
                nonce: cfg.nonce
            },
            dataType: 'json',
            beforeSend: function() {
                chatControl.isLoading = true;
            },
            complete: function() {
                chatControl.isLoading = false;
            },
            success: function(json) {
                if (json.success) {
                    localStorage.setItem('chat_auth_timestamp', Date.now());

                    $('#auth-form-register').addClass('hidden');
                    checkAuthStatus(); // Frissíti a státuszt (pl. "Bejelentkezve: [Név]")
                } else {
                    alert(cfg.error_fault + (json.error || cfg.error_unknown));
                }
            },
            error: function(e) {
                alert(cfg.error_during_request);
            }
        });
    }

    // Segédfüggvény email validációra
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function showLinkingForm() {
        $('#linking-form').removeClass('hidden');
        // Opcionális: Fókusz az email mezőre
        $('#chat-email').focus();
    }

    // Fiókok összekötése (AJAX a link_accounts_url-re)
    function linkAccounts() {
        const chatEmail = $('#chat-email').val().trim();
        const chatPassword = $('#chat-password').val().trim();

        if (!chatEmail || !chatPassword) {
            alert(cfg.text_fill_all_fields);
            return;
        }

        if (!validateEmail(chatEmail)) {
            alert(cfg.error_invalid_mail);
            return;
        }

        $.ajax({
            url: cfg.link_accounts_url,
            method: 'POST',
            data: { chat_email: chatEmail,
                chat_password: chatPassword,
                session_id: sessionId,
                nonce: cfg.nonce
            },
            dataType: 'json',
            beforeSend: function() {
                chatControl.isLoading = true;
            },
            complete: function() {
                chatControl.isLoading = false;
            },
            success: function(json) {
                if (json.success) {
                    $('#linking-form').addClass('hidden');
                    $('#linking-offer').addClass('hidden');
                    checkAuthStatus(); // Frissít státuszt
                } else {
                    alert(cfg.error_fault + (json.error || cfg.error_unknown));
                }
            },
            error: function(e) {
                alert(cfg.error_during_request || 'Error during request!');
            }
        });
    }

    // Új chat fiók létrehozása (AJAX a create_new_chat_account-re)
    function createNewChatAccount() {
        $.ajax({
            url: cfg.create_account_url,
            method: 'POST',
            dataType: 'json',
            data: {
                session_id: sessionId,
                nonce: cfg.nonce
            },
            beforeSend: function() {
                chatControl.isLoading = true;
            },
            complete: function() {
                chatControl.isLoading = false;
            },
            success: function(json) {
                if (json.success) {
                    $('#linking-offer').addClass('hidden');
                    checkAuthStatus();
                } else {
                    alert(cfg.error_fault + (json.error || cfg.error_unknown));
                }
            },
            error: function(e) {
                alert(cfg.error_during_request || 'Error during request!');
            }
        });
    }

    function checkAuthStatus() {
        $.ajax({
            url: cfg.check_auth_url,
            method: 'POST',
            dataType: 'json',
            data: {
                session_id: sessionId,
                nonce: cfg.nonce
            },
            success: function(json) {
                if (json.logged_in) {
                    $('#auth-status').html(`${cfg.text_logged_in} ${json.name} (${json.email}) <button onclick="logoutAuth()">${cfg.text_logout}</button>`);
                    $('#auth-status').removeClass('hidden');
                    $('#auth-btns').addClass('hidden');
                    $('#linking-offer').addClass('hidden');
                    loadAndCheckAnonymousHistory();

                } else if (json.offer_linking) {
                    $('#linking-offer').removeClass('hidden');
                    $('#auth-btns').addClass('hidden');

                } else {
                    $('#auth-btns').removeClass('hidden');
                    $('#linking-offer').addClass('hidden');
                    $('#auth-status').addClass('hidden');
                }
            }
        });
        loadChatHistory();
    }
    // Login form submit (hasonló register-hez, ha külön van)
    function submitAuthFormLogin() {
        const email = $('#auth-form-login input[name="auth_email"]').val().trim();
        const password = $('#auth-form-login input[name="auth_password"]').val().trim();

        if (!email || !password) {
            alert(cfg.text_fill_all_fields);
            return;
        }

        if (!validateEmail(email)) {
            alert(cfg.error_invalid_mail);
            return;
        }

        $.ajax({
            url: cfg.save_auth_url,
            method: 'POST',
            data: { email: email,
                password: password,
                type: 'login',
                session_id: sessionId,
                nonce: cfg.nonce
            },
            dataType: 'json',
            success: function(json) {
                if (json.success) {

                    $('#auth-form-login').addClass('hidden');
                    checkAuthStatus();
                    localStorage.setItem('chat_auth_timestamp', Date.now());

                } else {
                    alert(cfg.error_fault + (json.error || cfg.error_unknown));
                }
            },
            error: function(e) {
                alert(cfg.error_during_request || 'Error during request!');
            }
        });
    }

    function logoutAuth() {

        $('#chataiwd-body').off('scroll');
        $('.chataiwd-message:not(:first)').remove();
        $('#faq-container').remove();
        oldestId = null;

        $.ajax({
            url: cfg.logout_auth_url,
            method: 'POST',
            dataType: 'json',
            data: {
                session_id: sessionId,
                nonce: cfg.nonce
            },
            success: function(json) {
                if (json.success) {
                    localStorage.setItem("not_merge_history", '');
                    $('#anonymous-history-merge').addClass('hidden');
                    updateChatStatus(false);
                    localStorage.setItem('chat_auth_timestamp', Date.now());
                    $('#auth-status').addClass('hidden');
                    notMergeHistory = '';
                    checkAuthStatus();
                }
            }
        });
    }


    function loadAndCheckAnonymousHistory() {
        // Ha már van cache és már be vagyunk jelentkezve, ne kérdezzünk újra
        // Azért hívjuk, hogy ha be ven jelentkezve és van anonim historí, akkor feljanánljuk neki
        if (notMergeHistory) {
            $('#anonymous-history-merge').addClass('hidden');
            return;
        }

        $.ajax({
            url: cfg.load_history_url,
            method: 'POST',
            data: {
                registration_id: cfg.registration_id,
                session_id: sessionId,
                anonim: true,
                nonce: cfg.nonce
            },
            dataType: 'json',
            success: function(json) {
                if (json.success && json.history && json.history.length > 0) {
                    updateLastMessageId(json['last_message_id'])
                    anonymousHistoryCache = json.history;
                    $('#anonymous-history-merge').removeClass('hidden');
                } else {
                    anonymousHistoryCache = []; // nincs anonim
                }
            },
            error: function() {
                console.error('Error loading anonymous history');
                anonymousHistoryCache = [];
            }
        });
    }

    /**
     * Merge gomb kattintásakor – elküldjük a cache-elt anonim előzményeket a saját PHP endpointnek
     */
    function mergeAnonymousHistory() {
        if (!anonymousHistoryCache || anonymousHistoryCache.length === 0) {
            alert(cfg.text_nothing_save);
            return;
        }

        $.ajax({
            url: cfg.merge_history_url,
            method: 'POST',
            data: {
                anonymous_history: JSON.stringify(anonymousHistoryCache),
                registration_id: registrationId,
                session_id: sessionId,
                nonce: cfg.nonce
            },
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    $('#anonymous-history-merge').addClass('hidden');
                    alert(cfg.text_history_merge_success || 'Previous conversations have been successfully saved to your account!');
                    anonymousHistoryCache = null;
                    loadChatHistory();
                } else {
                    anonymousHistoryCache = null;

                    alert(cfg.error_occurred + (json.error || 'Unknown error'));
                }
            },
            error: function(e) {
                alert(cfg.error_while_saving);
            }
        });
    }

    function notMergeAnonymousHistory() {
        $('#anonymous-history-merge').addClass('hidden');
        localStorage.setItem("not_merge_history", 1);
    }

    $('input[name=auth_password]').on('keypress', function (e) {
        if (e.which === 13) { // Enter billentyű
            submitAuthFormLogin();
        }
    });

    $(document).ready(function () {

        if (cfg.vrcs_recovered_mode === true) {
            localStorage.setItem('vrcs_teaser_muted', 'true');
            localStorage.setItem('vrcs_bubble_muted', 'true');

            $('.share-btn-bubble').hide();
            $('#cart-share-teaser-bubble').addClass('hidden');
        }

        updateSendButtonVisibility();
        checkAuthStatus();
        startResponseCheckTimer();
        startBackgroundSyncTimer();

        window.addEventListener('storage', (e) => {
            if (e.key === 'chat_auth_timestamp') {
                checkAuthStatus();
            }
        });

        /* emoji */

        const trigger = document.querySelector('#emoji-picker-button');
        const input = document.querySelector('#chataiwd-input');

        if (trigger && input) {
            // UMD csomag esetén a 'picmo' és 'picmoPopup' globális változókat használjuk
            // Ellenőrizzük, hogy betöltődtek-e
            if (typeof picmoPopup !== 'undefined') {
                const picker = picmoPopup.createPopup({}, {
                    referenceElement: trigger,
                    triggerElement: trigger,
                    position: 'top-start',
                    showSearch: true
                });

                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    closeAllChatOverlays();
                    picker.toggle();
                });

                picker.addEventListener('emoji:select', (selection) => {
                    const start = input.selectionStart;
                    const end = input.selectionEnd;
                    const text = input.value;
                    const before = text.substring(0, start);
                    const after = text.substring(end, text.length);

                    input.value = before + selection.emoji + after;
                    input.dispatchEvent(new Event('input', { bubbles: true }));

                    input.focus();
                    input.selectionStart = input.selectionEnd = start + selection.emoji.length;
                });
            } else {
                console.error('Picmo picker not loaded properly.');
            }
        }

        /* extarea input magasság állítás */
        const tx = document.querySelector('#chataiwd-input');
        tx.addEventListener("input", OnInput, false);

        function OnInput() {
            // Először alaphelyzetbe állítjuk a magasságot, hogy a törlésnél is csökkenjen a doboz
            this.style.height = 'auto';

            // Beállítjuk az új magasságot a tartalom alapján
            // (A +2 vagy +5 pixel néha kell a keretek/padding miatt, hogy ne vibráljon a scrollbar)
            const newHeight = this.scrollHeight;
            this.style.height = newHeight + "px";

            // Opcionális: Görgetés a chat aljára, ha a táguló footer kitakarná az utolsó üzenetet
            const chatBody = document.getElementById('chataiwd-body');
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    });

    function typeWriter(element, htmlText, speed = 10, container = null, callback = null) {

        if (!container) {
            container = document.getElementById('chataiwd-body');
        }
        let i = 0;
        element.innerHTML = "";

        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = htmlText;

        const nodes = Array.from(tempDiv.childNodes);
        let nodeIndex = 0;
        let charIndex = 0;

        function type() {
            if (nodeIndex < nodes.length) {
                const currentNode = nodes[nodeIndex];

                if (currentNode.nodeType === Node.TEXT_NODE) {
                    if (charIndex < currentNode.textContent.length) {
                        element.innerHTML += currentNode.textContent.charAt(charIndex);
                        charIndex++;
                        setTimeout(type, speed);
                    } else {
                        nodeIndex++;
                        charIndex = 0;
                        setTimeout(type, speed);
                    }
                } else if (currentNode.nodeType === Node.ELEMENT_NODE) {
                    element.appendChild(currentNode.cloneNode(true));
                    nodeIndex++;
                    setTimeout(type, speed * 2);
                }

                // Folyamatos görgetés gépelés közben
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            } else {
                // --- BEFEJEZÉS UTÁNI VÉGSŐ GÖRGETÉS ---
                if (container) {
                    // Egy minimális késleltetés kell, hogy a böngésző renderelje az utolsó karaktert is
                    setTimeout(() => {
                        container.scrollTo({
                            top: container.scrollHeight,
                            behavior: 'smooth' // Szép, sima görgetés a végén
                        });
                        if (typeof callback === 'function') {
                            callback();
                        }
                    }, 50);
                }
            }
        }
        type();
    }

    function sendFaq(question, answer, isBase64 = false) {
        if (isBase64) {
            question = decodeURIComponent(escape(atob(question)));
            answer = decodeURIComponent(escape(atob(answer)));
        }

        const chatBody = document.getElementById('chataiwd-body');
        const faqContainer = document.getElementById('faq-container');

        $('#faq-overlay').fadeOut(200);

        // 2. Felhasználói kérdés megjelenítése
        if (question) {
            const userMessageDiv = document.createElement('div');
            userMessageDiv.className = 'chataiwd-message user';
            userMessageDiv.innerHTML = `<div class="message-content">${question.replace(/\n/g, '<br>')}</div>`;
            chatBody.appendChild(userMessageDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        // 3. Bot gépelés jelző (a három pont)
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chataiwd-message typing';
        typingDiv.id = 'faq-typing-indicator';
        typingDiv.innerHTML = `<div class="message-content"><span class="typing-dots"><span></span><span></span><span></span></span></div>`;
        chatBody.appendChild(typingDiv);
        chatBody.scrollTop = chatBody.scrollHeight;

        // 4. Szimulált gondolkodási idő, majd a gépelés indítása
        setTimeout(function() {
            // Gépelés jelző eltávolítása
            if (document.getElementById('faq-typing-indicator')) {
                document.getElementById('faq-typing-indicator').remove();
            }

            // Új bot üzenet létrehozása
            const botMessageDiv = document.createElement('div');
            botMessageDiv.className = 'chataiwd-message bot';

            // Létrehozunk egy belső divet a tartalomnak, amit a typeWriter tölt majd fel
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            botMessageDiv.appendChild(contentDiv);

            chatBody.appendChild(botMessageDiv);

            // 5. GÉPELÉS INDÍTÁSA
            typeWriter(contentDiv, answer, 10, chatBody);

        }, 600); // 1 mp várakozás a természetességért
    }

    $('#faq-picker-button').on('click', function() {
        const $overlay = $('#faq-overlay');
        closeAllChatOverlays();

        // Ha már nyitva van, csak csukjuk be
        if ($overlay.is(':visible')) {
            $overlay.fadeOut(200);
            return;
        }

        // AJAX lekérés a FAQ listához
        $.ajax({
            url: cfg.get_faq_url,
            dataType: 'json',
            method: 'POST',
            data: {
                nonce: cfg.nonce
            },
            beforeSend: function() {
                chatControl.isLoading = true;
                $('#faq-picker-button').addClass('fa-spin');

            },
            complete: function() {
                chatControl.isLoading = false;
                $('#faq-picker-button').removeClass('fa-spin');

            },

            success: function(json) {
                if (json['faqs'] && json['faqs'].length > 0) {
                    let html = '<div class="faq-grid">';


                    json['faqs'].forEach(function(faq) {
                        let ecodedQuestion = decodeURIComponent(escape(atob(faq.question)));

                        let visual = faq.type === 'image' ?
                            `<img src="${faq.image}" class="faq-img">` :
                            `<div class="faq-icon-wrapper"><i class="${faq.icon}"></i></div>`;

                        html += `
                            <div class="faq-card pulse-ai-faq" onclick="sendFaq('${faq.question}', '${faq.answer}', true)">
                                <div class="faq-visual-area">${visual}</div>
                                <div class="faq-label">${ecodedQuestion}</div>
                            </div>`;
                    });

                    html += '</div>';

                    $('#faq-grid-container').html(html);
                    $overlay.fadeIn(200);
                }
            }
        });
    });


    // Ellenőrizzük, támogatja-e a böngésző
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let isListening = false;
    let recognition;

    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.lang = 'hu-HU'; // Magyar nyelv beállítása
        recognition.continuous = false; // Megáll, ha befejezted a beszédet
        recognition.interimResults = false; // Csak a végleges szöveget kérjük


        $('#btn-voice-trigger').on('click', function() {
            closeAllChatOverlays();
            if (!isListening) {
                recognition.start();
            } else {
                recognition.stop();
            }
        });

        // Amikor elkezdi hallani
        recognition.onstart = function() {
            isListening = true;
            $('#btn-voice-trigger').addClass('text-danger pulse-ai'); // Piros szín + pulzálás jelzi a felvételt
            $('#voice-loader').css('display', 'flex'); // MEGJELENÍTÉS
        };

        // Amikor sikeres a felismerés
        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            const $input = $('#chataiwd-input'); // jQuery objektumként egyszerűbb a trigger

            // 1. Szöveg behelyezése
            const currentValue = $input.val();
            if (currentValue) {
                $input.val(currentValue + ' ' + transcript);
            } else {
                $input.val(transcript);
            }

            // 2. Események manuális kiváltása
            // Az 'input' eseményt figyelik leggyakrabban a modern UI-ok (pl. gomb megjelenítéshez)
            // A 'change' pedig a biztos, ami biztos
            $input.trigger('input').trigger('change');

            // 3. Automatikus magasság állítás (ha van ilyen funkciód)
            $input.css('height', 'auto').css('height', $input[0].scrollHeight + 'px');
        };

        // Amikor vége (akár hiba, akár stop miatt)
        recognition.onend = function() {
            isListening = false;
            $('#btn-voice-trigger').removeClass('text-danger pulse-ai');
            $('#voice-loader').hide(); // ELREJTÉS
        };

        // Hiba kezelése (pl. nincs mikrofon engedélyezve)
        recognition.onerror = function(event) {
            console.error('Speech recognition error:', event.error);
            isListening = false;
            $('#btn-voice-trigger').removeClass('text-danger pulse-ai');
            if(event.error === 'not-allowed') {
                alert(cfg.text_enable_microphone);
            }
        };

    } else {
        // Ha nem támogatja a böngésző (pl. régi Firefox)
        $('#btn-voice-trigger').hide();
    }


    // levél küldés az áruháznak: information/contact

    let chataiwdFormOpenedTime = 0;

    $('#btn-contact-trigger').on('click', function() {
        chataiwdFormOpenedTime = Date.now();

        closeAllChatOverlays();
        const $overlay = $('#information-contact');
        const $container = $('#information-contact-container');

        if ($overlay.is(':visible')) {
            $overlay.fadeOut(200);
            return;
        }

        if ( cfg.platform === 'wordpress') {
            $overlay.fadeIn(200);

            $('#btn-send-contact').off('click').on('click', function(e) {
                e.preventDefault();
                sendChataiwdEmail();
            });

        } else {
            $.ajax({
                url: 'index.php?route=information/contact',
                method: 'get',
                dataType: 'html',
                beforeSend: function () {
                    chatControl.isLoading = true;
                    $('#btn-contact-trigger').addClass('fa-spin');
                },
                complete: function () {
                    chatControl.isLoading = false;
                    $('#btn-contact-trigger').removeClass('fa-spin');
                },
                success: function (html) {
                    let form = $(html).find('#content form');

                    // A trükk: Keressük meg a submit gombot és fosszuk meg az erejétől
                    form.find('button[type="submit"], input[type="submit"]').each(function () {
                        $(this).attr('type', 'button'); // Átváltjuk sima gombra
                        $(this).attr('id', 'chat-contact-submit'); // Adunk neki egy egyedi ID-t
                        $(this).removeAttr('data-oc-action'); // Ha OC4-es attribútum lenne rajta, töröljük
                    });

                    $container.html(form);
                    $overlay.fadeIn(200);

                    // Most már nem a form 'submit' eseményére kötünk, hanem a gomb kattintásra
                    bindContactFormSubmit();
                }
            });
        }
    });

    function sendChataiwdEmail() {

        if (Date.now() - chataiwdFormOpenedTime < 3000) {
            console.warn("Spam protection: too fast.");
            showSuccessState(); // Meghívjuk a kamu sikert
            return; // KILÉPÜNK, nem fut le az AJAX
        }

        if ($('#chataiwd_honeypot').val() !== "") {
            showSuccessState();
            return;
        }

        const $form = $('#chataiwd-contact-form');
        if (!$form[0].reportValidity()) {
            return;
        }


        const $btn = $('#btn-send-contact');
        const $status = $('#contact-form-status');
        const $container = $('#information-contact-container');
        const originalFormHtml = $container.html();

        if ($('#chataiwd-contact-name').val().length < 3) {
            alert(cfg.text_name_short); return;
        }

        const formData = {
            action: 'chataiwd_send_contact_email',
            nonce: cfg.nonce, // Használd a cfg objektumot, amit a platformnál is
            name: $('#chataiwd-contact-name').val(),
            email: $('#chataiwd-contact-email').val(),
            message: $('#chataiwd-contact-message').val()
        };

        $.ajax({
            url: cfg.send_email_url,
            method: 'POST',
            data: formData,
            beforeSend: function() {
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                // Siker esetén kicseréljük a tartalmat, mint az OC-nál
                const successHtml = `
                    <div class="alert alert-success" style="padding:20px; text-align:center;">
                        <i class="fa fa-check-circle fa-2x" style="display:block; margin-bottom:10px; color:var(--chataiwd-main-color);"></i>
                        <strong style="display:block; margin-bottom:5px;">${response.data}</strong>
                        <span style="font-size:0.9em; color:#666;">${cfg.text_email_will_back}</span>
                    </div>`;

                $container.html(successHtml);

                setTimeout(() => {
                    $('#information-contact').fadeOut(400);
                    $container.html(originalFormHtml);

                    if (typeof sendFaq === 'function') {
                        sendFaq('', response.data + " 👋", false);
                    }
                }, 2500);
            },
            error: function() {
                alert(cfg.error_server);
                $btn.prop('disabled', false).html(cfg.text_send);
            }
        });
    }

    function showSuccessState() {
        const $container = $('#information-contact-container');
        $container.html('<div class="alert alert-success">' + cfg.text_message_sent + '</div>');
        setTimeout(() => { $('#information-contact').fadeOut(400); }, 2000);
    }

    function validateEmail(email) {
        // Egy fokkal precízebb regex, mint a sima \S
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function bindContactFormSubmit() {
        $('#chat-contact-submit').on('click', function(e) {
            e.preventDefault();

            // Csak a chat kontakt konténerén belül keresünk!
            const $container = $('#chataiwd-body #information-contact');
            const $form = $container.find('form');

            const $nameField = $container.find('input[name="name"]');
            const $emailField = $container.find('input[name="email"]');
            const $msgField = $container.find('textarea[name="enquiry"]');

            // Takarítás: töröljük a korábbi hibaüzeneteket
            $container.find('.chat-error-msg').remove();
            let hasError = false;

            // Tételes ellenőrzés
            if ($nameField.val().trim().length < 3) {
                $nameField.after('<div class="chat-error-msg" style="color:#e74c3c; font-size:11px;">'+cfg.error_enter_name+'</div>');
                hasError = true;
            }

            if (!validateEmail($emailField.val())) {
                $emailField.after('<div class="chat-error-msg" style="color:#e74c3c; font-size:11px;">'+cfg.error_invalid_mail+'</div>');
                hasError = true;
            }

            if ($msgField.val().trim().length < 10) {
                $msgField.after('<div class="chat-error-msg" style="color:#e74c3c; font-size:11px;">'+cfg.error_message_short+'</div>');
                hasError = true;
            }

            // Ha van hiba, megállunk
            if (hasError) return;

            // --- Innen jön a "Vaktában küldés" rész ---

            const successHtml = `
                <div class="alert alert-success" style="padding:15px; text-align:center;">
                    <i class="fa fa-check-circle"></i> ${cfg.text_recorded_respond}
                </div>`;

            // Kicseréljük a formot a siker üzenetre
            $('#information-contact-container').html(successHtml);

            let data = $form.serialize();
            if (typeof cfg !== 'undefined' && cfg.nonce) {
                data += '&nonce=' + encodeURIComponent(cfg.nonce);
            }

            $.ajax({
                url: $form.attr('action') || 'index.php?route=information/contact',
                type: 'post',
                data: data,
                global: false
            });
            const successMessage = cfg.text_thank_you_patience + " 👋";


            setTimeout(() => {
                $container.fadeOut(400, function() {
                    sendFaq('', successMessage, false);
                });
            }, 2500);



        });
    }

    /* Csengő (Diszpécser hívása) */
    $('#btn-call-human').on('click', function() {

        const $btn = $('#btn-call-human');

        // Ha már aktív a hívás (villog), ne küldjük el újra
        if ($btn.hasClass('pulse-ai-bell')) {
            $btn.removeClass('pulse-ai-bell');
            return;
        }

        closeAllChatOverlays();

        $.ajax({
            url: cfg.call_human_url,
            method: 'POST',
            data: {
                session_id: sessionId,
                registration_id: registrationId,
                nonce: cfg.nonce
            },
            dataType: 'json',
            beforeSend: function() {
                // Vizuális visszajelzés: a csengő elkezd pulzálni
                $btn.addClass('pulse-ai-bell').css('color', '#ffc107');

                // Egy kis bot üzenet a chatbe, hogy a user lássa: történt valami
                const waitMessage = cfg.text_notified_dispatcher;
                sendFaq('', waitMessage, false);
            },
            complete: function() {
                setTimeout(() => {
                    $('#btn-call-human').removeClass('pulse-ai-bell').css('color', '');
                }, 7000);
            },
            success: function(json) {
            },
            error: function (e) {
                debugger;
            }
        });
    });

    function closeAllChatOverlays() {

        if (isChatLoading) {
            $('.chataiwd-footer i, .chataiwd-footer button, #chataiwd-input').css({
                'pointer-events': 'none',
                'opacity': '0.6'
            });
        }


        function enableChatInteractions() {
            isChatLoading = false;
            $('.chataiwd-footer i, .chataiwd-footer button, #chataiwd-input').css({
                'pointer-events': 'auto',
                'opacity': '1'
            });
        }


        const overlays = [
            '#faq-overlay',
            '#information-contact',
            '#emoji-picker-container',
            '#voice-loader',
            '#share-cart-form'
        ];

        overlays.forEach(selector => {
            const $el = $(selector);
            if ($el.is(':visible')) {
                $el.fadeOut(150);
            }
        });


        $('.chataiwd-footer i, .chataiwd-footer button').removeClass('fa-spin pulse-ai pulse pulse-ai-bell');

        if (typeof recognition !== 'undefined' && isListening) {
            recognition.stop();
        }
    }


    $(document).on('mouseenter', '#btn-share-cart', function() {
        const $bubble = $('.share-btn-bubble');

        // Csak akkor futtatjuk a fadeOut-ot, ha a buborék még látható
        if ($bubble.is(':visible')) {
            $bubble.fadeOut(450, function() {
                // Opcionális: elmenthetjük a localStorage-ba is,
                // hogy többet ne jöjjön elő, hiszen már látta a júzer
                localStorage.setItem('vrcs_teaser_muted', 'true');
            });
        }
    });

    function toggleShareCartOverlay() {
        $('.share-btn-bubble').fadeOut(450);
        localStorage.setItem('vrcs_bubble_muted', 'true');

        const $form = $('#share-cart-form');

        if ($form.is(':visible')) {
            // Ha már nyitva van, bezárjuk
            $form.fadeOut(150);
        } else {
            // Ha nincs nyitva:
            // 1. Előbb minden mást bezárunk (FAQ, Emoji, stb.)
            closeAllChatOverlays();

            // 2. Kinyitjuk ezt (legyen rajta a .hidden, ha alapból rejtett, de a fadeIn kezeli)
            $form.removeClass('hidden').hide().fadeIn(150, function() {
                $('#share-recipient-email').focus();
            });

            // 3. Jelentsük az AI-nak vagy a logikának, hogy a júzer interakcióba lépett
            localStorage.setItem('vrcs_teaser_muted', 'true');
            cartLogicActive = false;

            // Ha van kint AI kártya, azt is elrejthetjük, mert már megnyitotta a formot
            $('.chataiwd-share-card').fadeOut(300);
        }
    }

    $(document).on('mouseenter', '#btn-share-cart', function() {
        $('.share-btn-bubble').fadeOut(450);
        localStorage.setItem('vrcs_bubble_muted', 'true');

    });

    function updateLastMessageId(newId) {
        newId = Number(newId) || 0;

        if (newId > lastMessageId) {
            lastMessageId = newId;
        }
    }



    function checkChatConsent() {
        if (!getCookie('vrcs_chat_consent_accepted')) {
            $('#chat-consent-overlay').removeClass('hidden');
        } else {
            $('#chat-consent-overlay').addClass('hidden');
        }
    }

    function acceptChatConsent() {
        const btn = $('.btn-accept');

        $.ajax({
            url: cfg.save_consent_url,
            method: 'POST',
            data: {
                session_id: sessionId,
                nonce: cfg.nonce,
            },
            dataType: 'json',
            beforeSend: function() {
                // 1. Gomb letiltása, hogy ne lehessen újra kattintani
                btn.prop('disabled', true);
                btn.addClass('btn-loading');
                btn.data('original-text', btn.html());
                btn.html('<i class="fa fa-spinner fa-spin"></i>');
            },
            complete: function() {
                // Ez akkor is lefut, ha hiba történik vagy ha sikeres
                // Ha nem zárod be azonnal az ablakot, itt kellene visszakapcsolni:
                // btn.prop('disabled', false);
                // btn.removeClass('btn-loading');
            },
            success: function(json) {
                if (json['success']) {
                    setCookie('vrcs_chat_consent_accepted', 'true', 365);
                    $('#chat-consent-overlay').addClass('hidden');

                }
            },
            error: function (e) {
                debugger;
                setCookie('vrcs_chat_consent_accepted', 'true', 365);
                $('#chat-consent-overlay').addClass('hidden');

                btn.prop('disabled', false);
                btn.removeClass('btn-loading');
                btn.html(btn.data('original-text'));
            }
        });
    }


    // Segédfüggvények a sütihez
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') {
                c = c.substring(1, c.length); // Itt hiányzott a "c." az elejéről
            }
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }



    function showLegal(view) {
        // Összes alnézet elrejtése
        document.getElementById('consent-main-view').classList.add('hidden');
        document.getElementById('legal-privacy-view').classList.add('hidden');
        document.getElementById('legal-terms-view').classList.add('hidden');

        // Csak a kért nézet megjelenítése
        if (view === 'privacy') {
            document.getElementById('legal-privacy-view').classList.remove('hidden');
        } else if (view === 'terms') {
            document.getElementById('legal-terms-view').classList.remove('hidden');
        } else {
            // Vissza a főoldalra
            document.getElementById('consent-main-view').classList.remove('hidden');
        }
    }


    function showWelcomeMessage() {
        if (!welcomeLogicActive) return;

        if ($('#cart-share-teaser-bubble').is(':visible')) return;

        const bubble = document.querySelector('#chat-welcome-bubble');
        const chatBtn = document.querySelector('.chataiwd-open-btn');
        const chatContainer = document.querySelector('#chataiwd-container');

        // Itt a trükk: lekérjük a számított stílust (Computed Style)
        let isHidden = true;
        if (chatContainer) {
            const style = window.getComputedStyle(chatContainer);
            isHidden = (style.display === 'none' || chatContainer.classList.contains('hidden'));
        }

        // Csak akkor mutassuk, ha a gomb létezik ÉS a chat ablak el van rejtve
        if (bubble && chatBtn && isHidden) {

            bubble.classList.add('show');
            //chatBtn.classList.add('chat-btn-shake');

            setTimeout(() => {
                bubble.classList.remove('show');
                //chatBtn.classList.remove('chat-btn-shake');
            }, 5000);
        }
    }


    // Az első megjelenés 5 másodperccel az oldalbetöltés után
    if (showMessageTimeout != -1) {
        showMessageTimeout = setTimeout(() => {
            showWelcomeMessage();

            // Utána 60 másodpercenként ismételjük
            showMessageInterval = setInterval(showWelcomeMessage, 16000);
        }, 10000);
    }

    function chataiwdOpenWhatsApp(phoneNumber) {
        if (!phoneNumber) {
            console.error("WhatsApp number is missing.");
            return;
        }

        // Alapértelmezett üzenet - ezt akár később is kiveheted a $data tömbbe
        const defaultText = cfg.text_interested_your || 'Hello, I am interested in your products.';
        const encodedText = encodeURIComponent(defaultText);

        const waUrl = `https://wa.me/${phoneNumber}?text=${encodedText}`;

        window.open(waUrl, '_blank');
    }



    let isSyncBusy = false;
    let syncInterval = 10000;

    function startBackgroundSyncTimer(isFirstRun = true) {

        if (isSyncBusy) {
            setTimeout(() => startBackgroundSyncTimer(false), syncInterval);
            return;
        }

        isSyncBusy = true;

        $.ajax({
            url: cfg.sync_context_url,
            method: 'POST',
            data: {
                session_id: sessionId,
                registration_id: registrationId,
                current_url: isFirstRun ? window.location.href : '',
                nonce: cfg.nonce
            },
            dataType: 'json',
            complete: function() {
                isSyncBusy = false;
                setTimeout(() => startBackgroundSyncTimer(false), syncInterval);
            },
            success: function(json) {
                if (json && json.success) {
                    const $statusContainer = $('#dispatcher-status-container');
                    const $statusDot = $('.chat-status-dot');
                    const $statusText = $('.chat-status-text');
                    const $bellBtn = $('#btn-call-human');

                    const isOnline = !!json.is_online;

                    if (lastIsOnlineStatus !== isOnline) {
                        lastIsOnlineStatus = isOnline;

                        if (isOnline) {
                            // ONLINE ÁLLAPOT
                            $statusContainer.removeClass('dispatcher-status-hidden');
                            $statusDot.addClass('online').removeClass('offline');
                            $statusText.text(cfg.text_operator_online);

                            // Csak akkor mutatjuk a csengőt, ha a diszpécser online
                            $bellBtn.fadeIn(400);
                        } else {
                            $statusContainer.removeClass('dispatcher-status-hidden');
                            // OFFLINE ÁLLAPOT
                            $statusDot.addClass('offline').removeClass('online');
                            $statusText.text(cfg.text_operator_offline);

                            // Ha fontos, el is rejthetjük az egész konténert
                            // de maradhat is szürke pöttyel, az is bizalmat épít
                            $bellBtn.fadeOut(400);
                        }
                    }
                }
            },
            error: function(e) {
                console.warn('Context sync failed.');
            }
        });
    }

    function showDispatcherTeaser(message, agentName, agentImg) {
        // Ha az ablak már nyitva van, ne zavarjuk a júzert buborékkal
        if (document.getElementById('chataiwd-container').classList.contains('active')) {
            return;
        }

        const bubble = document.getElementById('dispatcher-teaser-bubble');
        const welcomeBubble = document.getElementById('chat-welcome-bubble');

        // Ha kint van az AI welcome buborék, tüntessük el, hogy ne takarják egymást
        if (welcomeBubble) welcomeBubble.classList.add('hidden');

        // Adatok betöltése
        document.getElementById('teaser-message').innerText = message.substring(0, 60) + (message.length > 60 ? '...' : '');
        const defaultAgentName = cfg.text_default_dispatcher || 'Admin';
        document.getElementById('teaser-name').innerText = agentName || defaultAgentName;
        if (agentImg) {
            document.getElementById('teaser-avatar').src = agentImg;
        }

        bubble.classList.remove('hidden');
        bubble.classList.add('show');

    }

    function applyCouponDirectly(data) {
        const $btn = event.currentTarget ? $(event.currentTarget) : null;
        const originalText = $btn ? $btn.html() : '';

        $.ajax({
            url: cfg.apply_coupon_url,
            type: 'post',
            data: {
                coupon: data.code // Az OC alapértelmezetten a 'coupon' kulcsot várja
            },
            dataType: 'json',
            beforeSend: function() {
                const currentWidth = $btn.outerWidth();
                $btn.css({
                    'width': currentWidth + 'px',
                    'display': 'inline-block' // Biztosítjuk, hogy a width érvényesüljön
                });
                $btn.html('<i class="fa fas fa-spinner fa-spin"></i>').prop('disabled', true);
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false).css('width', '100%');
            },
            success: function(json) {
                if (json['success']) {
                    handleGlobalChatSuccess(data.value_text);


                    if ($btn) {
                        // 2. A gombot "Siker" állapotba tesszük és letiltjuk
                        const appliedText = json['text_applied'] || (cfg.text_applied || 'Applied');
                        $btn.html('<i class="fa fa-check"></i> ' + appliedText)

                            .css({
                                'background': '#2ecc71', // Zöld háttér a sikernek
                                'color': '#ffffff',
                                'cursor': 'default',
                                'box-shadow': 'none',
                                'pointer-events': 'none' // Teljesen megállítja a kattintást
                            })
                            .prop('disabled', true)
                            .removeAttr('onclick');

                        // Opcionális: A siker szövege a gomb alá (zölddel)
                        $btn.after('<div style="color:#2ecc71; font-size:10px; margin-top:5px; text-align:center;">' + (data.value_text || json['success']) + '</div>');
                    }

                if (typeof $().load === 'function') {
                        $('#cart').load('index.php?route=common/cart'+cfg.method_separator+'info');
                    }
                } else {
                    handleGlobalChatError(
                        json.error,
                        "",
                        "",
                        false
                    );
                    if ($btn) {
                        $btn.html(json['error_button'])
                            .css({
                                'background': '#bdc3c7',
                                'color': '#ffffff',
                                'cursor': 'not-allowed',
                                'box-shadow': 'none',
                                'text-decoration': 'line-through'
                            })
                            .prop('disabled', true)
                            .removeAttr('onclick');

                        // A hibaüzenetet azért kiírjuk alá, hogy tudja miért nem ment
                        $btn.after('<div style="color:#e74c3c; font-size:10px; margin-top:5px; text-align:center;">' + json['error'] + '</div>');
                    }
                }
            },
            error: function(e) {
                debugger;            }
        });
    }

    /**
     * Szerencsekerék interakció indítása a chatben
     * @param {Object} payload - { wheel_id: X, name: '...' }
     * @param {HTMLElement} buttonElement - A gomb, amire kattintottak
     */
    function startVrcsWheelInteration(payload, buttonElement) {
        const $btn = $(buttonElement);
        const wheelId = payload.wheel_id;

        // 1. Megkeressük a legközelebbi üzenetkonténert és kiolvassuk a beépített message_id-t
        const $messageContainer = $btn.closest('.chataiwd-message');
        const messageId = $messageContainer.data('message-id');

        if (!messageId) {
            console.error('Hiba: Nem található message_id az üzenetkonténerben!');
            return false;
        }

        if ($btn.hasClass('vrcs-disabled') || $btn.prop('disabled')) {
            return false;
        }

        $btn.prop('disabled', true).addClass('vrcs-disabled');

        const spinningText = cfg.text_wheel_spinning || 'Spinning...';
        $btn.html('<i class="fa fa-spinner fa-spin"></i> ' + spinningText);

        $.ajax({
            url: cfg.spin_wheel_url,
            type: 'POST',
            dataType: 'json',
            data: {
                wheel_id: wheelId,
                message_id: messageId, // JAVÍTÁS: Átküldjük a kritikus azonosítót
                session_id: sessionId,  // A js-bel legyártott chat session_id
            },
            success: function(json) {
                const $card = $btn.closest('.vrcs-wheel-card');

                if (json.is_claimed && json.html) {
                    if ($card.length) {
                        $card.fadeOut(200, function() {
                            $(this).replaceWith(json.html); // Kicseréljük a teljes kártyát a lezárt Twig sablonra
                        });
                    }
                    return;
                }

                // Ha a kontroller hibát dob (pl. első lépcsős védelem megfogta)
                if (json.error) {
                    alert(json.error); // Vagy egy szebb beépített chat hibaüzenet
                    // Visszaállítjuk a gombot
                    const spinButtonText = cfg.text_wheel_spin_btn || 'Spin!';
                    $btn.prop('disabled', false).removeClass('vrcs-disabled').html('<i class="fa fa-play-circle"></i> ' + spinButtonText);
                    return;
                }

                // Ha minden sikeres, a json.html-ben kapott kódot animáljuk be
                if (json.success && json.html) {
                    if ($card.length) {
                        $card.fadeOut(200, function() {
                            $(this).html(json.html).fadeIn(300);
                        });
                    } else {
                        $('#chataiwd-body').append(json.html); // A pontos chat törzs container
                    }
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.error('Sorsolási hiba: ' + thrownError);
                const spinButtonText = cfg.text_wheel_spin_btn || 'Spin!';
                $btn.prop('disabled', false).removeClass('vrcs-disabled').html('<i class="fa fa-play-circle"></i> ' + spinButtonText);
            }
        });
    }

    // EXPORTÁLÁS A GLOBÁLIS TÉRBE
    window.openChataiwd = openChataiwd;
    window.closeChataiwd = closeChataiwd;
    window.sendChataiwdMessage = sendChataiwdMessage;
    window.submitShareCart = submitShareCart;
    window.closeCartTeaser = closeCartTeaser;
    window.toggleShareCartOverlay = toggleShareCartOverlay;

    // Auth és History merge
    window.submitAuthFormLogin = submitAuthFormLogin;
    window.submitAuthFormRegister = submitAuthFormRegister; // Új!
    window.logoutAuth = logoutAuth; // Új!
    window.mergeAnonymousHistory = mergeAnonymousHistory;
    window.notMergeAnonymousHistory = notMergeAnonymousHistory;

    // Account Linking (Fiók összekötés)
    window.showLinkingForm = showLinkingForm;
    window.linkAccounts = linkAccounts;
    window.createNewChatAccount = createNewChatAccount;

    // Consent és Legal
    window.acceptChatConsent = acceptChatConsent;
    window.showLegal = showLegal;

    // UI és FAQ
    window.showWelcomeMessage = showWelcomeMessage;
    window.sendFaq = sendFaq;

    window.chataiwdOpenWhatsApp = chataiwdOpenWhatsApp;
    window.applyCouponDirectly = applyCouponDirectly;

    window.startVrcsWheelInteration = startVrcsWheelInteration;

})(jQuery);