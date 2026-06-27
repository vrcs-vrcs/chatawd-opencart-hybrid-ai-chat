<?php
class ModelExtensionModuleChataiwd extends Model {

    protected bool $type;
    private $method_separator;
    private $model_load = 'extension/module/chataiwd';
    private $model_function = 'model_extension_module_chataiwd';

    public function __construct($registry) {
        parent::__construct($registry);

        $this->method_separator = version_compare(VERSION, '4.0.0.0', '<')
            ? '/'
            : (version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|');

        $this->db->query("SET NAMES 'utf8mb4'");
        $this->db->query("SET CHARACTER SET utf8mb4");

        $sql = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "chat_requests (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `message_id` INT NOT NULL,
                    `session_id` VARCHAR(128) NOT NULL,
                    `registration_id` VARCHAR(128) NOT NULL,
                    `answer` TEXT NULL,
                    `dispatcher` VARCHAR(128) DEFAULT NULL,
                    `question` TEXT NULL,
                    `callback_url` VARCHAR(255) NULL,
                    `chat_user_id`  INT NOT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `status` TINYINT DEFAULT 0, -- 0: várakozás, 1: human mód, 2: válasz érkezett, 3: válasz továbbítva
                    `ai_human_status` TINYINT DEFAULT 0, -- ai: 0 human: 1
                    `attachment_thumb` VARCHAR(255) NULL,
                    `attachment_filename` VARCHAR(255) NULL,
                    pending_points TEXT NULL DEFAULT NULL,
                    INDEX `idx_session_status` (`session_id`, `status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $query = $this->db->query($sql);

        $new_columns = [
            'chat_user_id' => 'INT NOT NULL',
            'ai_human_status' => 'TINYINT DEFAULT 0',
            'question' => 'TEXT NULL AFTER answer',
            'attachment_thumb' => 'VARCHAR(255) NULL',
            'attachment_filename' => 'VARCHAR(255) NULL',
            'dispatcher' => 'VARCHAR(128) DEFAULT NULL',
            'pending_points' => 'TEXT NULL DEFAULT NULL',
        ];
        $this->newColumns('chat_requests',$new_columns);


        // Új chat_user tábla létrehozása vagy frissítése a gépelés funkcióval
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "chat_user` (
            `chat_user_id` int(11) NOT NULL AUTO_INCREMENT,
            `session_id` varchar(128) DEFAULT NULL,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password_hash` varchar(255) DEFAULT NULL,
            `customer_id` int(11) DEFAULT NULL,
            `dispatcher_is_typing` tinyint(1) NOT NULL DEFAULT 0,
            `is_human_mode` tinyint(1) NOT NULL DEFAULT 0,
            `date_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`chat_user_id`),
                KEY `idx_email` (`email`),
                KEY `idx_customer_id` (`customer_id`),
                KEY `idx_session_id` (`session_id`),
                KEY `idx_date_modified` (`date_modified`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $this->db->query($sql);

        $new_columns = [
            'session_id' => 'VARCHAR(128) DEFAULT NULL',
            'dispatcher_is_typing' => 'tinyint(1) NOT NULL DEFAULT 0',
            'is_human_mode' => 'tinyint(1) NOT NULL DEFAULT 0',
            'date_modified' => 'datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ];
        $new_indexes = [
            'idx_session_id' => ['session_id'],
            'idx_customer_id' => ['customer_id'],
            'idx_date_modified' => ['date_modified'],
            'idx_email' => ['email'],
        ];
        $this->newColumns('chat_user',$new_columns, $new_indexes);


        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "chat_user_session` (
            `chat_user_session_id` INT(11) NOT NULL AUTO_INCREMENT,
            `chat_user_id` INT(11) NOT NULL,
            `session_id` VARCHAR(128) NOT NULL, -- Ez a JS-ből jövő UUID (token)
            `last_activity` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_logged_out TINYINT(1) DEFAULT 0,
            PRIMARY KEY (`chat_user_session_id`),
            UNIQUE KEY `idx_session_id` (`session_id`), -- Egy gép/böngésző csak egy userhez tartozhat
            KEY `idx_chat_user_id` (`chat_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $this->db->query($sql);

        $new_columns = [
            'is_logged_out' => 'TINYINT(1) DEFAULT 0',
        ];
        $this->newColumns('chat_user_session',$new_columns);

        $sql = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "chat_card_claims (
                chat_card_claims_id INT(11) NOT NULL AUTO_INCREMENT,
                message_id INT(11) NOT NULL,
                session_id VARCHAR(255) NOT NULL,
                chat_user_id INT(11) NOT NULL DEFAULT 0,                
                card_type VARCHAR(50) NOT NULL,
                claimed_value VARCHAR(255) DEFAULT NULL,
                label VARCHAR(100) DEFAULT NULL,
                reward_points INT,
                date_added DATETIME NOT NULL,
            PRIMARY KEY (chat_card_claims_id),
        UNIQUE KEY `uidx_message_card` (`message_id`, `card_type`),
            KEY `idx_session_id` (`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $this->db->query($sql);

        $new_columns = [
            'reward_points' => 'INT',
            'label' => 'VARCHAR(100) DEFAULT NULL',
        ];
        $this->newColumns('chat_card_claims',$new_columns);
    }

    public function newColumns($table, $columns = [], $indexes = []) {
    // --- Oszlopok ---
        if ($columns) {
            foreach ($columns as $column => $definition) {
                $query = $this->db->query("
                SELECT COUNT(*) AS total
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = '" . DB_PREFIX . $table . "'
                  AND COLUMN_NAME = '" . $column . "'
            ");

                if (!$query->row['total']) {
                    $sql = "ALTER TABLE " . DB_PREFIX . $table .
                        " ADD COLUMN `" . $column . "` " . $definition;
                    $this->db->query($sql);
                }
            }
        }

        // --- Indexek ---
        if ($indexes) {
            foreach ($indexes as $index_name => $columns_list) {

                // Ellenőrizzük, hogy létezik-e már az index
                $query = $this->db->query("
                SELECT COUNT(*) AS total
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = '" . DB_PREFIX . $table . "'
                  AND INDEX_NAME = '" . $index_name . "'
            ");

                if (!$query->row['total']) {
                    $cols = implode('`,`', (array)$columns_list); // Több oszlop is lehet
                    $sql = "ALTER TABLE " . DB_PREFIX . $table .
                        " ADD INDEX `" . $index_name . "` (`" . $cols . "`)";
                    $this->db->query($sql);
                }
            }
        }
    }

    public function getManufacturers(): array {
        $cache_key = 'chataiwd_manufacturers_' . $this->config->get('config_language_id');
        $manufacturers = $this->cache->get($cache_key);

        if (!$manufacturers) {
            $this->load->model('catalog/manufacturer');
            $manufacturers = $this->model_catalog_manufacturer->getManufacturers();

            // Csak a szükséges adatokat tartjuk meg
            foreach ($manufacturers as &$manufacturer) {
                $manufacturer = [
                    'manufacturer_id' => $manufacturer['manufacturer_id'],
                    'name' => $manufacturer['name']
                ];
            }

            $this->cache->set($cache_key, $manufacturers);
        }

        return $manufacturers;
    }

/**
* Összes kategória (főkategóriák és alkategóriák) lekérdezése gyorsítótárazással
* @return array A kategóriák tömbje
*/
    public function getCategories(): array {
        $cache_key = 'chataiwd_categories_' . $this->config->get('config_language_id');
        $categories = $this->cache->get($cache_key);

        if (!$categories) {
            // Lekérdezzük az összes kategóriát a category_path tábla segítségével
            $sql = "SELECT c.`category_id`, c.`parent_id`, cd.`name`, cd.`description`, GROUP_CONCAT(cp.`path_id` ORDER BY cp.`level` SEPARATOR '_') AS `path`
                    FROM `" . DB_PREFIX . "category` c
                    LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`)
                    LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.`category_id` = c2s.`category_id`)
                    LEFT JOIN `" . DB_PREFIX . "category_path` cp ON (c.`category_id` = cp.`category_id`)
                    WHERE cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'
                    AND c2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "'
                    AND c.`status` = '1'
                    GROUP BY c.`category_id`
                    ORDER BY c.`sort_order`, LCASE(cd.`name`)";

            $query = $this->db->query($sql);
            $categories = $query->rows;

            // Formázás: csak a szükséges adatokat tartjuk meg
            foreach ($categories as &$category) {
                $category = [
                    'category_id' => $category['category_id'],
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'path' => $category['path'] // A teljes útvonal az URL generálásához
                ];
            }

            $this->cache->set($cache_key, $categories);
        }

        return $categories;
    }

    public function getShippingMethods() {
        $this->load->model('setting/extension');

        if (!isset($this->session->data['shipping_address'])) {
            $this->session->data['shipping_address'] = [
                'country_id' => $this->config->get('config_country_id'),
                'zone_id' => $this->config->get('config_zone_id'),
                'postcode' => '',
                'city' => '',
                'address_1' => '',
                'address_2' => ''
            ];
        }

        $results = $this->model_setting_extension->getExtensions('shipping');

        foreach ($results as $result) {
            if ($this->config->get('shipping_' . $result['code'] . '_status')) {
                $this->load->model('extension/shipping/' . $result['code']);

                $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

                if ($quote) {
                    $method_data[$result['code']] = array(
                        'title'      => $quote['title'],
                        'quote'      => $quote['quote'],
                        'sort_order' => $quote['sort_order'],
                        'error'      => $quote['error']
                    );
                }
            }
        }

        $sort_order = array();

        foreach ($method_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $method_data);

        return $method_data;
    }

    public function getPaymentMethods($address) {
        // Totals
        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        $this->load->model('setting/extension');

        $sort_order = array();

        $results = $this->model_setting_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);

                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }

        $address = $this->session->data['payment_address'] ?? $address;

        $results = $this->model_setting_extension->getExtensions('payment');

        $recurring = $this->cart->hasRecurringProducts();

        $method_data = [];

        foreach ($results as $result) {
            if ($this->config->get('payment_' . $result['code'] . '_status')) {
                $this->load->model('extension/payment/' . $result['code']);

                $method = $this->{'model_extension_payment_' . $result['code']}->getMethod($address , $total);

                if ($method) {
                    if ($recurring) {
                        if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
                            $method_data[$result['code']] = $method;
                        }
                    } else {
                        $method_data[$result['code']] = $method;
                    }
                }
            }
        }

        $sort_order = array();

        foreach ($method_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $method_data);

        return $method_data;
    }

    public function getPaymentAddress(): array {
        $address = [];

        // Ellenőrizzük, hogy a felhasználó bejelentkezett-e
        if ($this->customer->isLogged()) {
            $this->load->model('account/address');
            $customer_address = $this->model_account_address->getAddress($this->customer->getId(),$this->customer->getAddressId());

            if ($customer_address) {
                $address = [
                    'firstname' => $customer_address['firstname'],
                    'lastname' => $customer_address['lastname'],
                    'company' => $customer_address['company'],
                    'address_1' => $customer_address['address_1'],
                    'address_2' => $customer_address['address_2'],
                    'postcode' => $customer_address['postcode'],
                    'city' => $customer_address['city'],
                    'country_id' => $customer_address['country_id'],
                    'zone_id' => $customer_address['zone_id']
                ];
            }
        }

        if (empty($address)) {
            $address = [
                'firstname' => '',
                'lastname' => '',
                'company' => '',
                'address_1' => '',
                'address_2' => '',
                'postcode' => '',
                'city' => '',
                'country_id' => (int)$this->config->get('config_country_id'),
                'zone_id' => (int)$this->config->get('config_zone_id')
            ];

            $language_code = $this->config->get('config_language'); // pl. "hu-hu", "en-gb"
            $language_parts = explode('-', $language_code);
            $country_code = isset($language_parts[1]) ? strtoupper($language_parts[1]) : '';

            if ($country_code) {
                $this->load->model('localisation/country');
                $countries = $this->model_localisation_country->getCountries();
                foreach ($countries as $country) {
                    if (strtoupper($country['iso_code_2']) === $country_code) {
                        $address['country_id'] = $country['country_id'];
                        break;
                    }
                }

                if (empty($address['zone_id']) && $address['country_id']) {
                    $this->load->model('localisation/zone');
                    $zones = $this->model_localisation_zone->getZonesByCountryId($address['country_id']);
                    if (!empty($zones)) {
                        $address['zone_id'] = $zones[0]['zone_id']; // Az első zónát használjuk
                    }
                }
            }
        }

        return $address;
    }

    public function getModulesByCode($code) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `name`");

        return $query->rows;
    }

    public function createChatRequest($data=array()) {
        $this->db->query("SET NAMES 'utf8mb4'");
        $this->db->query("SET CHARACTER SET utf8mb4");

        $sql = "INSERT INTO `" . DB_PREFIX . "chat_requests` SET
            `message_id`        = '" . (int)($data['message_id'] ?? 0) . "',
            `session_id`        = '" . $this->db->escape($data['session_id'] ?? '') . "',
            `registration_id`   = '" . $this->db->escape($data['registration_id'] ?? '') . "',
            `chat_user_id`      = '" . (int)($data['chat_user_id'] ?? 0) . "',
            `status`            = '" . (int)($data['status'] ?? 0) . "',
            `created_at`        = NOW(),
            `callback_url`      = '" . $this->db->escape($data['callback_url'] ?? '') . "',
            `answer`            = '" . $this->db->escape($data['answer'] ?? '') . "',
            `question`          = '" . $this->db->escape($data['question'] ?? '') . "',
            `dispatcher`        = '" . $this->db->escape($data['dispatcher'] ?? '') . "',
            attachment_thumb    = '" . $this->db->escape($data['attachment_thumb'] ?? '') . "',
            attachment_filename = '" . $this->db->escape($data['attachment_filename'] ?? '') . "',
            pending_points      = '" . $this->db->escape($data['pending_points'] ?? '') . "',
            `ai_human_status`   = '" . (int)($data['ai_human'] ?? 0) . "'";

        $this->db->query($sql);
    }

    /**
     * Nyitott üzenetek lekérdezése last_id alapján, időkorláttal
     */
    public function getNewChatRequests($session_id, $chat_user_id, $last_id) {
        $sql = "SELECT `message_id`, `answer`,  question, attachment_thumb, attachment_filename, dispatcher, pending_points
                FROM `" . DB_PREFIX . "chat_requests` WHERE message_id > $last_id ";
        if ($chat_user_id) {
            $sql .= " AND  chat_user_id = '".$this->db->escape($chat_user_id)."' ";

        } else {
            $sql .= " AND `session_id`  = '" . $this->db->escape($session_id) . "' ";
            $sql .= " AND (chat_user_id = 0 OR chat_user_id IS NULL OR chat_user_id = '') ";

        }
        $sql .= " ORDER BY `message_id` ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function updateDispatcherTypingStatus($session_id, $chat_user_id, $is_typing) {
        if (!empty($chat_user_id)) {
            $sql = "SELECT chat_user_id FROM `" . DB_PREFIX . "chat_user` WHERE chat_user_id = '" . (int)$chat_user_id . "'";
        } else {
            $sql = "SELECT chat_user_id FROM `" . DB_PREFIX . "chat_user` WHERE session_id = '" . $this->db->escape($session_id) . "'";
        }

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            $this->db->query("UPDATE `" . DB_PREFIX . "chat_user` 
                      SET   dispatcher_is_typing = '" . (int)$is_typing . "', 
                            date_modified = NOW()
                      WHERE chat_user_id = '" . (int)$query->row['chat_user_id'] . "'");

        } else {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "chat_user` 
                      SET session_id = '" . $this->db->escape($session_id) . "', 
                          name = 'Guest', 
                          email = '',
                          dispatcher_is_typing = '" . (int)$is_typing . "',
                          created_at = NOW()");
        }
    }

    public function updateDispatcherModeStatus($session_id, $chat_user_id, $is_human_mode) {
        if (!empty($chat_user_id)) {
            $sql = "SELECT chat_user_id FROM `" . DB_PREFIX . "chat_user` WHERE chat_user_id = '" . (int)$chat_user_id . "'";
            $sql_human = " chat_user_id = '" . (int)$chat_user_id . "' ";

        } else {
            $sql = "SELECT chat_user_id FROM `" . DB_PREFIX . "chat_user` WHERE session_id = '" . $this->db->escape($session_id) . "'";
            $sql_human = " session_id = '" . $this->db->escape($session_id) . "' ";

        }

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            $sql = "UPDATE `" . DB_PREFIX . "chat_user` 
                      SET   is_human_mode = '" . (int)$is_human_mode . "', 
                            date_modified = NOW() 
                      WHERE " . $sql_human;
            $this->db->query($sql);

        } else {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "chat_user` 
                      SET session_id = '" . $this->db->escape($session_id) . "', 
                          name = 'Guest', 
                          email = '',
                          is_human_mode = '" . (int)$is_human_mode . "',
                          created_at = NOW()");
        }
    }

    public function getDispatcherTypingStatus($session_id, $chat_user_id = 0) {
        $sql = "SELECT dispatcher_is_typing, date_modified FROM `" . DB_PREFIX . "chat_user` WHERE 1 ";

        if ($chat_user_id > 0) {
            $sql .= " AND chat_user_id = " . (int)$chat_user_id;
        } else {
            $sql .= " AND session_id = '" . $this->db->escape($session_id) . "' ";
        }

        $sql .= " LIMIT 1";

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            $last_update = strtotime($query->row['date_modified']);

            if ($query->row['dispatcher_is_typing'] == 1 && (time() - $last_update) < 10) {
                return 1;
            }
        }

        return 0;
    }

    public function getIsHumanMode($session_id, $chat_user_id = 0) {
        $sql = "SELECT is_human_mode FROM `" . DB_PREFIX . "chat_user` WHERE 1 ";
        if ($chat_user_id > 0) {
            $sql .= " AND chat_user_id = " . (int)$chat_user_id;
        } else {
            $sql .= " AND session_id = '" . $this->db->escape($session_id) . "' ";
        }

        $sql .= " LIMIT 1";

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return $query->row['is_human_mode'];
        }

        return 0;
    }

    public function deleteDispatcherTyping() {
        // Azokat a rekordokat töröljük, amiknél:
        // 1. Van session_id (tehát ideiglenes/böngésző alapú)
        // 2. Régebbiek, mint 2 nap

        $sql = "DELETE FROM `" . DB_PREFIX . "chat_user` WHERE 1 ";

        $sql .= " AND session_id IS NOT NULL AND session_id != '' ";

        $sql .= " AND date_modified < DATE_SUB(NOW(), INTERVAL 3 DAY)";

        $this->db->query($sql);

        return $this->db->countAffected();
    }

    public function getLastMessageId($session_id, $chat_user_id = 0) {

        $sql = "SELECT message_id FROM " . DB_PREFIX . "chat_requests WHERE 1 ";
        if ($chat_user_id) {
            $sql .= " AND chat_user_id = " . (int)$chat_user_id;
            $sql .= " AND session_id = '" . $session_id ."' ";

        } else {
            $sql .= " AND session_id = '" . $session_id ."' ";
            $sql .= " AND (chat_user_id = 0 OR chat_user_id IS NULL OR chat_user_id = '') ";
        }

        $sql .= " ORDER BY message_id DESC LIMIT 1 ";


        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return (int)$query->row['message_id'];
        }

        return 0;
    }


    /**
     * Üzenet státuszának frissítése (pl. válasz érkezett után)
     */
    public function updateChatRequestStatus($message_id, $status) {
        $sql = "UPDATE `" . DB_PREFIX . "chat_requests` 
                SET `status` = '" . (int)$status . "' 
                WHERE `message_id` = '" . (int)$message_id . "'";
        $this->db->query($sql);
    }

    /**
     * Lejárt kérések törlése (időkorlát alapján)
     */
    public function cleanExpiredChatRequests($time_limit_seconds) {
        $sql = "DELETE FROM `" . DB_PREFIX . "chat_requests` 
                WHERE `status` != 2 
                AND chat_user_id != ''
                AND (`created_at` < DATE_SUB(NOW(), INTERVAL " . (int)$time_limit_seconds . " SECOND))";
        $this->db->query($sql);
    }

    public function updateChatRequestAnswer($message_id, $answer,$ai_human_status=0) {
        $this->db->query("SET NAMES 'utf8mb4'");
        $this->db->query("SET CHARACTER SET utf8mb4");

        $sql = "UPDATE `" . DB_PREFIX . "chat_requests` 
            SET `answer` = '" . $this->db->escape($answer) . "', 
                `status` = 2,
                ai_human_status = " . (int)$ai_human_status . "                
            WHERE `message_id` = " . (int)$message_id;
        $this->db->query($sql);
    }

    public function getRequestsByMessageId($message_id) {
        $sql = "SELECT message_id, pending_points FROM " . DB_PREFIX . "chat_requests 
                WHERE message_id = '" . $this->db->escape($message_id) . "' ";

        $query = $this->db->query($sql);

        return $query->num_rows ? $query->row : false;
    }

    public function getRequestsBySessionId($session_id, $chat_user_id) {
        $sql = "SELECT ai_human_status FROM " . DB_PREFIX . "chat_requests 
                WHERE 1 ";
        if ($chat_user_id) {
            $sql .= " AND chat_user_id = " . (int)$chat_user_id;
        } else {
            $sql .= " AND session_id = '" . $session_id ."' ";
        }
        $sql .= " ORDER BY created_at DESC LIMIT 1 ";

        $query = $this->db->query($sql);

        return $query->num_rows ? $query->row['ai_human_status'] : 0;
    }

    public function getPendingRewardRequests($session_id, $chat_user_id) {
        $sql = "SELECT `message_id`, `pending_points` 
            FROM `" . DB_PREFIX . "chat_requests` 
            WHERE `pending_points` IS NOT NULL AND `pending_points` != '' ";

        if ($chat_user_id) {
            $sql .= " AND chat_user_id = " . (int)$chat_user_id;
        } else {
            $sql .= " AND `session_id` = '" . $this->db->escape($session_id) . "' ";
            $sql .= " AND (chat_user_id = 0 OR chat_user_id IS NULL OR chat_user_id = '') ";
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function clearPendingPointsByMessageId($message_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "chat_requests` SET `pending_points` = NULL WHERE `message_id` = " . (int)$message_id);
    }

    public function updateChatRequestPendingPoints($message_id, $pending_data) {
        // Ha tömböt kapunk, itt helyben alakítjuk JSON-né, így a kontrollerben tisztább a kód
        if (is_array($pending_data)) {
            $pending_data = json_encode($pending_data, JSON_UNESCAPED_UNICODE);
        }

        $sql = "UPDATE `" . DB_PREFIX . "chat_requests` SET 
        `pending_points` = '" . $this->db->escape($pending_data) . "'  
        WHERE `message_id` = " . (int)$message_id;

        $this->db->query($sql);

        // Visszaadjuk, hogy sikeresen módosult-e a sor az adatbázisban
        return ($this->db->countAffected() > 0);
    }


    public function getSetting() {
        $sql = "SELECT * FROM " . DB_PREFIX . "chat_setting";
        $query = $this->db->query($sql);

        return $query->rows; //
    }

    public function getSettingKey($key) {
        $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "chat_setting WHERE `key` = '" . $this->db->escape($key) . "' AND `store_id` = 0");

        if ($query->num_rows) {
            return json_decode($query->row['value'], true) ?: [];
        }
        return [];
    }

    public function updateSetting($key, $value) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "chat_setting WHERE `key` = '" . $this->db->escape($key) . "' AND `store_id` = 0");

        if ($query->num_rows) {
            $this->db->query("UPDATE " . DB_PREFIX . "chat_setting SET `value` = '" . $this->db->escape($value) . "' WHERE `key` = '" . $this->db->escape($key) . "' AND `store_id` = 0");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "chat_setting (`key`, `value`, `store_id`) VALUES ('" . $this->db->escape($key) . "', '" . $this->db->escape($value) . "', 0)");
        }
    }

    public function getLanguageByCode($code) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($code) . "'");

        return $query->row;
    }

    /**
     * Calculates the proportional height for a given width while maintaining the aspect ratio.
     *
     * @param string $filename The image file name in DIR_IMAGE
     * @param int $width The desired width
     * @return int Returns the calculated height or null
     */
    public function getProportionalHeight(string $filename, int $width): int {
        if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != DIR_IMAGE) {
            return 0;
        }

        list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $filename);

        if (!in_array($image_type, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF])) {
                return 0;
        }

        if ($width_orig > 0) {
            $height = (int)round(($width / $width_orig) * $height_orig);
            return $height;
        }

        return 0;
    }

    public function getChatUserByEmail($email) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "chat_user WHERE email = '" . $this->db->escape($email) . "'");
        return $query->row;
    }

    public function getChatUserById($id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "chat_user WHERE chat_user_id = " . (int)$id);
        return $query->row;
    }

    public function getChatUserByCustomerId($customer_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "chat_user WHERE customer_id = " . (int)$customer_id);
        return $query->row;
    }

    public function getChatUserBySessionId($session_id) {
        $sql = "SELECT u.*, s.is_logged_out, s.last_activity FROM `" . DB_PREFIX . "chat_user_session` s
            JOIN `" . DB_PREFIX . "chat_user` u ON (s.chat_user_id = u.chat_user_id)
            WHERE s.session_id = '" . $this->db->escape($session_id) . "' 
            LIMIT 1";

        $query = $this->db->query($sql);

        return ($query->num_rows) ? $query->row : false;
    }

    /**
     * Session rögzítése vagy frissítése az adatbázisban
     * @param int $chat_user_id
     * @param string $session_id
     */
    public function setChatUserSession(int $chat_user_id, string $session_id): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "chat_user_session` SET 
                                `chat_user_id` = '" . (int)$chat_user_id . "', 
                                `session_id` = '" . $this->db->escape($session_id) . "', 
                                `last_activity` = NOW()
                            ON DUPLICATE KEY UPDATE 
                                `chat_user_id` = '" . (int)$chat_user_id . "', 
                                `is_logged_out` = 0, 
                                `last_activity` = NOW()"
        );
        $this->cleanOldSessions();
    }

    public function cleanOldSessions(int $days = 30): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "chat_user_session` 
                      WHERE `last_activity` < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)");
    }

    public function logoutChatUserSession(string $session_id): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "chat_user_session` 
                      SET `is_logged_out` = 1, `last_activity` = NOW() 
                      WHERE `session_id` = '" . $this->db->escape($session_id) . "'");
    }

    public function createChatUser($data) {
        $check_sql = "SELECT chat_user_id FROM " . DB_PREFIX . "chat_user WHERE email = '" . $this->db->escape($data['email']) . "'";
        $chat_user = $this->db->query($check_sql)->row;

        $customer_id = (int)($data['customer_id'] ?? 0);

        $password_hash = '';
        if (!empty($data['password_hash'])) {
            $password_hash = password_hash(html_entity_decode($data['password_hash'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT);
        }

        if ($chat_user) {
            $update_sql = "UPDATE " . DB_PREFIX . "chat_user SET
            customer_id = " . $customer_id . "
            WHERE chat_user_id = " . (int)$chat_user['chat_user_id'];

            $this->db->query($update_sql);

            return (int)$chat_user['chat_user_id'];

        } else {
            $sql = "INSERT INTO " . DB_PREFIX . "chat_user SET 
                name = '" . $this->db->escape($data['name']) . "',
                email = '" . $this->db->escape($data['email']) . "',
                password_hash = '" . $this->db->escape($password_hash) . "', 
                customer_id = " . (int)($data['customer_id'] ?? 0);

            $this->db->query($sql);

            return $this->db->getLastId();
        }
    }

    public function getIssetCustomerId($customer_id) {
        $sql = "SELECT chat_user_id FROM " . DB_PREFIX . "chat_user WHERE customer_id = '" . (int)$customer_id . "'";
        $query = $this->db->query($sql);

        return $query->num_rows;
    }

    public function updateChatUser($id, $data) {
        $set = [];
        if (isset($data['name'])) $set[] = "name = '" . $this->db->escape($data['name']) . "'";
        if (isset($data['password_hash'])) $set[] = "password_hash = '" . $this->db->escape($data['password_hash']) . "'";
        if (isset($data['customer_id'])) $set[] = "customer_id = " . (int)$data['customer_id'];
        if ($set) {
            $sql = "UPDATE " . DB_PREFIX . "chat_user SET " . implode(', ', $set) . " WHERE chat_user_id = " . (int)$id;
            $this->db->query($sql);
        }
    }

    public function updateChatUserCustomerId($id, $customer_id) {
        $sql = "UPDATE " . DB_PREFIX . "chat_user 
                      SET customer_id = NULL 
                      WHERE customer_id = " . (int)$customer_id;
        $this->db->query($sql);

        $sql = "UPDATE " . DB_PREFIX . "chat_user 
                      SET customer_id = " . (int)$customer_id . " 
                      WHERE chat_user_id = " . (int)$id;
        $this->db->query($sql);
    }

    public function getSeoUrlByKeyword($keyword) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "seo_url` 
            WHERE (`keyword` = '" . $this->db->escape($keyword) . "' 
               OR `keyword` LIKE '%/" . $this->db->escape($keyword) . "') 
            AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' 
            AND `language_id` = '" . (int)$this->config->get('config_language_id') . "' 
            LIMIT 1";

        $query = $this->db->query($sql);

        return $query->row;
    }

    public function getSettingsByLanguage($language_id) {
        $sql = "SELECT cs.key, csd.value 
            FROM " . DB_PREFIX . "chat_setting_description csd 
            LEFT JOIN " . DB_PREFIX . "chat_setting cs ON (csd.chat_setting_id = cs.id) 
            WHERE csd.language_id = '" . (int)$language_id . "'";

        $query = $this->db->query($sql);

        $settings = [];
        foreach ($query->rows as $result) {
            $settings[$result['key']] = $result['value'];
        }

        return $settings;
    }

    public function addRewardPoints($customer_id, $description, $points, $order_id=0) {
        $sql = "INSERT INTO " . DB_PREFIX . "customer_reward SET 
            customer_id = '" . (int)$customer_id . "', 
            order_id = '" . (int)$order_id . "', 
            description = '" . $this->db->escape($description) . "', 
            points = '" . (int)$points . "', 
            date_added = NOW()";

        $query = $this->db->query($sql);
    }

    public function sendAbandonedMail($oc_session_id, $mail_info) {
        if (empty($mail_info['email'])) return false;

        $this->load->language($this->model_load);
        $this->load->model('tool/image');
        $this->load->model('catalog/product');

        $email = $mail_info['email'];
        $subject_fallback = 'Abandoned cart notification';
        $subject = $mail_info['subject'] ?? (($this->language->get('text_mail_abandoned_subject') != 'text_mail_abandoned_subject') ? $this->language->get('text_mail_abandoned_subject') : $subject_fallback);
        $text_pcs = ($this->language->get('text_mail_pcs') != 'text_mail_pcs') ? $this->language->get('text_mail_pcs') : 'qty';

        $cart = $mail_info['cart'] ?? [];

        if (empty($cart)) {
            return false;
        }

        $products = $cart['products'] ?? [];

        if (empty($products)) {
            return false;
        }

        $text_th_product = ($this->language->get('text_mail_th_product') != 'text_mail_th_product') ? $this->language->get('text_mail_th_product') : 'Product';
        $text_th_quantity = ($this->language->get('text_mail_th_quantity') != 'text_mail_th_quantity') ? $this->language->get('text_mail_th_quantity') : 'Qty.';

        $product_list = '<table style="width:100%; border:1px solid #eee; border-collapse: collapse;">';
        $product_list .= '<tr style="background: #f9f9f9;"><th style="text-align:left; padding:8px; border:1px solid #eee;">' . $text_th_product . '</th><th style="text-align:right; padding:8px; border:1px solid #eee;">' . $text_th_quantity . '</th></tr>';


        foreach ($products as $product) {
            // 1. Kép url előkészítése a beépített resizer segítségével
            $image_url = '';
            $product_info = $this->model_catalog_product->getProduct($product['product_id']);

            if (!empty($product_info) && !empty($product_info['image']) && is_file(DIR_IMAGE . $product_info['image'])) {
                $image_url = $this->model_tool_image->resize($product_info['image'], 100, 100);
            } else {
                $image_url = !empty($product['image']) ? str_replace('&amp;', '&', $product['image']) : $this->model_tool_image->resize('placeholder.png', 100, 100);
            }

            $product_list .= '<tr>';
            $product_list .= '<td style="padding:12px 10px; border-bottom:1px solid #eee; width:60px; vertical-align: middle;">';
            $product_list .= '<img src="' . $image_url . '" width="50" style="display:block; border:1px solid #eee; border-radius:4px;">';
            $product_list .= '</td>';

            $product_list .= '<td style="padding:12px 10px; border-bottom:1px solid #eee; vertical-align: middle;">';
            $product_list .= '<a href="' . str_replace('amp;', '', $product['href']) . '" style="color: #2a6496; text-decoration: none; font-weight: bold;">' . $product['name'] . '</a><br>';
            $product_list .= '<small style="color: #777;">' . $product['quantity'] . ' ' . $text_pcs . '</small>';
            $product_list .= '</td>';

            // Itt közvetlenül a kapott formázott árat rakjuk be
            $product_list .= '<td style="padding:12px 10px; border-bottom:1px solid #eee; text-align:right; font-weight: bold; color: #333; vertical-align: middle;">';
            $product_list .= $product['total'] ?? '';
            $product_list .= '</td>';
            $product_list .= '</tr>';
        }
        $product_list .= '</table>';

        // 2. Szövegek beállítása
        $text_dear = ($this->language->get('text_mail_dear') != 'text_mail_dear') ? $this->language->get('text_mail_dear') : 'Dear';
        $text_back = ($this->language->get('text_mail_back_to_checkout') != 'text_mail_back_to_checkout') ? $this->language->get('text_mail_back_to_checkout') : 'Back to cart';

        // 3. Dinamikus Restore Link generálása
        $route = $this->model_load . $this->method_separator . 'restore';
        $recovery_link = $this->url->link($route, 'token=' . $oc_session_id, true);

        $html = '<div style="font-family: sans-serif; color: #333; max-width: 600px;">';
        $html .= '<h2>' . $text_dear . ' ' . htmlspecialchars($mail_info['customer_name']) . '!</h2>';
        $html .= '<p>' . nl2br(htmlspecialchars($mail_info['content'])) . '</p>';
        $html .= $product_list;
        $html .= '<p style="text-align:center; margin-top: 25px;">';
        $html .= '<a href="' . $recovery_link . '" style="background:#4CAF50; color:white; padding:12px 25px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block;">' . $text_back . '</a>';
        $html .= '</p></div>';

        return $this->sendMail($email,$subject,$html);
    }

    public function sendShareCartMail($email, $oc_session_id, $mail_info, $user_message, $sender_name) {
        if (empty($mail_info)) return false;

        $this->load->language($this->model_load);

        $subject = sprintf($this->language->get('text_share_subject'), $sender_name, $this->config->get('config_name'));
        $text_back = ($this->language->get('text_share_back') != 'text_share_back') ? $this->language->get('text_share_back') : 'Back to cart';
        $text_share_title = ($this->language->get('text_share_title') != 'text_share_title') ? $this->language->get('text_share_title') : 'Look what I picked out!';
        $text_share_heading = ($this->language->get('text_share_heading') != 'text_share_heading') ? $this->language->get('text_share_heading') : 'Shared Cart';
        $text_pcs = ($this->language->get('text_mail_pcs') != 'text_mail_pcs') ? $this->language->get('text_mail_pcs') : 'qty';

        // Képekkel dúsított terméklista
        $product_list = '<table style="width:100%; border-collapse: collapse; margin-top: 20px;">';
        foreach ($mail_info['products'] as $product) {
            $product_list .= '<tr>';
            $product_list .= '<td style="padding:10px; border-bottom:1px solid #eee; width:60px;"><img src="' . $product['image'] . '" width="50"></td>';
            $product_list .= '<td style="padding:10px; border-bottom:1px solid #eee;"><strong>' . $product['name'] . '</strong><br><small>' . $product['quantity'] . ' ' . $text_pcs . '</small></td>';
            $product_list .= '<td style="padding:10px; border-bottom:1px solid #eee; text-align:right;">' . $product['price'] . '</td>';
            $product_list .= '</tr>';
        }
        $product_list .= '</table>';

        // Link a visszaállításhoz
        $route = $this->model_load . $this->method_separator . 'restore';
        $share_link = $this->url->link($route, 'vrcs_s=' . $oc_session_id, true);

        $html = '<div style="font-family: sans-serif; color: #333; max-width: 600px; border: 1px solid #ddd; padding: 20px; border-radius: 10px;">';

        // Heading (kisbetűs kontextus)
        $html .= '<div style="font-size: 12px; color: #777; text-transform: uppercase; margin-bottom: 5px;">' . $text_share_heading . '</div>';

        // Főcím
        $html .= '<h2 style="color: #2a6496; margin-top: 0;">' . $text_share_title . '</h2>';

        // Felhasználói üzenet box
        if (!empty($user_message)) {
            $html .= '<div style="background: #fdf6e3; padding: 15px; border-left: 4px solid #f39c12; margin-bottom: 20px;">';
            $html .= '<i>' . nl2br(htmlspecialchars($user_message)) . '</i>';
            $html .= '</div>';
        }

        $html .= $product_list;

        // Gomb
        $html .= '<p style="text-align:center; margin-top: 30px;">';
        $html .= '<a href="' . $share_link . '" style="background:#2a6496; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block;">' . $text_back . '</a>';
        $html .= '</p></div>';

        return $this->sendMail($email, $subject, $html);
    }

    public function sendMail($email, $subject, $html) {
        if (version_compare(VERSION, '4.0.2.0', '<')) {

            if (version_compare(VERSION, '4.0.0.0', '<')) {
                $mail = new Mail();
                $mail->protocol = $this->config->get('config_mail_engine');
            } else {
                $mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));

            }

            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
        } else {

            $mail_option = [
                'parameter' => $this->config->get('config_mail_parameter'),
                'smtp_hostname' => $this->config->get('config_mail_smtp_hostname'),
                'smtp_username' => $this->config->get('config_mail_smtp_username'),
                'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
                'smtp_port' => $this->config->get('config_mail_smtp_port'),
                'smtp_timeout' => $this->config->get('config_mail_smtp_timeout')
            ];
            $mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'), $mail_option);
        }


        $mail->setTo($email);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name'));
        $mail->setSubject($subject);
        $mail->setHtml($html);

        try {
            $mail->send();
            $vissza = true;

        } catch (\Exception $e) {
            $this->log->write('VRCS Mail Error: ' . $e->getMessage());
            $vissza = false;
        }
        $this->writeTo('Siker: ' . $vissza);

        return $vissza;
    }

    public function isCardClaimed(int $message_id, string $card_type) {
        if (!$message_id) {
            return [];
        }

        $sql = "SELECT * FROM " . DB_PREFIX . "chat_card_claims 
                    WHERE message_id = '" . (int)$message_id . "' 
                        AND card_type = '" . $this->db->escape($card_type) . "'
                    LIMIT 1";
        $query = $this->db->query($sql);

        return !empty($query->row) ? $query->row : [];
    }

    /**
     * Rögzíti a sikeres kártyabeváltást az adatbázisban.
     *
     * @param array $data
     * @return int A beszúrt sor ID-ja
     */
    public function addCardClaim(array $data): int {
        $message_id   = (int)($data['message_id'] ?? 0);
        $chat_user_id = (int)($data['chat_user_id'] ?? 0);
        $session_id   = $this->db->escape($data['session_id'] ?? '');
        $card_type    = $this->db->escape($data['card_type'] ?? '');
        $value        = $this->db->escape($data['claimed_value'] ?? '');
        $label        = $this->db->escape($data['label'] ?? '');
        $reward_points= (int)$data['reward_points'] ?? 0;

        // Biztonsági mentés: IGNORE-ral hajtjuk végre, így ha valami csoda folytán
        // mégis egyszerre futna be két kérés, az adatbázis hibadobás helyett csendben eldobja a másodikat
        $sql = "INSERT IGNORE INTO " . DB_PREFIX . "chat_card_claims 
                    SET message_id = '" . $message_id . "',
                        chat_user_id = '" . $chat_user_id . "',
                        session_id = '" . $session_id . "',
                        card_type = '" . $card_type . "',
                        claimed_value = '" . $value . "',
                        reward_points = '" . $reward_points . "',
                        `label` = '" . $label . "',
                        date_added = NOW()";

        $this->db->query($sql);

        return $this->db->getLastId();
    }



    public function getTableDataForSync($table, $config, $limit=5, $mode='') {
        $fields = $config['fields'] ?? $this->db->escape($config['alias']) . ".*";
        $structure = $config['structure'] ?? [];
        $modify_column = $config['modify_column'] ?? false;
        $alias = $config['alias'] ?? $table;

        $offset =  0;

        $store_id = (int)$this->config->get('config_store_id');
        $to_store = '';

        if ($this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . $table) . "_to_store'")->num_rows) {
            $to_store = $table . '_to_store';
        }

        $sql = "SELECT " . $this->db->escape($fields) . " FROM " . DB_PREFIX . $this->db->escape($table) . " " . $this->db->escape($alias) . " ";

        $conditions = [];
        $sync_status = $this->getSettingKey('module_chataiwd_sync_status');
        $order = "ASC";

        if (!empty($modify_column)) {
            if ($mode === 'incremental') {
                // Itt a "második" logikád jön:
                // Ha az incremental paramétert kapjuk, akkor pl. a
                // table.'_incremental' kulcsot használjuk és DESC sorrendet
                $last_date = $sync_status[$table . '_incremental']['last_date'] ?? date('Y-m-d H:i:s');
                $conditions[] = "$alias.$modify_column < '$last_date'";
                $order = "DESC";
            } else {
                // Az alapértelmezett szinkron
                $last_date = $sync_status[$table]['last_date'] ?? '1970-01-01 00:00:00';
                $conditions[] = "$alias.$modify_column > '$last_date'";
                $order = "ASC";
            }
        } else {
            $last_id = $sync_status[$table]['last_id'] ?? 0;

            $conditions[] = $this->db->escape($alias) . "." . $this->db->escape($config['id']) . " > " . (int)$last_id;
            $order = "ASC";
        }

        if ($to_store) {
            $sql .= "LEFT JOIN " . DB_PREFIX . $to_store . " p2s ON (" . $this->db->escape($alias) . "." . $this->db->escape($config['id']) . " = p2s." . $this->db->escape($config['id']) . ") ";
            $conditions[] = "p2s.store_id = '" . $store_id . "'";
        }

        if (isset($config['status']) && $config['status']) {
            $conditions[] = $this->db->escape($alias) . ".status = '1' ";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if (!empty($modify_column)) {
            $sql .= " ORDER BY " . $this->db->escape($alias) . "." . $this->db->escape($modify_column) . " " . $order . " LIMIT " . (int)$limit;
        } else {
            $sql .= " ORDER BY " . $this->db->escape($alias) . "." . $this->db->escape($config['id']) . " ASC LIMIT " . $offset . ", " . $limit;
        }
        $query = $this->db->query($sql);

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
                    $source_key = $child['join']['source_key'];

                    // Prioritás 1: Ha a forrás a Főtábla (pl. product)
                    if ($source_table === $main_table) {
                        $ids_to_filter = $main_ids;
                    } // Prioritás 2: Ha a forrás már feldolgozott gyerek tábla (pl. product_option)
                    elseif (isset($children_data[$source_table])) {
                        $ids_to_filter = array_column($children_data[$source_table]['rows'] ?? [], $source_key);
                    } // Prioritás 3: Ha a forrás még nem létezik (hiba vagy rossz sorrend)
                    else {
                        $ids_to_filter = [];
                        $this->log->write("VRCS Hiba: A forrás tábla ($source_table) még nem érhető el a " . $c_table . " feldolgozásakor.");
                    }
                } else {
                    // Ha nincs megadva join, alapértelmezetten a főtáblához kötjük
                    $ids_to_filter = $main_ids;
                }


                $c_rows = [];
                if (!empty($ids_to_filter)) {
                    $sql = "SELECT * FROM " . DB_PREFIX . $this->db->escape($c_table) . " " . $this->db->escape($c_alias) .
                        " WHERE " . $this->db->escape($c_alias) . "." . $this->db->escape($filter_field) .
                        " IN (" . implode(',', array_unique(array_map('intval', $ids_to_filter))) . ")";

                    $query = $this->db->query($sql);
                    $c_rows = $query->rows;
                }
                $child_structure = [];
                $c_fields_query = $this->db->query("DESCRIBE `" . DB_PREFIX . $this->db->escape($c_table) . "`");
                foreach ($c_fields_query->rows as $field) {
                    $child_structure[$field['Field']] = $field['Type'];
                }

                $children_data[$c_table] = [
                    'table' => $c_table,
                    'rows' => $c_rows,
                    'structure' => $child_structure,
                ];
            }
        }

        return [
            'table' => $main_table,
            'structure' => $main_sturcture,
            'rows' => $main_rows,
            'children_data' => $children_data,
        ];
    }

    public function writeTo($message, $mehet = 0) {
        if ($mehet) {
            $fileName = DIR_LOGS . "vrcschat.txt";
            $file = fopen($fileName, 'a+', true);

            if ($file) {
                fwrite($file, $message);
                fwrite($file, "\n");
                fclose($file);
            }
        }
    }
}
?>