<?php
// Heading
$_['heading_title']     = '<font color="blue">ChatVRCS Nastavenia</font>';
$_['heading_title2']    = 'ChatVRCS Nastavenia';

$_['text_extension']    = 'Rozšírenia';
$_['text_edit']         = 'Upraviť nastavenia';
$_['entry_status']      = 'Stav';

$_['entry_api_key']     = 'OpenAI API kľúč';
$_['entry_model']       = 'Model (napr. gpt-4o-mini)';
$_['entry_temperature'] = 'Kreativita / Náhodnosť (0.0 – 2.0)';
$_['entry_prompt']      = 'Predvolený prompt (systémové pokyny)';
$_['entry_color']       = 'Farba chatu';
$_['entry_history_limit'] = 'Limit histórie';
$_['text_prompt_default'] = 'Odpovedaj ako asistent v obchode.';
$_['entry_get_api_key']   = 'Proces vyžiadania kľúča CHATGPT API:';
$_['entry_select_pack']   = 'Vyberte balík';
$_['entry_select_pack_help'] = 'Podrobnosti o balíku';
$_['entry_reward_presets'] = 'Predvoľby vernostných bodov';
$_['text_reward_help']     = 'Zadajte voliteľné hodnoty bodov oddelené čiarkami (napr. 10,20,50,100). Ak necháte pole prázdne, funkcia vernostných bodov sa v chate nezobrazí.';

$_['text_success']      = 'Nastavenia boli úspešne uložené!';
$_['error_permission']  = 'Upozornenie: Nemáte oprávnenie upravovať tento modul!';

// New texts for registration
$_['entry_registration_id'] = 'Registračné ID (vrcs.hu)';
$_['help_registration_id']  = 'Toto ID poskytuje vrcs.hu a slúži na identifikáciu vášho obchodu. Ak ho ešte nemáte, kliknite na tlačidlo Registrovať.';
$_['button_register']       = 'Registrovať';
$_['text_error_server']     = 'Pri komunikácii so serverom sa vyskytla chyba. Skúste to prosím neskôr.';
$_['error_registry']        = 'Registrácia na vrcs.hu zlyhala. HTTP kód: %s Chyba: %s';

$_['text_free_pack']        = 'Základný balík (Zdarma)';
$_['text_free_pack_descr']  = 'Popis: Aktuálna funkčnosť (základná verzia AI chatu, jednorazová pamäť kontextu počas konverzácie).';

$_['text_basic_pack']       = 'Pamätá si posledné 3 chaty (5 000 HUF/rok)';
$_['text_basic_pack_descr'] = 'AI si pamätá posledné 3 chaty, čo umožňuje personalizovanejšie odpovede.';

$_['text_standard_pack']    = 'Pamätá si posledných 6 chatov (7 000 HUF/rok)';
$_['text_standard_pack_descr'] = 'AI si pamätá posledných 6 chatov, čo umožňuje ešte lepšiu personalizáciu.';

$_['text_premium_pack']     = 'Prevzatie operátorom a vrátenie (15 000 HUF/rok)';
$_['text_premium_pack_descr'] = 'Majiteľ obchodu môže prevziať chat od AI, odpovedať manuálne a následne ho vrátiť späť AI. Zahŕňa prístup k rozhraniu Dispatcher.';

$_['text_vrcs_key']         = 'Použiť VRCS komfortný kľúč';
$_['text_chataiwd_key']      = 'Registrovať môj vlastný CHATGPT API kľúč';
$_['text_chataiwd_kredit']   = 'Kúpiť kredity pre používanie ChatGPT API';
$_['text_kredit_egyenleg']  = 'Môj zostatok kreditu:';
$_['text_billing']          = 'Ďalšia fakturácia, keď zostatok klesne pod 1 $. (10 $)';

$_['text_confirm_package_switch'] = 'Naozaj chcete prepnúť na balík %s?';
$_['entry_dispatcher']      = 'Rozhranie Dispatcher:';
$_['help_dispatcher']       = 'Rozhranie pre operátora:';
$_['button_dispatcher']     = 'Otvoriť Dispatcher';
$_['button_fizetés']        = 'Platba';
$_['button_fizetes']        = 'Platba';

// New entries for the added fields
$_['entry_chat_button']     = 'Text tlačidla chatu';
$_['help_chat_button']      = 'Zadajte text, ktorý sa zobrazí na tlačidle chatu, aby ste povzbudili používateľov k začatiu konverzácie.';
$_['placeholder_chat_button'] = 'napr.: Spýtať sa AI!';

$_['entry_ai_response_header'] = 'Hlavička odpovede AI';
$_['help_ai_response_header']  = 'Zadajte text hlavičky, ktorý sa zobrazí, keď v chate odpovedá AI.';
$_['placeholder_ai_response_header'] = 'napr.: Odpoveď AI';

$_['entry_dispatcher_response_header'] = 'Hlavička odpovede operátora';
$_['help_dispatcher_response_header']  = 'Zadajte text hlavičky, ktorý sa zobrazí, keď v chate odpovedá operátor (človek).';
$_['placeholder_dispatcher_response_header'] = 'napr.: Odpoveď operátora';

$_['entry_ai_response_indicator'] = 'Indikátor odpovede AI';
$_['help_ai_response_indicator']  = 'Text, ktorý sa zobrazí vedľa ikony chatu, keď AI práve píše odpoveď.';
$_['placeholder_ai_response_indicator'] = 'napr.: AI práve píše...';

$_['entry_dispatcher_response_indicator'] = 'Indikátor odpovede operátora';
$_['help_dispatcher_response_indicator']  = 'Text, ktorý sa zobrazí vedľa ikony chatu, keď operátor práve píše odpoveď.';
$_['placeholder_dispatcher_response_indicator'] = 'napr.: Operátor práve píše...';

$_['entry_welcome_message'] = 'Uvítacia správa';
$_['help_welcome_message']  = 'Zadajte uvítaciu správu, ktorá sa zobrazí pri otvorení chatu.';
$_['placeholder_welcome_message'] = 'napr.: Dobrý deň! Ako vám môžem dnes pomôcť?';

$_['help_packages'] = '
<b>Základný balík (Zdarma)</b><br>
Popis: Aktuálna funkčnosť (základná verzia AI chatu, obmedzená pamäť kontextu).<br><br>
<b>Pamäť 3 chaty (5 000 HUF/rok)</b><br>
AI si pamätá posledné 3 konverzácie pre lepšie odpovede.<br><br>
<b>Pamäť 6 chatov (7 000 HUF/rok)</b><br>
AI si pamätá posledných 6 konverzácií pre maximálnu personalizáciu.<br><br>
<b>Režim operátora (15 000 HUF/rok)</b><br>
Umožňuje majiteľovi e-shopu vstúpiť do živého chatu a spravovať konverzácie cez admin rozhranie.<br><br>
';

// Tooltips
$_['help_api_key']      = 'Zadajte svoj ChatGPT API kľúč získaný od OpenAI. Je potrebný pre komunikáciu.';
$_['help_model']        = 'Vyberte model ChatGPT, ktorý chcete použiť. Rôzne modely sa líšia cenou a schopnosťami. Vo väčšine prípadov je gpt-4o-mini najlepším pomerom ceny a výkonu.';
$_['help_temperature']  = "Ovláda kreativitu odpovedí:\n0.0 – Plne predvídateľné a presné.\n0.7 (predvolené) – Vyvážená kreativita a presnosť.\n1.0+ – Kreatívnejšie odpovede, môžu byť menej presné.\n2.0 – Maximálna kreativita, veľmi rozmanité odpovede.";
$_['help_prompt']       = 'Zadajte predvolený pokyn pre AI. Príklad: "Ste užitočný asistent v obchode, ktorý odpovedá na otázky zákazníkov o produktoch."';
$_['help_placeholder']  = 'Zadajte text, ktorý chcete poslať AI.';
$_['help_color']        = 'Vyberte primárnu farbu pre okno chatu (hlavička, tlačidlá atď.).';
$_['help_history_limit'] = 'Koľko predchádzajúcich párov otázka-odpoveď sa má poslať do ChatGPT (0–5). Vyššie hodnoty poskytujú viac kontextu, ale spotrebúvajú viac tokenov.';
$_['help_select_pack']  = 'Vyberte si úroveň služieb.';
$_['help_vrcs_source']  = 'Používa API kľúč poskytovaný VRCS.HU, platba prebieha cez nás. Nevyžaduje sa registrácia v OpenAI.';
$_['help_chataiwd_source'] = 'Musíte zadať vlastný kľúč ChatGPT API, ktorý získate priamo od OpenAI.';

$_['help_kredit_vrcs'] = '<br><b>Hybridná infraštruktúra ChatAWD a kredity za používanie</b><br><br>
ChatAWD je prémiový, vysoko výkonný hybridný motor na predaj a zákaznícku podporu. Systém kombinuje najpokročilejšie jazykové modely OpenAI (GPT-4o), vyladené lokálne vektorové vyhľadávanie a sledovanie preferencií užívateľov s inteligentným dispečerským panelom (obsahujúcim rekonštrukciu nákupního košíka v reálnom čase, nákupné upozornenia, kupóny, cielené sluby vernostných bodov a koleso šťastia).<br><br>
Udržiavanie tejto zložitej cloudovej infraštruktúry – vrátane výpočtových kapacít AI, zabezpečených trás API a synchronizácie databáz medzi obchodmi v reálnom čase – funguje na kreditnom systéme založenom na využití.<br><br>
Namiesto drahých a nepružných paušálnych mesačných predplatných uplatňuje ChatAWD úplne transparentné zúčtovanie založené na využití (pay-as-you-go). Platíte iba za transakcie AI a dispečera skutočne vygenerované vaším obchodem.<br><br>
<b>Aby ste si mohli úplne bez rizika vyskúšať účinok modulu na zvýšenie konverzie, poskytujeme pri registrácii štartovací kredit v hodnote 2 USD.</b><br><br>';

// Model descriptions
$_['gpt-4.1-nano']      = ' – Malý, rýchla odozva, nízke náklady – skvelý na jednoduché úlohy.';
$_['gpt-4']             = ' – Výkonnejší, ale môže byť pomalší a drahší.';
$_['gpt-4.1-mini']      = ' – Vyvážená voľba: rýchlosť a kvalita.';
$_['gpt-4-turbo']       = ' – Optimálna voľba: rýchly, lacnejší a výkonný.';
$_['gpt-3.5-turbo']     = ' – Ešte lacnejší, menej presný, ale postačujúci pre mnohé úlohy.';
$_['gpt-3.5-turbo-16k'] = ' – Väčšie kontextové okno – vhodné pre dlhšie konverzácie.';
$_['gpt-4O']            = ' – Najnovší model v reálnom čase, veľmi rýchly a inteligentný.';
$_['gpt-4O-mini']       = ' – Pre nenáročné úlohy a rýchle odpovede – napr. automatické odpovede, vyhľadávanie.';
$_['gpt-4.1']           = ' – Nová generácia GPT modelu (verzia preview).';

$_['entry_duration']    = 'Obdobie predplatného:';
$_['text_duration_3']   = '3 mesiace';
$_['text_duration_6']   = '6 mesiacov';
$_['text_duration_9']   = '9 mesiacov';
$_['text_duration_12']  = '12 mesiacov';
$_['text_duration_cancel'] = 'Zrušiť';

$_['help_recovery_info'] = 'Nastavte maximálne 5 pripomienkových e-mailov. Prázdne riadky nebudú odoslané.';
$_['entry_recovery_delay'] = 'Oneskorenie';
$_['entry_recovery_subject'] = 'Predmet e-mailu (Prázdne = neodosielať)';
$_['entry_recovery_content'] = 'Text správy';
$_['text_hours'] = 'Hodina';
$_['text_days'] = 'Deň';
$_['text_subject_placeholder'] = 'Napr.: Zabudli ste na niečo?';
$_['text_content_placeholder'] = 'Správa...';

$_['text_syncrestart'] = 'Reštartovať synchronizáciu';
$_['text_syncrestart_click'] = 'Kliknite sem, ak chcete znova synchronizovať obsah databázy s AI.';


// Predvolené hodnoty konštruktora
$_['text_chat_button_fallback']                   = 'Opýtajte sa AI teraz!';
$_['text_ai_response_header_fallback']            = 'Odpoveď AI';
$_['text_dispatcher_response_header_fallback']    = 'Dispečer odpovedá';
$_['text_ai_response_indicator_fallback']         = 'AI práve odpovedá...';
$_['text_dispatcher_response_indicator_fallback'] = 'Dispečer práve odpovedá...';
$_['text_welcome_message_fallback']               = 'Ahoj! Ako vám môžem dnes pomôcť?';

// AJAX / API chybové správy
$_['error_permission']                            = 'Varovanie: Nemáte oprávnenie na úpravu tohto modulu!';
$_['error_already_registered']                    = 'Už registrované';
$_['error_domain_not_found']                      = 'Nepodarilo sa určiť doménu obchodu.';
$_['error_invalid_input']                         = 'Zadajte prosím svoju e-mailovú adresu!';
$_['error_registry']                              = 'Registrácia zlyhala! HTTP kód: %s, Chyba: %s';
$_['error_invalid_server_response']               = 'Neplatná odpoveď od autorizačného servera';
$_['error_no_registration']                       = 'Nenašlo sa platné registračné ID!';
$_['error_server_communication']                  = 'Chyba pri komunikácii so vzdialeným serverom.';
$_['error_invalid_field']                         = 'Neplatný názov poľa!';
$_['error_unknown']                               = 'Neznáma chyba na základe odpovede servera.';

// Úspešná registrácia
$_['text_register_success']                       = 'Registrácia bola úspešná!';

$_['text_be_patient_registration']          = 'Prosím, buďte trpezliví, prebieha registrácia...';
$_['text_failed']                            = 'Chyba';
$_['text_choose_package']                   = 'Prosím, vyberte si balík';
$_['text_restart_sync']                     = 'Určite chcete reštartovať kompletnú synchronizáciu AI? Môže to chvíľu trvať.';
$_['text_intitializing']                     = 'Inicializácia modulu AI...';
$_['error_schema']                     = 'Chyba: Schéma nebola správne prijatá.';
$_['text_ai_learning_help'] = 'Prosím, nezatvárajte okno. <br>AI práve analyzuje a indexuje ponuku e-shopu, aby modul chatu a inteligentný vyhľadávač dispečera mohli poskytovať presné odpovede a odporúčania produktov.';
$_['tab_general'] = 'Všeobecné';
$_['tab_chat_settings'] = 'Nastavenia chatu';
$_['tab_faq'] = 'Často kladené otázky (FAQ)';
$_['tab_tools'] = 'Funkcie';
$_['tab_abandoned'] = 'Opuštený košík';
$_['entry_faq_icon_type'] = 'Typ ikony';
$_['entry_faq_visual_icon'] = 'Ikona';
$_['entry_faq_visual_image'] = 'Obrázok';
$_['entry_faq_question'] = 'Otázka';
$_['entry_faq_answer'] = 'Odpoveď';
$_['help_tools_info'] = 'Tu môžete zapnúť alebo vypnúť doplnkové funkcie, ktoré sa zobrazujú v okne chatu.';
$_['help_tool_voice'] = 'Zobraziť ikonu mikrofónu pre hlasový vstup.';
$_['help_tool_image'] = 'Umožňuje používateľom posielať obrázky dispečerovi AI.';
$_['help_tool_email'] = 'Zobraziť ikonu e-mailu pre priamy kontakt.';
$_['help_tool_faq'] = 'Úplné vypnutie alebo zapnutie zobrazenia FAQ.';
$_['help_tool_whatsapp'] = 'Zobraziť tlačidlo pre priamu správu na WhatsApp.';
$_['help_whatsapp_number'] = 'Zadajte telefónne číslo v medzinárodnom formáte.';
$_['text_time'] = 'Čas...';
$_['text_subject'] = 'Napr.: Zabudli ste na niečo?';
$_['error_initializing_ai'] = 'Chyba pri inicializácii AI';
$_['text_ai_prepared'] = 'AI úspešne pripravená! Spúšťam systém...';
$_['text_ai_learning'] = 'Proces učenia AI:';
$_['error_ai_arming'] = 'Pri aktivácii AI došlo k chybe:';
$_['error_network'] = 'Chyba siete počas vypínania. Opakujem o 5 sekúnd...';
$_['error_closing'] = 'Chyba počas ukončovania:';
$_['text_optimizing'] = 'Optimalizácia a tréning modelov AI... Prosím čakajte.';
$_['text_empty'] = 'Prázdne';
$_['text_error'] = 'Chyba';
$_['text_unknown_error'] = 'Neznáma chyba';
$_['error_save'] = 'Chyba pri ukladaní:';
$_['text_registration'] = 'Registrácia';
$_['entry_tool_bell']                             = 'Volať dispečera (Zvonček)';
$_['help_tool_bell']                              = 'Umožňuje používateľom upozorniť dispečera.';
$_['entry_tool_voice']                            = 'Hlasové rozpoznávanie';
$_['entry_tool_image']                            = 'Nahratie obrázka';
$_['entry_tool_emoji']                            = 'Výber emotikonov';
$_['entry_tool_email']                            = 'Kontaktný formulár e-mailu';
$_['entry_tool_faq']                              = 'Modul FAQ';
$_['entry_tool_whatsapp']                         = 'Kontakt na WhatsApp';
$_['entry_whatsapp_placeholder']                  = 'napr.: +421 900 123 456';
$_['button_save']                                 = 'Uložiť';
$_['button_back']                                 = 'Späť';
$_['button_add']                                  = 'Pridať';
$_['button_remove']                               = 'Odstrániť';
$_['text_enabled']                                = 'Povolené';
$_['text_disabled']                               = 'Zakázané';