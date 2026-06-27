// API kulcs útmutató szekció kinyitása/bezárása

const cfg = window.config || {};

let col_api_key_guide = 'kicsi';
let col_package_guide = 'kicsi';

$('#registration-form').keydown(function(e) {
    if (e.key === "Enter") {

    }
});

// Modal ablak kezelése - Duration
function openDurationModal() {
    document.getElementById('duration-modal').style.display = 'block';
    // Alapértelmezésként töröljük a radio gombok kijelölését
    $('input[name="duration"]').prop('checked', false);
}

function closeDurationModal() {
    document.getElementById('duration-modal').style.display = 'none';
    // Visszaállítjuk a package-select eredeti értékét (pl. "free")
    $('#package-select').val($('#package-select option:first').val());
}

// Escape billentyű kezelése (a meglévő keydown eseménykezelő kiegészítése)
$(document).keydown(function(e) {
    if (e.key === "Escape") {
        closeRegistrationModal();
        closeDurationModal();
    }
});

$('#dispatcher-button').on('click', function(e) {
    e.preventDefault();

    $('#human-ai').submit();
});

$('input[name="module_chataiwd_billing"]').on('change', function() {
    const billingValue = $(this).is(':checked') ? 1 : 0; // Checkbox értéke (1 vagy 0)
    const billingName = 'module_chataiwd_billing'; // A mező neve

    $.ajax({
        url: cfg.billing_url,
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({
            name: billingName,
            value: billingValue
        }),
        success: function(data) {
            if (data.success) {
                window.location.reload(); // Frissítés sikeres válasz esetén
            } else {
                $('#registration-message').text(cfg.text_error + (data.error || cfg.text_unknown_error));
            }
        },
        error: function(xhr, status, error) {
            debugger;
            $('#registration-message').text(cfg.text_error_server);

        }
    });
});


$('#content-close-api-key-guide, .content-close-api-key-guide').bind('click', function() {
    if (col_api_key_guide == 'kicsi') {
        col_api_key_guide = 'nagy';
        $('.content-close-api-key-guide').css('font-size', '13px');
        $('#content-close-api-key-guide i').removeClass('fa-plus');
        $('#content-close-api-key-guide i').addClass('fa-minus');
    } else {
        col_api_key_guide = 'kicsi';
        $('.content-close-api-key-guide').css('font-size', '4px');
        $('#content-close-api-key-guide i').removeClass('fa-minus');
        $('#content-close-api-key-guide i').addClass('fa-plus');
    }
});

// jQuery ellenőrzés
if (typeof jQuery === 'undefined') {
    console.error('jQuery nincs betöltve!');
} else {
    console.log('jQuery betöltve, verzió: ' + jQuery.fn.jquery);
}

// Modal ablak kezelése
function openRegistrationModal() {
    // Mentés előtte
    const $status = $('#input-status');
    if ($status.is('select')) {
        $status.val('1');
    } else {
        $status.prop('checked', true);
    }
    saveFormBeforeRegistration(function() {
        document.getElementById('registration-modal').style.display = 'block';
        document.getElementById('user-email').focus();

    });
}

function closeRegistrationModal() {
    document.getElementById('registration-modal').style.display = 'none';
    document.getElementById('registration-message').textContent = '';
}

$('#vrcs-payment-button').on('click', function() {
    if (!registrationId) {
        alert('Please register the module first to use it!');
        return;
    }
    openPaymentModal();
});

function openPaymentModal() {
    saveFormBeforeRegistration(function() {
        document.getElementById('payment-modal').style.display = 'block';
        document.getElementById('payment-message').textContent = '';
    });
}

function closePaymentModal() {
    document.getElementById('payment-modal').style.display = 'none';
}

// Regisztrációs űrlap egységesített elküldése (Enter és klikk esetén is működik)
$(document).on('click', '#registration-form button[type="button"]', function(e) {
    e.preventDefault();

    const userEmail = $('#user-email').val().trim();
    if (!userEmail) {
        $('#registration-message').text(cfg.error_invalid_input);
        return;
    }

    registerWithVrcs(userEmail);
});

// Regisztráció indítása a vrcs.hu felé
function registerWithVrcs(userEmail) {
    $.ajax({
        url: cfg.register_url,
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({ user_email: userEmail }),
        beforeSend: function() {
            $('#registration-message').text(cfg.text_be_patient_registration);
        },
        complete: function() {
            $('#registration-message').text('');
        },
        success: function(data) {
            if (data.success) {
                sessionStorage.setItem('vrcs_needs_initial_sync', '1');
                closeRegistrationModal();
                window.location.reload();
            } else {
                $('#registration-message').text(cfg.text_failed + data.error);
            }
        },
        error: function(xhr, status, error) {
            debugger;
            var responseText = xhr.responseText;
            if (responseText && responseText.includes('"success"')) {
                closeRegistrationModal();
                $('#registration-message').text("Successful registration!"); // Sikeres üzenet

                window.location.reload();
            }
            $('#registration-message').text(cfg.text_error_server);

        }
    });
}

// Hibrid értesítő banner generáló automatikus eltűnéssel (4 másodperc)
function showAdminSuccessAlert(message) {
    const alertHtml = `
        <div class="alert alert-success alert-dismissible" id="chataiwd-success-alert">
            <i class="fa fa-solid fa-check-circle"></i> ${message}
            <button type="button" class="close btn-close" data-dismiss="alert" data-bs-dismiss="alert" style="float: right; background: none; border: none; font-size: 20px; line-height: 1;">&times;</button>
        </div>`;

    // Töröljük a korábbi értesítéseket az oldalon, ha újat nyomott a diszpécser
    $('.alert-dismissible').remove();

    // Beillesztjük az új sikeres mentés értesítést a tartalom tetejére
    $('#content > .container-fluid').prepend(alertHtml);

    // ÚJ: 4 másodperc után sima elhalványítás (500ms) és törlés a DOM-ból
    setTimeout(function() {
        const $alert = $('#chataiwd-success-alert');
        if ($alert.length) {
            $alert.fadeOut(500, function() {
                $(this).remove(); // Teljesen letakarítjuk a memóriából
            });
        }
    }, 4000);
}

function saveFormBeforeRegistration(callback = null, showAlert = false) {

        const formData = $('#form-ai').serialize();
    $.ajax({
        url: cfg.save_url,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (callback && typeof callback === 'function') {
                    callback();
                }

                if (showAlert) {
                    const successMsg = response.success || 'Settings saved successfully!';
                    showAdminSuccessAlert(successMsg);
                }
            } else {
                alert(cfg.error_save + (response.error || cfg.text_unknown_error));
            }
        },
        error: function(xhr, status, error) {
            alert(cfg.text_error_server);
        }
    });
}



// Csomagváltás kezelése - átirányítás az URL-re
$(document).ready(function() {

    if (registrationId) {
        if (sessionStorage.getItem('vrcs_needs_initial_sync') === '1') {
            $("#vrcs-normal-settings").hide();
            sessionStorage.removeItem('vrcs_needs_initial_sync');
            startVrcsInitialSync(registrationId);
        }
    }

    const confirmMessageTemplate = cfg.text_confirm_package_switch;

    $('#package-select').on('change', function() {
        const selectedPackage = $(this).val();

        // Ellenőrizzük, hogy a kiválasztott opció az első-e ("free")
        const selectedIndex = $('#package-select option:selected').index();
        if (selectedIndex === 0) {
            return;
        }

        // Modal megnyitása az időtartam kiválasztásához
        openDurationModal();
    });

    // Gomb eseménykezelő a megújításhoz
    $('#renew-subscription').on('click', function() {
        const selectedPackage = $('#package-select').val();
        if (selectedPackage && selectedPackage != 'free') {
            openDurationModal();
        } else {
            $('.alert-dismissible').remove();
            $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i>'+cfg.text_choose_package+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        }
    });

    // Fizetések - kredit egyenleg
    $('#payment_kredit').on('click',function () {
        $('#payment-modal').hide();

        openPostWindow(cfg.checkout_credit, {
            registration_id: registrationId,
            amount: 10,
            duration: 1,
            package: 'kredit',
        });

    });

    // Fizetések - csomagváltás
    $('input[name="duration"]').on('change', function() {
        const duration = $(this).val();
        const selectedPackage = $('#package-select').val();

        if (duration === "0") {
            $('#duration-section').hide();
            $('#package-select').val('free');
            $('input[name="duration"]').prop('checked', false);
            return;
        }

        if (packagesUrl[selectedPackage]) {
            const rawUrl = packagesUrl[selectedPackage];

            const cleanUrl = rawUrl
                .replace(/([&?])package=[^&]*/, '')
                .replace(/[?&]$/, '');

            openPostWindow(cleanUrl, {
                registration_id: registrationId,
                package: selectedPackage,
                duration: duration
            });
            $('#duration-modal').hide();
            $('input[name="duration"]').prop('checked', false);
        }
    });
});



function openPostWindow(url, data, target = '_blank') {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.target = target;
    form.style.display = 'none';

    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = data[key];
            form.appendChild(input);
        }
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}


function addFaqRow() {

    html  = '<tr id="faq-row-' + faq_row + '">';

    // Típus választó
    html += '  <td class="text-start">';
    html += '    <select name="module_chataiwd_faq[' + faq_row + '][type]" class="form-select" onchange="toggleFaqType(' + faq_row + ', this.value);">';
    html += '      <option value="icon" selected>'+cfg.entry_faq_visual_icon+'(FontAwesome)</option>';
    html += '      <option value="image">'+cfg.entry_faq_visual_image+'</option>';
    html += '    </select>';
    html += '  </td>';

    // 2. Ikon input
    html += '  <td class="text-start text-start-icon" style="position: relative">';
    html += '    <div class="input-group" style="text-align: center">';
    html += '      <span class="input-group-text"><i class="fa-solid fa-icons"></i></span>';
    html += '      <input type="text" name="module_chataiwd_faq[' + faq_row + '][icon]" value="fa-solid fa-question" class="form-control" placeholder="fa-solid fa-icon"/>';
    html += '    </div>';
    html += '    <div class="arnyek" style="position: absolute"></div>';
    html += '  </td>';

    // Képkezelő (a Product oldal mintájára)
    html += '  <td class="text-start text-start-image" style="position: relative">';
    html += '    <div class="image-input-' + faq_row + '" >';
    html += '      <div class="card image" style="width: 100px;">';
    html += '        <img src="'+cfg.placeholder+'" alt="" title="" id="thumb-image-' + faq_row + '" data-oc-placeholder="'+cfg.placeholder+'" class="card-img-top"/>';
    html += '        <input type="hidden" name="module_chataiwd_faq[' + faq_row + '][image]" value="" id="input-image-' + faq_row + '"/>';
    html += '        <div class="card-body p-1 text-center">';
    html += '          <button type="button" data-oc-toggle="image" data-oc-target="#input-image-' + faq_row + '" data-oc-thumb="#thumb-image-' + faq_row + '" class="btn btn-primary btn-sm"><i class="fa-solid fa-pencil"></i></button>';
    html += '          <button type="button" data-oc-toggle="clear" data-oc-target="#input-image-' + faq_row + '" data-oc-thumb="#thumb-image-' + faq_row + '" class="btn btn-warning btn-sm"><i class="fa-regular fa-trash-can"></i></button>';
    html += '        </div>';
    html += '      </div>';
    html += '    </div>';
    html += '    <div class="arnyek" style="position: absolute"></div>';
    html += '  </td>';

    html += '  <td class="text-start"><input type="text" name="module_chataiwd_faq[' + faq_row + '][question]" value="" class="form-control"/></td>';
    html += '  <td class="text-start"><textarea name="module_chataiwd_faq[' + faq_row + '][answer]" rows="3" class="form-control"></textarea></td>';

    // A checkbox HTML része a JS-ben ígynézzen ki:
    html += '  <td class="text-center">';
    html += '    <div class="form-check form-switch d-inline-block">';
    html += '      <input type="hidden" name="module_chataiwd_faq[' + faq_row + '][status]" value="0"/>';
    html += '      <input type="checkbox" name="module_chataiwd_faq[' + faq_row + '][status]" value="1" class="form-check-input" onchange="toggleFaqStatus(' + faq_row + ', this.checked);" checked/>';
    html += '    </div>';
    html += '  </td>';

    html += '  <td class="text-end"><button type="button" onclick="$(\'#faq-row-' + faq_row + '\').remove();" class="btn btn-danger"><i class="fa-solid fa-minus-circle"></i></button></td>';
    html += '</tr>';

    $('#faq-table tbody').append(html);
    toggleFaqType(faq_row, 'icon');
    toggleFaqStatus(faq_row, true);
    faq_row++;
}

function toggleFaqStatus(row, isChecked) {
    const $sor = $('#faq-row-' + row);

    if (isChecked) {
        $sor.removeClass('faq-disabled');
    } else {
        $sor.addClass('faq-disabled');
    }
}

function toggleFaqType(row, value) {
    const $sor = $('#faq-row-' + row);

    $sor.find('.arnyek').removeClass('d-arnyek');

    if (value === 'icon') {
        $sor.find('.text-start-image .arnyek').addClass('d-arnyek');
    } else {
        $sor.find('.text-start-icon .arnyek').addClass('d-arnyek');
    }
}


function triggerManualSync() {
    if (!confirm(cfg.text_restart_sync)) {
        return;
    }

    // 1. Elrejtjük a normál beállításokat
    $("#vrcs-normal-settings").hide();

    // 2. Előkészítjük a sync konténert (legyen tiszta)
    $("#vrcs-slider").css("width", "0%");
    $("#sync-table-list").empty();

    // 3. Elindítjuk a folyamatot
    startVrcsInitialSync();
}

var vrcsLimitIg = 20;
window.vrcsStats = {}; // Mostantól ez tartalmazza a { label: '...', count: N } objektumokat

function startVrcsInitialSync() {
    $("#vrcs-sync-container").show();
    $("#sync-status-text").text(cfg.text_intitializing);

    $.ajax({
        url: cfg.local_stats_url,
        type: 'post',
        dataType: 'json',
        success: function(json) {
            // Biztonsági ellenőrzés
            if (!json.schema || typeof json.schema !== 'object') {
                debugger;
                alert(cfg.error_schema);
                return;
            }

            window.vrcsStats = json.stats || {};
            window.vrcsSchema = json.schema;

            var listHtml = '<ul style="margin:10px 0; padding:0; list-style:none;">';
            $.each(window.vrcsSchema, function(tableName, config) {
                var label = (window.vrcsStats[tableName] && window.vrcsStats[tableName].label)
                    ? window.vrcsStats[tableName].label
                    : tableName;

                // Itt a módosítás: hozzáfűzzük a progress-bar container-t
                listHtml += '<li id="li-vrcs-' + tableName + '" class="row" style="margin-bottom: 8px; align-items: center;">' +
                    // Bal oldal: név (6 oszlop)
                    '<div class="col-sm-6" style="display: flex; align-items: center;">' +
                    '<i class="fas fa-circle-notch fa-spin me-2" style="display:none; color:#00a8c6;"></i>' +
                    '<span style="font-size: 13px;">' + label + '</span>' +
                    '</div>' +
                    // Jobb oldal: csík (6 oszlop)
                    '<div class="col-sm-6">' +
                    '<div style="height: 8px; background: #eee; border-radius: 4px; width: 100%;">' +
                    '<div id="bar-vrcs-' + tableName + '" style="width: 0%; height: 100%; background: #08daff; border-radius: 4px; transition: width 0.2s;"></div>' +
                    '</div>' +
                    '</div>' +
                    '</li>';
            });
            listHtml += '</ul>';
            $("#sync-table-list").html(listHtml);

            // Itt hívjuk meg a sort
            processVrcsQueue(0);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            debugger;
            alert(cfg.error_initializing_ai + thrownError);
        }
    });
}

function processVrcsQueue(tableIndex) {
    // Biztonsági ellenőrzés: ha nincs séma, ne fusson le az Object.keys
    if (!window.vrcsSchema) {
        return;
    }

    var tableKeys = Object.keys(window.vrcsSchema);

    if (tableIndex >= tableKeys.length) {
        $("#vrcs-slider").css("background", "#4CAF50").css("width", "100%");
        $("#sync-status-text").text(cfg.text_ai_prepared);
        finalizeVrcsSync();
        return;
    }

    var currentTable = tableKeys[tableIndex];
    var currentConfig = window.vrcsSchema[currentTable];

    var tableData = window.vrcsStats[currentTable] || { label: currentTable, count: 0 };
    var totalCount = tableData.count;

    $("#sync-table-list li").css({"color": "#999", "font-weight": "normal"});
    $("#sync-table-list li i").hide();

    var $currentLi = $("#li-vrcs-" + currentTable);

    if (totalCount === 0) {
        $("#li-vrcs-" + currentTable).css({"color": "#ccc"}).append(" ("+cfg.text_empty+")");
        processVrcsQueue(tableIndex + 1);
        return;
    }

    var adaptiveLimit = Math.max(1, Math.ceil(totalCount / 10));

    $currentLi.css({"color": "#00a8c6", "font-weight": "bold"});
    $currentLi.find("i").show();
    $("#sync-status-text").text(cfg.text_ai_learning + tableData.label + "...");

    runVrcsStep(tableKeys, tableIndex, currentTable, currentConfig, 0, totalCount);
}

function runVrcsStep(tables, tableIndex, currentTable, currentConfig, offset, totalCount) {
    $.ajax({
        url: cfg.push_chunk_url,
        type: 'post',
        data: {
            table: currentTable,
            config: currentConfig,
            offset: offset,
            limit: vrcsLimitIg
        },
        dataType: 'json',
        success: function(json) {
            if (json.success) {
                var percent = Math.round(((offset + vrcsLimitIg) / totalCount) * 100);
                if (percent > 100) percent = 100;

                $("#bar-vrcs-" + currentTable).css("width", percent + "%");
                //$("#vrcs-slider").css("background", "#08daff").css("width", percent + "%");

                if (offset + vrcsLimitIg < totalCount) {
                    setTimeout(function() {
                        runVrcsStep(tables, tableIndex, currentTable, currentConfig, offset + vrcsLimitIg, totalCount);
                    }, 100);
                } else {
                    processVrcsQueue(tableIndex + 1);
                }
            } else {
                handleSyncError(tables, tableIndex, currentTable, currentConfig, offset, totalCount);
            }
        },
        error: function(e) {
            debugger;

            handleSyncError(tables, tableIndex, currentTable, currentConfig, offset, totalCount);
        }
    });
}

function finalizeVrcsSync() {
    $("#sync-status-text").text(cfg.text_optimizing);
    $("#vrcs-slider").css("background", "#4CAF50").css("width", "100%");

    $.ajax({
        url: cfg.finalize_sync_url,
        type: 'post',
        dataType: 'json',
        success: function(json) {
            if (json.success) {

                window.location.reload();
            } else {
                $("#vrcs-slider").css("background", "#f44336");
                $("#sync-status-text").text(cfg.error_closing + json.error);
                alert(cfg.error_ai_arming + json.error);
            }
        },
        error: function(e) {
            debugger;

            $("#vrcs-slider").css("background", "#f44336");
            $("#sync-status-text").text(cfg.error_network);

            setTimeout(function() {
                finalizeVrcsSync();
            }, 5000);
        }
    });
}


function handleSyncError(tables, tableIndex, currentTable, currentConfig, offset, totalCount) {
    $("#bar-vrcs-" + currentTable).css("background", "#ffc107");

    setTimeout(function() {
        $("#bar-vrcs-" + currentTable).css("background", "#08daff"); // Vissza kékre
        runVrcsStep(tables, tableIndex, currentTable, currentConfig, offset, totalCount);
    }, 3000);
}

