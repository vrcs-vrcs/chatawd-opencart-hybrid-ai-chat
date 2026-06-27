<?php
namespace Opencart\Catalog\Controller\Extension\chataiwd\Module;

class chataiwd extends \Opencart\System\Engine\Controller {

    protected $method_separator;
    private $chat_url = 'https://www.vrcs.hu/index.php?route=tool/vrcschatawd';
    private $module_chataiwd_prompt;
    private $module_chataiwd_package;
    private $module_chataiwd_color;
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
    private $module_chataiwd_tool_whatsapp;
    private $module_chataiwd_whatsapp_number;
    private $module_chataiwd_tool_faq;
    private $module_chataiwd_recovery;
    private $module_chataiwd_sync_status;
    private $callback_url;
    private $model_load = 'extension/chataiwd/module/chataiwd';
    private $model_function = 'model_extension_chataiwd_module_chataiwd';

    public function __construct(\Opencart\System\Engine\Registry $registry) {
        parent::__construct($registry);

        if (defined('CHATAIWD_URL')) $this->chat_url = CHATAIWD_URL;

        $this->method_separator = version_compare(VERSION, '4.0.0.0', '<')
            ? '/'
            : (version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|');

        $this->load->model($this->model_load);
        $this->load->language($this->model_load);

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

        $this->module_chataiwd_prompt                = $settingValues['module_chataiwd_prompt'] ?? '';
        $this->module_chataiwd_package               = $settingValues['module_chataiwd_package'] ?? '';
        $this->module_chataiwd_registration_id       = $settingValues['module_chataiwd_registration_id'] ?? '';
        $this->module_chataiwd_color                 = $settingValues['module_chataiwd_color'] ?? '#007bff';

        $language_id = (int)$this->config->get('config_language_id');
        $descriptions = $this->{$this->model_function}->getSettingsByLanguage($language_id);

        $this->load->language($this->model_load);

        $this->module_chat_button = ($descriptions['module_chat_button'] ?? null)
            ?? (($settingValues['module_chat_button'] ?? null) ?: (($this->language->get('text_chat_button_fallback') != 'text_chat_button_fallback')
                ? $this->language->get('text_chat_button_fallback') : 'ChatGpt Ask Now!'));

        $this->module_ai_response_header = ($descriptions['module_ai_response_header'] ?? null)
            ?? (($settingValues['module_ai_response_header'] ?? null) ?: (($this->language->get('text_ai_response_header_fallback') != 'text_ai_response_header_fallback')
                ? $this->language->get('text_ai_response_header_fallback') : 'AI Response'));

        $this->module_dispatcher_response_header = ($descriptions['module_dispatcher_response_header'] ?? null)
            ?? (($settingValues['module_dispatcher_response_header'] ?? null) ?: (($this->language->get('text_dispatcher_response_header_fallback') != 'text_dispatcher_response_header_fallback')
                ? $this->language->get('text_dispatcher_response_header_fallback') : 'Dispatcher Response'));

        $this->module_ai_response_indicator = ($descriptions['module_ai_response_indicator'] ?? null)
            ?? (($settingValues['module_ai_response_indicator'] ?? null) ?: (($this->language->get('text_ai_response_indicator_fallback') != 'text_ai_response_indicator_fallback')
                ? $this->language->get('text_ai_response_indicator_fallback') : 'AI is responding'));

        $this->module_dispatcher_response_indicator = ($descriptions['module_dispatcher_response_indicator'] ?? null)
            ?? (($settingValues['module_dispatcher_response_indicator'] ?? null) ?: (($this->language->get('text_dispatcher_response_indicator_fallback') != 'text_dispatcher_response_indicator_fallback')
                ? $this->language->get('text_dispatcher_response_indicator_fallback') : 'Dispatcher is responding'));

        $this->module_welcome_message = ($descriptions['module_welcome_message'] ?? null)
            ?? (($settingValues['module_welcome_message'] ?? null) ?: (($this->language->get('text_welcome_message_fallback') != 'text_welcome_message_fallback')
                ? $this->language->get('text_welcome_message_fallback') : 'Hi! How can I help you today?'));


        $this->module_chataiwd_tool_bell    = $settingValues['module_chataiwd_tool_bell']       ?? '1';
        $this->module_chataiwd_tool_voice   = $settingValues['module_chataiwd_tool_voice']      ?? '1';
        $this->module_chataiwd_tool_image   = $settingValues['module_chataiwd_tool_image']      ?? '1';
        $this->module_chataiwd_tool_emoji   = $settingValues['module_chataiwd_tool_emoji']      ?? '1';
        $this->module_chataiwd_tool_email   = $settingValues['module_chataiwd_tool_email']      ?? '1';
        $this->module_chataiwd_tool_faq     = $settingValues['module_chataiwd_tool_faq']        ?? '1';

        $this->module_chataiwd_whatsapp_number= preg_replace('/[^0-9]/','',$settingValues['module_chataiwd_whatsapp_number']   ?? '');
        $this->module_chataiwd_tool_whatsapp= $this->module_chataiwd_whatsapp_number ? ($settingValues['module_chataiwd_tool_whatsapp']  ?? 0) : 0;



        $this->module_chataiwd_recovery = $settingValues['module_chataiwd_recovery'] ?? '';

        if ($this->module_chataiwd_recovery) {
            $this->module_chataiwd_recovery = json_decode((string)$this->module_chataiwd_recovery, true);
        }

        if (!is_array($this->module_chataiwd_recovery)) {
            $this->module_chataiwd_recovery = [];
        }

        // ELŐFELDOLGOZÁS: Csak az aktívakat hagyjuk meg, kiszámoljuk az órákat és sorba rendezzük
        $processed_recovery = [];

        foreach ($this->module_chataiwd_recovery as $index => $row) {
            if (!empty($row['status']) && !empty($row['value']) && !empty($row['subject']) && !empty($row['content'])) {
                $total_hours = ($row['unit'] == 'days') ? (int)$row['value'] * 24 : (int)$row['value'];

                $processed_recovery[] = [
                    'mail_type_id'=> $index + 1, // Ez lesz a mail_type_X azonosítója
                    'delay_hours' => $total_hours,
                    'subject'     => $row['subject'],
                    'content'     => $row['content'],
                    'status'      => $row['status']
                ];
            }
        }

        usort($processed_recovery, function($a, $b) {
            return $b['delay_hours'] <=> $a['delay_hours'];
        });

        $this->module_chataiwd_recovery = $processed_recovery;



        $this->module_chataiwd_sync_status = $settingValues['module_chataiwd_sync_status'] ?? '';

        if ($this->module_chataiwd_sync_status) {
            $this->module_chataiwd_sync_status = json_decode((string)$this->module_chataiwd_sync_status, true);
        }

        if (!is_array($this->module_chataiwd_sync_status)) {
            $this->module_chataiwd_sync_status = [];
        }

        $this->callback_url = $this->url->link($this->model_load.$this->method_separator.'receiveResponse', 'language=' . $this->config->get('config_language'), true);
    }


    public function index(): string {
        $this->load->language($this->model_load);

        $prompt = $this->module_chataiwd_prompt;
        $data['prompt'] = empty($prompt) ? 'You are a helpful assistant in an online store answering questions about products.' : $prompt;

        $data['chat_color']                     = $this->module_chataiwd_color;
        $data['chat_button']                    = $this->module_chat_button;
        $data['ai_response_header']             = $this->module_ai_response_header;
        $data['dispatcher_response_header']     = $this->module_dispatcher_response_header;
        $data['ai_response_indicator']          = $this->module_ai_response_indicator;
        $data['dispatcher_response_indicator']  = $this->module_dispatcher_response_indicator;
        $data['welcome_message']                = $this->module_welcome_message;

        $data['tool_bell']      = (int)$this->module_chataiwd_tool_bell;
        $data['tool_voice']     = (int)$this->module_chataiwd_tool_voice;
        $data['tool_image']     = (int)$this->module_chataiwd_tool_image;
        $data['tool_emoji']     = (int)$this->module_chataiwd_tool_emoji;
        $data['tool_email']     = (int)$this->module_chataiwd_tool_email;
        $data['tool_faq']       = (int)$this->module_chataiwd_tool_faq;
        $data['tool_whatsapp']  = (int)$this->module_chataiwd_tool_whatsapp;
        $data['whatsapp_number']= $this->module_chataiwd_whatsapp_number;

        $lang_param = !empty($_GET['language']) ? '&language=' . $_GET['language'] : '';

        if (function_exists('oc_token')) {
            $upload_token = oc_token(32);
        } elseif (function_exists('token')) {
            $upload_token = token(32);
        } else {
            $upload_token = bin2hex(random_bytes(16)); // Végső biztonsági tartalék
        }

        $this->session->data['upload_token'] = $this->session->data['upload_token']  ?? $upload_token;

        // belépési pont: receiveResponse

        // Chat ablak AJAX URL
        $data['send_message_url']           = $this->url->link($this->model_load . $this->method_separator . 'sendMessage' . $lang_param, '', true); // azonnali üznet küldés
        $data['check_response_url']         = $this->url->link($this->model_load . $this->method_separator . 'checkForResponse' . $lang_param, '', true); // válaszok lekérése 1mp
        $data['sync_context_url']           = $this->url->link($this->model_load . $this->method_separator . 'syncChatContext' . $lang_param, '', true); // 10mp-enként kosár és egyéb infók köldése vrcs-re
        $data['share_cart_url']             = $this->url->link($this->model_load . $this->method_separator . 'shareCart' . $lang_param, '', true); // kosás megosztása - email küldés
        $data['load_history_url']           = $this->url->link($this->model_load . $this->method_separator . 'loadChatHistory' . $lang_param, '', true);
        $data['thumbnail_url']              = $this->url->link($this->model_load . $this->method_separator . 'generateThumbnail' . $lang_param, '', true);
        $data['delete_temp_url']            = $this->url->link($this->model_load . $this->method_separator . 'deleteTempFile' . $lang_param, '', true);
        $data['save_consent_url']           = $this->url->link($this->model_load . $this->method_separator . 'saveConsent' . $lang_param, '', true);

        // Auth és egyéb URL-ek
        $data['check_auth_url']             = $this->url->link($this->model_load . $this->method_separator . 'checkAuth' . $lang_param, '', true);
        $data['save_auth_url']              = $this->url->link($this->model_load . $this->method_separator . 'saveAuth' . $lang_param, '', true);
        $data['logout_auth_url']            = $this->url->link($this->model_load . $this->method_separator . 'logoutAuth' . $lang_param, '', true);
        $data['link_accounts_url']          = $this->url->link($this->model_load . $this->method_separator . 'linkAccounts' . $lang_param, '', true);
        $data['create_account_url']         = $this->url->link($this->model_load . $this->method_separator . 'createNewChatAccount' . $lang_param, '', true);
        $data['merge_history_url']          = $this->url->link($this->model_load . $this->method_separator . 'mergeAnonymousHistory' . $lang_param, '', true);
        $data['get_faq_url']                = $this->url->link($this->model_load . $this->method_separator . 'getFaqList' . $lang_param, '', true);
        $data['call_human_url']             = $this->url->link($this->model_load . $this->method_separator . 'callHuman' . $lang_param, '', true);
        $data['product_info_url']           = $this->url->link($this->model_load . $this->method_separator . 'productInfo' . $lang_param, '', true);
        $data['add_to_cart_url']            = $this->url->link($this->model_load . $this->method_separator . 'addToCart' . $lang_param, '', true);
        $data['apply_coupon_url']           = $this->url->link($this->model_load . $this->method_separator . 'applyCoupon' . $lang_param, '', true);
        $data['spin_wheel_url']             = $this->url->link($this->model_load . $this->method_separator . 'spinWheel' . $lang_param, '', true); // Szerencsekerék

        $data['file_upload']                = $this->url->link('tool/upload', 'upload_token=' . $this->session->data['upload_token'], true);

        $data['css_url'] = HTTP_SERVER . 'extension/chataiwd/catalog/view/stylesheet/chataiwd_styles.css?chat_color=' . urlencode($this->module_chataiwd_color);
        $data['js_url']  = HTTP_SERVER . 'extension/chataiwd/catalog/view/javascript/chataiwd_script.js';
        $data['css_product_url'] = HTTP_SERVER . 'extension/chataiwd/catalog/view/stylesheet/chataiwd_product.css';
        $data['js_product_url'] = HTTP_SERVER . 'extension/chataiwd/catalog/view/javascript/chataiwd_product.js';

        $data['customer_logged_in'] = false;
        $data['customer_name'] = '';

        if ($this->customer->isLogged()) {
            $data['customer_logged_in'] = true;
            $data['customer_name'] = $this->customer->getFirstName();
            $data['customer_last_name'] = $this->customer->getLastName();
        }

        $base_img_path = strstr($this->chat_url, '/index.php', true);

        if ($base_img_path === false) {
            $base_img_path = $this->chat_url;
        }
        $data['human_img']  = $base_img_path . '/image/human.jpg';
        $data['ai_img']     = $base_img_path . '/image/ai.png';
        $data['loader_gif'] = $base_img_path . '/image/betolt.gif';
        $data['pdf_icon'] = $base_img_path . '/image/pdf_icon.png';

        $data['registration_id']    = $this->module_chataiwd_registration_id;
        $active_faqs = array_filter($this->module_chataiwd_faq, function($faq) {
            return isset($faq['status']) && (int)$faq['status'] === 1;
        });
        $data['has_active_faq'] = !empty($active_faqs);

        $data['config'] = [
            // Alapadatok
            'registration_id'       => $data['registration_id'],
            'chat_color'            => $data['chat_color'],
            'nonce'                 => '', // WP kompatibilitás miatt
            'method_separator'      => $this->method_separator,

            // Core AJAX URL-ek
            'send_message_url'      => $data['send_message_url'],
            'check_response_url'    => $data['check_response_url'],
            'share_cart_url'        => $data['share_cart_url'],
            'load_history_url'      => $data['load_history_url'],
            'thumbnail_url'         => $data['thumbnail_url'],
            'delete_temp_url'       => $data['delete_temp_url'],
            'save_consent_url'      => $data['save_consent_url'],

            // Kiterjesztett funkciók URL-jei
            'get_faq_url'           => $data['get_faq_url'],
            'call_human_url'        => $data['call_human_url'],
            'merge_history_url'     => $data['merge_history_url'],
            'spin_wheel_url'        => $data['spin_wheel_url'],

            // Auth / Felhasználói URL-ek
            'check_auth_url'        => $data['check_auth_url'],
            'save_auth_url'         => $data['save_auth_url'],
            'logout_auth_url'       => $data['logout_auth_url'],
            'link_accounts_url'     => $data['link_accounts_url'],
            'create_account_url'    => $data['create_account_url'],
            'sync_context_url'      => $data['sync_context_url'],
            'product_info_url'      => $data['product_info_url'],
            'add_to_cart_url'       => $data['add_to_cart_url'],
            'apply_coupon_url'      => $data['apply_coupon_url'],
            'file_upload'           => $data['file_upload'],

            // UI Szövegek és Indikátorok
            'heading_ai'            => $data['ai_response_header'],
            'heading_dispatcher'    => $data['dispatcher_response_header'],
            'indicator_ai'          => $data['ai_response_indicator'],
            'indicator_dispatcher'  => $data['dispatcher_response_indicator'],
            'text_waiting'          => $this->language->get('text_waiting_for_human'),
            'text_error'            => $this->language->get('text_error_server'),
            'text_logout'           => $this->language->get('text_logout'),
            'text_logged_in'        => $this->language->get('text_logged_in'),
            'text_send_share'       => $this->language->get('text_send_share'),
            'text_sending'          => $this->language->get('text_sending'),
            'text_send'             => $this->language->get('text_send'),
            'text_share_success_title'  => $this->language->get('text_share_success_title'),
            'text_share_success_detail' => $this->language->get('text_share_success_detail'),
            'error_upload_size'         => $this->language->get('error_upload_size'),
            'error_max_size'            => $this->language->get('error_max_size'),

            'text_mb'                   => ($this->language->get('text_mb') != 'text_mb') ? $this->language->get('text_mb') : 'MB',
            'text_share_cart_title'     => ($this->language->get('text_share_cart_title') != 'text_share_cart_title') ? $this->language->get('text_share_cart_title') : 'Can I help you decide?',
            'text_share_cart_desc'      => ($this->language->get('text_share_cart_desc') != 'text_share_cart_desc') ? $this->language->get('text_share_cart_desc') : 'I see you\'ve picked out some great products! Would you like to ask a friend for their opinion? Send them the contents of your cart with one click.',
            'text_share_cart_header'    => ($this->language->get('text_share_cart_header') != 'text_share_cart_header') ? $this->language->get('text_share_cart_header') : 'Share cart via email',

            'error_technical'           => ($this->language->get('error_technical') != 'error_technical')                       ? $this->language->get('error_technical') : 'Technical error: Invalid registration.',
            'error_report'              => ($this->language->get('error_report') != 'error_report')                             ? $this->language->get('error_report') : 'Please report this error to our customer service by clicking one of the icons below!',
            'text_notified_dispatcher'  => ($this->language->get('text_notified_dispatcher') != 'text_notified_dispatcher')     ? $this->language->get('text_notified_dispatcher') : 'I have notified the dispatcher, please wait a moment while one of our staff members connects...',
            'text_enable_microphone'    => ($this->language->get('text_enable_microphone') != 'text_enable_microphone')         ? $this->language->get('text_enable_microphone') : 'Please enable microphone in your browser!',
            'text_file_attached'        => ($this->language->get('text_file_attached') != 'text_file_attached')                 ? $this->language->get('text_file_attached') : 'File attached:',
            'text_message_sent'         => ($this->language->get('text_message_sent') != 'text_message_sent')                   ? $this->language->get('text_message_sent') : 'Thank you! Your message has been sent.',
            'text_name_short'           => ($this->language->get('text_name_short') != 'text_name_short')                       ? $this->language->get('text_name_short') : 'Name too short',
            'error_invalid_mail'        => ($this->language->get('error_invalid_mail') != 'error_invalid_mail')                 ? $this->language->get('error_invalid_mail') : 'Invalid email address!',
            'error_message_short'       => ($this->language->get('error_message_short') != 'error_message_short')               ? $this->language->get('error_message_short') : 'The message is too short (min. 10 characters)!',
            'error_enter_name'          => ($this->language->get('error_enter_name') != 'error_enter_name')                     ? $this->language->get('error_enter_name') : 'Please enter your name (min. 3 characters)!',
            'text_thank_you_patience'   => ($this->language->get('text_thank_you_patience') != 'text_thank_you_patience')       ? $this->language->get('text_thank_you_patience') : 'Thank you for your patience! Your information and message have been successfully transmitted. You will be contacted soon at the email address provided.!',
            'text_document_uploaded'    => ($this->language->get('text_document_uploaded') != 'text_document_uploaded')         ? $this->language->get('text_document_uploaded') : 'The document has been uploaded to the dispatcher.',
            'text_operator_online'      => ($this->language->get('text_operator_online') != 'text_operator_online')             ? $this->language->get('text_operator_online') : 'Operator online',
            'text_operator_offline'     => ($this->language->get('text_operator_offline') != 'text_operator_offline')           ? $this->language->get('text_operator_offline') : 'Operator offline',
            'text_interested_your'      => ($this->language->get('text_interested_your') != 'text_interested_your')             ? $this->language->get('text_interested_your') : 'Hi! I\'m interested in your products/services.',
            'text_recorded_respond'     => ($this->language->get('text_recorded_respond') != 'text_recorded_respond')           ? $this->language->get('text_recorded_respond') : 'Thank you!<br>We have recorded your message and will respond shortly.',
            'text_fill_all_fields'      => ($this->language->get('text_fill_all_fields') != 'text_fill_all_fields')             ? $this->language->get('text_fill_all_fields') : 'Fill in all fields!',
            'error_during_request'      => ($this->language->get('error_during_request') != 'error_during_request')              ? $this->language->get('error_during_request') : 'Error during request!',
            'error_server'              => ($this->language->get('error_server') != 'error_server')                             ? $this->language->get('error_server') : 'Server error.',
            'text_nothing_save'         => ($this->language->get('text_nothing_save') != 'text_nothing_save')                   ? $this->language->get('text_nothing_save') : 'There is nothing to save.',
            'error_occurred'            => ($this->language->get('error_occurred') != 'error_occurred')                         ? $this->language->get('error_occurred') : 'An error occurred: ',
            'error_while_saving'        => ($this->language->get('error_while_saving') != 'error_while_saving')                 ? $this->language->get('error_while_saving') : 'Server-side error while saving.',
            'error_fault'               => ($this->language->get('error_fault') != 'error_fault')                               ? $this->language->get('error_fault') : 'Fault: ',
            'error_sending_share_email' => ($this->language->get('error_sending_share_email') != 'error_sending_share_email')   ? $this->language->get('error_sending_share_email') : 'Error sending share email.',
            'error_unknown'             => ($this->language->get('error_unknown') != 'error_unknown')                           ? $this->language->get('error_unknown') : 'Unknown error',
            'error_service_unavailable' => ($this->language->get('error_service_unavailable') != 'error_service_unavailable')   ? $this->language->get('error_service_unavailable') : 'The service is temporarily unavailable...',
            'text_system_message'       => ($this->language->get('text_system_message') != 'text_system_message')   ? $this->language->get('text_system_message') : 'The service is temporarily unavailable...',


            'text_success'                => $this->language->get('text_success'),
            'text_default_dispatcher'     => $this->language->get('text_default_dispatcher') ?: 'Admin',
            'text_history_merge_success'  => $this->language->get('text_history_merge_success'),


            // Grafikai elemek
            'human_img'             => $data['human_img'],
            'ai_img'                => $data['ai_img'],
            'loader_gif'            => $data['loader_gif'],
            'pdf_icon'              => $data['pdf_icon'],

            // Eszköztár (Tools) kapcsolók
            'tool_bell'          => $data['tool_bell'],
            'tool_voice'         => $data['tool_voice'],
            'tool_image'         => $data['tool_image'],
            'tool_emoji'         => $data['tool_emoji'],
            'tool_email'         => $data['tool_email'],
            'tool_faq'           => $data['tool_faq'],
            'tool_whatsapp'      => $data['tool_whatsapp'],
            'whatsapp_number'    => $data['whatsapp_number'],

            'vrcs_recovered_mode' => !empty($this->session->data['vrcs_recovered_token']),


            'send_email_url'          => $this->url->link('extension/chataiwd/module/chataiwd|sendEmail', '', true),
            'platform'                => 'opencart', // vagy 'wordpress' környezettől függően
            'text_email_will_back'    => $this->language->get('text_email_will_back'),

            'text_applied'            => $this->language->get('text_applied'),
            'text_wheel_spinning'     => $this->language->get('text_wheel_spinning'),
            'text_wheel_spin_btn'     => $this->language->get('text_wheel_spin_btn')

            ];

        return $this->load->view($this->model_load, $data);
    }

    public function sendMessage(): void {
        $this->load->language($this->model_load);
        $this->load->model('localisation/language');
        $this->load->model($this->model_load);

        $json = [];

        $prompt = $this->module_chataiwd_prompt;
        $prompt = empty($prompt) ? 'You are a helpful assistant in an online store answering questions about products.' : $prompt;

        $userMessage = $this->request->post['message'] ?? '';
        if (!empty($userMessage)) {
            $userMessage = str_replace("\r\n",'<br>',$userMessage);
            $userMessage = str_replace("\n",'<br>',$userMessage);
        }
        $attachment_data = $this->handleAttachment($_FILES);
        if (isset($attachment_data['error'])) {
            $json['error'] = $attachment_data['error'];
            $json['error_send'] = '';
            $json['error_service_unavailable'] = '';
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }

        if (empty($userMessage) && !$attachment_data) {
            $json['error'] = $this->language->get('error_no_registration');
            $json['error_send'] = $this->language->get('error_no_registration_send');
            $json['error_service_unavailable'] = $this->language->get('error_service_unavailable');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }

        $registration_id = $this->module_chataiwd_registration_id;
        if (empty($registration_id)) {
            $json['error'] = $this->language->get('error_no_registration');
            $json['error_send'] = $this->language->get('error_no_registration_send');
            $json['error_service_unavailable'] = $this->language->get('error_service_unavailable');
            $json['error_registration'] = 1;
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }

        $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);
        if (!$domain) {
            $json['error'] = $this->language->get('error_no_registration');
            $json['error_send'] = $this->language->get('error_no_registration_send');
            $json['error_service_unavailable'] = $this->language->get('error_service_unavailable');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }
        $session_id = $this->request->post['session_id'] ?? '';
        $chat_user = null;

        if ($session_id) {
            $chat_user = $this->{$this->model_function}->getChatUserBySessionId($session_id);
        }

        $languageCode = $this->config->get('config_language');
        $languageInfo = $this->{$this->model_function}->getLanguageByCode($languageCode);
        $languageName = $languageInfo['name'] ?? 'English';
        $callback_url = $this->url->link($this->model_load.$this->method_separator.'receiveResponse', 'language=' . $this->config->get('config_language'), true);

        $customer = $this->getCustomer($chat_user);
        $chat_user_id = $customer['chat_user_id'];

        $analysisUrl = $this->chat_url.'.analyzeQuestion';
        $analysisPostData = [
            'domain' => $domain,
            'chat_user_id'    => $chat_user_id,
            'registration_id' => $registration_id,
            'session_id' => $session_id,
            'userMessage' => $userMessage,
            'languageName' => $languageName,
            'callback_url' => $callback_url,
            'web_type' => 'OC',
            'customer' => $customer,
            'language_id' => (int)$this->config->get('config_language_id'),
        ];

        $fileData = null;
        if ($attachment_data) {
            $filePath = DIR_UPLOAD . $attachment_data['path'];
            $fileContent = file_get_contents($filePath);
            $base64File = base64_encode($fileContent);
            $analysisPostData['attachment'] = [
                'filename' => $attachment_data['filename'],
                'mime_type' => $attachment_data['mime_type'],
                'content' => $base64File,
                'attachment_thumb' => $this->request->post['attachment_thumb'] ?? '',
            ];
        }

        $attachment_thumb = $this->request->post['attachment_thumb'] ?? '';

        if ($attachment_thumb) {
            $this->load->model('tool/image');
            $this->load->model($this->model_load);

            $height = $this->{$this->model_function}->getProportionalHeight(basename($attachment_thumb), 160);
            $thumbnail_url = $this->model_tool_image->resize(basename($attachment_thumb), 160, $height);
            $attachment_thumb = $thumbnail_url;
        }

        $response = $this->postCurl($analysisUrl, $analysisPostData);
        $analysisResponse = $response['respons'];
        $httpCode = $response['httpCode'];

        if ($analysisResponse === false || $httpCode !== 200) {
            $json['error'] = $this->language->get('error_api_failed');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }

        $analysisData = json_decode($analysisResponse, true);
        if (!isset($analysisData['success']) || !$analysisData['success'] || (empty($analysisData['analysis']) && empty($analysisData['human']) ) ) {
            $json['error'] = (($analysisData['error'] ?? null) == 'error_kredit' ) ? $this->language->get('error_kredit') : $this->language->get('error_api_failed');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }
        $message_id = $analysisData['message_id'] ?? 0;
        $ai_human = $analysisData['human'] ? 1 : 0; // ai -0 human - 1

        $attachment_filename = (isset($attachment_data['filename']) ? $attachment_data['filename'] : '');

        $request_data = [
            'message_id' => $message_id,
            'session_id' => $session_id,
            'registration_id' => $registration_id,
            'chat_user_id' => $chat_user_id,
            'status' => 1, // 1: kérdés - 2: üzenet diszpécsertől - 4: kérdés/válasz AI-val
            'callback_url' => $callback_url,
            'question' => $userMessage,
            'answer' => '',
            'ai_human' => $ai_human,
            'attachment_thumb' => $attachment_thumb,
            'attachment_filename' => $attachment_filename,
        ];

        if (!empty($analysisData['human'])) {
            if ($message_id) {
                $this->{$this->model_function}->createChatRequest($request_data);
            }
            $json['success'] = true;
            $json['human'] = true;
            $json['message'] = $this->language->get('text_waiting_for_human') ?: 'Your question has been saved, waiting for human response.';
            $json['message_id'] = $analysisData['message_id'] ?? 0;
            $json['attachment'] = $analysisData['attachment'] ?? false;
            $json['attachment_thumb'] = $attachment_thumb;
            $json['attachment_filename'] = $attachment_filename;

            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;

        } elseif (!empty($analysisData['attachment']) && empty($analysisPostData['userMessage'])) {
            if ($message_id) {
                $this->{$this->model_function}->createChatRequest($request_data);
            }
            $json['success'] = true;
            $json['human'] = false;
            $json['message_id'] = $message_id;
            $json['attachment'] = true;
            $json['attachment_thumb'] = $attachment_thumb;
            $json['attachment_filename'] = $attachment_filename;

            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }

        $analysisResult = json_decode($analysisData['analysis'], true);
        if (empty($analysisResult) || !isset($analysisResult['keywords']) || !isset($analysisResult['intent'])) {
            $analysisResult = $this->parseAnalysisResponseManually($analysisData['analysis'], $userMessage);
            if (empty($analysisResult) || !isset($analysisResult['keywords']) || !isset($analysisResult['intent'])) {
                $json['error'] = $this->language->get('error_invalid_analysis');
                $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
                $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                return;
            }
        }

        $keywords = $analysisResult['keywords'];
        $intent = $analysisResult['intent'];
        $attributes = $analysisResult['attributes'];
        $query_vector = $analysisData['query_vector'] ?? [];

        $storeData = $this->load->controller($this->model_load.'_search',[
            'keywords' => $keywords,
            'intent' => $intent,
            'attributes' => $attributes,
            'query_vector'     => $query_vector,
            'method_separator' => $this->method_separator]);

        $url = $this->chat_url.'.generateResponse';
        $postData = [
            'registration_id' => $registration_id,
            'chat_user_id'  => $chat_user_id,
            'domain' => $domain,
            'storeData' => mb_convert_encoding($storeData, 'UTF-8', 'auto'),
            'userMessage' => $userMessage,
            'prompt' => $prompt,
            'session_id' => $session_id,
            'intent' => $intent,
            'keywords' => $keywords,
            'attributes' => $attributes,
            'request_token' => $analysisData['request_token'],
            'customer' => $customer,
        ];

        $response = $this->postCurl($url, $postData);
        $httpCode = $response['httpCode'];
        $curlError = $response['curlError'];
        $response = $response['respons'];

        if ($response === false || $httpCode !== 200) {
            $json['error'] = $this->language->get('error_api_failed');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }

        $responseData = json_decode($response, true);
        if (!isset($responseData['success']) || !$responseData['success'] || empty($responseData['message'])) {
            $json['error'] = $responseData['error'] ?? $this->language->get('error_api_failed');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }

        $response = $responseData['message'];
        $answer = $this->convertMarkdownLinksToHtml($response);

        $request_data['status'] = 4; // 1: kérdés - 2: üzenet diszpécsertől - 4: kérdés/válasz AI-val
        $request_data['answer'] = $answer;
        $this->{$this->model_function}->createChatRequest($request_data);

        $json['success'] = true;
        $json['message'] = $answer;
        $json['message_id'] = $message_id;
        $json['attachment'] = $analysisData['attachment'] ?? false;
        $json['attachment_thumb'] = $attachment_thumb;
        $json['attachment_filename'] = $attachment_filename;

        $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
        $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function getCustomer($chat_user) {
        $customer = [];
        $customer['customer_id'] = (int)$this->customer->getId();
        $customer['chat_user_id'] = 0;

        if ($chat_user && !(int)$chat_user['is_logged_out']) {

            $customer['name'] = $chat_user['name'];
            $customer['email'] = $chat_user['email'];
            $customer['chat_user_id'] = $chat_user['chat_user_id'];

        } elseif ($this->customer->isLogged()) {
            $customer['name'] = trim($this->customer->getFirstName() . ' ' . $this->customer->getLastName());
            $customer['email'] = $this->customer->getEmail();
        }
        return $customer;
    }

    private function convertMarkdownLinksToHtml($response) {
        $pattern = '/\[(.*?)\]\((https?:\/\/[^\s)]+)\)/';
        $replacement = '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>';
        $message = preg_replace($pattern, $replacement, $response);

        return stripslashes(str_replace("\n", "<br>", $message));
    }

    private function parseAnalysisResponseManually(string $response, string $userMessage): array {
        $analysisData = [
            'keywords' => [],
            'intent' => '',
            'time_info' => '',
            'raw' => $userMessage
        ];

        // Sorokra bontjuk a választ
        $lines = explode("\n", trim($response));
        $isKeywordsArray = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            if ($line === '{' || $line === '}') {
                continue;
            }

            if (strpos($line, ':') !== false) {
                [$key, $value] = array_map('trim', explode(':', $line, 2));

                $value = trim($value, '"');

                if ($key === '"keywords"') {
                    if (strpos($value, '[') !== false) {
                        $isKeywordsArray = true;
                        $value = trim($value, '[]');
                        $keywords = array_map(function ($item) {
                            return trim($item, '"');
                        }, explode(',', $value));
                        $analysisData['keywords'] = array_filter($keywords);
                    }
                } elseif ($key === '"intent"') {
                    $analysisData['intent'] = $value;
                } elseif ($key === '"time_info"') {
                    $analysisData['time_info'] = $value;
                } elseif ($key === '"raw"') {
                    $analysisData['raw'] = $value;
                }
            } elseif ($isKeywordsArray && strpos($line, ']') === false) {
                // Ha a keywords tömb még nem zárult le, és ez egy tömbelem
                $value = trim($line, ',"');
                if (!empty($value)) {
                    $analysisData['keywords'][] = $value;
                }
            }
        }

        if (empty($analysisData['intent'])) {
            $analysisData['intent'] = 'product search'; // Alapértelmezett szándék
        }

        if (empty($analysisData['keywords'])) {
            $words = explode(' ', trim($userMessage));
            $analysisData['keywords'] = array_filter($words, function ($word) {
                return strlen($word) > 2; // Csak a 2 karakternél hosszabb szavakat vesszük figyelembe
            });
        }

        return $analysisData;
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

    public function checkForResponse(): void {

        try {
            $json = ['success' => false];

            $this->load->language($this->model_load);
            $this->load->model($this->model_load);


            if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
                $json['error'] = $this->language->get('error_method_not_allowed');
                $this->response->addHeader('HTTP/1.1 405 Method Not Allowed');
                return;
            }

            $postData = $this->request->post;
            if (!isset($postData['session_id']) || !isset($postData['registration_id'])) {
                $json['error'] = $this->language->get('error_missing_field');
                $this->response->addHeader('HTTP/1.1 400 Bad Request');
                return;
            }

            // Kosár állapotának ellenőrzése a Teaserhez
            $json['has_cart'] = false;
            $json['cart_count'] = 0;

            if ($this->cart->hasProducts()) {
                $json['has_cart'] = true;
                $json['cart_count'] = $this->cart->countProducts();
            }

            $is_customer_logged_in = $this->customer->isLogged();
            $has_pending_points = isset($this->session->data['vrcs_pending_points']);


            $session_id = $this->db->escape($postData['session_id']);
            $this->handleChatSession($session_id);

            $last_id = (int)($postData['last_id'] ?? 0);
            $chat_user_id = 0;

            $chat_user = $this->{$this->model_function}->getChatUserBySessionId($session_id);
            if ($chat_user && !(int)$chat_user['is_logged_out']) {
                $chat_user_id = $chat_user['chat_user_id'];
            }

            $open_requests = $this->{$this->model_function}->getNewChatRequests($session_id, $chat_user_id, $last_id);

            $json['dispatcher_is_typing'] = $this->{$this->model_function}->getDispatcherTypingStatus($session_id, $chat_user_id);
            $json['ai_human_status'] = $this->{$this->model_function}->getIsHumanMode($session_id, $chat_user_id);

            if (empty($open_requests)) {
                $json['success'] = true;
                $json['responses'] = [];
                return;
            }

            $last_message_id = $last_id;
            $responses = [];

            foreach ($open_requests as $request) {
                $last_message_id = $request['message_id'];

                if (!empty($request['answer']) || !empty($request['question']) || !empty($request['attachment_thumb']) ) {
                    $answer_text = $request['answer'] ?? '';

                    $product_ids = $this->findProductIdsInText($answer_text);

                    $product_htmls = [];
                    if (!empty($product_ids)) {
                        foreach ($product_ids as $p_id) {
                            $product_htmls[] = $this->renderProductCard($p_id);
                        }
                    }

                    if (strpos($answer_text, '<a ') !== false) {
                        $answer_text = str_replace('<a ', '<a class="chataiwd-detected-link" target="_blank" rel="noopener" ', $answer_text);
                    }

                    // --- JUTALOMPONT KÁRTYA ELLENŐRZÉSE A POLLINGBAN ---
                    if (strpos($answer_text, 'vrcs-reward-card') !== false) {
                        preg_match('/(\d+)\s*<span[^>]*>pont/', $answer_text, $matches);
                        $points = isset($matches[1]) ? $matches[1] : '';

                        if (strpos($answer_text, 'fa-hourglass-half') !== false) {
                            // A: EZ EGY ÍGÉRET KÁRTYA (Homokórás)
                            // Vendégként és regisztráltként is érintetlenül hagyjuk, nem kell rá login gomb!
                            $text_fallback = "🎁 New offer: " . $points . " reward points are waiting for you!";
                            $teaser = ($this->language->get('text_teaser_reward_promise') != 'text_teaser_reward_promise')
                                ? sprintf($this->language->get('text_teaser_reward_promise'), $points)
                                : $text_fallback;

                        } else {
                            // B: EZ EGY AZONNALI JUTALOM KÁRTYA (Nincs homokóra)

                            // Ha a háttérben (receiveResponse) vendégként detektáltuk és bekerült a sessionbe
                            if ($request['pending_points']) {
                                // Dinamikusan rátesszük a login gombot a kimenetre, az adatbázis tiszta marad

                                $answer_text = $this->loginToReward($answer_text, false);

                                $text_fallback = "🎁 " . $points . " reward points are waiting for you! Log in to claim.";
                                $teaser = ($this->language->get('text_teaser_reward_guest') != 'text_teaser_reward_guest')
                                    ? sprintf($this->language->get('text_teaser_reward_guest'), $points)
                                    : $text_fallback;
                            } else {
                                // Regisztrált tag volt a fogadáskor, vagy időközben belépett és a sync már jóváírta
                                $text_fallback = "🌟 Congratulations! We have credited " . $points . " points to your account!";
                                $teaser = ($this->language->get('text_teaser_reward_customer') != 'text_teaser_reward_customer')
                                    ? sprintf($this->language->get('text_teaser_reward_customer'), $points)
                                    : $text_fallback;
                            }
                        }

                    } elseif (strpos($answer_text, 'vrcs-coupon-card') !== false) {
                        $teaser = ($this->language->get('text_teaser_coupon') != 'text_teaser_coupon')
                            ? $this->language->get('text_teaser_coupon')
                            : '🎫 You received an exclusive discount coupon!';

                    } else {
                        // Ha sima szöveg, csak szedjük le a HTML tageket és nl2br-t a biztonság kedvéért
                        $teaser = strip_tags(html_entity_decode($answer_text));
                        if (mb_strlen($teaser) > 75) {
                            $teaser = mb_substr($teaser, 0, 72) . '...';
                        }
                    }

                    // --- SZERENCSEKERÉK ELLENŐRZÉS ÚJ KÉRÉSNÉL (MULTI-TAB VÉDELEM) ---
                    if (strpos($answer_text, 'vrcs-wheel-card') !== false) {
                        $claim_data = $this->{$this->model_function}->isCardClaimed($request['message_id'], 'wheel');

                        if (!empty($claim_data)) {
                            $card_data = [
                                'claimed_value' => !empty($claim_data['claimed_value']) ? $claim_data['claimed_value'] : '',
                                'date_added'    => !empty($claim_data['date_added']) ? $claim_data['date_added'] : ''
                            ];

                            // Itt is közvetlenül a sablon generálja le a HTML-t
                            $answer_text = $this->load->view('extension/chataiwd/module/chataiwd_wheel_card', $card_data);
                        } else {
                            $teaser = ($this->language->get('text_teaser_wheel') != 'text_teaser_wheel')
                                ? $this->language->get('text_teaser_wheel')
                                : '🎁 You got a lucky wheel! Spin it for a prize!';
                        }
                    }

                    $responses[] = [
                        'message_id' => $request['message_id'],
                        'answer'   => nl2br($answer_text),
                        'teaser'     => $teaser,
                        'product' => $product_htmls,
                        'question' => nl2br($request['question'] ?? ''),
                        'attachment_thumb' => $request['attachment_thumb'],
                        'attachment_filename' => $request['attachment_filename'],
                        'dispatcher' => $request['dispatcher'],
                    ];
                }
            }

            $json['success'] = true;
            $json['responses'] = $responses;
            $json['last_message_id'] = $last_message_id;

            if (rand(1, 50) === 1) {
                $this->{$this->model_function}->cleanExpiredChatRequests(600);
            }

        } catch (\Throwable $t) {
            $this->log->write('Chat Error: ' . $t->getMessage());
            $json['success'] = false;
            $json['error'] = 'Internal server error';

        } finally {

            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
    }

    private function loginToReward($answer_text='', $hidden=true) {
        $login_url = $this->url->link('account/login', '', true);

        // Adjunk hozzá egy extra osztályt (vrcs-reward-login-hidden), és alapból rejtsük el inline
        if ($hidden) {
            $login_button_html = '<div class="vrcs-reward-login-zone vrcs-reward-login-hidden" style="margin-top:10px; text-align:center; display: none;">';
        } else {
            $login_button_html = '<div class="vrcs-reward-login-zone" style="margin-top:10px; text-align:center;">';
        }

        $text_info = ($this->language->get('text_reward_login_info') != 'text_reward_login_info')
            ? $this->language->get('text_reward_login_info')
            : 'Reward points can only be credited to a registered account.';

        $text_btn = ($this->language->get('text_reward_login_btn') != 'text_reward_login_btn')
            ? $this->language->get('text_reward_login_btn')
            : 'Login for points';

        // A FIX MAGYARÁZÓ SZÖVEG A GOMB FELETT
        $login_button_html .= '  <div style="font-size:11px; color:#7f8c8d; margin-bottom:8px; line-height:1.4;">';
        $login_button_html .= '    <i class="fa fa-info-circle text-info"></i> ' . $text_info;
        $login_button_html .= '  </div>';

        $login_button_html .= '  <a href="' . $login_url . '" class="vrcs-claim-btn" style="padding:5px 10px; font-size:12px; font-weight:bold;">';
        $login_button_html .= '    <i class="fa fa-sign-in"></i> ' . $text_btn;
        $login_button_html .= '  </a>';
        $login_button_html .= '</div>';

        if (!empty($answer_text)) {
            return str_replace('<!--login-->', $login_button_html, $answer_text);
        } else {
            return $login_button_html;
        }
    }

    private function findProductIdsInText($text) { // Nevet többes számra váltottuk
        if (empty($text)) return [];

        $urls = [];

        // 1. HTML Linkek gyűjtése
        if (preg_match_all('/<a\s+[^>]*?href=["\'](https?:\/\/[^"\']+)["\']/i', $text, $matches)) {
            $urls = array_merge($urls, $matches[1]);
        }

        // 2. Markdown Linkek gyűjtése
        if (preg_match_all('/\[.*?\]\((https?:\/\/[^\s)]+)\)/i', $text, $matches)) {
            $urls = array_merge($urls, $matches[1]);
        }

        // 3. Nyers Linkek gyűjtése
        if (preg_match_all('/(https?:\/\/[^\s<"\'\)]+)/i', $text, $matches)) {
            $urls = array_merge($urls, $matches[1]);
        }

        if (empty($urls)) return [];

        $product_ids = [];
        $urls = array_unique($urls); // Ne dolgozzunk ugyanazzal a linkkel kétszer

        foreach ($urls as $url) {
            $url = html_entity_decode($url);
            $current_id = null;

            // A: Paraméteres eset
            $query_str = parse_url($url, PHP_URL_QUERY);
            if ($query_str) {
                parse_str($query_str, $params);
                if (isset($params['product_id'])) {
                    $current_id = (int)$params['product_id'];
                }
            }

            // B: SEO URL eset (ha az A nem talált semmit)
            if (!$current_id) {
                $path = parse_url($url, PHP_URL_PATH);
                if ($path) {
                    $parts = explode('/', trim($path, '/'));
                    $keyword = end($parts);

                    if ($keyword && $keyword != 'index.php') {
                        $this->load->model('design/seo_url');
                        $seo_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword);
                        if ($seo_info && isset($seo_info['key']) && $seo_info['key'] == 'product_id') {
                            $current_id = (int)$seo_info['value'];
                        }
                    }
                }
            }

            if ($current_id && !in_array($current_id, $product_ids)) {
                $product_ids[] = $current_id;
            }
        }

        return $product_ids;
    }

    private function renderProductCard(int $product_id): string {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $this->load->language('product/thumb');

        $result = $this->model_catalog_product->getProduct($product_id);
        if (!$result) return '';

        $width = $this->config->get('config_image_product_width') ?: 200;
        $height = $this->config->get('config_image_product_height') ?: 200;

        if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
            $image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $width, $height);
        } else {
            $image = $this->model_tool_image->resize('placeholder.png', $width, $height);
        }

        $price = false;
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            if ($result['price']) {
                $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            }
        }

        $special = false;
        if ((float)$result['special'] > 0) {
            $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
        }

        $tax = false;
        if ($this->config->get('config_tax')) {
            $tax = $this->currency->format((float)$result['special'] > 0 ? $result['special'] : $result['price'], $this->session->data['currency']);
        }

        $data = [
            'product_id'  => $result['product_id'],
            'thumb'       => $image,
            'name'        => $result['name'],
            'description' => mb_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, 80) . '..',
            'price'       => $price,
            'special'     => $special,
            'tax'         => $tax,
            'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
            'rating'      => $result['rating'],
            'review_status' => (int)$this->config->get('config_review_status'),
            'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
            'cart'        => $this->url->link('common/cart'.$this->method_separator.'info', 'language=' . $this->config->get('config_language'),true),
            'cart_add'    => $this->url->link('checkout/cart'.$this->method_separator.'add', 'language=' . $this->config->get('config_language')),
        ];


        $data['button_cart'] = $this->language->get('button_cart');
        $data['button_wishlist'] = $this->language->get('button_wishlist');
        $data['button_compare'] = $this->language->get('button_compare');
        $data['text_tax'] = $this->language->get('text_tax');

        $lang_param = !empty($_GET['language']) ? '&language=' . $_GET['language'] : '';
        $data['product_info_url']           = $this->url->link($this->model_load . $this->method_separator . 'productInfo' . $lang_param, '', true);

        return $this->load->view($this->model_load.'_product', $data);
    }

    public function receiveResponse(): void {
        $this->load->language($this->model_load);
        $this->load->model($this->model_load);

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->addHeader('HTTP/1.1 405 Method Not Allowed');
            $this->response->setOutput(json_encode(['success' => false, 'error' => 'Method not allowed']));
            return;
        }

        $postData = json_decode(file_get_contents('php://input'), true);

        if (!isset($postData['is_typing']) && (!isset($postData['message_id']) || !isset($postData['answer'])) && !isset($postData['action'])) {
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            $this->response->setOutput(json_encode(['success' => false, 'error' => 'Missing fields']));
            return;
        }

        $output = json_encode(['success' => true]);

        $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
        $this->response->addHeader('Connection: close');
        $this->response->addHeader('Content-Length: ' . strlen($output));
        $this->response->setOutput($output);

        $this->response->output();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        $session_id = $this->db->escape($postData['session_id']);
        $chat_user_id = (int)($postData['chat_user_id'] ?? 0);

        if (isset($postData['is_typing'])) {
            $is_typing = (int)$postData['is_typing'];

            $this->{$this->model_function}->updateDispatcherTypingStatus($session_id, $chat_user_id, $is_typing);

        } elseif (isset($postData['action']) && $postData['action'] == 'mode_status' )    {
            $this->{$this->model_function}->updateDispatcherModeStatus($session_id, $chat_user_id, $postData['mode']);

        } elseif (isset($postData['action']) && $postData['action'] == 'reward_points') {
            $this->request->post['customer_id'] = $postData['customer_id'] ?? 0;
            $this->request->post['points']      = $postData['points'] ?? 0;
            $this->request->post['order_id']    = $postData['order_id'] ?? 0;


            $text_reward_fallback = 'Chat reward points';
            $this->request->post['description'] = $postData['description']
                ?? (($this->language->get('text_chat_reward_points') != 'text_chat_reward_points') ? $this->language->get('text_chat_reward_points') : $text_reward_fallback);
            $this->applyRewardPoints();

        } else {
            $message_id = (int)$postData['message_id'];
            $answer = $postData['answer'];
            $dispatcher = $postData['dispatcher'] ?? '';
            $registration_id = $this->module_chataiwd_registration_id;
            $ai_human = $postData['status'] ?? 0;

            $reward_points = (int)($postData['reward_points'] ?? 0);
            $customer_id = (int)($postData['customer_id'] ?? 0);

            $text_reward_id_fallback = 'Chat reward points (#' . $message_id . ')';
            $description = ($this->language->get('text_chat_reward_points_id') != 'text_chat_reward_points_id')
                ? sprintf($this->language->get('text_chat_reward_points_id'), $message_id)
                : $text_reward_id_fallback;

            if ($reward_points && $customer_id) {
                $this->{$this->model_function}->addRewardPoints($customer_id, $description, $reward_points, 0);

            } elseif ($reward_points) {
                $pending_data = [
                    'points'      => $reward_points,
                    'description' => $description,
                    'order_id'    => 0
                ];

                $pending_points_json = json_encode($pending_data);
            }

            $this->{$this->model_function}->updateDispatcherTypingStatus($session_id, $chat_user_id, 0);

            if ($this->{$this->model_function}->getRequestsByMessageId($message_id)) {
                $this->{$this->model_function}->updateChatRequestAnswer($message_id, $answer, $ai_human);
            } else {
                $request_data = [
                    'message_id'        => $message_id,
                    'session_id'        => $session_id,
                    'registration_id'   => $registration_id,
                    'chat_user_id'      => $chat_user_id,
                    'status'            => 2,
                    'callback_url'      => '',
                    'dispatcher'        => $dispatcher,
                    'question'          => '',
                    'answer'            => $answer,
                    'ai_human'          => $ai_human,
                    'pending_points'    => !empty($pending_points_json) ? $pending_points_json : null
                ];
                $this->{$this->model_function}->createChatRequest($request_data);
            }
        }
    }

    private function handleAttachment($file): ?array {
        if (!isset($file['attachment']) || $file['attachment']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $max_file_size = 5 * 1024 * 1024; // 5 MB
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

        if ($file['attachment']['size'] > $max_file_size) {
            return ['error' => ($this->language->get('error_file_too_large') != 'error_file_too_large')
                ? $this->language->get('error_file_too_large')
                : 'The file size is too large (max. 5 MB)'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['attachment']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            return ['error' => ($this->language->get('error_invalid_file_type') != 'error_invalid_file_type')
                ? $this->language->get('error_invalid_file_type')
                : 'Invalid file type. Only images (JPEG, PNG, GIF) and PDFs are allowed.'];
        }

        $temp_dir = DIR_UPLOAD . 'temp_attachments/';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }

        $original_filename = $file['attachment']['name'];
        $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
        $base_filename = pathinfo($original_filename, PATHINFO_FILENAME);
        $temp_destination = $temp_dir . $original_filename;

        $counter = 1;
        while (file_exists($temp_destination)) {
            $new_filename = $base_filename . '_' . $counter . '.' . $file_extension;
            $temp_destination = $temp_dir . $new_filename;
            $counter++;
        }

        if (move_uploaded_file($file['attachment']['tmp_name'], $temp_destination)) {
            return [
                'path' => 'temp_attachments/' . basename($temp_destination),
                'filename' => basename($temp_destination), // Az eredeti vagy egyedi fájlnév
                'mime_type' => $mime_type
            ];
        }

        return ['error' => ($this->language->get('error_tmp_upload_failed') != 'error_tmp_upload_failed')
            ? $this->language->get('error_tmp_upload_failed')
            : 'Error temporarily uploading file'];
    }



    public function loadChatHistory() {
        $chat_user_id = 0;
        $session_id = $this->request->post['session_id'] ?? '';
        $this->handleChatSession($session_id);

        $this->load->model($this->model_load);
        $this->load->language($this->model_load);

        if (empty($this->request->post['anonim'])) {
            $chat_user = $this->{$this->model_function}->getChatUserBySessionId($session_id);

            if ($chat_user && !(int)$chat_user['is_logged_out']) {
                $chat_user_id = $chat_user['chat_user_id'];
            }
        }

        $url = $this->chat_url . '.loadChatHistory';
        $postData = [
            'registration_id' => $this->request->post['registration_id'],
            'session_id' => $this->request->post['session_id'],
            'domain' => parse_url(HTTP_SERVER, PHP_URL_HOST),
            'chat_user_id' =>  $chat_user_id,
            'limit'     => isset($this->request->post['limit']) ? (int)$this->request->post['limit'] : 20,
            'oldest_id' => !empty($this->request->post['oldest_id']) ? (int)$this->request->post['oldest_id'] : null,
        ];

        $response = $this->postCurl($url, $postData);
        $response = $response['respons'];

        $response = json_decode($response,true);

        if (empty($response['success'])) {
            $response['error'] = $this->language->get('error_no_registration');
            $response['error_send'] = $this->language->get('error_no_registration_send');
            $response['error_service_unavailable'] = $this->language->get('error_service_unavailable');
            $response['last_message_id'] = $this->{$this->model_function}->getLastMessageId($session_id, $chat_user_id);
            $response['history'] = [];

        } else {
            if (isset($response['is_human_mode'])) {
                $this->{$this->model_function}->updateDispatcherModeStatus(
                    $session_id,
                    $chat_user_id,
                    $response['is_human_mode']
                );
            }

            if (!empty($response['history'])) {
                $this->load->model('tool/image');
                $this->load->model($this->model_load);

                foreach ($response['history'] as &$history) {

                    $product_htmls = [];
                    $answer_text = $history['answer'] ?? '';
                    $message_id = (int)($history['id'] ?? 0); // OpenCart history-ban az ID a message_id

                    $product_ids = $this->findProductIdsInText($answer_text);

                    if (!empty($product_ids)) {
                        foreach ($product_ids as $p_id) {
                            $product_htmls[] = $this->renderProductCard($p_id);
                        }
                    }
                    $history['product'] = $product_htmls;

                    if (strpos($answer_text, '<a ') !== false) {
                        $history['answer'] = str_replace('<a ', '<a class="chataiwd-detected-link" target="_blank" rel="noopener" ', $answer_text);
                    }

                    // --- SZERENCSEKERÉK HISTORY VÉDELEM ---

                    if (strpos($answer_text, 'vrcs-wheel-card') !== false && $message_id) {
                        $claim_data = $this->{$this->model_function}->isCardClaimed($message_id, 'wheel');

                        if (!empty($claim_data)) {
                            $prizes = $this->parsePrizesFromHtml($answer_text);
                            if (!empty($prizes)) {
                                $total_slices = count($prizes);
                                $winning_slice = 1;
                                $search_term = !empty($claim_data['name']) ? $claim_data['name'] : '';

                                if ($search_term) {
                                    foreach ($prizes as $prize) {
                                        if (strpos($prize['label'], $search_term) !== false) {
                                            $winning_slice = $prize['id'];
                                            break;
                                        }
                                    }
                                }
                                $local_request = $this->{$this->model_function}->getRequestsByMessageId($message_id);
                                $is_pending = false;

                                if (!empty($local_request['pending_points'])) {
                                    $pending_json = json_decode($local_request['pending_points'], true);
                                    if (!empty($pending_json['points'])) {
                                        $is_pending = true;
                                    }
                                }

                                $login = $this->loginToReward('',false);

                                $card_data = [
                                    'prizes'        => $prizes,
                                    'total_slices'  => $total_slices,
                                    'winning_slice' => $winning_slice,
                                    'prize_label'   => $claim_data['label'],          // A diszpécser által generált pontos név
                                    'prize_value'   => $claim_data['reward_points'], // A tiszta pontérték (ha az volt)
                                    'login'         => $login,
                                    'date_added'    => $claim_data['date_added'],
                                    'claimed_value' => !empty($claim_data['claimed_value']) ? $claim_data['claimed_value'] : '',
                                ];

                                if ($is_pending && !$this->customer->isLogged()) {
                                    $history['answer'] = $this->load->view($this->model_load . '_wheel_card_pending', $card_data);
                                } else {
                                    $history['answer'] = $this->load->view($this->model_load . '_wheel_card', $card_data);
                                }
                            } else {
                                $history['answer'] = $answer_text;
                            }
                        }
                    }


                    // --- JUTALOMPONT HISTORY VÉDELEM (ÚJ) ---
                    if (strpos($answer_text, 'vrcs-reward-card') !== false && $message_id) {
                        preg_match('/(\d+)\s*<span[^>]*>pont/', $answer_text, $matches);
                        $points = isset($matches[1]) ? $matches[1] : '';

                        if (strpos($answer_text, 'fa-hourglass-half') !== false) {
                        } else {

                            $local_request = $this->{$this->model_function}->getRequestsByMessageId($message_id);

                            if (!empty($local_request['pending_points'])) {

                                if (!$this->customer->isLogged()) {
                                    $history['answer'] = $this->loginToReward($answer_text, false);
                                }
                            }
                        }
                    }


                    if (!empty($history['attachment_thumb'])) {

                        if (strtolower(pathinfo($history['attachment_thumb'], PATHINFO_EXTENSION)) == 'pdf') {
                            $base_img_path = strstr($this->chat_url, '/index.php', true);

                            if ($base_img_path === false) {
                                $base_img_path = $this->chat_url;
                            }
                            $history['attachment_thumb'] = $base_img_path . '/image/pdf_icon.png';

                        } else {
                            $height = $this->{$this->model_function}->getProportionalHeight(basename($history['attachment_thumb']), 160);
                            $thumbnail_url = $this->model_tool_image->resize(basename($history['attachment_thumb']), 160, $height);
                            $history['attachment_thumb'] = $thumbnail_url;
                        }
                    }
                }
            } else {
                $response['show_faq'] = true;
                $response['faqs'] = [];

                if (!empty($this->module_chataiwd_faq)) {
                    $this->load->model('tool/image');
                    foreach ($this->module_chataiwd_faq as $faq) {
                        if (isset($faq['status']) && $faq['status']) {

                            $image = '';
                            if ($faq['type'] == 'image' && !empty($faq['image'])) {
                                $image = $this->model_tool_image->resize($faq['image'], 50, 50);
                            }

                            $faq['question'] = str_replace("\r\n", '<br>', $faq['question']);
                            $faq['question'] = str_replace("\n", '<br>', $faq['question']);

                            $faq['answer'] = str_replace("\r\n", '<br>', $faq['answer']);
                            $faq['answer'] = str_replace("\n", '<br>', $faq['answer']);

                            $response['faqs'][] = [
                                'type' => $faq['type'],
                                'icon' => $faq['icon'],
                                'image' => $image,
                                'question' => $faq['question'],
                                'answer' => $faq['answer']
                            ];
                        }
                    }
                }
            }

            $response['last_message_id'] = isset($response['history'][0]['id']) ? $response['history'][0]['id'] : 0;
            if (!$response['last_message_id']) {
                $response['last_message_id'] = $this->{$this->model_function}->getLastMessageId($session_id, $chat_user_id);
            }
            $response['is_logged'] = $chat_user_id ? true : false;
        }

        if (rand(1, 10) === 1) {
            $this->{$this->model_function}->deleteDispatcherTyping();
        }

        $response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
        $this->response->setOutput($response);
    }

    private function parsePrizesFromHtml($html_text) {
        $prizes = [];

        // Kiszedjük az összes <li> elemet, ami a lehetséges nyereményeket tartalmazza
        preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $html_text, $matches);

        if (!empty($matches[1])) {
            // Fix színpaletta a szeleteknek, hogy szép színes legyen a kerék
            $wheel_colors = ['#f1c40f', '#e67e22', '#9b59b6', '#3498db', '#2ecc71', '#e74c3c', '#1abc9c', '#34495e'];

            $color_count = count($wheel_colors);

            foreach ($matches[1] as $index => $li_content) {
                $clean_label = strip_tags($li_content);
                $clean_label = preg_replace('/\s+/', ' ', $clean_label);
                $clean_label = trim($clean_label);

                if (!empty($clean_label)) {
                    $prizes[] = [
                        'id'    => $index + 1,
                        'label' => $clean_label,
                        'color' => $wheel_colors[$index % $color_count],
                        'value' => ''
                    ];
                }
            }
        }

        return $prizes;
    }

    public function getFaqList() {
        $this->load->model('tool/image');
        $json = ['faqs' => []];

        if (is_array($this->module_chataiwd_faq)) {
            foreach ($this->module_chataiwd_faq as $faq) {
                if (isset($faq['status']) && $faq['status']) {
                    $image = '';
                    if ($faq['type'] == 'image' && !empty($faq['image'])) {
                        $image = $this->model_tool_image->resize($faq['image'], 40, 40);
                    }

                    $json['faqs'][] = [
                        'type'     => $faq['type'],
                        'icon'     => $faq['icon'],
                        'image'    => $image,
                        'question' => base64_encode($faq['question']),
                        'answer'   => base64_encode($faq['answer'])
                    ];
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function generateThumbnail() {
        $json = [];

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && isset($this->request->files['attachment'])) {
            $file = $this->request->files['attachment'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $max_file_size = 5 * 1024 * 1024; // 5 MB

            if (!in_array($file['type'], $allowed_types)) {
                $json['error'] = ($this->language->get('error_invalid_file_type') != 'error_invalid_file_type') ? $this->language->get('error_invalid_file_type') : 'Invalid file type. Only images (JPEG, PNG, GIF) and PDFs are allowed.';
                $this->response->setOutput(json_encode($json));
                return;
            }
            if ($file['size'] > $max_file_size) {
                $json['error'] = 'The file size is too large (max. 5 MB).';
                $this->response->setOutput(json_encode($json));
                return;
            }

            $temp_filename = uniqid() . '-' . basename($file['name']);
            $temp_file = DIR_IMAGE . $temp_filename;
            move_uploaded_file($file['tmp_name'], $temp_file);

            $thumbnail_url = '';
            if (in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
                $this->load->model('tool/image');
                $this->load->model($this->model_load);

                $height = $this->{$this->model_function}->getProportionalHeight($temp_filename , 160);
                $thumbnail_url = $this->model_tool_image->resize($temp_filename, 160, $height);

            } elseif ($file['type'] === 'application/pdf') {
                $base_img_path = strstr($this->chat_url, '/index.php', true);

                if ($base_img_path === false) {
                    $base_img_path = $this->chat_url;
                }
                $thumbnail_url = $base_img_path . '/image/pdf_icon.png';
            }

            $json['success'] = true;
            $json['thumbnail'] = $thumbnail_url;
            $json['temp_file_path'] = $temp_file;
        } else {
            $json['error'] = ($this->language->get('error_invalid_request') != 'error_invalid_request') ? $this->language->get('error_invalid_request') : 'Invalid request or missing file.';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function deleteTempFile() {
        $json = [];

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && isset($this->request->post['temp_file_path'])) {
            $temp_file_path = $this->request->post['temp_file_path'];
            if (file_exists($temp_file_path)) {
                unlink($temp_file_path);
                $json['success'] = true;
            } else {
                $json['error'] = ($this->language->get('error_file_not_found') != 'error_file_not_found') ? $this->language->get('error_file_not_found') : 'A file not found.';
            }
        } else {
            $json['error'] = ($this->language->get('error_invalid_request') != 'error_invalid_request') ? $this->language->get('error_invalid_request') : 'Invalid request.';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function checkAuth(): void {
        $session_id = $this->request->post['session_id'] ?? '';
        $this->handleChatSession($session_id);

        $json = [];
        if (!$session_id) {
            $json['logged_in'] = false;
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->model($this->model_load);
        $chat_session = $this->{$this->model_function}->getChatUserBySessionId($session_id);

        if ($chat_session && $chat_session['chat_user_id'] > 0 && !(int)$chat_session['is_logged_out']) {

            $chat_user = $this->{$this->model_function}->getChatUserById($chat_session['chat_user_id']);

            if ($chat_user) {
                $json['logged_in'] = true;
                $json['name'] = $chat_user['name'];
                $json['email'] = $chat_user['email'];

                // Ha be van lépve a webshopba, de nincs még összekötve a chat fiókkal
                if ($this->customer->isLogged() && !$chat_user['customer_id']) {
                    $json['offer_linking'] = true;
                    $json['oc_email'] = $this->customer->getEmail();
                }
            } else {
                $json['logged_in'] = false;
            }

        } else {

            if ($this->customer->isLogged()) {
                $was_manual_logout = ($chat_session && (int)$chat_session['is_logged_out'] === 1);

                if (!$was_manual_logout) {
                    $chat_user_linked = $this->{$this->model_function}->getChatUserByCustomerId($this->customer->getId());

                    if ($chat_user_linked) {
                        $this->{$this->model_function}->setChatUserSession($chat_user_linked['chat_user_id'], $session_id);

                        $json['logged_in'] = true;
                        $json['name'] = $chat_user_linked['name'];
                        $json['email'] = $chat_user_linked['email'];
                    } else {
                        $json['logged_in'] = false;
                        $json['offer_linking'] = true;
                        $json['oc_email'] = $this->customer->getEmail();
                    }
                } else {
                    $json['logged_in'] = false;
                    $json['was_manual_logout'] = true;
                }
            } else {
                $json['logged_in'] = false;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function saveAuth(): void {
        $this->load->language($this->model_load);

        $json = [];
        $type = $this->request->post['type'] ?? '';
        $name = $this->request->post['name'] ?? '';
        $email = $this->request->post['email'] ?? '';
        $password = $this->request->post['password'] ?? '';
        $session_id = $this->request->post['session_id'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$password) {
            $json['error'] = ($this->language->get('error_invalid_email_password') != 'error_invalid_email_password') ? $this->language->get('error_invalid_email_password') : 'Invalid email or password';
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->model($this->model_load);
        $chat_user = $this->{$this->model_function}->getChatUserByEmail($email);
        $customer_id = $this->customer->isLogged() ? $this->customer->getId() : null;

        if ($type === 'login') {
            if ($chat_user) {

                if (empty($chat_user['password_hash'])) {
                    $json['error'] = ($this->language->get('error_chat_account_linked_store') != 'error_chat_account_linked_store')
                        ? $this->language->get('error_chat_account_linked_store')
                        : 'This account is linked with your store profile. Please use the "Link Accounts" option to log in!';
                    $this->response->setOutput(json_encode($json));
                    return;
                }

                if (password_verify($password, $chat_user['password_hash'])) {
                    $session_id = $this->request->post['session_id'] ?? '';
                    $this->{$this->model_function}->setChatUserSession($chat_user['chat_user_id'], $session_id);

                    if ($customer_id) {
                        $owner_count = $this->{$this->model_function}->getIssetCustomerId($customer_id);
                        if ($owner_count == 0) {
                            $data = [];
                            $data['customer_id'] = $customer_id;
                            $this->{$this->model_function}->updateChatUser($chat_user['chat_user_id'], $data);
                        }
                    }

                    $chat_user_id = $chat_user['chat_user_id'];
                    $current_chat_user = $chat_user;

                } else {
                    $json['error'] = ($this->language->get('error_incorrect_credentials') != 'error_incorrect_credentials') ? $this->language->get('error_incorrect_credentials') : 'Incorrect email or password';
                    $this->response->setOutput(json_encode($json));
                    return;
                }

            } else {
                $json['error'] = ($this->language->get('error_incorrect_credentials') != 'error_incorrect_credentials') ? $this->language->get('error_incorrect_credentials') : 'Incorrect email or password';
                $this->response->setOutput(json_encode($json));
                return;
            }

        } elseif ($type === 'register') {
            if (!$name) {
                $json['error'] = ($this->language->get('error_name_required') != 'error_name_required') ? $this->language->get('error_name_required') : 'Name required for registration';
                $this->response->setOutput(json_encode($json));
                return;
            }
            if ($chat_user) {
                $json['error'] = ($this->language->get('error_email_exists') != 'error_email_exists') ? $this->language->get('error_email_exists') : 'Email already registered';
                $this->response->setOutput(json_encode($json));
                return;
            }


            $final_customer_id = null;
            if ($customer_id) {
                $already_linked = $this->{$this->model_function}->getChatUserByCustomerId($customer_id);
                if (!$already_linked) {
                    $final_customer_id = $customer_id; // első fiók → auto-link
                }
            }

            $data = [
                'name' => $name,
                'email' => $email,
                'password_hash' => $password,
                'customer_id' => $final_customer_id
            ];

            $chat_user_id = $this->{$this->model_function}->createChatUser($data);
            if ($chat_user_id) {
                $current_chat_user = $this->{$this->model_function}->getChatUserById($chat_user_id);
                $session_id = $this->request->post['session_id'] ?? '';
                $this->{$this->model_function}->setChatUserSession($chat_user_id, $session_id);
            }

        } else {
            $json['error'] = ($this->language->get('error_invalid_type') != 'error_invalid_type') ? $this->language->get('error_invalid_type') : 'Invalid type';
            $this->response->setOutput(json_encode($json));
            return;
        }


        if ($chat_user_id && $current_chat_user) {
            $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);

            $sync_data = [
                'registration_id'   => $this->module_chataiwd_registration_id,
                'session_id'        => $session_id,
                'chat_user_id'      => $chat_user_id,
                'name'              => $current_chat_user['name'],
                'email'             => $current_chat_user['email'],
                'telephone'         => $current_chat_user['telephone'] ?? '',
                'image_url'         => $current_chat_user['image_url'] ?? '',
                'is_bell'           => 0,
                'domain'            => $domain,

            ];
            $url = $this->chat_url . '.syncChatUser';
            $response = $this->postCurl($url,$sync_data,true);


            $json['success'] = true;
            $json['name'] = $current_chat_user['name'];
            $json['logged_in'] = true;
        } else {
            $json['error'] = ($this->language->get('error_save_auth_failed') != 'error_save_auth_failed') ? $this->language->get('error_save_auth_failed') : 'Save error or invalid data';
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function callHuman(): void {
        $session_id = $this->request->post['session_id'] ?? '';

        if (!$session_id) return;
        $chat_user_id = 0;

        $chat_user = $this->{$this->model_function}->getChatUserBySessionId($session_id);
        if ($chat_user && (int)$chat_user['is_logged_out'] !== 1) {
            $chat_user_id = (int)$chat_user['chat_user_id'];
        }

        $this->load->model($this->model_load);

        $sync_data = [
            'registration_id'   => $this->module_chataiwd_registration_id,
            'session_id'        => $session_id,
            'chat_user_id'      => $chat_user_id,
            'is_bell'           => 1,
            'domain'            => parse_url(HTTP_SERVER, PHP_URL_HOST)
        ];

        $url = $this->chat_url . '.syncChatUser';
        $this->postCurl($url, $sync_data, true);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => true]));
    }

    public function linkAccounts(): void {
        $this->load->language($this->model_load);

        $json = [];
        $chat_email = $this->request->post['chat_email'] ?? '';
        $chat_password = $this->request->post['chat_password'] ?? '';
        $session_id = $this->request->post['session_id'] ?? '';

        if ($this->customer->isLogged() && $chat_email && $chat_password) {
            $this->load->model($this->model_load);

            $chat_user = $this->{$this->model_function}->getChatUserByEmail($chat_email);

            if ($chat_user && password_verify($chat_password, $chat_user['password_hash'])) {

                if (empty($chat_user['customer_id']) || (int)$chat_user['customer_id'] === (int)$this->customer->getId()) {
                    $customer_id = (int)$this->customer->getId();

                    $this->{$this->model_function}->updateChatUserCustomerId($chat_user['chat_user_id'], $customer_id);

                    if ($session_id) {
                        $this->{$this->model_function}->setChatUserSession($chat_user['chat_user_id'], $session_id);
                    }

                    $json['success'] = true;
                    $json['chat_user_id'] = $chat_user['chat_user_id'];
                    $json['name'] = $chat_user['name'];

                } else {
                    $json['error'] = ($this->language->get('error_chat_already_linked') != 'error_chat_already_linked') ? $this->language->get('error_chat_already_linked') : 'This chat account is already linked to another store profile.';
                }
            } else {
                $json['error'] = ($this->language->get('error_incorrect_chat_credentials') != 'error_incorrect_chat_credentials') ? $this->language->get('error_incorrect_chat_credentials') : 'Incorrect chat email or password.';
            }
        } else {
            $json['error'] = ($this->language->get('error_auth_required') != 'error_auth_required') ? $this->language->get('error_auth_required') : 'Authentication required or missing data.';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function createNewChatAccount(): void {
        $json = [];
        $this->load->model($this->model_load);

        $session_id = $this->request->post['session_id'] ?? '';

        if ($this->customer->isLogged()) {
            $customer_id = (int)$this->customer->getId();

            $chat_user = $this->{$this->model_function}->getChatUserByCustomerId($customer_id);

            if (!$chat_user) {
                $data = [
                    'name'          => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
                    'email'         => $this->customer->getEmail(),
                    'password_hash' => '',
                    'customer_id'   => $customer_id
                ];

                $chat_user_id = $this->{$this->model_function}->createChatUser($data);
            } else {
                $chat_user_id = $chat_user['chat_user_id'];
            }

            if ($chat_user_id) {
                if ($session_id) {
                    $this->{$this->model_function}->setChatUserSession($chat_user_id, $session_id);
                }

                $json['success'] = true;
                $json['chat_user_id'] = $chat_user_id;
            } else {
                $json['error'] = ($this->language->get('error_incorrect_chat_data') != 'error_incorrect_chat_data') ? $this->language->get('error_incorrect_chat_data') : 'Incorrect chat data or already linked';
            }
        } else {
            $json['error'] = ($this->language->get('error_not_logged_in') != 'error_not_logged_in') ? $this->language->get('error_not_logged_in') : 'Not logged in or missing data';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function productInfo() {
        $product_id = $this->request->post['product_id'] ?? '';
        $this->load->language($this->model_load);
        $this->load->language('product/product');


        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            $this->load->model('catalog/manufacturer');

            $data['heading_title'] = $product_info['name'];

            $data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
            $data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', 'language=' . $this->config->get('config_language')), $this->url->link('account/register', 'language=' . $this->config->get('config_language')));
            $data['text_reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
            $data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

            $data['error_upload_size'] = sprintf($this->language->get('error_upload_size'), $this->config->get('config_file_max_size'));

            $data['config_file_max_size'] = ((int)$this->config->get('config_file_max_size') * 1024 * 1024);

            $data['product_id'] = $product_id;

            $data['manufacturer'] = '';
            $data['manufacturers'] = '';
            if ($product_info['manufacturer_id']) {
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);

                if ($manufacturer_info) {
                    $data['manufacturer'] = $manufacturer_info['name'];
                }
                $data['manufacturers'] = $this->url->link('product/manufacturer'.$this->method_separator.'info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $product_info['manufacturer_id']);
            }

            $data['model'] = $product_info['model'];
            $data['reward'] = $product_info['reward'];
            $data['points'] = $product_info['points'];
            $data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');


            $data['stock'] = $product_info['quantity'];

            $data['rating'] = (int)$product_info['rating'];
            $data['review_status'] = (int)$this->config->get('config_review_status');
            $data['review'] = $this->load->controller('product/review');


            $this->load->model('tool/image');

            if (version_compare(VERSION, '4.0.0.0', '>=')) {
                // OC 4.x
                $popup_w = $this->config->get('config_image_popup_width');
                $popup_h = $this->config->get('config_image_popup_height');
                $thumb_w = $this->config->get('config_image_thumb_width');
                $thumb_h = $this->config->get('config_image_thumb_height');
                $add_w   = $this->config->get('config_image_additional_width');
                $add_h   = $this->config->get('config_image_additional_height');

                // Modell metódusok OC 4-ben
                $images_results = $this->model_catalog_product->getImages($product_id);
                $discounts = $this->model_catalog_product->getDiscounts($product_id);
                $product_options = $this->model_catalog_product->getOptions($product_id);
                $data['attribute_groups'] = $this->model_catalog_product->getAttributes($product_id);


            } else {
                // OC 3.x
                $theme_prefix = 'theme_' . $this->config->get('config_theme');
                $popup_w = $this->config->get($theme_prefix . '_image_popup_width');
                $popup_h = $this->config->get($theme_prefix . '_image_popup_height');
                $thumb_w = $this->config->get($theme_prefix . '_image_thumb_width');
                $thumb_h = $this->config->get($theme_prefix . '_image_thumb_height');
                $add_w   = $this->config->get($theme_prefix . '_image_additional_width');
                $add_h   = $this->config->get($theme_prefix . '_image_additional_height');

                $images_results = $this->model_catalog_product->getProductImages($product_id);
                $discounts = $this->model_catalog_product->getProductDiscounts($product_id);
                $product_options = $this->model_catalog_product->getProductOptions($product_id);
                $data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($product_id);

            }

            if ($product_info['image'] && is_file(DIR_IMAGE . html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'))) {
                $data['popup'] = $this->model_tool_image->resize($product_info['image'], $popup_w, $popup_h);
                $data['thumb'] = $this->model_tool_image->resize($product_info['image'], $thumb_w, $thumb_h);
            } else {
                $data['popup'] = ''; $data['thumb'] = '';
            }

            $data['images'] = [];
            foreach ($images_results as $result) {
                if ($result['image'] && is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
                    $data['images'][] = [
                        'popup' => $this->model_tool_image->resize($result['image'], $popup_w, $popup_h),
                        'thumb' => $this->model_tool_image->resize($result['image'], $add_w, $add_h)
                    ];
                }
            }


            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $data['price'] = false;
            }

            if ((float)$product_info['special']) {
                $data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $data['special'] = false;
            }

            if ($this->config->get('config_tax')) {
                $data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
            } else {
                $data['tax'] = false;
            }

            $data['discounts'] = [];

            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                foreach ($discounts as $discount) {
                    $data['discounts'][] = ['price' => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])] + $discount;
                }
            }

            $data['options'] = [];

            foreach ($product_options as $option) {
                if ((int)$product_id && !isset($product_info['override']['variant'][$option['product_option_id']])) {
                    $product_option_value_data = [];

                    foreach ($option['product_option_value'] as $option_value) {
                        if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                            if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
                                $price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                            } else {
                                $price = false;
                            }

                            if ($option_value['image'] && is_file(DIR_IMAGE . html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8'))) {
                                $image = $option_value['image'];
                            } else {
                                $image = '';
                            }

                            $product_option_value_data[] = [
                                    'image' => $this->model_tool_image->resize($image, 50, 50),
                                    'price' => $price
                                ] + $option_value;
                        }
                    }

                    $data['options'][] = ['product_option_value' => $product_option_value_data] + $option;
                }
            }

            $data['cart'] = $this->url->link('checkout/cart'.$this->method_separator.'add', 'language=' . $this->config->get('config_language'),true);

            $data['subscription_plans'] = [];
            if (version_compare(VERSION, '4.0.0.0', '>=') && method_exists($this->model_catalog_product, 'getSubscriptions')) {
                $results = $this->model_catalog_product->getSubscriptions($product_id);

                foreach ($results as $result) {
                    $description = '';

                    if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                        if ($result['duration']) {
                            $price = ($product_info['special'] ?: $product_info['price']) / $result['duration'];
                        } else {
                            $price = ($product_info['special'] ?: $product_info['price']);
                        }

                        $price = $this->currency->format($this->tax->calculate($price, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                        $cycle = $result['cycle'];
                        $frequency = $this->language->get('text_' . $result['frequency']);
                        $duration = $result['duration'];

                        if ($duration) {
                            $description = sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
                        } else {
                            $description = sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
                        }
                    }

                    $data['subscription_plans'][] = ['description' => $description] + $result;
                }
            }

            if ($product_info['minimum']) {
                $data['minimum'] = $product_info['minimum'];
            } else {
                $data['minimum'] = 1;
            }

            $data['share'] = $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . (int)$product_id);



            $data['tags'] = [];

            if ($product_info['tag']) {
                $tags = explode(',', $product_info['tag']);

                foreach ($tags as $tag) {
                    $data['tags'][] = [
                        'tag' => trim($tag),
                        'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . '&tag=' . trim($tag))
                    ];
                }
            }
        }

        $html = $this->load->view($this->model_load.'_product_info', $data);
        echo $html;
    }

    public function logoutAuth(): void {
        $json = [];
        $this->load->model($this->model_load);

        $session_id = $this->request->post['session_id'] ?? '';

        if ($session_id) {
            $this->{$this->model_function}->logoutChatUserSession($session_id);
        }

        $json['success'] = true;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function mergeAnonymousHistory(): void {
        $json = [];

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $json['error'] = ($this->language->get('error_invalid_method') != 'error_invalid_method') ? $this->language->get('error_invalid_method') : 'Invalid request method';
            $this->response->setOutput(json_encode($json));
            return;
        }

        $session_id = $this->request->post['session_id'] ?? null;
        $registration_id = $this->module_chataiwd_registration_id;

        if (!$session_id  || !$registration_id) {
            $json['error'] = ($this->language->get('error_missing_data') != 'error_missing_data') ? $this->language->get('error_missing_data') : 'Missing data';
            $this->response->setOutput(json_encode($json));
            return;
        }
        $chat_user = $this->{$this->model_function}->getChatUserBySessionId($session_id);
        if (!$chat_user || (int)$chat_user['is_logged_out'] === 1) {
            $json['error'] = ($this->language->get('error_not_logged_in_chat') != 'error_not_logged_in_chat') ? $this->language->get('error_not_logged_in_chat') : 'Not logged into chat account';
            $this->response->setOutput(json_encode($json));
            return;
        }
        $chat_user_id = (int)$chat_user['chat_user_id'];

        $url = $this->chat_url . '.mergeHistory';
        $postData = [
            'registration_id' => $registration_id,
            'session_id' => $session_id,
            'chat_user_id' => $chat_user_id,
        ];

        $response = $this->postCurl($url, $postData);
        $httpCode = $response['httpCode'];
        $responseData = json_decode($response['respons'], true);

        if ($httpCode === 200 && isset($responseData['success']) && $responseData['success']) {
            $json['success'] = true;
        } else {
            $json['error'] = $responseData['error'] ?? (($this->language->get('error_merge_failed') != 'error_merge_failed') ? $this->language->get('error_merge_failed') : 'Error during merge');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function saveConsent() {
        $this->load->model($this->model_load);

        $session_id = $this->request->post['session_id'] ?? '';
        $registration_id = $this->module_chataiwd_registration_id;

        if (empty($session_id) || empty($registration_id) ) {
            $json['error'] = ($this->language->get('error_not_logged_in_chat') != 'error_not_logged_in_chat') ? $this->language->get('error_not_logged_in_chat') : 'Not logged into chat account';
            $this->response->setOutput(json_encode($json));
            return;
        }

        $chat_user_id = 0;

        $chat_user = $this->{$this->model_function}->getChatUserBySessionId($session_id);
        if ($chat_user && (int)$chat_user['is_logged_out'] !== 1) {
            $chat_user_id = (int)$chat_user['chat_user_id'];
        }

        $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);
        if (!$domain) {
            $json['error'] = $this->language->get('error_invalid_domain') ?: (($this->language->get('error_determine_domain_failed') != 'error_determine_domain_failed') ? $this->language->get('error_determine_domain_failed') : 'Could not determine store domain name.');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }

        $url = $this->chat_url . '.saveConsent';
        $postData = [
            'registration_id' => $registration_id,
            'session_id' => $session_id,
            'chat_user_id' => $chat_user_id,
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
            'consent_version' => '1.0',
            'domain' => $domain,
        ];

        $response = $this->postCurl($url, $postData);
        $httpCode = $response['httpCode'];
        $json = json_decode($response['respons'], true);

        if (!isset($json['error'])) {
            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
        $this->response->setOutput(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function syncChatContext() {

        $json = ['success' => false];

        try {
            $this->load->model($this->model_load);

            $session_id = $this->request->post['session_id'] ?? '';
            $chat_user = $this->{$this->model_function}->getChatUserBySessionId($session_id);
            $customer = $this->getCustomer($chat_user);
            $chat_user_id = $customer['chat_user_id'];
            $oc_session_id = $this->session->getId();

            $cart_info = $this->getCart();

            if ($this->customer->isLogged()) {
                $pending_requests = $this->{$this->model_function}->getPendingRewardRequests($session_id, $chat_user_id);

                if (!empty($pending_requests)) {
                    foreach ($pending_requests as $p_request) {

                        $pending_data = json_decode($p_request['pending_points'], true);

                        if (!empty($pending_data['points'])) {
                            $p_points = (int)$pending_data['points'];
                            $p_order  = (int)($pending_data['order_id'] ?? 0);
                            $text_reward_fallback = 'Chat reward points';
                            $p_desc = $pending_data['description'] ?? (($this->language->get('text_chat_reward_points') != 'text_chat_reward_points') ? $this->language->get('text_chat_reward_points') : $text_reward_fallback);

                            if (isset($pending_data['type']) && $pending_data['type'] === 'wheel') {
                                $text_wheel_fallback = 'Lucky wheel reward points';
                                $p_desc = !empty($pending_data['description']) ? $pending_data['description'] : (($this->language->get('text_wheel_reward_points') != 'text_wheel_reward_points') ? $this->language->get('text_wheel_reward_points') : $text_wheel_fallback);
                            }

                            $this->{$this->model_function}->addRewardPoints(
                                (int)$this->customer->getId(),
                                $p_desc,
                                $p_points,
                                $p_order
                            );

                            // 2. Azonnal töröljük a pending_points tartalmát ENNÉL A SPECIFIKUS message_id-nál,
                            // így a következő 1mp-es polling már tudni fogja, hogy át kell váltani a Gratulálunk szövegre!
                            $this->{$this->model_function}->clearPendingPointsByMessageId($p_request['message_id']);
                        }
                    }
                }
            }

            $this->handleChatSession($session_id);

            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            $url = $this->chat_url . '.syncContext';
            $postData = [
                'registration_id'   => $this->request->post['registration_id'] ?? '',
                'session_id'        => $session_id,
                'domain'            => parse_url(HTTP_SERVER, PHP_URL_HOST),
                'chat_user_id'      => $chat_user_id,
                'oc_session_id'     => $oc_session_id,
                'current_url'       => $this->request->post['current_url'] ?? '',
                'cart'              => $cart_info,
                'callback_url'      => $this->callback_url,
                'customer'          => $customer,
                'ip'                => $ip,
            ];

            $response = $this->postCurl($url, $postData);

            $json = [];
            if (!empty($response)) {
                $vrcs_data = is_array($response['respons']) ? $response['respons'] : json_decode($response['respons'], true);

                $json['success'] = false;
                $json['is_online'] = false;

                if (is_array($vrcs_data)) {
                    // Itt már biztonságos a ?? használata, mert tudjuk, hogy tömbbel dolgozunk
                    $json['success'] = $vrcs_data['success'] ?? false;
                    $json['is_online'] = $vrcs_data['is_online'] ?? false;

                    if (isset($vrcs_data['is_human_mode'])) {
                        $this->{$this->model_function}->updateDispatcherModeStatus(
                            $session_id,
                            $chat_user_id,
                            $vrcs_data['is_human_mode']
                        );
                    }
                }
            }



        } catch (\Throwable $t) {
            $this->log->write('Chat Sync Error: ' . $t->getMessage());
            $json['error'] = 'Sync failed';

        } finally {
            $output = json_encode($json);

            // 1. Fejlécek beállítása
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->addHeader('Connection: close'); // Megmondjuk, hogy zárható a kapcsolat

            // 2. Kiszámoljuk a válasz pontos méretét
            $size = strlen($output);
            header("Content-Length: $size");

            // 3. Kiírjuk a választ és kikényszerítjük a küldést
            echo $output;

            if (function_exists('fastcgi_finish_request')) {
                // Ha a szerver támogatja (FPM esetén), ez azonnal lezárja a kapcsolatot a JS felé
                fastcgi_finish_request();
            } else {
                // Hagyományos Apache/PHP esetén kiürítjük a puffereket
                ob_end_flush();
                flush();
            }

            // 4. Megakadályozzuk, hogy a PHP leálljon a kapcsolat megszakadása miatt
            ignore_user_abort(true);

            // 5. Beállítjuk, hogy ne fusson örökké (pl. 5 perc bőven elég a leveleknek)
            set_time_limit(300);
        }
        $this->abandonedCart($session_id);
        $this->cronVectorize();
    }

    private function abandonedCart($session_id) {
        if (empty($this->module_chataiwd_recovery)) {
            return;
        }

        $last_run = $this->cache->get('vrcs_abandoned_last_run');
        if ($last_run && (time() - $last_run < 1800)) {
            return;
        }

        $lock_name = 'vrcs_abandoned_lock';
        $lock = $this->db->query("SELECT GET_LOCK('" . $this->db->escape($lock_name) . "', 0) as confirmed");

        if (!$lock->row['confirmed']) {
            return;
        }

        try {
            $this->cache->set('vrcs_abandoned_last_run', time());



            $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);
            if (!$domain) return;

            $url = $this->chat_url . '.getAbandonedCartData';
            $postData = [
                'registration_id'   => $this->module_chataiwd_registration_id,
                'domain'            => $domain,
                'session_id'        => $session_id,
                'module_chataiwd_recovery' => $this->module_chataiwd_recovery,
            ];

            $vrcs_response = $this->postCurl($url, $postData);

            if (!empty($vrcs_response['respons'])) {
                $vrcs_data = json_decode($vrcs_response['respons'], true);

                if (!empty($vrcs_data['status']) && $vrcs_data['status'] == 'success') {
                    $mails_to_send = $vrcs_data['data'] ?? [];

                    if (!empty($mails_to_send)) {
                        $sent_count = 0;
                        foreach ($mails_to_send as $oc_session_id => $mail_info) {
                            // A $mail_info tartalmazza: email, customer_name, mail_type_id, subject, content
                            if ($this->{$this->model_function}->sendAbandonedMail($oc_session_id, $mail_info)) {
                                $sent_count++;
                            }
                        }
                        if ($sent_count > 0) {
                            $this->log->write('VRCS Abandoned Cart: ' . $sent_count . ' mails sent.');
                        }
                    }
                }
            }

        } finally {
            $this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock_name) . "')");
        }
    }



    public function cronVectorize(): void {
        $last_run = $this->cache->get('vrcs_vectorize_last_run');
        if ($last_run && (time() - $last_run < 60)) {
            return;
        }

        $lock_name = 'vrcs_vectorize_lock';
        $lock = $this->db->query("SELECT GET_LOCK('" . $this->db->escape($lock_name) . "', 0) as confirmed");

        if (!$lock->row['confirmed']) {
            return;
        }

        try {
            $this->cache->set('vrcs_vectorize_last_run', time());

            $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);
            $post_data = [
                'registration_id' => $this->module_chataiwd_registration_id,
                'domain'          => $domain,
            ];

            $url = $this->chat_url . '.getSyncSchema';
            $res = $this->postCurl($url, $post_data, false);
            $vrcs_data = json_decode($res['respons'] ?? '', true);

            if (empty($vrcs_data['status']) || $vrcs_data['status'] !== 'success' || empty($vrcs_data['results'])) {
                $this->response->setOutput(json_encode(['error' => 'Schema lekérése sikertelen']));
                return;
            }

            $schema = $vrcs_data['results'];

            if (!empty($schema) && is_array($schema) ) {
                foreach ($schema as $table => $config) {
                    // 1. Adatok lekérése (az általad írt zseniális getTableDataForSync)
                    $limit = $config['sync_limit'] ?? 5;
                    $data = $this->{$this->model_function}->getTableDataForSync(
                        $table,
                        $config,
                        $limit
                    );

                    $sync_mode = '';

                    if (empty($data['rows'])) {
                        $sync_mode = 'incremental';
                        $data = $this->{$this->model_function}->getTableDataForSync(
                            $table,
                            $config,
                            $limit,
                            $sync_mode
                        );
                    }

                    if (empty($data['rows']) && $sync_mode === 'incremental') {
                        $sync_status = $this->{$this->model_function}->getSettingKey('module_chataiwd_sync_status');
                        $sync_status[$table . '_incremental']['last_date'] = date('Y-m-d H:i:s');
                        $this->{$this->model_function}->updateSetting('module_chataiwd_sync_status', json_encode($sync_status));
                    }

                    if (!empty($data['rows'])) {
                        $post_data = [
                            'registration_id' => $this->module_chataiwd_registration_id,
                            'domain' => $domain,
                            'polling' => 1,
                            'table' => $table,
                            'structure' => $data['structure'],
                            'rows' => $data['rows'],
                            'children_data' => $data['children_data'],
                        ];


                        $url = $this->chat_url . '.saveInitialChunk';
                        $res = $this->postCurl($url, $post_data, false);

                        $res['httpCode'] = 200;

                        if ($res['httpCode'] === 200) {
                            $sync_status = $this->{$this->model_function}->getSettingKey('module_chataiwd_sync_status');

                            // MELYIK A LEGNAGYOBB DÁTUM A LEKÉRDEZETT 5 SOR KÖZÜL?
                            $last_row = end($data['rows']);
                            $last_date = !empty($config['modify_column']) ? $last_row[$config['modify_column']] : date('Y-m-d H:i:s');

                            $key = ($sync_mode === 'incremental') ? $table . '_incremental' : $table;

                            $sync_status[$key] = [
                                'last_id'   => max(array_column($data['rows'], $config['id'])),
                                'last_date' => $last_date
                            ];

                            $this->{$this->model_function}->updateSetting('module_chataiwd_sync_status', json_encode($sync_status));
                        }
                    }
                }
            }

        } finally {
            $this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock_name) . "')");
        }
    }


    private function handleChatSession($session_id) {
        if (!$session_id) return;

        $is_already_synced = isset($this->session->data['vrcs_chat_session_id']);
        if (!$is_already_synced) {
            $this->session->data['vrcs_chat_session_id'] = $session_id;
            $this->session->close();
        }

        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('session_id');
        $property->setAccessible(true);
        $property->setValue($this->session, '');
    }

    public function addToCart() {
        if (empty($this->request->post['product_id'])) {
            $json['error'] = ($this->language->get('error_no_product_id') != 'error_no_product_id') ? $this->language->get('error_no_product_id') : 'no product ID';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->language('checkout/cart');
        $this->load->model('catalog/product');

        $product_id = (int)$this->request->post['product_id'];
        $quantity = (int)$this->request->post['quantity'];
        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info && ($quantity < $product_info['minimum'])) {
            $json = array();
            $json['error']['option']['quantity'] = sprintf($this->language->get('error_minimum'), $product_info['name'], $product_info['minimum']);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->controller('checkout/cart' . $this->method_separator . 'add');
        $output = $this->response->getOutput();
        $json = json_encode([]);

        if ($output) {
            $json = json_decode($output, true);

            if (isset($json['error']) && is_array($json['error'])) {
                $new_errors = [];
                foreach ($json['error'] as $key => $message) {
                    if (strpos($key, 'option_') === 0) {
                        $option_id = str_replace('option_', '', $key);
                        // Átalakítjuk OC3/Saját JS barát formátumra: option[218]
                        $new_errors['option'][$option_id] = $message;
                    } else {
                        $new_errors[$key] = $message;
                    }
                }
                $json['error'] = $new_errors;
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function applyCoupon() {
        $json = array();

        $coupon = isset($this->request->post['coupon']) ? $this->request->post['coupon'] : '';

        // Model betöltése verziótól függően
        if (VERSION >= '4.0.0.0') {
            $this->load->language('extension/opencart/checkout/coupon');
            $this->load->model('marketing/coupon');
            $coupon_info = $this->model_marketing_coupon->getCoupon($coupon);

        } else {
            $this->load->language('extension/total/coupon');
            $this->load->model('extension/total/coupon');
            $coupon_info = $this->model_extension_total_coupon->getCoupon($coupon);
        }

        if ($coupon_info) {
            $this->session->data['coupon'] = $coupon;
            $json['success'] = $this->language->get('text_success');
            $json['discount'] = $coupon_info['discount'];

        } else {
            $this->load->language($this->model_load);
            $json['error'] = $this->language->get('error_coupon');
            $json['error_button'] = $this->language->get('error_coupon');
        }

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            return $json;
        }
    }

    public function applyRewardPoints() {
        $this->load->language($this->model_load);
        $this->load->model($this->model_load);

        $json = ['success' => false];

        // Adatok fogadása (jöhet POST-ból vagy belső meghívásból)
        $customer_id = (int)($this->request->post['customer_id'] ?? 0);
        $points      = (int)($this->request->post['points'] ?? 0);
        $description = $this->request->post['description'] ?? 'Chat reward points';
        $order_id    = (int)($this->request->post['order_id'] ?? 0);

        // Ha a chatből/API-ból nem jött customer_id, ellenőrizzük, hogy a jelenlegi session be van-e lépve
        if (!$customer_id && $this->customer->isLogged()) {
            $customer_id = (int)$this->customer->getId();
        }

        if ($customer_id > 0 && $points > 0) {
            $this->{$this->model_function}->addRewardPoints($customer_id, $description, $points, $order_id);
            $json['success'] = true;
            $json['status'] = 'credited';
            $json['message'] = $points . ' ' . $this->language->get('text_points_credited_success');

        } elseif ($points > 0) {
            $this->session->data['vrcs_pending_points'] = [
                'points'      => $points,
                'description' => $description,
                'order_id'    => $order_id
            ];
            $json['success'] = true;
            $json['status'] = 'pending';
            $json['message'] = $this->language->get('text_points_pending_login');
        }

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            return $json;
        }
    }

    public function restore() {
        $this->load->language($this->model_load);

        $token = $this->request->get['token'] ?? '';
        $share_token = $this->request->get['vrcs_s'] ?? '';

        $final_token = (!empty($share_token)) ? $share_token : $token;
        $is_converted = (!empty($share_token)) ? 12 : 2;

        if (!$final_token) {
            $this->response->redirect($this->url->link('common/home'));
            return;
        }

        if (!empty($share_token)) {
            $this->session->data['vrcs_source_mode'] = 'share';
        } else {
            $this->session->data['vrcs_source_mode'] = 'recovery';
        }

        $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);
        $url = $this->chat_url . '.getAbandonedCartData';

        $postData = [
            'registration_id' => $this->module_chataiwd_registration_id,
            'domain'          => $domain,
            'oc_session_id'   => $final_token,
            'is_converted'    => $is_converted
        ];

        $response = $this->postCurl($url, $postData);
        $vrcs_data = json_decode($response['respons'] ?? '', true);

        if (!empty($vrcs_data['status']) && $vrcs_data['status'] == 'success' && !empty($vrcs_data['data']) && !empty($vrcs_data['data'][$final_token])) {
            $cart_info = $vrcs_data['data'][$final_token];

            if (!empty($cart_info['cart']) && !empty($cart_info['cart']['products']) ) {
                $products = $cart_info['cart']['products'];
                foreach ($products as $product) {
                    $this->cart->add($product['product_id'], $product['quantity'], $product['option'] ?? []);
                }
            }

            $this->session->data['vrcs_recovered_token'] = $final_token;

            $this->session->data['success'] = $this->language->get('text_restore_cart'); //"Örömmel látunk újra! Visszatöltöttük a korábban félbehagyott kosarad tartalmát.";
        }


        $target_url = str_replace('&amp;', '&', $this->url->link('checkout/cart'));
        $this->response->setOutput('<meta http-equiv="refresh" content="0;url=' . $target_url . '">');
    }

    private function getCart() {
        $this->load->model('tool/image');

        $current_total_raw = $this->cart->getTotal();
        $current_total_format = $this->currency->format($current_total_raw, $this->session->data['currency'] ?? $this->config->get('config_currency'));
        $current_count = $this->cart->countProducts();
        $current_products = $this->cart->getProducts();

        $cart_info = [];

        if ($current_products) {
            $cart_info = [
                'products' => [],
                'total_raw' => $current_total_raw,
                'total_format' => $current_total_format,
                'count' => $current_count
            ];

            foreach ($current_products as $product) {
                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], 100, 100);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 100, 100);
                }

                $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

                $cart_info['products'][] = [
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'quantity' => $product['quantity'],
                    'price' => $price,
                    'image' => $image,
                    'total' => $this->currency->format($product['total'], $this->session->data['currency']),
                    'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                ];
            }
        }
        return $cart_info;
    }

    public function shareCart() {
        $json = [];

        // Csak POST és csak ha van e-mail
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['email'])) {
            $this->load->language($this->model_load);

            $email = $this->request->post['email'];
            $message = $this->request->post['message'] ?? '';
            $registration_id = $this->module_chataiwd_registration_id;
            $session_id = $this->request->post['session_id'] ?? '';
            $sender_name = $this->request->post['sender_name'] ?? '';
            if (empty($sender_name)) {
                $sender_name = ($this->language->get('text_share_sender_fallback') != 'text_share_sender_fallback') ? $this->language->get('text_share_sender_fallback') : 'your friend';
            }

            $cart_info = $this->getCart();
            $oc_session_id = $this->session->getId();
            // 2. Egyedi token generálása a visszaállításhoz (ezt mentjük a VRCS-be is)

            // 3. Adatok küldése a VRCS API-nak
            $post_data = [
                'registration_id' => $registration_id,
                'chat_session_id' => $session_id,
                'oc_session_id'   => $oc_session_id,
                'email'           => $email,
                'message'         => $message,
                'cart'              => $cart_info,
                'is_converted'    => 11, // "Share Cart" státusz
                'domain'          => parse_url(HTTP_SERVER, PHP_URL_HOST),
            ];

            $response = $this->postCurl($this->chat_url . '.processShareCart', $post_data);

            if (!empty($response)) {
                $vrcs_data = json_decode($response['respons'] ?? '', true);

                if (!empty($vrcs_data['status']) && $vrcs_data['status'] == 'success') {
                    $mail_sent = $this->{$this->model_function}->sendShareCartMail($email, $oc_session_id, $cart_info, $message, $sender_name);
                    if ($mail_sent) {
                        $json['success'] = $this->language->get('text_share_success');
                    }
                }

            } else {
                $json['error'] = $this->language->get('text_share_error');
            }
        } else {
            $json['error'] = ($this->language->get('error_invalid_request') != 'error_invalid_request') ? $this->language->get('error_invalid_request') : 'Invalid request.';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


    public function onOrderUpdate(&$route, &$args, &$output) {
        $order_id = (int)$args[0];
        $status_id = (int)$args[1];

        $valid_statuses = array_merge(
            (array)$this->config->get('config_processing_status'),
            (array)$this->config->get('config_complete_status')
        );

        if (in_array($status_id, $valid_statuses)) {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($order_id);

            if ($order_info) {
                $registration_id = $this->module_chataiwd_registration_id;
                // Ezt a session-ből szedjük, mert a rendelés pillanatában ez kötötte össze a chattel
                $session_id = $this->session->data['vrcs_chat_session_id'] ?? null;

                if ($registration_id && $session_id) {
                    $domain = parse_url(HTTP_SERVER, PHP_URL_HOST);

                    $recovered_token = $this->session->data['vrcs_recovered_token'] ?? null;
                    $source_mode = $this->session->data['vrcs_source_mode'] ?? 'recovery';

                    if ($recovered_token) {

                        $cart_info = $this->getCart();

                        $is_converted = 3;
                        if ($source_mode == 'share') {
                            $is_converted = 13;
                        }

                        $reports = [];
                        $reports[] = [
                            'oc_session_id' => $recovered_token,
                            'is_converted'  => $is_converted,
                            'cart'      => $cart_info,
                            //'products'      => $products_snapshot
                        ];

                        if ($this->session->getId() !== $recovered_token) {
                            $reports[] = [
                                'oc_session_id' => $this->session->getId(),
                                'is_converted'  => -1,
                                'cart'      => $cart_info,
                                //'products'      => $products_snapshot
                            ];
                        }

                        $this->postCurl($this->chat_url . '.updateMailStatuses', [
                            'registration_id' => $registration_id,
                            'domain'          => $domain,
                            'reports'         => $reports
                        ], true);

                        unset($this->session->data['vrcs_recovered_token']);
                        unset($this->session->data['vrcs_source_mode']);
                    }

                    $this->load->model($this->model_load);

                    $chat_user = $this->{$this->model_function}->getChatUserBySessionId($session_id);
                    $chat_user_id = ($chat_user && (int)$chat_user['is_logged_out'] !== 1) ? (int)$chat_user['chat_user_id'] : 0;

                    $order_info['registration_id'] = $registration_id;
                    $order_info['session_id']      = $session_id;
                    $order_info['chat_user_id']    = $chat_user_id;

                    $url = $this->chat_url . '.processOrderUpdate';
                    $this->postCurl($url, $order_info, true);
                }
            }
        }
    }

    public function injectChat(string &$route, array &$args, string &$output): void {
        if (!$this->config->get('module_chataiwd_status')) {
            return;
        }

        if (isset($this->request->get['popup']) || isset($this->request->get['iframe'])) {
            return;
        }

        if (strpos($output, '</body>') === false) {
            return;
        }

        $chat_html = $this->load->controller($this->model_load);

        if (strpos($output, 'chataiwd-open-btn-wrapper') !== false) {
            return;
        }

        if ($chat_html) {
            $output = str_replace('</body>', $chat_html . '</body>', $output);
        }
    }

    public function spinWheel(): void {
        $this->load->language($this->model_load);
        $this->load->model($this->model_load);

        $json = ['success' => false];

        // Paraméterek biztonságos átvétele
        $wheel_id   = (int)($this->request->post['wheel_id'] ?? 0);
        $message_id = (int)($this->request->post['message_id'] ?? 0);
        $session_id = $this->request->post['session_id'] ?? ''; // A JS-ben gyártott egyedi chat session

        // Alapvető validáció
        if (!$wheel_id || !$message_id || empty($session_id)) {
            $json['error'] = ($this->language->get('error_wheel_missing_params') != 'error_wheel_missing_params') ? $this->language->get('error_wheel_missing_params') : 'Missing or invalid parameters for drawing.';
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // 1. LÉPCSŐS VÉDELEM: Helyi ellenőrzés az áruház adatbázisában (Nem terheljük a VRCS-t feleslegesen)
        $claim_data = $this->{$this->model_function}->isCardClaimed($message_id, 'wheel');

        if (!empty($claim_data)) {
            $card_data = [
                'claimed_value' => !empty($claim_data['claimed_value']) ? $claim_data['claimed_value'] : '',
                'date_added'    => !empty($claim_data['date_added']) ? $claim_data['date_added'] : ''
            ];

            $json['html']       = $this->load->view($this->model_load . '_wheel_card', $card_data);
            $json['is_claimed'] = true; // Flag a JS-nek, hogy ne alertet dobjon, hanem cseréljen
            $json['error']      = ($this->language->get('error_wheel_already_spun') != 'error_wheel_already_spun') ? $this->language->get('error_wheel_already_spun') : 'You have already spun this lucky wheel! Only one spin per player allowed.';

            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
            return;
        }


        // Lekérjük a chat felhasználót a session alapján a statisztikához (ha létezik)
        $chat_user = $this->{$this->model_function}->getChatUserBySessionId($session_id);
        $chat_user_id = ($chat_user && (int)$chat_user['is_logged_out'] !== 1) ? (int)$chat_user['chat_user_id'] : 0;


        // 2. LÉPCSŐ: Logika a VRCS szerveren (CURL kérés a sorsolásért)
        $url = $this->chat_url . '.spinWheel';
        $postData = [
            'wheel_id'        => $wheel_id,
            'message_id'      => $message_id,
            'session_id'      => $session_id,
            'chat_user_id'    => $chat_user_id,
            'registration_id' => $this->module_chataiwd_registration_id
        ];

        $vrcs_res = $this->postCurl($url, $postData);
        $httpCode = $vrcs_res['httpCode'] ?? 0;

        if ($vrcs_res['respons'] === false || $httpCode !== 200) {
            $json['error'] = ($this->language->get('error_wheel_server_unavailable') != 'error_wheel_server_unavailable') ? $this->language->get('error_wheel_server_unavailable') : 'The central drawing server is temporarily unavailable. Please try again later!';
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $vrcs_data = json_decode($vrcs_res['respons'], true);

        // Ha a VRCS valamiért hibát dob (pl. ottani extra biztonsági ellenőrzés)
        if (empty($vrcs_data['success'])) {
            $json['error'] = $vrcs_data['error'] ?? (($this->language->get('error_wheel_server_error') != 'error_wheel_server_error') ? $this->language->get('error_wheel_server_error') : 'A drawing error occurred on the central server.');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // 3. LÉPCSŐ: Könyvelés és lezárás az áruház oldalon
        // A VRCS-től visszakapott eredményt (pl. "nyert_10_szazalek_kupon" vagy "50_pont") elmentjük a claimed_value mezőbe
        $claim_data = [
            'message_id'    => $message_id,
            'chat_user_id'  => $chat_user_id,
            'session_id'    => $session_id,
            'card_type'     => 'wheel',
            'claimed_value' => $vrcs_data['prize_slug'] ?? 'spun',
            'label' => $vrcs_data['label'] ?? '',
            'reward_points' => $vrcs_data['reward_points'] ?? '',
        ];

        $this->{$this->model_function}->addCardClaim($claim_data);


        // Ha kupon típusú a nyeremény (pl. a prize_slug 'coupon'), betesszük az OC sessionbe
        if (isset($vrcs_data['prize_slug']) && $vrcs_data['prize_slug'] == 'coupon' && !empty($vrcs_data['prize_value'])) {
            $this->request->post['coupon'] = $vrcs_data['prize_value'];
            $this->applyCoupon();
        }


        // Opcionális áruházoldali azonnali jutalomkezelés (Pl. ha pontot nyert, itt helyben jóváírhatjuk)
        if (!empty($vrcs_data['reward_points']) && $vrcs_data['reward_points'] > 0 ) {
            $points = (int)$vrcs_data['reward_points'];
            $text_wheel_fallback = 'Lucky wheel prize (Message ID: #%s)';
            $description = sprintf(($this->language->get('text_wheel_prize_description') != 'text_wheel_prize_description') ? $this->language->get('text_wheel_prize_description') : $text_wheel_fallback, $message_id);

            if ($this->customer->isLogged()) {
                $this->{$this->model_function}->addRewardPoints(
                    (int)$this->customer->getId(),
                    $description,
                    $points,
                    0
                );
            } else {
                // VENDÉG: Elmentjük függőként a chat_requests táblába JSON-ben
                $pending_data = [
                    'type'        => 'reward',
                    'points'      => $points,
                    'description' => $description,
                    'order_id'    => 0
                ];

                $this->{$this->model_function}->updateChatRequestPendingPoints($message_id, json_encode($pending_data));

                // Mivel vendég és pontot nyert, a visszakapott animációs/nyertes HTML-re rátesszük a bejelentkezés gombot!
                if (!empty($vrcs_data['html'])) {
                    $vrcs_data['html'] = $this->loginToReward($vrcs_data['html'],true);
                }
            }


        }

        // Minden sikeres: Visszaadjuk a kész HTML-t (animációt/eredményt) a JS-nek
        $json['success'] = true;

        if (!empty($vrcs_data['html'])) {
            $json['html'] = $vrcs_data['html'];
        } else {
            $success_text = ($this->language->get('text_wheel_success') != 'text_wheel_success')
                ? $this->language->get('text_wheel_success')
                : 'Successful spin!';

            $json['html'] = '<div class="alert alert-success">' . $success_text . '</div>';
        }

        $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
        $this->response->setOutput(json_encode($json));
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
