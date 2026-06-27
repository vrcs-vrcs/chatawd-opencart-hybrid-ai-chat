<?php
// Heading
$_['heading_title']    = '<font color="blue">Setări ChatVRCS</font>';
$_['heading_title2']   = 'Setări ChatVRCS';

$_['text_extension']   = 'Extensii';
$_['text_edit']        = 'Editare setări';
$_['entry_status']     = 'Status';

$_['entry_api_key']      = 'Cheie API OpenAI';
$_['entry_model']        = 'Model (ex: gpt-3.5-turbo)';
$_['entry_temperature']  = 'Creativitate (0.0 – 2.0)';
$_['entry_prompt']       = 'Instrucțiune implicită';
$_['entry_color']        = 'Culoare Chat';
$_['entry_history_limit'] = 'Limită istoric mesaje';
$_['text_prompt_default'] = 'Răspunde ca și cum ai fi un asistent de magazin.';
$_['entry_get_api_key']   = 'Procesul de solicitare a cheii API CHATGPT:';
$_['entry_select_pack']   = 'Alege un pachet';
$_['entry_select_pack_help'] = 'Detalii pachet';
$_['entry_reward_presets'] = 'Setări puncte de recompensă';
$_['text_reward_help']     = 'Introduceți valorile punctelor selectabile separate prin virgule (ex: 5,10,25,50). Dacă lăsați gol, funcția de puncte de recompensă nu va apărea în chat.';

$_['text_success']      = 'Setările au fost salvate cu succes!';
$_['error_permission']  = 'Nu aveți permisiunea de a modifica acest modul!';

// Texte înregistrare
$_['entry_registration_id'] = 'ID Înregistrare (vrcs.hu)';
$_['help_registration_id']  = 'Acest ID este furnizat de vrcs.hu și servește la identificarea magazinului. Dacă nu aveți încă un ID, faceți clic pe butonul Înregistrare.';
$_['button_register']       = 'Înregistrare';
$_['text_error_server']     = 'A apărut o eroare în timpul comunicării cu serverul. Vă rugăm să încercați din nou mai târziu.';
$_['error_registry']        = 'Nu s-a putut efectua înregistrarea pe vrcs.hu. Cod HTTP: %s Eroare: %s';

$_['text_free_pack']        = 'Pachet de bază (Gratuit)';
$_['text_free_pack_descr']  = 'Descriere: Funcționalitate actuală (versiune de bază AI chat, memorie de context pentru o singură interacțiune).';

$_['text_basic_pack']       = 'Memorare ultimele 3 mesaje (5.000 Ft/an)';
$_['text_basic_pack_descr'] = 'AI-ul reține ultimele 3 mesaje, oferind răspunsuri mai personalizate.';

$_['text_standard_pack']    = 'Memorare ultimele 6 mesaje (7.000 Ft/an)';
$_['text_standard_pack_descr'] = 'AI-ul reține ultimele 6 mesaje, oferind răspunsuri mai personalizate.';

$_['text_premium_pack']       = 'Preluare și returnare operator (15.000 Ft/an)';
$_['text_premium_pack_descr']  = 'Proprietarul magazinului poate prelua conversația de la AI, poate răspunde manual, apoi o poate returna AI-ului. Include acces la panoul de administrare.';
$_['text_vrcs_key']           = 'Folosesc cheia de confort VRCS';
$_['text_chataiwd_key']        = 'Înregistrez propria cheie API CHATGPT';
$_['text_chataiwd_kredit']     = 'Cumpărare credite utilizare API ChatGPT';
$_['text_kredit_egyenleg']    = 'Soldul meu de credite:';
$_['text_billing']            = 'Următoarea facturare când soldul scade sub 1 USD. (10 USD)';

$_['text_confirm_package_switch'] = 'Sunteți sigur că doriți să treceți la următorul pachet: %s?';
$_['entry_dispatcher']            = 'Interfață Dispecer:';
$_['help_dispatcher']             = 'Interfață Dispecer:';
$_['button_dispatcher']           = 'Interfață Dispecer';
$_['button_fizetés']              = 'Plată';
$_['button_fizetes']              = 'Plată';

// Câmpuri noi pentru setările adăugate
$_['entry_chat_button']           = 'Text buton chat';
$_['help_chat_button']            = 'Introduceți textul care va apărea pe butonul de chat pentru a încuraja utilizatorii să comunice.';
$_['placeholder_chat_button']     = 'ex: ChatGPT Întreabă acum!';

$_['entry_ai_response_header']    = 'Antet răspuns AI';
$_['help_ai_response_header']     = 'Introduceți textul antetului care apare când AI-ul răspunde în chat.';
$_['placeholder_ai_response_header'] = 'ex: Răspuns AI';

$_['entry_dispatcher_response_header'] = 'Antet răspuns Dispecer';
$_['help_dispatcher_response_header']  = 'Introduceți textul antetului care apare când dispecerul răspunde în chat.';
$_['placeholder_dispatcher_response_header'] = 'ex: Răspuns Dispecer';

$_['entry_ai_response_indicator']      = 'Indicator răspuns AI';
$_['help_ai_response_indicator']       = 'Introduceți textul care apare lângă pictograma de chat în timp ce AI-ul răspunde.';
$_['placeholder_ai_response_indicator'] = 'ex: AI răspunde...';

$_['entry_dispatcher_response_indicator']      = 'Indicator răspuns Dispecer';
$_['help_dispatcher_response_indicator']       = 'Introduceți textul care apare lângă pictograma de chat în timp ce dispecerul răspunde.';
$_['placeholder_dispatcher_response_indicator'] = 'ex: Dispecerul răspunde...';

$_['entry_welcome_message']       = 'Mesaj de bun venit';
$_['help_welcome_message']        = 'Introduceți mesajul de bun venit care apare la pornirea chat-ului.';
$_['placeholder_welcome_message'] = 'ex: Bună ziua! Cu ce vă pot ajuta astăzi?';

$_['help_packages'] = '
<b>Pachet de bază (Gratuit)</b><br>
Descriere: Funcționalitate actuală (versiune de bază AI chat, memorie de context limitată).<br><br>

<b>Memorare ultimele 3 mesaje (5.000 Ft/an)</b><br>
AI-ul reține ultimele 3 mesaje pentru răspunsuri mai personalizate.<br><br>

<b>Memorare ultimele 6 mesaje (7.000 Ft/an)</b><br>
AI-ul reține ultimele 6 mesaje pentru o mai bună continuitate a discuției.<br><br>

<b>Preluare și returnare operator (15.000 Ft/an)</b><br>
Proprietarul magazinului poate prelua manual conversația. Include acces la interfața de admin.<br><br>
';

// Tooltips
$_['help_api_key']      = 'Introduceți cheia API ChatGPT primită de la OpenAI. Este necesară pentru comunicare.';
$_['help_model']        = 'Alegeți modelul ChatGPT dorit. Modelele diferă prin preț și capabilități. gpt-3.5-turbo oferă de obicei un echilibru bun.';
$_['help_temperature']  = "Controlează creativitatea răspunsurilor:\n0.0 – Complet previzibil, precis.\n0.7 (implicit) – Echilibru între creativitate și precizie.\n1.0+ – Răspunsuri mai variate, dar pot fi mai puțin precise.\n2.0 – Creativitate maximă.";
$_['help_prompt']       = 'Introduceți o instrucțiune de bază care ghidează răspunsurile AI. Ex: „Ești un vânzător amabil care răspunde la întrebările clienților.”';
$_['help_placeholder']  = 'Scrieți ce doriți să trimiteți către AI. Ex: Răspunde ca un asistent de vânzări.';
$_['help_color']        = 'Alegeți culoarea principală a ferestrei de chat (antet, butoane etc.).';
$_['help_history_limit'] = 'Câte perechi de întrebări-răspunsuri anterioare să fie trimise către ChatGPT (0–5). 0 = fără istoric.';
$_['help_select_pack']   = 'Selectați pachetul dorit.';
$_['help_vrcs_source']   = 'Folosește cheia API furnizată de VRCS.HU, plata se face la noi. Nu necesită înregistrare la ChatGPT.';
$_['help_chataiwd_source'] = 'Trebuie să introduceți propria cheie API obținută de la OpenAI.';
$_['help_kredit_vrcs'] = '<br><b>Infrastructura hibridă ChatAWD și creditele de utilizare</b><br><br>
ChatAWD este un motor hibrid de vânzări și asistență clienți premium, de înaltă performanță. Sistemul îmbină cele mai avansate modele lingvistice de la OpenAI (GPT-4o), căutarea vectorială locală optimizată și urmărirea preferințelor utilizatorilor cu un panou de control inteligent pentru dispeceri (cu reconstrucție în timp real a coșului, alerte de achiziție, cupoane, promisiuni țintite de puncte de loialitate și roata norocului).<br><br>
Menținerea acestei infrastructuri cloud complexe – inclusiv a capacităților de calcul AI, a rutelor API securizate și a sincronizării bazei de date în timp real între magazine – funcționează pe baza unui sistem de credite bazat pe utilizare.<br><br>
În locul abonamentelor lunare fixe, scumpe și rigide, ChatAWD aplică o facturare complet transparentă, bazată pe utilizare (pay-as-you-go). Plătiți doar pentru tranzacțiile AI și de dispecerat generate efectiv de magazinul dumneavoastră.<br><br>
<b>Pentru a vă asigura că puteți experimenta efectul de sporire a conversiei al modulului complet fără riscuri, vă oferim un credit de pornire în valoare de 2 USD la înregistrare.</b><br><br>';

$_['help_get_api_key'] = '🔑 Cum puteți obține o cheie API ChatGPT?<br>Accesați următorul site web:<br><a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a><br><br>Autentificați-vă sau creați un cont la OpenAI (puteți folosi și contul Google).<br><br>După autentificare, faceți clic pe butonul „Create new secret key”.<br><br>Copiați cheia API generată și introduceți-o în acest modul.<br><br>Important: Cheia API completă poate fi văzută o singură dată!<br><br>Dacă o pierdeți, generați una nouă pe aceeași pagină.<br><br>
💰 Verificare utilizare API și sold:<br><br>
<a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank">Prezentare facturare</a><br><br>
<a href="https://platform.openai.com/usage" target="_blank">Statistici utilizare</a><br><br>
';

// Descrieri modele
$_['gpt-4.1-nano']      = ' – Mic, timp de răspuns rapid, ieftin – excelent pentru sarcini simple.';
$_['gpt-4']             = ' – Mai puternic, dar poate fi mai lent și mai scump.';
$_['gpt-4.1-mini']      = ' – Echilibrat: viteză și calitate.';
$_['gpt-4-turbo']       = ' – Alegerea optimă: rapid, mai ieftin și puternic.';
$_['gpt-3.5-turbo']     = ' – Și mai ieftin, mai puțin precis, dar suficient pentru multe sarcini.';
$_['gpt-3.5-turbo-16k'] = ' – Fereastră de context mai lungă – pentru conversații prelungite.';
$_['gpt-4O']            = ' – Cel mai nou model în timp real, foarte rapid și puternic.';
$_['gpt-4O-mini']       = ' – Pentru sarcini ușoare și răspunsuri rapide – ex: răspunsuri automate.';
$_['gpt-4.1']           = ' – Model GPT de generație următoare (în funcție de versiunea API).';

// Durată abonament
$_['entry_duration']       = 'Durată abonament:';
$_['text_duration_3']      = '3 luni';
$_['text_duration_6']      = '6 luni';
$_['text_duration_9']      = '9 luni';
$_['text_duration_12']     = '12 luni';
$_['text_duration_cancel'] = 'Dezabonare';

$_['help_recovery_info'] = 'Setați maximum 5 e-mailuri de reamintire. Rândurile goale nu vor fi trimise.';
$_['entry_recovery_delay'] = 'Întârziere';
$_['entry_recovery_subject'] = 'Subiect e-mail (Gol = nu trimite)';
$_['entry_recovery_content'] = 'Conținut mesaj';
$_['text_hours'] = 'Oră';
$_['text_days'] = 'Zi';
$_['text_subject_placeholder'] = 'Ex: Ați uitat ceva?';
$_['text_content_placeholder'] = 'Mesaj...';

$_['text_syncrestart'] = 'Restartare sincronizare';
$_['text_syncrestart_click'] = 'Click aici dacă doriți să resincronizați conținutul bazei de date cu AI.';


// Valori implicite constructor
$_['text_chat_button_fallback']                   = 'Întreabă AI acum!';
$_['text_ai_response_header_fallback']            = 'Răspuns AI';
$_['text_dispatcher_response_header_fallback']    = 'Dispecerul răspunde';
$_['text_ai_response_indicator_fallback']         = 'AI răspunde în acest moment...';
$_['text_dispatcher_response_indicator_fallback'] = 'Dispecerul răspunde în acest moment...';
$_['text_welcome_message_fallback']               = 'Bună! Cu ce te pot ajuta astăzi?';

// Mesaje de eroare AJAX / API
$_['error_permission']                            = 'Avertisment: Nu aveți permisiunea de a modifica acest modul!';
$_['error_already_registered']                    = 'Deja înregistrat';
$_['error_domain_not_found']                      = 'Nu s-a putut determina domeniul magazinului.';
$_['error_invalid_input']                         = 'Vă rugăm să introduceți adresa de e-mail!';
$_['error_registry']                              = 'Înregistrarea a eșuat! Cod HTTP: %s, Eroare: %s';
$_['error_invalid_server_response']               = 'Răspuns nevalid de la serverul de autorizare';
$_['error_no_registration']                       = 'Nu a fost găsit un ID de înregistrare valid!';
$_['error_server_communication']                  = 'Eroare în timpul comunicării cu serverul la distanță.';
$_['error_invalid_field']                         = 'Numele câmpului este nevalid!';
$_['error_unknown']                               = 'Eroare necunoscută bazată pe răspunsul serverului.';

// Înregistrare reușită
$_['text_register_success']                       = 'Înregistrare reușită!';

$_['text_be_patient_registration']          = 'Vă rugăm să aveți răbdare, înregistrarea este în curs...';
$_['text_failed']                            = 'Eroare';
$_['text_choose_package']                   = 'Vă rugăm să alegeți un pachet';
$_['text_restart_sync']                     = 'Sigur doriți să reporniți sincronizarea completă AI? Acest lucru poate dura puțin.';
$_['text_intitializing']                     = 'Inițializarea modulului AI...';
$_['error_schema']                     = 'Eroare: Schema nu a fost primită corect.';
$_['text_ai_learning_help'] = 'Vă rugăm să nu închideți fereastra. <br>AI analizează și indexează oferta magazinului online, astfel încât modulul de chat și motorul de căutare al dispecerului inteligent să poată oferi răspunsuri precise și recomandări de produse.';
$_['tab_general'] = 'General';
$_['tab_chat_settings'] = 'Setări Chat';
$_['tab_faq'] = 'FAQ';
$_['tab_tools'] = 'Funcții';
$_['tab_abandoned'] = 'Coș abandonat';
$_['entry_faq_icon_type'] = 'Tip pictogramă';
$_['entry_faq_visual_icon'] = 'Pictogramă';
$_['entry_faq_visual_image'] = 'Imagine';
$_['entry_faq_question'] = 'Întrebare';
$_['entry_faq_answer'] = 'Răspuns';
$_['help_tools_info'] = 'Aici puteți activa sau dezactiva funcțiile suplimentare care apar în fereastra de chat.';
$_['help_tool_voice'] = 'Afișează pictograma microfonului pentru introducerea vocală.';
$_['help_tool_image'] = 'Permite utilizatorilor să trimită imagini către dispecerul AI.';
$_['help_tool_email'] = 'Afișează pictograma de mail pentru contact direct.';
$_['help_tool_faq'] = 'Activarea sau dezactivarea completă a afișării FAQ.';
$_['help_tool_whatsapp'] = 'Afișează butonul pentru mesaje directe pe WhatsApp.';
$_['help_whatsapp_number'] = 'Introduceți numărul de telefon în format internațional.';
$_['text_time'] = 'Timp...';
$_['text_subject'] = 'De ex.: Ai uitat ceva?';
$_['error_initializing_ai'] = 'Eroare la inițializarea AI';
$_['text_ai_prepared'] = 'AI pregătit cu succes! Se pornește sistemul...';
$_['text_ai_learning'] = 'Procesul de învățare AI:';
$_['error_ai_arming'] = 'A apărut o eroare la activarea AI:';
$_['error_network'] = 'Eroare de rețea la închidere. Se reîncearcă în 5 secunde...';
$_['error_closing'] = 'Eroare la închidere:';
$_['text_optimizing'] = 'Se optimizează și se antrenează modelele AI... Vă rugăm așteptați.';
$_['text_empty'] = 'Gol';
$_['text_error'] = 'Eroare';
$_['text_unknown_error'] = 'Eroare necunoscută';
$_['error_save'] = 'Eroare la salvare:';
$_['text_registration'] = 'Înregistrare';
$_['entry_tool_bell']                             = 'Apel dispecer (Clopoțel)';
$_['help_tool_bell']                              = 'Permite utilizatorilor să notifice dispecerul.';
$_['entry_tool_voice']                            = 'Recunoaștere vocală';
$_['entry_tool_image']                            = 'Încărcare imagine';
$_['entry_tool_emoji']                            = 'Selector Emoji';
$_['entry_tool_email']                            = 'Formular de contact e-mail';
$_['entry_tool_faq']                              = 'Modul FAQ';
$_['entry_tool_whatsapp']                         = 'Contact WhatsApp';
$_['entry_whatsapp_placeholder']                  = 'de ex.: +40 712 345 678';
$_['button_save']                                 = 'Salvare';
$_['button_back']                                 = 'Înapoi';
$_['button_add']                                  = 'Adăugare';
$_['button_remove']                               = 'Eliminare';
$_['text_enabled']                                = 'Activat';
$_['text_disabled']                               = 'Dezactivat';