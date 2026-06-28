<?php
// Heading
$_['heading_title']     = '<font color="blue">ChatVRCS Einstellungen</font>';
$_['heading_title2']    = 'ChatVRCS Einstellungen';

$_['text_extension']    = 'Erweiterungen';
$_['text_edit']         = 'Einstellungen bearbeiten';
$_['entry_status']      = 'Status';

$_['entry_api_key']     = 'OpenAI API-Key';
$_['entry_model']       = 'Modell (z.B. gpt-4o-mini)';
$_['entry_temperature'] = 'Kreativität / Zufälligkeit (0.0 – 2.0)';
$_['entry_prompt']      = 'Standard-Prompt (Systemanweisung)';
$_['entry_color']       = 'Chat-Farbe';
$_['entry_history_limit'] = 'Verlauf-Limit (History)';
$_['text_prompt_default'] = 'Antworte als hilfreicher Shop-Assistent.';
$_['entry_get_api_key']   = 'Prozess zur Beantragung des ChatGPT API-Keys:';
$_['entry_select_pack']   = 'Paket auswählen';
$_['entry_select_pack_help'] = 'Paketdetails';
$_['entry_reward_presets'] = 'Treuepunkte-Voreinstellungen';
$_['text_reward_help']     = 'Geben Sie die auswählbaren Punktwerte durch Kommas getrennt ein (z. B. 5,10,25,50). Wenn das Feld leer bleibt, wird die Treuepunkte-Funktion im Chat nicht angezeigt.';

$_['text_success']      = 'Einstellungen erfolgreich gespeichert!';
$_['error_permission']  = 'Warnung: Sie haben keine Berechtigung, dieses Modul zu ändern!';

// New texts for registration
$_['entry_registration_id'] = 'Registrierungs-ID (vrcs.hu)';
$_['help_registration_id']  = 'Diese ID wird von vrcs.hu vergeben und identifiziert Ihren Shop. Falls Sie noch keine haben, klicken Sie auf "Registrieren".';
$_['button_register']       = 'Registrieren';
$_['text_error_server']     = 'Fehler bei der Kommunikation mit dem Server. Bitte versuchen Sie es später erneut.';
$_['error_registry']        = 'Registrierung bei vrcs.hu fehlgeschlagen. HTTP-Code: %s Fehler: %s';

$_['text_free_pack']        = 'Basis-Paket (Kostenlos)';
$_['text_free_pack_descr']  = 'Beschreibung: Aktueller Funktionsumfang (Basis-KI-Chat, einmaliges Kontextgedächtnis während des Gesprächs).';

$_['text_basic_pack']       = 'Speichert die letzten 3 Chats (5.000 HUF/Jahr)';
$_['text_basic_pack_descr'] = 'Die KI erinnert sich an die letzten 3 Chats, was personalisiertere Antworten ermöglicht.';

$_['text_standard_pack']    = 'Speichert die letzten 6 Chats (7.000 HUF/Jahr)';
$_['text_standard_pack_descr'] = 'Die KI erinnert sich an die letzten 6 Chats für eine noch bessere Kundenansprache.';

$_['text_premium_pack']     = 'Operator-Übernahme & Dispatcher (15.000 HUF/Jahr)';
$_['text_premium_pack_descr'] = 'Der Shopbetreiber kann den Chat live von der KI übernehmen, manuell antworten und wieder zurückgeben. Inklusive Zugang zum Dispatcher-Interface.';

$_['text_vrcs_key']         = 'VRCS Komfort-Key verwenden';
$_['text_chataiwd_key']      = 'Eigenen ChatGPT API-Key registrieren';
$_['text_chataiwd_kredit']   = 'ChatGPT API-Guthaben kaufen';
$_['text_kredit_egyenleg']  = 'Mein Guthabenstand:';
$_['text_billing']          = 'Nächste Abrechnung, wenn das Guthaben unter 1 $ fällt. (10 $)';

$_['text_confirm_package_switch'] = 'Sind Sie sicher, dass Sie zum Paket %s wechseln möchten?';
$_['entry_dispatcher']      = 'Dispatcher-Interface:';
$_['help_dispatcher']       = 'Zugang zum Dispatcher-Interface:';
$_['button_dispatcher']     = 'Dispatcher-Interface öffnen';
$_['button_fizetés']        = 'Zahlung';
$_['button_fizetes']        = 'Zahlung';

// New entries for the added fields
$_['entry_chat_button']     = 'Text der Chat-Schaltfläche';
$_['help_chat_button']      = 'Text, der auf dem Chat-Button angezeigt wird, um Nutzer zum Chatten zu animieren.';
$_['placeholder_chat_button'] = 'z.B.: Jetzt KI fragen!';

$_['entry_ai_response_header'] = 'Header der KI-Antwort';
$_['help_ai_response_header']  = 'Kopfzeile, die angezeigt wird, wenn die KI antwortet.';
$_['placeholder_ai_response_header'] = 'z.B.: KI-Assistent';

$_['entry_dispatcher_response_header'] = 'Header der Dispatcher-Antwort';
$_['help_dispatcher_response_header']  = 'Kopfzeile, die angezeigt wird, wenn ein Mensch (Dispatcher) antwortet.';
$_['placeholder_dispatcher_response_header'] = 'z.B.: Kundenbetreuer';

$_['entry_ai_response_indicator'] = 'KI-Antwort-Indikator';
$_['help_ai_response_indicator']  = 'Text neben dem Icon, während die KI schreibt.';
$_['placeholder_ai_response_indicator'] = 'z.B.: KI schreibt gerade...';

$_['entry_dispatcher_response_indicator'] = 'Dispatcher-Antwort-Indikator';
$_['help_dispatcher_response_indicator']  = 'Text neben dem Icon, während der Dispatcher schreibt.';
$_['placeholder_dispatcher_response_indicator'] = 'z.B.: Mitarbeiter schreibt gerade...';

$_['entry_welcome_message'] = 'Willkommensnachricht';
$_['help_welcome_message']  = 'Begrüßungstext, der beim Öffnen des Chats erscheint.';
$_['placeholder_welcome_message'] = 'z.B.: Hallo! Wie kann ich Ihnen heute helfen?';

$_['help_packages'] = '
<b>Basis-Paket (Kostenlos)</b><br>
Aktueller Funktionsumfang (Basis-KI-Chat, begrenztes Gedächtnis).<br><br>
<b>Verlauf 3 Chats (5.000 HUF/Jahr)</b><br>
Die KI nutzt den Kontext der letzten 3 Gespräche.<br><br>
<b>Verlauf 6 Chats (7.000 HUF/Jahr)</b><br>
Die KI nutzt den Kontext der letzten 6 Gespräche.<br><br>
<b>Operator-Modus (15.000 HUF/Jahr)</b><br>
Ermöglicht die Live-Übernahme von Chats und bietet Zugriff auf das Admin-Dashboard.<br><br>
';

// Tooltips
$_['help_api_key']      = 'Geben Sie Ihren OpenAI API-Key ein. Dieser wird für die Kommunikation benötigt.';
$_['help_model']        = 'Wählen Sie das Modell. gpt-4o-mini bietet meist das beste Preis-Leistungs-Verhältnis.';
$_['help_temperature']  = "Steuert die Kreativität:\n0.0 – Präzise und faktenbasiert.\n0.7 (Standard) – Ausgewogen.\n1.0+ – Sehr kreativ, evtl. weniger genau.\n2.0 – Maximale Vielfalt, teils unvorhersehbar.";
$_['help_prompt']       = 'Geben Sie der KI eine Rolle vor. Beispiel: "Du bist ein hilfreicher Verkäufer in einem Onlineshop für Technik."';
$_['help_placeholder']  = 'Tippen Sie eine Test-Anfrage ein.';
$_['help_color']        = 'Primärfarbe des Chat-Fensters auswählen.';
$_['help_history_limit'] = 'Wie viele vorherige Fragen/Antworten an ChatGPT gesendet werden (0–5). Höhere Werte bieten mehr Kontext, kosten dezent mehr Token.';
$_['help_select_pack']  = 'Wählen Sie Ihr gewünschtes Service-Level.';
$_['help_vrcs_source']  = 'Nutzt den API-Key von VRCS.HU. Keine eigene Registrierung bei OpenAI erforderlich, Abrechnung über uns.';
$_['help_chataiwd_source'] = 'Sie verwenden Ihren eigenen OpenAI API-Key und verwalten die Kosten dort selbst.';
$_['help_kredit_vrcs'] = '<br><b>ChatAWD Hybrid-Infrastruktur und Nutzungsguthaben</b><br><br>
ChatAWD ist eine erstklassige, leistungsstarke hybride Vertriebs- und Kundendienst-Engine. Das System kombiniert die fortschrittlichsten Sprachmodelle von OpenAI (GPT-4o), die fein abgestimmte lokale Vektorsuche und die Verfolgung von Benutzerpräferenzen mit einem intelligenten Dispatcher-Dashboard (mit Echtzeit-Warenkorbrekonstruktion, Kaufwarnungen, Coupons, gezielten Treuepunkteversprechungen und einem Glücksrad).<br><br>
Die Aufrechterhaltung dieser komplexen Cloud-Infrastruktur – einschließlich KI-Rechenkapazitäten, sicherer API-Routen und Echtzeit-Datenbanksynchronisierung zwischen den Shops – arbeitet mit einem nutzungsbasierten Guthabensystem.<br><br>
Anstelle teurer und starrer monatlicher Pauschalabonnements bietet ChatAWD eine völlig transparente, nutzungsbasierte Abrechnung (Pay-as-you-go). Sie zahlen nur für die KI- und Dispatcher-Transaktionen, die Ihr Shop tatsächlich generiert.<br><br>
<b>Um sicherzustellen, dass Sie die konversionssteigernde Wirkung des Moduls völlig risikofrei erleben können, stellen wir Ihnen bei der Registrierung ein Startguthaben im Wert von 2 USD zur Verfügung.</b><br><br>';

$_['help_get_api_key'] = '🔑 Wie erhalte ich einen API-Key?<br>Besuchen Sie:<br><a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI API Keys</a><br><br>Erstellen Sie ein Konto, klicken Sie auf "Create new secret key" und kopieren Sie diesen hierher.<br><br>
💰 Guthaben und Verbrauch prüfen:<br><br>
<a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank">Abrechnungs-Übersicht</a><br><br>
<a href="https://platform.openai.com/usage" target="_blank">Nutzungsstatistiken</a><br><br>';

// Model descriptions
$_['gpt-4.1-nano']      = ' – Klein, schnell, kostengünstig – ideal für einfache Aufgaben.';
$_['gpt-4']             = ' – Leistungsstark, aber teurer und langsamer.';
$_['gpt-4.1-mini']      = ' – Ausgewogene Wahl zwischen Geschwindigkeit und Qualität.';
$_['gpt-4-turbo']       = ' – Optimale Wahl: Schnell, günstig und mächtig.';
$_['gpt-3.5-turbo']     = ' – Günstiger Klassiker, ausreichend für Standard-Fragen.';
$_['gpt-3.5-turbo-16k'] = ' – Größeres Kontextfenster für lange Gespräche.';
$_['gpt-4O']            = ' – Aktuelles Echtzeit-Modell, extrem schnell und intelligent.';
$_['gpt-4O-mini']       = ' – Für schnelle Antworten und leichte Aufgaben – sehr kosteneffizient.';
$_['gpt-4.1']           = ' – Nächste GPT-Generation (Vorschauversion).';

$_['entry_duration']    = 'Abonnement-Zeitraum:';
$_['text_duration_3']   = '3 Monate';
$_['text_duration_6']   = '6 Monate';
$_['text_duration_9']   = '9 Monate';
$_['text_duration_12']  = '12 Monate';
$_['text_duration_cancel'] = 'Abbrechen';

$_['help_recovery_info'] = 'Richten Sie maximal 5 Erinnerungs-E-Mails ein. Leere Zeilen werden nicht versendet.';
$_['entry_recovery_delay'] = 'Verzögerung';
$_['entry_recovery_subject'] = 'E-Mail Betreff (Leer = nicht senden)';
$_['entry_recovery_content'] = 'Nachrichtentext';
$_['text_hours'] = 'Stunde';
$_['text_days'] = 'Tag';
$_['text_subject_placeholder'] = 'Z.B.: Haben Sie etwas vergessen?';
$_['text_content_placeholder'] = 'Nachricht...';

$_['text_ai_syncr'] = 'KI-Synchronisierung';
$_['text_syncrestart'] = 'Synchronisation neu starten';
$_['text_syncrestart_click'] = 'Klicken Sie hier, wenn Sie den Datenbankinhalt erneut mit der KI synchronisieren möchten.';



// Konstruktor-Fallback-Werte
$_['text_chat_button_fallback']                   = 'KI jetzt fragen!';
$_['text_ai_response_header_fallback']            = 'KI-Antwort';
$_['text_dispatcher_response_header_fallback']    = 'Disponent antwortet';
$_['text_ai_response_indicator_fallback']         = 'KI antwortet gerade...';
$_['text_dispatcher_response_indicator_fallback'] = 'Disponent antwortet gerade...';
$_['text_welcome_message_fallback']               = 'Hallo! Wie kann ich Ihnen heute helfen?';

// AJAX / API-Fehlermeldungen
$_['error_permission']                            = 'Warnung: Sie haben keine Berechtigung, dieses Modul zu ändern!';
$_['error_already_registered']                    = 'Bereits registriert';
$_['error_domain_not_found']                      = 'Die Domain des Shops konnte nicht ermittelt werden.';
$_['error_invalid_input']                         = 'Bitte geben Sie Ihre E-Mail-Adresse an!';
$_['error_registry']                              = 'Registrierung fehlgeschlagen! HTTP-Code: %s, Fehler: %s';
$_['error_invalid_server_response']               = 'Ungültige Antwort vom Autorisierungsserver';
$_['error_no_registration']                       = 'Keine gültige Registrierungs-ID gefunden!';
$_['error_server_communication']                  = 'Fehler bei der Kommunikation mit dem Remote-Server.';
$_['error_invalid_field']                         = 'Ungültiger Feldname!';
$_['error_unknown']                               = 'Unbekannter Fehler basierend auf der Serverantwort.';

// Erfolgreiche Registrierung
$_['text_register_success']                       = 'Registrierung erfolgreich!';

$_['text_be_patient_registration']          = 'Bitte haben Sie Geduld, die Registrierung läuft...';
$_['text_failed']                            = 'Fehler';
$_['text_choose_package']                   = 'Bitte wählen Sie ein Paket aus';
$_['text_restart_sync']                     = 'Sind Sie sicher, dass Sie die vollständige KI-Synchronisierung neu starten möchten? Dies kann einige Zeit dauern.';
$_['text_intitializing']                     = 'KI-Modul wird initialisiert...';
$_['error_schema']                     = 'Fehler: Das Schema wurde nicht korrekt empfangen.';
$_['text_ai_learning_help'] = 'Bitte schließen Sie das Fenster nicht. <br>Die KI analysiert und indiziert gerade das Angebot des Webshops, damit das Chat-Modul und die intelligente Disponenten-Suchmaschine präzise Antworten und Produktempfehlungen liefern können.';
$_['tab_general'] = 'Allgemein';
$_['tab_chat_settings'] = 'Chat-Einstellungen';
$_['tab_faq'] = 'FAQ';
$_['tab_tools'] = 'Funktionen';
$_['tab_abandoned'] = 'Warenkorbabbrüche';
$_['entry_faq_icon_type'] = 'Icon-Typ';
$_['entry_faq_visual_icon'] = 'Icon';
$_['entry_faq_visual_image'] = 'Bild';
$_['entry_faq_question'] = 'Frage';
$_['entry_faq_answer'] = 'Antwort';
$_['help_tools_info'] = 'Hier können Sie die im Chat-Fenster erscheinenden Zusatzfunktionen ein- oder ausschalten.';
$_['help_tool_voice'] = 'Zeigt das Mikrofon-Symbol für die Spracheingabe an.';
$_['help_tool_image'] = 'Erlaubt Benutzern, Bilder an den KI-Disponenten zu senden.';
$_['help_tool_email'] = 'Zeigt das E-Mail-Symbol für die direkte Kontaktaufnahme an.';
$_['help_tool_faq'] = 'Aktiviert oder deaktiviert die Anzeige der FAQs.';
$_['help_tool_whatsapp'] = 'Zeigt den WhatsApp-Button für Direktnachrichten an.';
$_['help_whatsapp_number'] = 'Geben Sie die Telefonnummer im internationalen Format ein.';
$_['text_time'] = 'Zeit...';
$_['text_subject'] = 'Z.B.: Haben Sie etwas vergessen?';
$_['error_initializing_ai'] = 'Fehler bei der Initialisierung der KI';
$_['text_ai_prepared'] = 'KI erfolgreich vorbereitet! System wird gestartet...';
$_['text_ai_learning'] = 'KI-Lernprozess:';
$_['error_ai_arming'] = 'Fehler beim Scharfschalten der KI:';
$_['error_network'] = 'Netzwerkfehler während des Herunterfahrens. Erneuter Versuch in 5 Sekunden...';
$_['error_closing'] = 'Fehler beim Schließen:';
$_['text_optimizing'] = 'KI-Modelle werden optimiert und trainiert... Bitte warten.';
$_['text_empty'] = 'Leer';
$_['text_error'] = 'Fehler';
$_['text_unknown_error'] = 'Unbekannter Fehler';
$_['error_save'] = 'Fehler beim Speichern:';
$_['text_registration'] = 'Registrierung';
$_['entry_tool_bell']                             = 'Disponenten rufen (Glocke)';
$_['help_tool_bell']                              = 'Erlaubt Benutzern, den Disponenten zu benachrichtigen.';
$_['entry_tool_voice']                            = 'Spracherkennung';
$_['entry_tool_image']                            = 'Bild-Upload';
$_['entry_tool_emoji']                            = 'Emoji-Auswahl';
$_['entry_tool_email']                            = 'E-Mail-Kontaktformular';
$_['entry_tool_faq']                              = 'FAQ-Modul';
$_['entry_tool_whatsapp']                         = 'WhatsApp-Kontakt';
$_['entry_whatsapp_placeholder']                  = 'z.B.: +49 170 123 4567';
$_['button_save']                                 = 'Speichern';
$_['button_back']                                 = 'Zurück';
$_['button_add']                                  = 'Hinzufügen';
$_['button_remove']                               = 'Entfernen';
$_['text_enabled']                                = 'Aktiviert';
$_['text_disabled']                               = 'Deaktiviert';