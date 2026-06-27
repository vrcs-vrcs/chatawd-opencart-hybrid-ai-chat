<?php
/*
 * 2025-04-24
 */
class ControllerExtensionModuleChataiwd extends Controller {

    protected $method_separator;
    private $chataiwdurl = 'https://www.vrcs.hu/index.php?route=tool/vrcschatawd';
    private $model_load = 'extension/module/chataiwd';
    private $model_function = 'model_extension_module_chataiwd';
    private $module_chataiwd_prompt;
    private $module_chataiwd_package;
    private $module_chataiwd_color;
    private $module_chataiwd_rewards;
    private $module_chataiwd_registration_id;
    private $module_ai_response_header;
    private $module_dispatcher_response_header;
    private $module_ai_response_indicator;
    private $module_dispatcher_response_indicator;
    private $module_welcome_message;
    private $module_chat_button;
    private $module_chataiwd_faq;
    private $module_chataiwd_tool_bell;
    private $module_chataiwd_tool_voice;
    private $module_chataiwd_tool_image;
    private $module_chataiwd_tool_emoji;
    private $module_chataiwd_tool_email;
    private $module_chataiwd_tool_faq;
    private $module_chataiwd_tool_whatsapp;
    private $module_chataiwd_whatsapp_number;
    private $module_chataiwd_recovery;

    public function __construct($registry) {
        parent::__construct($registry);

        if (defined('CHATAIWD_URL')) $this->chataiwdurl = CHATAIWD_URL;

        $this->method_separator = version_compare(VERSION, '4.0.0.0', '<')
            ? '/'
            : (version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|');

        $this->load->model($this->model_load);
        $this->load->language($this->model_load);

        $this->{$this->model_function}->initializeDefaultSettings();
        $settings = $this->{$this->model_function}->getSetting();
        $settingValues = [];
        foreach ($settings as $setting) {
            $settingValues[$setting['key']] = $setting['value'];
        }

        $this->module_chataiwd_faq               = $settingValues['module_chataiwd_faq'] ?? '';
        if ($this->module_chataiwd_faq) {
            $this->module_chataiwd_faq = json_decode((string)$this->module_chataiwd_faq, true);
        } else {
            $this->module_chataiwd_faq = [];
        }

        $this->module_chataiwd_tool_bell             = $settingValues['module_chataiwd_tool_bell']  ?? '1';
        $this->module_chataiwd_tool_voice            = $settingValues['module_chataiwd_tool_voice'] ?? '1';
        $this->module_chataiwd_tool_image            = $settingValues['module_chataiwd_tool_image'] ?? '1';
        $this->module_chataiwd_tool_emoji            = $settingValues['module_chataiwd_tool_emoji'] ?? '1';
        $this->module_chataiwd_tool_email            = $settingValues['module_chataiwd_tool_email'] ?? '1';
        $this->module_chataiwd_tool_faq              = $settingValues['module_chataiwd_tool_faq']   ?? '1';
        $this->module_chataiwd_tool_whatsapp         = $settingValues['module_chataiwd_tool_whatsapp'] ?? '1';
        $this->module_chataiwd_whatsapp_number       = $settingValues['module_chataiwd_whatsapp_number'] ?? '1';

        $this->module_chataiwd_prompt                = $settingValues['module_chataiwd_prompt'] ?? '';
        $this->module_chataiwd_package               = $settingValues['module_chataiwd_package'] ?? 'free';
        $this->module_chataiwd_registration_id       = $settingValues['module_chataiwd_registration_id'] ?? '';
        $this->module_chataiwd_color                 = $settingValues['module_chataiwd_color'] ?? '';
        $this->module_chataiwd_rewards               = $settingValues['module_chataiwd_rewards'] ?? '';


        // Nyelvek lekérése az áruházból
        $this->load->model('localisation/language');
        $store_languages = $this->model_localisation_language->getLanguages();

        $languages = $this->{$this->model_function}->getSettingDescriptions() ?: [];

        $this->module_chat_button = [];
        $this->module_ai_response_header = [];
        $this->module_dispatcher_response_header = [];
        $this->module_ai_response_indicator = [];
        $this->module_dispatcher_response_indicator = [];
        $this->module_welcome_message = [];

        foreach ($store_languages as $lang) {
            $lang_id   = $lang['language_id'];
            $lang_dir  = !empty($lang['directory']) ? $lang['directory'] : $lang['code'];
            $lang_code = $lang['code'];

            // Létrehozunk egy üres nyelvi objektumot a fülnek
            if (version_compare(VERSION, '4.0.0.0', '>=')) {
                $lang_obj = new \Opencart\System\Library\Language($lang_code);
                $en_file = DIR_EXTENSION . 'chataiwd/catalog/language/en-gb/module/chataiwd.php';
                $current_file = DIR_EXTENSION . 'chataiwd/catalog/language/' . $lang_dir . '/module/chataiwd.php';

            } else {
                $lang_obj = new \Language($lang_dir);
                $en_file = DIR_CATALOG . 'language/en-gb/' . $this->model_load . '.php';
                $current_file = DIR_CATALOG . 'language/' . $lang_dir . '/' . $this->model_load . '.php';

            }

            $_ = array();
            if (is_file($en_file)) {
                require($en_file);
            }

            // 2. Aktuális nyelv (pl. cs-cz, de-de, hu-hu) rátöltése a catalog mappából

            if (is_file($current_file) && $lang_dir != 'en-gb') {
                require($current_file);
            }

            // Átadjuk a kinyert $_ adatokat a nyelvi objektumnak
            foreach ($_ as $key => $value) {
                $lang_obj->set($key, $value);
            }

            // --- INNENTŐL AZ ÉRTÉKADÁS MÁR KÖZVETLENÜL A CATALOG-BÓL JÖN ---

            // 1. Chat gomb szövege
            $this->module_chat_button[$lang_id] = (!empty($languages['module_chat_button'][$lang_id]))
                ? $languages['module_chat_button'][$lang_id]
                : (($lang_obj->get('text_chat_button_fallback') != 'text_chat_button_fallback')
                    ? $lang_obj->get('text_chat_button_fallback') : 'ChatGpt Ask Now!');

            // 2. AI Fejléc
            $this->module_ai_response_header[$lang_id] = (!empty($languages['module_ai_response_header'][$lang_id]))
                ? $languages['module_ai_response_header'][$lang_id]
                : (($lang_obj->get('text_ai_response_header_fallback') != 'text_ai_response_header_fallback')
                    ? $lang_obj->get('text_ai_response_header_fallback') : 'AI Response');

            // 3. Diszpécser Fejléc
            $this->module_dispatcher_response_header[$lang_id] = (!empty($languages['module_dispatcher_response_header'][$lang_id]))
                ? $languages['module_dispatcher_response_header'][$lang_id]
                : (($lang_obj->get('text_dispatcher_response_header_fallback') != 'text_dispatcher_response_header_fallback')
                    ? $lang_obj->get('text_dispatcher_response_header_fallback') : 'Dispatcher Response');

            // 4. AI Indikátor
            $this->module_ai_response_indicator[$lang_id] = (!empty($languages['module_ai_response_indicator'][$lang_id]))
                ? $languages['module_ai_response_indicator'][$lang_id]
                : (($lang_obj->get('text_ai_response_indicator_fallback') != 'text_ai_response_indicator_fallback')
                    ? $lang_obj->get('text_ai_response_indicator_fallback') : 'AI is responding');

            // 5. Diszpécser Indikátor
            $this->module_dispatcher_response_indicator[$lang_id] = (!empty($languages['module_dispatcher_response_indicator'][$lang_id]))
                ? $languages['module_dispatcher_response_indicator'][$lang_id]
                : (($lang_obj->get('text_dispatcher_response_indicator_fallback') != 'text_dispatcher_response_indicator_fallback')
                    ? $lang_obj->get('text_dispatcher_response_indicator_fallback') : 'Dispatcher is responding');

            // 6. Üdvözlő üzenet
            $this->module_welcome_message[$lang_id] = (!empty($languages['module_welcome_message'][$lang_id]))
                ? $languages['module_welcome_message'][$lang_id]
                : (($lang_obj->get('text_welcome_message_fallback') != 'text_welcome_message_fallback')
                    ? $lang_obj->get('text_welcome_message_fallback') : 'Hi! How can I help you today?');
        }

        $this->module_chataiwd_recovery = $settingValues['module_chataiwd_recovery'] ?? '';

        if ($this->module_chataiwd_recovery) {
            $this->module_chataiwd_recovery = json_decode((string)$this->module_chataiwd_recovery, true);
        }

        if (!is_array($this->module_chataiwd_recovery)) {
            $this->module_chataiwd_recovery = [];
        }

        for ($i = 0; $i < 5; $i++) {
            if (!isset($this->module_chataiwd_recovery[$i])) {
                $this->module_chataiwd_recovery[$i] = [
                    'value'   => '',
                    'unit'    => 'hours',
                    'subject' => '',
                    'status'  => 0
                ];
            }
        }

    }

    public function index(): void {
        $this->load->language($this->model_load);
        $this->document->setTitle($this->language->get('heading_title2'));

        $data['local_stats_url']    = str_replace('&amp;', '&', $this->url->link($this->model_load . $this->method_separator . 'getLocalStats' , 'user_token=' . $this->session->data['user_token'], true));
        $data['push_chunk_url']     = str_replace('&amp;', '&', $this->url->link($this->model_load . $this->method_separator . 'pushTableChunk' , 'user_token=' . $this->session->data['user_token'], true));
        $data['finalize_sync_url']  = str_replace('&amp;', '&', $this->url->link($this->model_load . $this->method_separator . 'finalizeSync' , 'user_token=' . $this->session->data['user_token'], true));
        $data['billing_url']        = str_replace('&amp;', '&', $this->url->link($this->model_load . $this->method_separator . 'billing' , 'user_token=' . $this->session->data['user_token'], true));
        $data['register_url']       = str_replace('&amp;', '&', $this->url->link($this->model_load . $this->method_separator . 'register' , 'user_token=' . $this->session->data['user_token'], true));
        $data['save_url']           = str_replace('&amp;', '&', $this->url->link($this->model_load . $this->method_separator . 'save' , 'user_token=' . $this->session->data['user_token'], true));

        $this->load->model('setting/setting');
        $this->load->model($this->model_load);
        $this->load->model('tool/image');

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->model_load, 'user_token=' . $this->session->data['user_token'], true)
        ];


        // Regisztrációs azonosító lekérdezése
        $registrationId = $this->module_chataiwd_registration_id;

        // 1. Ellenőrizzük a registration_id érvényességét a vrcs.hu-n keresztül
        if (!empty($registrationId)) {
            $isValid = $this->checkRegistrationWithVrcsHu($registrationId);
            if (!$isValid) {
                // Ha nem érvényes, töröljük a registration_id-t a chat_setting táblából
                $this->{$this->model_function}->deleteSetting('module_chataiwd_registration_id');
                $this->{$this->model_function}->updateSetting('module_chataiwd_registration_id', '');
                $registrationId = '';
            }
        }
        // 2. Csomagok lekérdezése a vrcs.hu-ról, ha van érvényes registration_id
        $data['packages'] = [];
        $data['packages_descr'] = [];
        $data['packages_url'] = [];
        $data['human'] = '';
        $data['domain'] = '';
        $data['kredit'] = 0;
        $data['credit_monitoring'] = null;
        $data['expiration_warning'] = ['days' => -2, 'message' => 'No expiration data available'];

        if (!empty($registrationId)) {
            $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);
            $packagesData = $this->fetchPackagesFromVrcsHu($registrationId, $domain);

            $data['kredit'] = round($packagesData['kredit'],2);
            $data['credit_monitoring'] = $packagesData['credit_monitoring'];

            if ($packagesData && isset($packagesData['packages']) && isset($packagesData['packages_descr']) && isset($packagesData['packages_url'])) {
                $data['packages'] = $packagesData['packages'];
                $data['packages_descr'] = $packagesData['packages_descr'];
                $data['packages_url'] = $packagesData['packages_url'];
                $data['package_help'] = $packagesData['help_package'];
                $data['message'] = $packagesData['message'] ?? null;
                $data['expiration_warning'] = $packagesData['expiration_warning'];

                if (!empty($packagesData['human'])) {
                    $data['human'] = $this->chataiwdurl . '.dispatcher';
                    $data['domain'] = $domain;
                }

                // Aktuális csomag meghatározása az első csomagra (vrcs.hu logika alapján)
                $currentPackageFromVrcs = array_key_first($packagesData['packages']);

                // Frissítjük a beállításokat az aktuális csomaggal
                $savedPackage = $this->module_chataiwd_package ?? 'free';
                if ($savedPackage !== $currentPackageFromVrcs) {
                    $this->load->model('setting/setting');
                    $settings = $this->model_setting_setting->getSetting('module_chataiwd');
                    $settings['module_chataiwd_package'] = $currentPackageFromVrcs;
                    $this->model_setting_setting->editSetting('module_chataiwd', $settings);
                }

            } else {
                // Ha a lekérdezés sikertelen, alapértelmezett csomagokat használunk
                $data['packages'] = [
                    'free' => $this->language->get('text_free_pack'),
                    'basic' => $this->language->get('text_basic_pack'),
                    'standard' => $this->language->get('text_standard_pack'),
                    'premium' => $this->language->get('text_premium_pack'),
                ];
                $data['packages_descr'] = [
                    'free' => $this->language->get('text_free_pack_descr'),
                    'basic' => $this->language->get('text_basic_pack_descr'),
                    'standard' => $this->language->get('text_standard_pack_descr'),
                    'premium' => $this->language->get('text_premium_pack_descr'),
                ];
                $data['packages_url'] = [
                    'free' => "",
                    'basic' => $this->chataiwdurl."checkout&package=basic",
                    'standard' => $this->chataiwdurl."checkout&package=standard",
                    'premium' => $this->chataiwdurl."checkout&package=premium",
                ];
            }
        } else {
            // Ha nincs registration_id, alapértelmezett csomagokat használunk
            $data['packages'] = [];
            $data['packages_descr'] = [];
            $data['packages_url'] = [];
        }
        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['module_chataiwd_faqs'] = [];
        foreach ($this->module_chataiwd_faq as $faq) {

            // Kép thumb generálás
            if (!empty($faq['image']) && is_file(DIR_IMAGE . $faq['image'])) {
                $thumb = $this->model_tool_image->resize($faq['image'], 100, 100);
            } else {
                $thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
            }

            $data['module_chataiwd_faqs'][] = [
                'type'     => $faq['type'] ?? 'icon',
                'icon'     => $faq['icon'] ?? '',
                'image'    => $faq['image'] ?? '',
                'thumb'    => $thumb,
                'question' => $faq['question'] ?? '',
                'answer'   => $faq['answer'] ?? '',
                'status'   => $faq['status'] ?? 0
            ];
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        $data['faq_row'] = count($data['module_chataiwd_faqs']);

        // Adatok betöltése az űrlaphoz
        $data['module_chataiwd_prompt']                  = $this->module_chataiwd_prompt;

        $data['module_chataiwd_status']                  = $this->config->get('module_chataiwd_status') ?? ''; // Ezt a setting táblából kérjük
        $data['module_chataiwd_package']                 = $this->module_chataiwd_package;
        $data['module_chataiwd_color']                   = $this->module_chataiwd_color;
        $data['module_chataiwd_rewards']                 = $this->module_chataiwd_rewards;
        $data['module_chataiwd_registration_id']         = $registrationId;
        $data['module_ai_response_header']              = $this->module_ai_response_header;
        $data['module_dispatcher_response_header']      = $this->module_dispatcher_response_header;
        $data['module_ai_response_indicator']           = $this->module_ai_response_indicator;
        $data['module_dispatcher_response_indicator']   = $this->module_dispatcher_response_indicator;
        $data['module_welcome_message']                 = $this->module_welcome_message;
        $data['module_chat_button']                     = $this->module_chat_button;

        $data['module_chataiwd_tool_bell']  = $this->module_chataiwd_tool_bell;
        $data['module_chataiwd_tool_voice'] = $this->module_chataiwd_tool_voice;
        $data['module_chataiwd_tool_image'] = $this->module_chataiwd_tool_image;
        $data['module_chataiwd_tool_emoji'] = $this->module_chataiwd_tool_emoji;
        $data['module_chataiwd_tool_email'] = $this->module_chataiwd_tool_email;
        $data['module_chataiwd_tool_whatsapp'] = $this->module_chataiwd_tool_whatsapp;
        $data['module_chataiwd_whatsapp_number'] = $this->module_chataiwd_whatsapp_number;
        $data['module_chataiwd_tool_faq']   = $this->module_chataiwd_tool_faq;
        $data['module_chataiwd_recovery'] = $this->module_chataiwd_recovery;

        if ($this->config->get('config_language_admin')) {
            $language_code = $this->config->get('config_language_admin');
        } elseif ($this->config->get('config_language_catalog')) {
            $language_code = $this->config->get('config_language_catalog');
        } elseif ($this->config->get('config_language')) {
            $language_code = $this->config->get('config_language');
        } else {
            $language_code = 'en-gb';
        }
        $data['config_language'] = $language_code;

        $data['separator'] = $this->method_separator;
        $data['model_function'] = $this->model_load;
        $data['checkout_credit'] = "https://www.vrcs.hu/index.php?route=tool/chatgptcheckout.kredit";

        // --- KUPONOK LEKÉRDEZÉSE A DISZPÉCSER SZÁMÁRA ---
        $data['coupons'] = [];

        $coupons = $this->{$this->model_function}->getCoupons();

        $currency  = $this->config->get('config_currency');
        $currency_right = $this->currency->getSymbolRight($currency);
        $currency_left = $this->currency->getSymbolLeft($currency);

        foreach ($coupons as $coupon) {
            $data['coupons'][] = [
                'coupon_id'     => $coupon['coupon_id'],
                'name'          => $coupon['name'],
                'code'          => $coupon['code'],
                'shipping'      => $coupon['shipping'],
                'type'          => $coupon['type'],
                'total'         => $coupon['total'],
                'discount'      => $coupon['discount'],
                'currency_right'=> $currency_right,
                'currency_left' => $currency_left,
                'text'      => $coupon['name'] . ' (' . $coupon['code'] . ') - ' . ($coupon['type'] == 'P' ? (float)$coupon['discount'] . '%' : $this->currency->format($coupon['discount'], $this->config->get('config_currency')))
            ];
        }



        $data['coupons_json'] = json_encode($data['coupons'], JSON_UNESCAPED_UNICODE);

        $data['save']       = $this->url->link($this->model_load.$this->method_separator.'save', 'user_token=' . $this->session->data['user_token']);
        $data['register']   = $this->url->link($this->model_load.$this->method_separator.'register');
        $data['back']       = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $data['user_token'] = $this->session->data['user_token'];
        $data['vrcs_version'] = '3.2.0.0';
        $data['dispatcher_id'] = (int)$this->user->getId();
        $data['dispatcher_name'] = $this->user->getUsername();

        $this->document->addStyle('view/stylesheet/chataiwd.css');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->model_load, $data));
    }

    public function save(): void {
        $this->load->language($this->model_load);
        $this->load->model($this->model_load);
        $this->load->model('setting/setting');

        $json = [];

        if (!$this->user->hasPermission('modify', $this->model_load)) {
            $json['error'] = ($this->language->get('error_permission') != 'error_permission')
                ? $this->language->get('error_permission')
                : 'Warning: You do not have permission to modify this module!';
        }

        if (!$json) {
            $data = $this->request->post;

            // A status-t a setting táblába mentjük
            $this->model_setting_setting->editSetting('module_chataiwd', [
                'module_chataiwd_status' => $data['module_chataiwd_status'] ?? ''
            ]);

            $this->{$this->model_function}->saveSettings($data);

            $this->load->model('design/layout');
            $this->db->query("DELETE FROM " . DB_PREFIX . "layout_module WHERE `code` IN ('chataiwd.chataiwd', 'chataiwd') AND position = 'content_bottom'");

            $this->addEvent();

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function addEvent() {
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('chataiwd_catalog');
        $trigger = 'catalog/view/*/after';
        $action = $this->model_load . $this->method_separator . 'injectChat';
        $this->model_setting_event->addEvent('chataiwd_catalog', $trigger, $action, 1);


        $this->model_setting_event->deleteEventByCode('chataiwd_reward_trigger');
        $trigger = 'catalog/model/checkout/order/addOrderHistory/after';
        $action = 'extension/module/chataiwd/onOrderUpdate';

        $this->model_setting_event->addEvent('chataiwd_reward_trigger', $trigger, $action, 1);
    }

    /**
     * Ellenőrzi a registration_id érvényességét a vrcs.hu-n keresztül
     *
     * @param string $registrationId A regisztrációs azonosító
     * @return bool Érvényes-e a regisztrációs azonosító
     */
    private function checkRegistrationWithVrcsHu(string $registrationId): bool {
        $url = $this->chataiwdurl.'.checkRegistration';

        $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);

        $postData = [
            'registration_id'   => $registrationId,
            'domain'            => $domain
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($response === false || $httpCode !== 200) {
            error_log("cURL hiba a vrcs.hu regisztráció ellenőrzése során: HTTP kód: $httpCode, Hiba: $curlError, Válasz: $response");
            curl_close($ch);
            return false; // Ha hiba történik, feltételezzük, hogy nem érvényes
        }

        curl_close($ch);

        $responseData = json_decode($response, true);
        if (empty($responseData) || !isset($responseData['success'])) {
            error_log("Érvénytelen válasz a vrcs.hu-tól az ellenőrzés során: $response");
            return false;
        }

        return $responseData['success'] === true;
    }

    public function register(): void {
        $this->load->language($this->model_load);

        $json = [];

        // Jogosultság ellenőrzés
        if (!$this->user->hasPermission('modify', $this->model_load)) {
            $json['error'] = ($this->language->get('error_permission') != 'error_permission')
                ? $this->language->get('error_permission')
                : 'Warning: You do not have permission to modify this module!';

            $this->response->addHeader('HTTP/1.1 403 Forbidden');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Ellenőrizzük, hogy van-e már regisztrációs azonosító
        $registrationId = $this->module_chataiwd_registration_id;
        if (!empty($registrationId)) {
            $json['error'] = ($this->language->get('error_already_registered') != 'error_already_registered')
                ? $this->language->get('error_already_registered')
                : 'Already registered';

            $this->response->addHeader('HTTP/1.1 409 Conflict');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Az áruház domainjének lekérdezése
        $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);
        if (!$domain) {
            $json['error'] = ($this->language->get('error_domain_not_found') != 'error_domain_not_found')
                ? $this->language->get('error_domain_not_found')
                : 'The store domain name could not be determined.';



            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }


        $postData = json_decode(file_get_contents('php://input'), true);
        $userEmail = $postData['user_email'] ?? '';

        if (empty($userEmail)) {
            $json['error'] = ($this->language->get('error_invalid_input') != 'error_invalid_input')
                ? $this->language->get('error_invalid_input')
                : 'Please provide your email address!';

            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }
        $storeEmail = $this->config->get('config_email') ?? '';

        $registrationData = [
            'domain' => $domain,
            'user_email' => $userEmail,
            'store_email' => $storeEmail,
            'language_code' => $this->config->get('config_language_admin') ?? 'en-gb',
        ];

        $ch = curl_init($this->chataiwdurl);

        curl_setopt($ch, CURLOPT_URL, $this->chataiwdurl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registrationData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        //$this->sendMailRegister($registrationData, $response, $curlError, $httpCode);

        if ($response === false || $httpCode !== 200) {
            $json['error'] = ($this->language->get('error_registry') != 'error_registry')
                ? sprintf($this->language->get('error_registry'), $httpCode, $curlError)
                : sprintf('Registration failed! HTTP Code: %s, Error: %s', $httpCode, $curlError);

            if ($response) {
                $response = json_decode($response, true);
                if (!empty($response['error'])) {
                    $json['error'] = $response['error'];
                }
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Válasz feldolgozása
        $responseData = json_decode($response, true);
        if (empty($responseData) || !isset($responseData['registration_id'])) {
            error_log("Érvénytelen válasz a vrcs.hu-tól: $response");

            $json['error'] = (($this->language->get('error_invalid_server_response') != 'error_invalid_server_response')
                    ? $this->language->get('error_invalid_server_response')
                    : 'Invalid response from authorization server') . ': ' . $response;

            $this->response->addHeader('HTTP/1.1 500 Internal Server Error');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $registrationId = $responseData['registration_id'];

        // Regisztrációs azonosító mentése
        $this->load->model($this->model_load);
        $this->{$this->model_function}->updateSetting('module_chataiwd_registration_id',$registrationId);

        $json['success'] = ($this->language->get('text_register_success') != 'text_register_success')
            ? $this->language->get('text_register_success')
            : 'Successful registration!';

        $this->response->addHeader('HTTP/1.1 200 OK');
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function fetchPackagesFromVrcsHu(string $registrationId, string $domain): ?array {
        $postData = [
            'registration_id' => $registrationId,
            'domain' => $domain
        ];

        $ch = curl_init($this->chataiwdurl.'.getPackages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($response === false || $httpCode !== 200) {
            error_log("cURL hiba a vrcs.hu csomagok lekérdezése során: HTTP kód: $httpCode, Hiba: $curlError, Válasz: $response");
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $responseData = json_decode($response, true);
        if (empty($responseData) || !isset($responseData['success']) || !$responseData['success']) {
            error_log("Érvénytelen vagy sikertelen válasz a vrcs.hu-tól: $response");
            return null;
        }
        $responseData['expiration_warning'] = $responseData['expiration_warning'] ?? ['days' => -2, 'message' => 'No expiration data available'];

        return $responseData;
    }


    public function install(): void {
        // add events
        $this->load->model('setting/event');
        if (version_compare(VERSION,'4.0.1.0','>=')) {
            $data = [
                'code'        => 'chataiwd',
                'description' => '',
                'trigger'     => 'admin/controller/common/column_left/after',
                'action'      => $this->model_load.$this->method_separator.'eventControllerCommonColumnLeftAfter',
                'status'      => true,
                'sort_order'  => 0
            ];
            $this->model_setting_event->addEvent($data);
        } else {
            if (version_compare(VERSION,'4.0.0.0','<')) {
                $this->model_setting_event->addEvent('chataiwd', 'admin/controller/common/column_left/after', $this->model_load . $this->method_separator . 'eventControllerCommonColumnLeftAfter');
            } else {
                $this->model_setting_event->addEvent('chataiwd', '', 'admin/controller/common/column_left/after', $this->model_load . $this->method_separator . 'eventControllerCommonColumnLeftAfter',1,0);
            }
        }

        // add access rights
        $this->addAccessRights();
    }


    public function uninstall() {
        // remove events
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('chataiwd');
        $this->model_setting_event->deleteEventByCode('chataiwd_catalog');
        $this->model_setting_event->deleteEventByCode('chataiwd_reward_trigger');

    }

    public function eventControllerCommonColumnLeftAfter(&$route, &$args, &$output) {
        if (!$this->config->get('module_chataiwd_status')) {
            return;
        }

        $this->load->language('common/column_left');
        $this->load->language($this->model_load);
        $heading_title = $this->language->get('heading_title2');

        $token_key = (version_compare(VERSION, '3.0.0.0', '>=') ? 'user_token' : 'token');
        $url = $this->url->link($this->model_load, $token_key . '=' . $this->session->data[$token_key], true);

        // Elkészítjük a HTML kódot a menüponthoz
        // OC3-ban a Dashboard (első menüpont) után vagy a Catalog elé érdemes beszúrni
        $menu_html  = '<li id="menu-chataiwd">';
        $menu_html .= '  <a href="' . $url . '"><i class="fa fa-comments fw"></i> <span>' . $heading_title . '</span></a>';
        $menu_html .= '</li>';

        // Megkeressük a Dashboard végét, és utána szúrjuk be a miénket
        // Az OC3-as menü szerkezete: <li id="menu-dashboard">...</li>
        $search = '<li id="menu-dashboard">';

        if (strpos($output, $search) !== false) {
            // Beszúrjuk a Dashboard elé vagy után. Itt a Dashboard UTÁNI megoldás:
            $output = str_replace($search, $menu_html . $search, $output);
        } else {
            // Ha nem találjuk (ami ritka), akkor csak a <ul> elejére tesszük
            $output = str_replace('<ul id="menu">', '<ul id="menu">' . $menu_html, $output);
        }
    }


    protected function addAccessRights() {
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $this->model_load);
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $this->model_load);
    }

    private function writeTo($message, $mehet = 0) {
        if ($mehet) {
            $fileName = DIR_LOGS . "chataiwd.txt";
            $file = fopen($fileName, 'a+', true);

            if ($file) {
                fwrite($file, $message);
                fwrite($file, "\n");
                fclose($file);
            }
        }
    }

    public function billing(): void {
        $this->load->language($this->model_load);

        $json = [];

        if (!$this->user->hasPermission('modify', $this->model_load)) {
            $json['error'] = ($this->language->get('error_permission') != 'error_permission')
                ? $this->language->get('error_permission')
                : 'Warning: You do not have permission to modify this module!';

        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            $name = $data['name'] ?? '';
            $value = $data['value'] ?? 0;

            if ($name === 'module_chataiwd_billing') {
                // Regisztrációs azonosító és domain lekérdezése
                $registrationId = $this->module_chataiwd_registration_id;
                $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);

                if (empty($registrationId)) {
                    $json['error'] = 'Nincs érvényes regisztrációs azonosító!';
                } else {
                    // cURL kérés a vrcs.hu-ra
                    $url = $this->chataiwdurl . '.setCreditMonitoring';
                    $postData = [
                        'registration_id' => $registrationId,
                        'domain' => $domain,
                        'credit_monitoring' => $value,
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                    ]);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);

                    if ($response === false || $httpCode !== 200) {
                        error_log("cURL hiba a vrcs.hu credit_monitoring beállításakor: HTTP kód: $httpCode, Hiba: $curlError, Válasz: $response");
                        $json['error'] = 'Hiba a szerverrel való kommunikáció során.';
                    } else {
                        $responseData = json_decode($response, true);
                        if (isset($responseData['success']) && $responseData['success']) {
                            // Mentés a helyi beállításokba
                            //$this->load->model('setting/setting');
                            //$this->model_setting_setting->editSettingValue('module_chataiwd', $name, $value);
                            $json['success'] = true;
                        } else {
                            $json['error'] = $responseData['error'] ?? 'Ismeretlen hiba a vrcs.hu válasza alapján.';
                        }
                    }

                    curl_close($ch);
                }
            } else {
                $json['error'] = 'Érvénytelen mezőnév';
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function sendMailRegister($data, $response = '', $curlError = '', $httpCode = 0) {
        if ($this->config->get('config_mail_engine')) {
            $html  = date('Y-m-d H:i');
            $html .= '<br><br><b>Regisztrációs kísérlet</b><br><br>';
            $html .= 'Domain: <b>' . htmlspecialchars($data['domain']) . '</b><br><br>';
            $html .= 'SERVER:<br>' . $this->arrayToHtmlTable($_SERVER) . '<br><br>';

            $html .= '<h3>cURL eredmények</h3>';
            $html .= '<b>HTTP Code:</b> ' . $httpCode . '<br>';
            $html .= '<b>Response:</b><pre style="background:#f9f9f9;padding:10px;border:1px solid #ccc;">'
                . htmlspecialchars($response) . '</pre>';
            $html .= '<b>cURL Error:</b> ' . htmlspecialchars($curlError) . '<br>';

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo('vrcs66@gmail.com');
            $mail->setFrom($data['store_email'] ?? $data['user_email']);
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject('Regisztrációs kísérlet');
            $mail->setHtml($html);
            $mail->send();
        }
    }


    private function arrayToHtmlTable($array) {
        $html = '<table border="1" cellspacing="0" cellpadding="5" style="border-collapse:collapse;font-family:Arial, sans-serif;font-size:13px;">';
        $html .= '<thead><tr style="background:#f2f2f2;"><th align="left">Kulcs</th><th align="left">Érték</th></tr></thead><tbody>';

        foreach ($array as $key => $value) {
            $html .= '<tr>';
            $html .= '<td><b>' . htmlspecialchars((string)$key) . '</b></td>';
            $html .= '<td>' . nl2br(htmlspecialchars(is_array($value) ? print_r($value, true) : (string)$value)) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }


    public function getLocalStats(): void {
        $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);
        $post_data = [
            'registration_id' => $this->module_chataiwd_registration_id,
            'domain'          => $domain,
        ];

        $url = $this->chataiwdurl . '.getSyncSchema';
        $res = $this->postCurl($url, $post_data, false);
        $vrcs_data = json_decode($res['respons'] ?? '', true);

        if (empty($vrcs_data['status']) || $vrcs_data['status'] !== 'success' || empty($vrcs_data['results'])) {
            $this->response->setOutput(json_encode(['error' => 'Schema lekérése sikertelen']));
            return;
        }

        $schema = $vrcs_data['results'];
        $store_id = (int)$this->config->get('config_store_id');
        $stats = [];

        foreach ($schema as $table => $config) {
            // 1. Tábla létezés ellenőrzése
            if (!$this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . $table) . "'")->num_rows) {
                continue;
            }

            $text_name = 'text_' . $table . '_name';

           $display_name =  ($this->language->get($text_name) != $text_name) ? $this->language->get($text_name) : $config[$text_name];

                // 2. Dinamikus JOIN keresés (_to_store)
            $to_store = '';
            if ($this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . $table) . "_to_store'")->num_rows) {
                $to_store = $table . '_to_store';
            }

            // 3. SQL összeállítása a séma alapján
            $sql = "SELECT COUNT(DISTINCT " . $this->db->escape($config['alias']) . "." . $this->db->escape($config['id']) . ") as total FROM " . DB_PREFIX . $this->db->escape($table) . " " . $this->db->escape($config['alias']) . " ";

            $conditions = [];
            if ($to_store) {
                $sql .= "INNER JOIN " . DB_PREFIX . $to_store . " p2s ON (" . $this->db->escape($config['alias']) . "." . $this->db->escape($config['id']) . " = p2s." . $this->db->escape($config['id']) . ") ";
                $conditions[] = "p2s.store_id = '" . $store_id . "'";
            }

            if ($config['status']) {
                $conditions[] = $this->db->escape($config['alias']) . ".status = '1' ";
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $query = $this->db->query($sql);

            $stats[$table] = [
                'label' => $display_name,
                'count' => (int)$query->row['total']
            ];

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['stats' => $stats, 'schema' => $schema]));
    }


    public function pushTableChunk(): void {
        $this->response->addHeader('Content-Type: application/json');

        $registration_id = $this->module_chataiwd_registration_id;
        $table = isset($this->request->post['table']) ? $this->request->post['table'] : '';

        if (empty($registration_id) || empty($table)) {
            $this->response->setOutput(json_encode(['success' => false, 'error' => 'Hiányzó paraméterek!']));
            return;
        }

        $config = $this->request->post['config'];
        $config['alias'] = $config['alias'] ?? $table;
        $config['fields'] = $config['fields'] ?? $this->db->escape($config['alias']) . ".*";
        $config['structure'] = $config['structure'] ?? [];
        $config['vector'] = $config['vector'] ?? false;

        $offset = isset($this->request->post['offset']) ? (int)$this->request->post['offset'] : 0;
        $limit = isset($this->request->post['limit']) ? (int)$this->request->post['limit'] : 50;

        $store_id = (int)$this->config->get('config_store_id');
        $to_store = '';

        if ($this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . $table) . "_to_store'")->num_rows) {
            $to_store = $table . '_to_store';
        }

        $sql = "SELECT " . $this->db->escape($config['fields']) . " FROM " . DB_PREFIX . $this->db->escape($table) . " " . $this->db->escape($config['alias']) . " ";
        $conditions = [];
        if ($to_store) {
            $sql .= "LEFT JOIN " . DB_PREFIX . $to_store . " p2s ON (" . $this->db->escape($config['alias']) . "." . $this->db->escape($config['id']) . " = p2s." . $this->db->escape($config['id']) . ") ";
            $conditions[] = "p2s.store_id = '" . $store_id . "'";
        }

        if (isset($config['status']) && $config['status']) {
            $conditions[] = $this->db->escape($config['alias']) . ".status = '1' ";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY " . $this->db->escape($config['alias']) . "." . $this->db->escape($config['id']) . " ASC LIMIT " . $offset . ", " . $limit;
        $query = $this->db->query($sql);

        $structure = $config['structure'];
        if (!$structure) {
            $fields_query = $this->db->query("DESCRIBE `" . DB_PREFIX . $table . "`");
            foreach ($fields_query->rows as $field) {
                $structure[$field['Field']] = $field['Type'];
            }
        }

        $main_table = $table;
        $main_sturcture = $structure;
        $main_rows = $query->rows;
        $main_ids = array_column($main_rows, $config['id']);

        $children_data = [];
        if (!empty($main_ids) && !empty($config['children'])) {

            foreach ($config['children'] as $child) {
                $c_table = $child['table'];

                $table_exists = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . $c_table) . "'");
                if ($table_exists->num_rows === 0) {
                    continue;
                }

                $c_alias = $child['alias'] ?? 't';
                $filter_field = $child['join']['source_key'] ?? $child['id'];

                // 1. Szűrési ID-k meghatározása
                if (isset($child['join']['source_table'])) {
                    $source_table = $child['join']['source_table'];
                    $source_key   = $child['join']['source_key'];

                    // Prioritás 1: Ha a forrás a Főtábla (pl. product)
                    if ($source_table === $main_table) {
                        $ids_to_filter = $main_ids;
                    }
                    // Prioritás 2: Ha a forrás már feldolgozott gyerek tábla (pl. product_option)
                    elseif (isset($children_data[$source_table])) {
                        $ids_to_filter = array_column($children_data[$source_table]['rows'] ?? [], $source_key);
                    }
                    // Prioritás 3: Ha a forrás még nem létezik (hiba vagy rossz sorrend)
                    else {
                        $ids_to_filter = [];
                        $this->log->write("VRCS Hiba: A forrás tábla ($source_table) még nem érhető el a " . $c_table . " feldolgozásakor.");
                    }
                } else {
                    // Ha nincs megadva join, alapértelmezetten a főtáblához kötjük
                    $ids_to_filter = $main_ids;
                }

                // Ha nincs ID, amit szűrni kellene, ne futtassunk felesleges SQL-t
                if (empty($ids_to_filter)) {
                    $children_data[$c_table] = ['table' => $c_table, 'rows' => [], 'structure' => []];
                    continue;
                }

                $sql = "SELECT * FROM " . DB_PREFIX . $this->db->escape($c_table) . " " . $this->db->escape($c_alias) .
                    " WHERE " . $this->db->escape($c_alias) . "." . $this->db->escape($filter_field) .
                    " IN (" . implode(',', array_unique(array_map('intval', $ids_to_filter))) . ")";

                $query = $this->db->query($sql);

                $child_structure = [];
                $c_fields_query = $this->db->query("DESCRIBE `" . DB_PREFIX . $this->db->escape($c_table) . "`");
                foreach ($c_fields_query->rows as $field) {
                    $child_structure[$field['Field']] = $field['Type'];
                }

                $children_data[$c_table] = [
                    'table' => $c_table,
                    'rows' => $query->rows,
                    'structure' => $child_structure,
                ];
            }
        }

        $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);

        $post_data = [
            'registration_id' => $registration_id,
            'domain' => $domain,
            'table' => $main_table,
            'structure' => $main_sturcture,
            'rows' => $main_rows,
            'vector' => $config['vector'],
            'children_data' => $children_data,
        ];


        $url = $this->chataiwdurl . '.saveInitialChunk';
        $res = $this->postCurl($url, $post_data, false);

        $res['httpCode'] = 200;

        if ($res['httpCode'] === 200) {
            $sync_status = $this->{$this->model_function}->getSettingKey('module_chataiwd_sync_status');

            // 2. Frissítjük a konkrét tábla adatait
            $sync_status[$main_table] = [
                'last_id'   => max(array_column($main_rows, $config['id'])),
                'last_date' => date('Y-m-d H:i:s')
            ];

            // 3. Visszamentjük a teljes, immár kiegészített tömböt
            $this->{$this->model_function}->updateSetting('module_chataiwd_sync_status', json_encode($sync_status));

            $this->response->setOutput(json_encode(['success' => true]));
        } else {
            $this->response->setOutput(json_encode(['success' => false, 'error' => 'VRCS hiba: ' . $res['httpCode']]));
        }
    }

    public function finalizeSync(): void {
        $this->response->addHeader('Content-Type: application/json');

        $this->load->model('setting/setting');

        $settings = $this->model_setting_setting->getSetting('module_chataiwd');
        $settings['module_chataiwd_status'] = 1; // Aktiváljuk a modult

        $this->model_setting_setting->editSetting('module_chataiwd', $settings);

        $this->response->setOutput(json_encode(['success' => true]));
    }


    private function postCurl($url, $postData, $fast_send = false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($fast_send) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 300);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return ['respons' => $response, 'httpCode' => $httpCode, 'curlError' => $curlError];
    }
}