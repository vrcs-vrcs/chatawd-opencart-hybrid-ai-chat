<?php
$_['heading_title'] = '<font color="blue">ChatVRCS Beállítások</font>';
$_['heading_title2'] = 'ChatVRCS Beállítások';

$_['text_extension'] = 'Bővítmények';
$_['text_edit'] = 'Beállítások szerkesztése';
$_['entry_status'] = 'Állapot';

$_['entry_api_key'] = 'OpenAI API Kulcs';
$_['entry_model'] = 'Modell (pl.: gpt-3.5-turbo)';
$_['entry_temperature'] = 'Véletlenszerűség (0.0 – 2.0)';
$_['entry_prompt'] = 'Alapértelmezett utasítás';
$_['entry_color'] = 'Chat Szín';
$_['entry_history_limit'] = 'Előzmények korlátja';
$_['text_prompt_default'] = 'Válaszolj úgy, mintha bolti asszisztens lennél.';
$_['entry_get_api_key'] = 'CHATGPT API kulcs igénylési folyamata:';
$_['entry_select_pack'] = 'Válassz csomagot';
$_['entry_select_pack_help'] = 'Csomag részletei';
$_['entry_reward_presets'] = 'Jutalompont gombok';
$_['text_reward_help']     = 'Adja meg a választható pontértékeket vesszővel elválasztva (pl: 50,100,250,500). Ha üresen hagyja, a jutalompont funkció nem jelenik meg a chaten.';

$_['text_success'] = 'A beállítások sikeresen elmentve!';
$_['error_permission'] = 'Nincs jogosultságod a modul módosításához!';

// Regisztrációs szövegek
$_['entry_registration_id'] = 'Regisztrációs azonosító (vrcs.hu)';
$_['help_registration_id'] = 'Ezt az azonosítót a vrcs.hu biztosítja, és a bolt azonosítására szolgál. Ha még nincs ilyen azonosítód, kattints a Regisztráció gombra.';
$_['button_register'] = 'Regisztráció';
$_['text_error_server'] = 'Hiba történt a szerverrel való kommunikáció során. Kérlek, próbáld újra később.';
$_['error_registry'] = 'Nem sikerült regisztrálni a vrcs.hu-n. HTTP kód: %s Hiba: %s';

$_['text_free_pack'] = 'Alap csomag (Ingyenes)';
$_['text_free_pack_descr'] = 'Leírás: Jelenlegi funkcionalitás (alap AI chat verzió, egyszeri kontextus memória beszélgetés közben).';

$_['text_basic_pack'] = 'Utolsó 3 üzenet megjegyzése (5.000 Ft/év)';
$_['text_basic_pack_descr'] = 'Az AI megjegyzi az utolsó 3 üzenetet, így személyre szabottabb válaszokat tud adni.';

$_['text_standard_pack'] = 'Utolsó 6 üzenet megjegyzése (7.000 Ft/év)';
$_['text_standard_pack_descr'] = 'Az AI megjegyzi az utolsó 6 üzenetet, így személyre szabottabb válaszokat tud adni.';

$_['text_premium_pack'] = 'Operátor átvétel és visszaadás (15.000 Ft/év)';
$_['text_premium_pack_descr'] = 'A bolt tulajdonosa átveheti az AI-tól a beszélgetést, kézzel válaszolhat, majd visszaadhatja. Tartalmaz admin felülethez való hozzáférést is.';
$_['text_vrcs_key'] = 'Használom a VRCS kényelmi kulcsot';
$_['text_chataiwd_key'] = 'Saját CHATGPT API kulcsot regisztrálok';
$_['text_chataiwd_kredit'] = 'ChatGPT API használati kreditek megvásárlása';
$_['text_kredit_egyenleg'] = 'Kredit egyenlegem:';
$_['text_billing'] = 'Következő számlázás, ha az egyenleg 1 USD alá csökken. (10 USD)';

$_['text_confirm_package_switch'] = 'Biztosan át szeretnél váltani a következő csomagra: %s?';
$_['entry_dispatcher'] = 'Diszpécser felület:';
$_['help_dispatcher'] = 'Diszpécser felület:';
$_['button_dispatcher'] = 'Diszpécser felület';
$_['button_fizetés'] = 'Fizetés';
$_['button_fizetes'] = 'Fizetés';

// Új mezők a hozzáadott beállításokhoz
$_['entry_chat_button'] = 'Chat gomb szövege';
$_['help_chat_button'] = 'Adja meg a chat gombhoz megjelenő szöveget, amely a felhasználókat csevegésre ösztönzi.';
$_['placeholder_chat_button'] = 'pl.: ChatGpt Kérdezz most!';

$_['entry_ai_response_header'] = 'AI válasz fejléc';
$_['help_ai_response_header'] = 'Add meg a fejléc szövegét, amely megjelenik, amikor az AI válaszol a csevegésben.';
$_['placeholder_ai_response_header'] = 'pl.: AI válasz';

$_['entry_dispatcher_response_header'] = 'Diszpécser válasz fejléc';
$_['help_dispatcher_response_header'] = 'Add meg a fejléc szövegét, amely megjelenik, amikor a diszpécser válaszol a csevegésben.';
$_['placeholder_dispatcher_response_header'] = 'pl.: Diszpécser válasz';

$_['entry_ai_response_indicator'] = 'AI válasz jelző';
$_['help_ai_response_indicator'] = 'Add meg a szöveget, amely a chat ikon mellett jelenik meg, amikor az AI válaszol.';
$_['placeholder_ai_response_indicator'] = 'pl.: AI éppen válaszol';

$_['entry_dispatcher_response_indicator'] = 'Diszpécser válasz jelző';
$_['help_dispatcher_response_indicator'] = 'Add meg a szöveget, amely a chat ikon mellett jelenik meg, amikor a diszpécser válaszol.';
$_['placeholder_dispatcher_response_indicator'] = 'pl.: Diszpécser éppen válaszol';

$_['entry_welcome_message'] = 'Üdvözlő üzenet';
$_['help_welcome_message'] = 'Add meg az üdvözlő üzenetet, amely megjelenik, amikor a chat elindul.';
$_['placeholder_welcome_message'] = 'pl.: Üdvözlöm! Miben segíthetek ma?';


$_['help_packages'] = '
<b>Alap csomag (Ingyenes)</b><br>
Leírás: Jelenlegi funkcionalitás (alap AI chat verzió, egyszeri kontextus memória beszélgetés közben).<br><br>

<b>Utolsó 3 üzenet megjegyzése (5.000 Ft/év)</b><br>
Az AI megjegyzi az utolsó 3 üzenetet, így személyre szabottabb válaszokat tud adni.<br><br>

<b>Utolsó 6 üzenet megjegyzése (7.000 Ft/év)</b><br>
Az AI megjegyzi az utolsó 6 üzenetet, így személyre szabottabb válaszokat tud adni.<br><br>

<b>Operátor átvétel és visszaadás (15.000 Ft/év)</b><br>
A bolt tulajdonosa átveheti az AI-tól a beszélgetést, kézzel válaszolhat, majd visszaadhatja. Tartalmaz admin felülethez való hozzáférést is.<br><br>
';

// Tooltip-ek
$_['help_api_key'] = 'Add meg az OpenAI-tól kapott ChatGPT API kulcsodat. A kommunikációhoz szükséges.';
$_['help_model'] = 'Válaszd ki a használni kívánt ChatGPT modellt. A különböző modellek eltérnek árban és képességekben. A gpt-3.5-turbo általában jó egyensúlyt nyújt.';
$_['help_temperature'] = "A válaszok kreativitását szabályozza:\n0.0 – Teljesen kiszámítható, pontos, nincs kreativitás.\n0.7 (alapértelmezett) – Kiegyensúlyozott kreativitás és pontosság.\n1.0+ – Kreatívabb, változatosabb válaszok, de kevésbé pontosak lehetnek.\n2.0 – Maximális kreativitás, sokféle válasz, akár túl laza.";
$_['help_prompt'] = 'Adj meg egy alapértelmezett utasítást, amely vezeti az AI válaszait. Pl.: „Segítőkész bolti eladó vagy, aki a vásárlók kérdéseire válaszol.”';
$_['help_placeholder'] = 'Írd be, mit szeretnél küldeni az AI-nak. Pl.: Válaszolj úgy, mint egy bolti eladó.';
$_['help_color'] = 'Válaszd ki a chat ablak elsődleges színét (fejléc, gombok stb.).';
$_['help_history_limit'] = 'Hány korábbi kérdés-válasz párt küldjön el a ChatGPT-nek (0–5). 0 = nincs előzmény. Nagyobb érték több kontextust ad, de több tokenbe kerül.';
$_['help_select_pack'] = 'help_select_pack';
$_['help_vrcs_source'] = 'A VRCS.HU által biztosított API kulcsot használja, fizetés nálunk történik. Nem kell ChatGPT regisztráció.';
$_['help_chataiwd_source'] = 'Saját ChatGPT API kulcsot kell megadni, amit az OpenAI-től szerezhetsz be.';
$_['help_kredit_vrcs'] = '<br><b>A ChatAWD hibrid infrastruktúra és a használati kreditek</b><br><br>
A ChatAWD egy prémium kategóriás, nagyteljesítményű hibrid értékesítési és ügyfélszolgálati motor. A rendszer az OpenAI legfejlettebb nyelvi modelljeit (GPT-4o), a finomhangolt helyi vektoros keresést és a látogatói preferenciák követését ötvözi egy intelligens diszpécseri irányítópulttal (valós idejű kosár-rekonstrukció, vásárlási riasztások, kuponok, célzott hűségpont-ígéretek és szerencsekerék).<br><br>
Ennek az összetett felhő-infrastruktúrának a fenntartása – beleértve az AI számítási kapacitásokat, a biztonságos API-útvonalakat és az áruházak közötti valós idejű adatbázis-szinkronizációt – használat-alapú kreditrendszerben működik.<br><br>
A drága és rugalmatlan fix havidíjas előfizetések helyett a ChatAWD egy teljesen transzparens, mérés-alapú (pay-as-you-go) elszámolást alkalmaz. Ön kizárólag a boltja által ténylegesen generált AI és diszpécseri tranzakciók után fizet.<br><br>
<b>Hogy teljesen kockázatmentesen győződhessen meg a modul konverzió-növelő hatásáról, a regisztrációkor 2 USD értékű indító kreditet biztosítunk.</b><br><br>';




$_['help_get_api_key'] = '🔑 Hogyan szerezhetsz ChatGPT API kulcsot?<br>Nyisd meg a következő weboldalt:<br><a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a><br><br>Jelentkezz be vagy regisztrálj egy fiókot az OpenAI-nál (Google fiók is használható).<br><br>Bejelentkezés után kattints a „Create new secret key” gombra.<br><br>Másold ki a generált API kulcsot, és illeszd be ebbe a modulba.<br><br>Fontos: A teljes API kulcsot csak egyszer láthatod!<br><br>Ha elveszted, generálj újat ugyanazon az oldalon.<br><br>
💰 API használat és egyenleg ellenőrzése:<br><br>
<a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank">Számlázási áttekintés</a><br><br>
<a href="https://platform.openai.com/usage" target="_blank">Használati statisztika</a><br><br>
';

// Modell leírások
$_['gpt-4.1-nano'] = ' – Kicsi, gyors válaszidő, olcsó – egyszerű feladatokra kiváló.';
$_['gpt-4'] = ' – Erőteljesebb, de lassabb és drágább lehet.';
$_['gpt-4.1-mini'] = ' – Kiegyensúlyozott: sebesség és minőség.';
$_['gpt-4-turbo'] = ' – Optimális választás: gyors, olcsóbb és erős. Általában a legjobb ár-érték arány.';
$_['gpt-3.5-turbo'] = ' – Még olcsóbb, kevésbé pontos, de sok feladatra elegendő.';
$_['gpt-3.5-turbo-16k'] = ' – Hosszabb kontextusablak – hosszabb beszélgetésekhez vagy több hivatkozáshoz.';
$_['gpt-4O'] = ' – A legújabb valós idejű modell, nagyon gyors és erős. Használd, ha elérhető.';
$_['gpt-4O-mini'] = ' – Könnyű feladatokra és gyors válaszokra – pl. automatikus válaszokhoz, kereséshez.';
$_['gpt-4.1'] = ' – Következő generációs GPT modell, előnézeti vagy speciális verzió lehet (API verziótól függően).';

// Előfizetés időtartama
$_['entry_duration'] = 'Előfizetés időtartama:';
$_['text_duration_3'] = '3 hónap';
$_['text_duration_6'] = '6 hónap';
$_['text_duration_9'] = '9 hónap';
$_['text_duration_12'] = '12 hónap';
$_['text_duration_cancel'] = 'Leiratkozás';

$_['help_recovery_info'] = 'Állítson be maximum 5 emlékeztető emailt. Az üresen hagyott sorok nem kerülnek kiküldésre.';
$_['entry_recovery_delay'] = 'Késleltetés';
$_['entry_recovery_subject'] = 'Email tárgya (Üres = nem küldi)';
$_['entry_recovery_content'] = 'Üzenet szövege';
$_['text_hours'] = 'Óra';
$_['text_days'] = 'Nap';
$_['text_subject_placeholder'] = 'Pl.: Ottfelejtett valamit?';
$_['text_content_placeholder'] = 'Üzenet...';


$_['text_ai_syncr'] = 'AI szinkronizáció';
$_['text_syncrestart'] = 'Szinkronizáció Újraindítása';
$_['text_syncrestart_click'] = 'Kattints ide, ha az adatbázis tartalmát újra le akarod szinkronizálni az AI-al.';






// új

// Konstruktor fallback értékek (ha még üres az adatbázis)
$_['text_chat_button_fallback']                   = 'Kérdezz az MI-től most!';
$_['text_ai_response_header_fallback']            = 'MI Válaszol';
$_['text_dispatcher_response_header_fallback']    = 'Diszpécser válaszol';
$_['text_ai_response_indicator_fallback']         = 'Az MI éppen válaszol...';
$_['text_dispatcher_response_indicator_fallback'] = 'A diszpécser éppen válaszol...';
$_['text_welcome_message_fallback']               = 'Szia! Miben segíthetek neked ma?';

// AJAX / API hibaüzenetek (register és billing metódusok)
$_['error_permission']                            = 'Figyelmeztetés: Nincs jogosultságod a modul módosításához!';
$_['error_already_registered']                    = 'Már regisztrálva van';
$_['error_domain_not_found']                      = 'Nem sikerült meghatározni az áruház domain nevét.';
$_['error_invalid_input']                         = 'Kérlek, add meg az email címedet!';
$_['error_registry']                              = 'Sikertelen regisztráció! HTTP Kód: %s, Hiba: %s';
$_['error_invalid_server_response']               = 'Érvénytelen válasz a hitelesítő szervertől';
$_['error_no_registration']                       = 'Nincs érvényes regisztrációs azonosító!';
$_['error_server_communication']                  = 'Hiba a távoli szerverrel való kommunikáció során.';
$_['error_invalid_field']                         = 'Érvénytelen mezőnév!';
$_['error_unknown']                               = 'Ismeretlen hiba a szerver válasza alapján.';

// Sikeres regisztráció
$_['text_register_success']                       = 'Sikeres regisztráció!';

$_['text_be_patient_registration']          = 'Kérlek, légy türelemmel, a regisztráció folyamatban van...';
$_['text_failed']                            = 'Hiba';
$_['text_choose_package']                   = 'Kérlek, válassz egy csomagot';
$_['text_restart_sync']                     = 'Biztosan újra akarod indítani a teljes AI szinkronizációt? Ez eltarthat egy ideig.';
$_['text_intitializing']                     = 'AI modul inicializálása...';
$_['error_schema']                     = 'Hiba: A séma nem érkezett meg megfelelően.';
$_['text_ai_learning_help'] = 'Kérjük, ne zárd be az ablakot. <br>Az AI épp elemzi és indexeli a webáruház kínálatát, hogy a chat modul és az intelligens diszpécser kereső tűpontos válaszokat és termékajánlásokat adhasson.';
$_['tab_general'] = 'Általános';
$_['tab_chat_settings'] = 'Chat Beállítások';
$_['tab_faq'] = 'GYIK';
$_['tab_tools'] = 'Funkciók';
$_['tab_abandoned'] = 'Elhagyott kosár';
$_['entry_faq_icon_type'] = 'Ikon típusa';
$_['entry_faq_visual_icon'] = 'Ikon';
$_['entry_faq_visual_image'] = 'Kép';
$_['entry_faq_question'] = 'Kérdés';
$_['entry_faq_answer'] = 'Válasz';
$_['help_tools_info'] = 'Itt kapcsolhatod be vagy ki a chat ablakban megjelenő extra funkciókat.';
$_['help_tool_voice'] = 'Mikrofon ikon megjelenítése hangalapú beviteli lehetőséghez.';
$_['help_tool_image'] = 'Lehetővé teszi a felhasználóknak, hogy képet küldjenek az AI diszpécsernek.';
$_['help_tool_email'] = 'Megjeleníti az e-mail ikont a közvetlen kapcsolatfelvételhez.';
$_['help_tool_faq'] = 'A GYIK megjelenítésének teljes be- vagy kikapcsolása.';
$_['help_tool_whatsapp'] = 'WhatsApp közvetlen üzenetküldő gomb megjelenítése.';
$_['help_whatsapp_number'] = 'Add meg a telefonszámot nemzetközi formátumban.';
$_['text_time'] = 'Idő...';
$_['text_subject'] = 'Például: Elfelejtettél valamit?';
$_['error_initializing_ai'] = 'Hiba az AI inicializálása során';
$_['text_ai_prepared'] = 'AI sikeresen felkészítve! Rendszer indítása...';
$_['text_ai_learning'] = 'AI tanulási folyamat:';
$_['error_ai_arming'] = 'Hiba történt az AI élesítése során:';
$_['error_network'] = 'Hálózati hiba a leállítás során. Újrapróbálkozás 5 másodperc múlva...';
$_['error_closing'] = 'Hiba a lezárás során:';
$_['text_optimizing'] = 'AI modellek optimalizálása és betanítása... Kérlek várj.';
$_['text_empty'] = 'Üres';
$_['text_error'] = 'Hiba';
$_['text_unknown_error'] = 'Ismeretlen hiba';
$_['error_save'] = 'Mentési hiba:';
$_['text_registration'] = 'Regisztráció';
$_['entry_tool_bell']                             = 'Diszpécser hívása (Csengő)';
$_['help_tool_bell']                              = 'Lehetővé teszi a felhasználóknak a diszpécser értesítését.';
$_['entry_tool_voice']                            = 'Hangfelismerés';
$_['entry_tool_image']                            = 'Képfeltöltés';
$_['entry_tool_emoji']                            = 'Emoji választó';
$_['entry_tool_email']                            = 'E-mail kapcsolatfelvételi űrlap';
$_['entry_tool_faq']                              = 'GYIK modul';
$_['entry_tool_whatsapp']                         = 'WhatsApp kapcsolat';
$_['entry_whatsapp_placeholder']                  = 'pl.: +36 70 123 4567';
$_['button_save']                                 = 'Mentés';
$_['button_back']                                 = 'Vissza';
$_['button_add']                                  = 'Hozzáadás';
$_['button_remove']                               = 'Eltávolítás';
$_['text_enabled']                                = 'Engedélyezve';
$_['text_disabled']                               = 'Letiltva';