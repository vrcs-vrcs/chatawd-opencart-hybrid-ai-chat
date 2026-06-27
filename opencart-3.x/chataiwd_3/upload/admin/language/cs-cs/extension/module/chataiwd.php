<?php
$_['heading_title'] = '<font color="blue">Nastavení ChatVRCS</font>';
$_['heading_title2'] = 'Nastavení ChatVRCS';

$_['text_extension'] = 'Rozšíření';
$_['text_edit'] = 'Upravit nastavení';
$_['entry_status'] = 'Stav';

$_['entry_api_key'] = 'OpenAI API klíč';
$_['entry_model'] = 'Model (např. gpt-3.5-turbo)';
$_['entry_temperature'] = 'Náhodnost (0.0 – 2.0)';
$_['entry_prompt'] = 'Výchozí prompt';
$_['entry_color'] = 'Barva chatu';
$_['entry_history_limit'] = 'Limit historie';
$_['text_prompt_default'] = 'Odpovídej jako asistent v obchodě.';
$_['entry_get_api_key']     = 'Proces žádosti o CHATGPT API klíč:';
$_['entry_select_pack']     = 'Vyberte balíček';
$_['entry_select_pack_help']     = 'Podrobnosti balíčku';
$_['entry_reward_presets'] = 'Předvolby věrnostních bodů';
$_['text_reward_help']     = 'Zadejte volitelné hodnoty bodů oddělené čárkami (např. 10,20,50,100). Pokud necháte pole prázdné, funkce věrnostních bodů se v chatu nezobrazí.';

$_['text_success'] = 'Nastavení byla úspěšně uložena!';
$_['error_permission'] = 'Nemáte oprávnění upravovat tento modul!';

// Nové texty pro registraci
$_['entry_registration_id'] = 'Registrační ID (vrcs.hu)';
$_['help_registration_id'] = 'Toto ID poskytuje vrcs.hu a slouží k identifikaci vašeho obchodu. Pokud jej ještě nemáte, klikněte na tlačítko Registrovat.';
$_['button_register'] = 'Registrovat';
$_['text_error_server'] = 'Při komunikaci se serverem došlo k chybě. Zkuste to prosím později.';
$_['error_registry'] = 'Registrace na vrcs.hu se nezdařila. HTTP kód: %s Chyba: %s';

$_['text_free_pack'] = 'Základní balíček (Zdarma)';
$_['text_free_pack_descr'] = 'Popis: Aktuální funkčnost (základní verze AI chatu, jednorázová paměť kontextu během konverzace).';

$_['text_basic_pack'] = 'Pamatuje si poslední 3 chaty (5 000 HUF/rok)';
$_['text_basic_pack_descr'] = 'AI si pamatuje poslední 3 chaty, což umožňuje personalizovanější odpovědi.';

$_['text_standard_pack'] = 'Pamatuje si posledních 6 chatů (7 000 HUF/rok)';
$_['text_standard_pack_descr'] = 'AI si pamatuje posledních 6 chatů, což umožňuje personalizovanější odpovědi.';

$_['text_premium_pack'] = 'Převzetí operátorem a vrácení (15 000 HUF/rok)';
$_['text_premium_pack_descr'] = 'Majitel obchodu může převzít chat od AI, odpovědět ručně a poté jej vrátit zpět. Zahrnuje přístup do administrátorského rozhraní.';

$_['text_vrcs_key'] = 'Použít pohodlný klíč VRCS';
$_['text_chataiwd_key'] = 'Registrovat můj vlastní CHATGPT API klíč';
$_['text_chataiwd_kredit'] = 'Koupit kredity pro používání ChatGPT API';
$_['text_kredit_egyenleg'] = 'Můj zůstatek kreditu:';
$_['text_billing'] = 'Další fakturace, když zůstatek klesne pod 1 $. (10 $) ';

$_['text_confirm_package_switch'] = 'Opravdu chcete přejít na balíček %s?';
$_['entry_dispatcher'] = 'Rozhraní dispečera:';
$_['help_dispatcher'] = 'Rozhraní dispečera:';
$_['button_dispatcher'] = 'Rozhraní dispečera';
$_['button_fizetés'] = 'Platba';
$_['button_fizetes'] = 'Platba';

// Nové položky pro přidaná pole
$_['entry_chat_button'] = 'Text tlačítka chatu';
$_['help_chat_button'] = 'Zadejte text, který se zobrazí na tlačítku chatu a povzbudí uživatele k zahájení konverzace.';
$_['placeholder_chat_button'] = 'např.: ChatGPT Zeptejte se teď!';

$_['entry_ai_response_header'] = 'Hlavička odpovědi AI';
$_['help_ai_response_header'] = 'Zadejte text hlavičky, který se zobrazí, když AI odpovídá v chatu.';
$_['placeholder_ai_response_header'] = 'např.: Odpověď AI';

$_['entry_dispatcher_response_header'] = 'Hlavička odpovědi dispečera';
$_['help_dispatcher_response_header'] = 'Zadejte text hlavičky, který se zobrazí, když dispečer odpovídá v chatu.';
$_['placeholder_dispatcher_response_header'] = 'např.: Odpověď dispečera';

$_['entry_ai_response_indicator'] = 'Indikátor odpovědi AI';
$_['help_ai_response_indicator'] = 'Zadejte text, který se zobrazí vedle ikony chatu, když AI odpovídá.';
$_['placeholder_ai_response_indicator'] = 'např.: AI odpovídá...';

$_['entry_dispatcher_response_indicator'] = 'Indikátor odpovědi dispečera';
$_['help_dispatcher_response_indicator'] = 'Zadejte text, který se zobrazí vedle ikony chatu, když dispečer odpovídá.';
$_['placeholder_dispatcher_response_indicator'] = 'např.: Dispečer odpovídá...';

$_['entry_welcome_message'] = 'Uvítací zpráva';
$_['help_welcome_message'] = 'Zadejte uvítací zprávu, která se zobrazí při zahájení chatu.';
$_['placeholder_welcome_message'] = 'např.: Dobrý den! Jak vám mohu dnes pomoci?';

$_['help_packages'] = '
<b>Základní balíček (Zdarma)</b><br>
Popis: Aktuální funkčnost (základní verze AI chatu, jednorázová paměť kontextu během konverzace).<br><br>

<b>Pamatuje si poslední 3 chaty (5 000 HUF/rok)</b><br>
AI si pamatuje poslední 3 chaty, což umožňuje personalizovanější odpovědi.<br><br>

<b>Pamatuje si posledních 6 chatů (7 000 HUF/rok)</b><br>
AI si pamatuje posledních 6 chatů, což umožňuje personalizovanější odpovědi.<br><br>

<b>Převzetí operátorem a vrácení (15 000 HUF/rok)</b><br>
Majitel obchodu může převzít chat od AI, odpovědět ručně a poté jej vrátit zpět. Zahrnuje přístup do administrátorského rozhraní.<br><br>
';

// Nápovědy (Tooltips)
$_['help_api_key'] = 'Zadejte svůj API klíč ChatGPT získaný od OpenAI. Je vyžadován pro komunikaci.';
$_['help_model'] = 'Vyberte model ChatGPT, který chcete použít. Různé modely se liší cenou a schopnostmi. Ve většině případů je gpt-3.5-turbo dobrou rovnováhou mezi cenou a výkonem.';
$_['help_temperature'] = "Ovládá kreativitu odpovědí:\n0.0 – Plně předvídatelné a přesné, žádná kreativita.\n0.7 (výchozí) – Vyvážená kreativita a přesnost.\n1.0+ – Kreativnější a rozmanitější odpovědi, možná méně přesné.\n2.0 – Maximální kreativita, velmi rozmanité odpovědi, potenciálně příliš volné.";
$_['help_prompt'] = 'Zadejte výchozí prompt pro vedení odpovědí AI. Příklad: "Jste užitečný asistent v obchodě odpovídající na dotazy zákazníků ohledně produktů."';
$_['help_placeholder'] = 'Napište něco, co chcete poslat AI. Např.: Odpovídej jako asistent v obchodě.';
$_['help_color'] = 'Vyberte primární barvu okna chatu (hlavička, tlačítka atd.).';
$_['help_history_limit'] = 'Kolik předchozích párů otázka-odpověď se má poslat do ChatGPT (0–5). 0 znamená, že se neposílá žádná historie. Vyšší hodnoty poskytují více kontextu, ale spotřebovávají více tokenů.';
$_['help_select_pack'] = 'help_select_pack';
$_['help_vrcs_source'] = 'Používá API klíč poskytovaný VRCS.HU, platba probíhá přes nás. Není vyžadována registrace u ChatGPT.';
$_['help_chataiwd_source'] = 'Musíte uvést svůj vlastní API klíč ChatGPT, který lze získat od OpenAI.';
$_['help_kredit_vrcs'] = '<br><b>Důležité informace o používání ChatGPT</b><br><br>

Modul je připojen k externí službě ChatGPT, jejíž provoz generuje náklady.<br><br>
Proto vyžaduje používání této funkce zakoupení <b>samostatných kreditů.</b><br><br>
Je důležité zdůraznit, že se nejedná o rozhodnutí vývojáře modulu,<br>
ale spíše o <b>pokrytí technických nákladů externí služby</b>.<br><br>

<b>Pro vyzkoušení modulu poskytujeme bezplatný kredit v hodnotě 0,5 USD,</b><br>
takže si můžete funkci otestovat bez jakýchkoli závazků.<br><br>
Děkujeme za pochopení!<br><br>';

$_['help_get_api_key'] = '🔑 Jak získat API klíč ChatGPT?<br>Otevřete následující webovou stránku:<br><a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a><br><br>Přihlaste se nebo si zaregistrujte účet na platformě OpenAI (můžete použít i Google účet).<br><br>Po přihlášení klikněte na tlačítko „Create new secret key“.<br><br>Zkopírujte vygenerovaný API klíč a vložte jej do tohoto modulu.<br><br>Důležité: Celý API klíč si můžete zobrazit pouze jednou!<br><br>Pokud jej ztratíte, vygenerujte na stejné stránce nový.
<br><br>
💰 Zůstatek využití API zkontrolujete zde:<br><br>
<a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank">Billing Overview (Přehled fakturace)</a><br><br>
<a href="https://platform.openai.com/usage" target="_blank">Statistiky využití</a><br><br>
';

// Popisy modelů
$_['gpt-4.1-nano'] = ' – Malý, rychlá doba odezvy, nízké náklady – skvělé pro jednoduché úkoly.';
$_['gpt-4'] = ' – Výkonnější, ale může být pomalejší a dražší.';
$_['gpt-4.1-mini'] = ' – Vyvážená volba: rychlost a kvalita.';
$_['gpt-4-turbo'] = ' – Optimální volba: rychlá, levnější a výkonná. Obvykle nejlepší model v poměru cena/výkon.';
$_['gpt-3.5-turbo'] = ' – Ještě levnější, méně přesný, ale pro mnoho úkolů postačující.';
$_['gpt-3.5-turbo-16k'] = ' – Větší kontextové okno – vhodné pro delší konverzace nebo reference.';
$_['gpt-4O'] = ' – Nejnovější model v reálném čase, velmi rychlý a výkonný. Použijte tento, pokud je k dispozici.';
$_['gpt-4O-mini'] = ' – Pro lehké úkoly a rychlé odpovědi – např. automatické odpovědi, vyhledávání.';
$_['gpt-4.1'] = ' – Model GPT nové generace, může se jednat o preview nebo speciální edici (v závislosti na verzi API).';

$_['entry_duration'] = 'Předplatné na období:';
$_['text_duration_3'] = '3 měsíce';
$_['text_duration_6'] = '6 měsíců';
$_['text_duration_9'] = '9 měsíců';
$_['text_duration_12'] = '12 měsíců';
$_['text_duration_cancel'] = 'Zrušit';

$_['help_recovery_info'] = 'Nastavte maximálně 5 připomínacích e-mailů. Prázdné řádky nebudou odeslány.';
$_['entry_recovery_delay'] = 'Zpoždění';
$_['entry_recovery_subject'] = 'Předmět e-mailu (Prázdné = neodesílat)';
$_['entry_recovery_content'] = 'Text zprávy';
$_['text_hours'] = 'Hodina';
$_['text_days'] = 'Den';
$_['text_subject_placeholder'] = 'Např.: Zapomněli jste na něco?';
$_['text_content_placeholder'] = 'Zpráva...';