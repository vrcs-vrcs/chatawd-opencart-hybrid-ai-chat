<?php
$_['heading_title'] = '<font color="blue">Ustawienia ChatVRCS</font>';
$_['heading_title2'] = 'Ustawienia ChatVRCS';

$_['text_extension'] = 'Rozszerzenia';
$_['text_edit'] = 'Edytuj ustawienia';
$_['entry_status'] = 'Status';

$_['entry_api_key'] = 'Klucz API OpenAI';
$_['entry_model'] = 'Model (np. gpt-3.5-turbo)';
$_['entry_temperature'] = 'Losowość (0.0 – 2.0)';
$_['entry_prompt'] = 'Domyślny prompt';
$_['entry_color'] = 'Kolor czatu';
$_['entry_history_limit'] = 'Limit historii';
$_['text_prompt_default'] = 'Odpowiadaj tak, jakbyś był asystentem sklepu.';
$_['entry_get_api_key']     = 'Proces uzyskania klucza API CHATGPT:';
$_['entry_select_pack']     = 'Wybierz pakiet';
$_['entry_select_pack_help']     = 'Szczegóły pakietu';
$_['entry_reward_presets'] = 'Ustawienia punktów lojalnościowych';
$_['text_reward_help']     = 'Wprowadź wybieralne wartości punktów oddzielone przecinkami (np. 5,10,25,50). Jeśli pozostawisz to pole puste, funkcja punktów lojalnościowych nie pojawi się w czacie.';

$_['text_success'] = 'Ustawienia zapisane pomyślnie!';
$_['error_permission'] = 'Nie masz uprawnień do modyfikowania tego modułu!';

// New texts for registration
$_['entry_registration_id'] = 'ID rejestracji (vrcs.hu)';
$_['help_registration_id'] = 'Ten identyfikator jest dostarczany przez vrcs.hu i służy do identyfikacji Twojego sklepu. Jeśli jeszcze go nie masz, kliknij przycisk Rejestracja.';
$_['button_register'] = 'Rejestracja';
$_['text_error_server'] = 'Wystąpił błąd podczas komunikacji z serwerem. Spróbuj ponownie później.';
$_['error_registry'] = 'Nie udało się zarejestrować w vrcs.hu. Kod HTTP: %s Błąd: %s';

$_['text_free_pack'] = 'Pakiet podstawowy (Darmowy)';
$_['text_free_pack_descr'] = 'Opis: Aktualna funkcjonalność (podstawowa wersja czatu AI, jednorazowa pamięć kontekstu podczas rozmowy).';

$_['text_basic_pack'] = 'Zapamiętuje ostatnie 3 czaty (5 000 HUF/rok)';
$_['text_basic_pack_descr'] = 'AI zapamiętuje ostatnie 3 czaty, umożliwiając bardziej spersonalizowane odpowiedzi.';

$_['text_standard_pack'] = 'Zapamiętuje ostatnie 6 czatów (7 000 HUF/rok)';
$_['text_standard_pack_descr'] = 'AI zapamiętuje ostatnie 6 czatów, umożliwiając bardziej spersonalizowane odpowiedzi.';

$_['text_premium_pack'] = 'Przejęcie czatu przez operatora i zwrot (15 000 HUF/rok)';
$_['text_premium_pack_descr'] = 'Właściciel sklepu może przejąć czat od AI, odpowiadać ręcznie, a następnie oddać go z powrotem, gdy jest niedostępny. Zawiera dostęp do interfejsu administracyjnego.';

$_['text_vrcs_key'] = 'Użyj klucza wygody VRCS';
$_['text_chataiwd_key'] = 'Zarejestruj własny klucz API CHATGPT';
$_['text_chataiwd_kredit'] = 'Kup kredyty użycia API ChatGPT';
$_['text_kredit_egyenleg'] = 'Moje saldo kredytów:';
$_['text_billing'] = 'Następne rozliczenie, gdy saldo spadnie poniżej 1 USD. (10 USD) ';

$_['text_confirm_package_switch'] = 'Czy na pewno chcesz przełączyć się na pakiet %s?';
$_['entry_dispatcher'] = 'Interfejs dyspozytora:';
$_['help_dispatcher'] = 'Interfejs dyspozytora:';
$_['button_dispatcher'] = 'Interfejs dyspozytora';
$_['button_fizetés'] = 'Płatność';
$_['button_fizetes'] = 'Płatność';

// New entries for the added fields
$_['entry_chat_button'] = 'Tekst przycisku czatu';
$_['help_chat_button'] = 'Wprowadź tekst wyświetlany na przycisku czatu, aby zachęcić użytkowników do rozpoczęcia rozmowy.';
$_['placeholder_chat_button'] = 'np.: Zapytaj teraz!';

$_['entry_ai_response_header'] = 'Nagłówek odpowiedzi AI';
$_['help_ai_response_header'] = 'Wprowadź nagłówek wyświetlany, gdy AI odpowiada na czacie.';
$_['placeholder_ai_response_header'] = 'np.: Odpowiedź AI';

$_['entry_dispatcher_response_header'] = 'Nagłówek odpowiedzi dyspozytora';
$_['help_dispatcher_response_header'] = 'Wprowadź nagłówek wyświetlany, gdy dyspozytor odpowiada na czacie.';
$_['placeholder_dispatcher_response_header'] = 'np.: Odpowiedź dyspozytora';

$_['entry_ai_response_indicator'] = 'Wskaźnik odpowiedzi AI';
$_['help_ai_response_indicator'] = 'Wprowadź tekst wyświetlany obok ikony czatu, gdy odpowiada AI.';
$_['placeholder_ai_response_indicator'] = 'np.: AI odpowiada';

$_['entry_dispatcher_response_indicator'] = 'Wskaźnik odpowiedzi dyspozytora';
$_['help_dispatcher_response_indicator'] = 'Wprowadź tekst wyświetlany obok ikony czatu, gdy odpowiada dyspozytor.';
$_['placeholder_dispatcher_response_indicator'] = 'np.: Dyspozytor odpowiada';

$_['entry_welcome_message'] = 'Wiadomość powitalna';
$_['help_welcome_message'] = 'Wprowadź wiadomość powitalną wyświetlaną po rozpoczęciu czatu.';
$_['placeholder_welcome_message'] = 'np.: Witaj! Jak mogę Ci pomóc dzisiaj?';

$_['help_packages'] = '
<b>Pakiet podstawowy (Darmowy)</b><br>
Opis: Aktualna funkcjonalność (podstawowa wersja czatu AI, jednorazowa pamięć kontekstu podczas rozmowy).<br><br>

<b>Zapamiętuje ostatnie 3 czaty (5 000 HUF/rok)</b><br>
AI zapamiętuje ostatnie 3 czaty, umożliwiając bardziej spersonalizowane odpowiedzi.<br><br>

<b>Zapamiętuje ostatnie 6 czatów (7 000 HUF/rok)</b><br>
AI zapamiętuje ostatnie 6 czatów, umożliwiając bardziej spersonalizowane odpowiedzi.<br><br>

<b>Przejęcie czatu przez operatora i zwrot (15 000 HUF/rok)</b><br>
Właściciel sklepu może przejąć czat od AI, odpowiadać ręcznie, a następnie oddać go z powrotem, gdy jest niedostępny. Zawiera dostęp do interfejsu administracyjnego.<br><br>
';

// Tooltips
$_['help_api_key'] = 'Wprowadź klucz API ChatGPT uzyskany z OpenAI. Jest on wymagany do komunikacji.';
$_['help_model'] = 'Wybierz model ChatGPT, którego chcesz używać. Różne modele różnią się ceną i możliwościami. W większości przypadków gpt-3.5-turbo to dobry balans między kosztem a wydajnością.';
$_['help_temperature'] = "Kontroluje kreatywność odpowiedzi:\n0.0 – W pełni przewidywalne i dokładne, bez kreatywności.\n0.7 (domyślnie) – Zrównoważona kreatywność i dokładność.\n1.0+ – Bardziej kreatywne i zróżnicowane odpowiedzi, możliwie mniej dokładne.\n2.0 – Maksymalna kreatywność, bardzo zróżnicowane odpowiedzi, potencjalnie zbyt swobodne.";
$_['help_prompt'] = 'Podaj domyślny prompt, aby ukierunkować odpowiedzi AI. Przykład: "Jesteś pomocnym asystentem sklepu odpowiadającym na pytania klientów dotyczące produktów."';
$_['help_placeholder'] = 'Wpisz coś, co chcesz wysłać do AI. Np.: Odpowiadaj jak asystent sklepu.';
$_['help_color'] = 'Wybierz główny kolor okna czatu (nagłówek, przyciski itp.).';
$_['help_history_limit'] = 'Ile poprzednich par pytanie-odpowiedź wysłać do ChatGPT (0–5). 0 oznacza brak historii. Wyższe wartości zapewniają więcej kontekstu, ale zużywają więcej tokenów.';
$_['help_select_pack'] = 'help_select_pack';
$_['help_vrcs_source'] = 'Używa klucza API dostarczonego przez VRCS.HU, płatność jest obsługiwana przez nas. Nie jest wymagana rejestracja ChatGPT.';
$_['help_chataiwd_source'] = 'Musisz podać własny klucz API ChatGPT, który można uzyskać z OpenAI.';
$_['help_kredit_vrcs'] = '<br><b>Hybrydowa infrastruktura ChatAWD i kredyty na użytkowanie</b><br><br>
ChatAWD to najwyższej klasy, wydajny, hybrydowy silnik wsparcia sprzedaży i obsługi klienta. System łączy najnowocześniejsze modele językowe OpenAI (GPT-4o), precyzyjnie dostrojone lokalne wyszukiwanie wektorowe oraz śledzenie preferencji użytkowników z inteligentnym panelem dyspozytorskim (wyposażonym w rekonstrukcję koszyka w czasie rzeczywistym, alerty zakupowe, kupony, celowane obietnice punktów lojalnościowych i koło fortuny).<br><br>
Utrzymanie tej złożonej infrastruktury chmurowej – w tym mocy obliczeniowych AI, bezpiecznych tras API oraz synchronizacji baz danych w czasie rzeczywistym między sklepami – działa w oparciu o system kredytowy zależny od użycia.<br><br>
Zamiast drogich i sztywnych abonamentów miesięcznych, ChatAWD stosuje w pełni przejrzyste rozliczenia oparte na zużyciu (pay-as-you-go). Płacisz wyłącznie za transakcje AI i dyspozytorskie faktycznie wygenerowane przez Twój sklep.<br><br>
<b>Aby umożliwić Ci całkowicie bezryzykowne przetestowanie wpływu modułu na wzrost konwersji, przy rejestracji przyznajemy kredyt startowy o wartości 2 USD.</b><br><br>';

$_['help_get_api_key'] = '🔑 Jak uzyskać klucz API ChatGPT?<br>Otwórz następującą stronę:<br><a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a><br><br>Zaloguj się lub zarejestruj konto na platformie OpenAI (możesz również użyć konta Google).<br><br>Po zalogowaniu kliknij przycisk „Create new secret key”.<br><br>Skopiuj wygenerowany klucz API i wklej go do tego modułu.<br><br>Ważne: Pełny klucz API możesz zobaczyć tylko raz!<br><br>Jeśli go zgubisz, wygeneruj nowy na tej samej stronie.
<br><br>
💰 Sprawdź saldo użycia API tutaj:<br><br>
<a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank">Billing Overview</a><br><br>
<a href="https://platform.openai.com/usage" target="_blank">Statystyki użycia</a><br><br>
';

// Model descriptions
$_['gpt-4.1-nano'] = ' – Mały, szybki czas odpowiedzi, niski koszt – idealny do prostych zadań.';
$_['gpt-4'] = ' – Bardziej zaawansowany, ale może być wolniejszy i droższy.';
$_['gpt-4.1-mini'] = ' – Zrównoważony wybór: szybkość i jakość.';
$_['gpt-4-turbo'] = ' – Optymalny wybór: szybki, tańszy i wydajny. Zazwyczaj najlepszy stosunek jakości do ceny.';
$_['gpt-3.5-turbo'] = ' – Jeszcze tańszy, mniej dokładny, ale wystarczający do wielu zadań.';
$_['gpt-3.5-turbo-16k'] = ' – Większe okno kontekstu – odpowiedni do dłuższych rozmów lub odniesień.';
$_['gpt-4O'] = ' – Najnowszy model czasu rzeczywistego, bardzo szybki i wydajny. Użyj, jeśli dostępny.';
$_['gpt-4O-mini'] = ' – Do lekkich zadań i szybkich odpowiedzi – np. autorespondery, wyszukiwania.';
$_['gpt-4.1'] = ' – Model GPT nowej generacji, może być wersją podglądową lub specjalną (w zależności od wersji API).';

$_['entry_duration'] = 'Okres subskrypcji:';
$_['text_duration_3'] = '3 miesiące';
$_['text_duration_6'] = '6 miesięcy';
$_['text_duration_9'] = '9 miesięcy';
$_['text_duration_12'] = '12 miesięcy';
$_['text_duration_cancel'] = 'Anuluj';

$_['help_recovery_info'] = 'Skonfiguruj maksymalnie 5 e-maili przypominających. Puste wiersze nie zostaną wysłane.';
$_['entry_recovery_delay'] = 'Opóźnienie';
$_['entry_recovery_subject'] = 'Temat e-maila (Puste = nie wysyłaj)';
$_['entry_recovery_content'] = 'Treść wiadomości';
$_['text_hours'] = 'Godzina';
$_['text_days'] = 'Dzień';
$_['text_subject_placeholder'] = 'Np.: Czy o czymś zapomniałeś?';
$_['text_content_placeholder'] = 'Wiadomość...';

$_['text_ai_syncr'] = 'Synchronizacja AI';
$_['text_syncrestart'] = 'Restart synchronizacji';
$_['text_syncrestart_click'] = 'Kliknij tutaj, jeśli chcesz ponownie zsynchronizować zawartość bazy danych z AI.';


// Wartości domyślne konstruktora
$_['text_chat_button_fallback']                   = 'Zapytaj AI teraz!';
$_['text_ai_response_header_fallback']            = 'Odpowiedź AI';
$_['text_dispatcher_response_header_fallback']    = 'Dyspozytor odpowiada';
$_['text_ai_response_indicator_fallback']         = 'AI odpowiada...';
$_['text_dispatcher_response_indicator_fallback'] = 'Dyspozytor odpowiada...';
$_['text_welcome_message_fallback']               = 'Cześć! W czym mogę Ci dzisiaj pomóc?';

// Komunikaty o błędach AJAX / API
$_['error_permission']                            = 'Ostrzeżenie: Nie masz uprawnień do modyfikowania tego modułu!';
$_['error_already_registered']                    = 'Już zarejestrowany';
$_['error_domain_not_found']                      = 'Nie można ustalić domeny sklepu.';
$_['error_invalid_input']                         = 'Proszę podać swój adres e-mail!';
$_['error_registry']                              = 'Rejestracja nie powiodła się! Kod HTTP: %s, Błąd: %s';
$_['error_invalid_server_response']               = 'Nieprawidłowa odpowiedź serwera autoryzacyjnego';
$_['error_no_registration']                       = 'Nie znaleziono prawidłowego identyfikatora rejestracji!';
$_['error_server_communication']                  = 'Błąd podczas komunikacji z serwerem zdalnym.';
$_['error_invalid_field']                         = 'Nieprawidłowa nazwa pola!';
$_['error_unknown']                               = 'Nieznany błąd na podstawie odpowiedzi serwera.';

// Udana rejestracja
$_['text_register_success']                       = 'Rejestracja zakończona sukcesem!';

$_['text_be_patient_registration']          = 'Prosím, buďte trpěliví, registrace probíhá...';
$_['text_failed']                            = 'Chyba';
$_['text_choose_package']                   = 'Prosím, vyberte balíček';
$_['text_restart_sync']                     = 'Opravdu chcete restartovat úplnou synchronizaci AI? To může chvíli trvat.';
$_['text_intitializing']                     = 'Inicializace modulu AI...';
$_['error_schema']                     = 'Chyba: Schéma nebylo správně přijato.';
$_['text_ai_learning_help'] = 'Prosím, nezavírejte okno. <br>AI právě analyzuje a indexuje nabídku e-shopu, aby chatovací modul a inteligentní vyhledávač dispečera mohly poskytovat přesné odpovědi a doporučení produktů.';
$_['tab_general'] = 'Obecné';
$_['tab_chat_settings'] = 'Nastavení chatu';
$_['tab_faq'] = 'Často kladené dotazy (FAQ)';
$_['tab_tools'] = 'Funkce';
$_['tab_abandoned'] = 'Opuštěný košík';
$_['entry_faq_icon_type'] = 'Typ ikony';
$_['entry_faq_visual_icon'] = 'Ikona';
$_['entry_faq_visual_image'] = 'Obrázek';
$_['entry_faq_question'] = 'Otázka';
$_['entry_faq_answer'] = 'Odpověď';
$_['help_tools_info'] = 'Zde můžete zapnout nebo vypnout doplňkové funkce, které se zobrazují v chatovacím okně.';
$_['help_tool_voice'] = 'Zobrazit ikonu mikrofonu pro hlasové zadávání.';
$_['help_tool_image'] = 'Umožňuje uživatelům posílat obrázky AI dispečerovi.';
$_['help_tool_email'] = 'Zobrazit ikonu e-mailu pro přímý kontakt.';
$_['help_tool_faq'] = 'Úplné vypnutí nebo zapnutí zobrazení FAQ.';
$_['help_tool_whatsapp'] = 'Zobrazit tlačítko pro přímou zprávu na WhatsApp.';
$_['help_whatsapp_number'] = 'Zadejte telefonní číslo v mezinárodním formátu.';
$_['text_time'] = 'Čas...';
$_['text_subject'] = 'Např.: Zapomněli jste něco?';
$_['error_initializing_ai'] = 'Chyba při inicializaci AI';
$_['text_ai_prepared'] = 'AI úspěšně připravena! Spouštění systému...';
$_['text_ai_learning'] = 'Proces učení AI:';
$_['error_ai_arming'] = 'Při aktivaci AI došlo k chybě:';
$_['error_network'] = 'Chyba sítě během vypínání. Opakování za 5 sekund...';
$_['error_closing'] = 'Chyba během ukončování:';
$_['text_optimizing'] = 'Optimalizace a trénink modelů AI... Prosím čekejte.';
$_['text_empty'] = 'Prázdné';
$_['text_error'] = 'Chyba';
$_['text_unknown_error'] = 'Neznámá chyba';
$_['error_save'] = 'Chyba při ukládání:';
$_['text_registration'] = 'Registrace';
$_['entry_tool_bell']                             = 'Volat dispečera (Zvonek)';
$_['help_tool_bell']                              = 'Umožňuje uživatelům upozornit dispečera.';
$_['entry_tool_voice']                            = 'Hlasové rozpoznávání';
$_['entry_tool_image']                            = 'Nahrání obrázku';
$_['entry_tool_emoji']                            = 'Výběr emotikonů';
$_['entry_tool_email']                            = 'Kontaktní formulář e-mailu';
$_['entry_tool_faq']                              = 'Modul FAQ';
$_['entry_tool_whatsapp']                         = 'Kontakt na WhatsApp';
$_['entry_whatsapp_placeholder']                  = 'např.: +420 777 123 456';
$_['button_save']                                 = 'Uložit';
$_['button_back']                                 = 'Zpět';
$_['button_add']                                  = 'Přidat';
$_['button_remove']                               = 'Odstranit';
$_['text_enabled']                                = 'Povoleno';
$_['text_disabled']                               = 'Zakázáno';