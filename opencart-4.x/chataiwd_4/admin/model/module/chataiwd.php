<?php
namespace Opencart\Admin\Model\Extension\chataiwd\Module;

class chataiwd extends \Opencart\System\Engine\Model
{
    public function __construct($registry)
    {
        parent::__construct($registry);

        $sql = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "chat_setting (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `key` VARCHAR(128) NOT NULL,
                    `value` text NOT NULL,
                    `store_id` int DEFAULT 0
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "chat_setting_description (
                `chat_setting_id` INT NOT NULL,
                `language_id` INT NOT NULL,
                `value` TEXT NOT NULL,
                PRIMARY KEY (`chat_setting_id`, `language_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->db->query($sql);

        $this->initializeDefaultSettings();
    }

    public function initializeDefaultSettings() {
        $settings = $this->getSetting();
        $settingKeys = array_column($settings, 'key');

        $module_chataiwd_recovery = [
            [
                'value'   => 1,
                'unit'    => 'hours',
                'subject' => 'Did you forget something?',
                'content' => 'We noticed that you left some items in your cart. If you encountered any technical issues during checkout, please reply to this email and we will be happy to help!',
                'status'  => 1
            ],
            [
                'value'   => 1,
                'unit'    => 'days',
                'subject' => 'We are still holding your cart!',
                'content' => 'We wanted to remind you that the items you placed in your cart are still waiting for you. Don\'t miss out, complete your purchase by clicking the button below.',
                'status'  => 1
            ],
            [
                'value'   => 3,
                'unit'    => 'days',
                'subject' => 'Last chance for your saved items',
                'content' => 'We have been holding your selected items for three days, but we will have to put them back on the shelf soon. If you still want to get them, now is the time!',
                'status'  => 1
            ],
            [
                'value'   => '',
                'unit'    => 'hours',
                'subject' => '',
                'content' => '',
                'status'  => 0
            ],
            [
                'value'   => '',
                'unit'    => 'hours',
                'subject' => '',
                'content' => '',
                'status'  => 0
            ]
        ];

        $defaultSettings = [
            'module_chataiwd_prompt' => 'Respond as if you were a store employee.', // Frissítve a nyelvfüggő szöveg helyett egy statikus értékkel
            'module_chataiwd_package' => 'free',
            'module_chataiwd_color' => '#2f9fb3',
            'module_chataiwd_rewards' => '50,100,500,1000',
            'module_chataiwd_registration_id' => '',
            'module_chataiwd_faq' => '',
            'module_chataiwd_tool_bell'  => '1',
            'module_chataiwd_tool_voice' => '1',
            'module_chataiwd_tool_image' => '1',
            'module_chataiwd_tool_emoji' => '1',
            'module_chataiwd_tool_email' => '1',
            'module_chataiwd_tool_whatsapp' => '1',
            'module_chataiwd_whatsapp_number' => '',
            'module_chataiwd_tool_faq'   => '1',
            'module_chataiwd_recovery'   => $module_chataiwd_recovery,
        ];

        foreach ($defaultSettings as $key => $defaultValue) {
            if (!in_array($key, $settingKeys)) {
                // Ha tömb (pl. recovery vagy faq), JSON formátumba hozzuk
                if (is_array($defaultValue)) {
                    $defaultValue = json_encode($defaultValue, JSON_UNESCAPED_UNICODE);
                }

                $this->db->query("INSERT INTO " . DB_PREFIX . "chat_setting SET 
                `key` = '" . $this->db->escape($key) . "', 
                `value` = '" . $this->db->escape($defaultValue) . "', 
                `store_id` = 0");
            }
        }
    }

    public function getSetting() {
        $sql = "SELECT * FROM " . DB_PREFIX . "chat_setting";
        $query = $this->db->query($sql);

        return $query->rows; // Visszaadjuk a sorokat tömbként
    }

    public function getSettingKey($key) {
        $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "chat_setting WHERE `key` = '" . $this->db->escape($key) . "' AND `store_id` = 0");

        if ($query->num_rows) {
            $value = $query->row['value'];

            // Megpróbáljuk dekódolni
            $decoded = json_decode($value, true);

            // Ha sikeres a dekódolás (és tömböt kaptunk), adjuk vissza azt.
            // Ellenkező esetben adjuk vissza a nyers értéket.
            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : $value;
        }

        return null; // Vagy [] - döntsd el, mi legyen az alapértelmezett, ha nincs érték
    }

    public function updateSetting($key, $value) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "chat_setting WHERE `key` = '" . $this->db->escape($key) . "' AND `store_id` = 0");

        if ($query->num_rows) {
            $this->db->query("UPDATE " . DB_PREFIX . "chat_setting SET `value` = '" . $this->db->escape($value) . "' WHERE `key` = '" . $this->db->escape($key) . "' AND `store_id` = 0");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "chat_setting (`key`, `value`, `store_id`) VALUES ('" . $this->db->escape($key) . "', '" . $this->db->escape($value) . "', 0)");
        }
    }

    public function deleteSetting($key)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "chat_setting WHERE `key` = '" . $this->db->escape($key) . "'");
    }

    public function saveSettings($data) {
        // A megadott beállítások mentése a chat_setting táblába
        $settingsToSave = [
            'module_chataiwd_registration_id',
            'module_chataiwd_package',
            'module_chataiwd_prompt',
            'module_chataiwd_color',
            'module_chataiwd_rewards',
            'module_chataiwd_faq',
            'module_chataiwd_tool_bell',
            'module_chataiwd_tool_voice',
            'module_chataiwd_tool_image',
            'module_chataiwd_tool_emoji',
            'module_chataiwd_tool_email',
            'module_chataiwd_tool_faq',
            'module_chataiwd_tool_whatsapp',
            'module_chataiwd_whatsapp_number',
            'module_chataiwd_recovery',
        ];


        foreach ($settingsToSave as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                $this->updateSetting($key, $value);
            }
        }


        if (isset($data['chat_setting_description'])) {
            foreach ($data['chat_setting_description'] as $language_id => $values) {
                foreach ($values as $key => $value) {

                    // MEGOLDÁS: Megnézzük létezik-e a kulcs, ha nem, létrehozzuk
                    $query = $this->db->query("SELECT id FROM " . DB_PREFIX . "chat_setting WHERE `key` = '" . $this->db->escape($key) . "' LIMIT 1");

                    if ($query->num_rows) {
                        $chat_setting_id = $query->row['id'];
                    } else {
                        // Ha még nincs ilyen kulcs, beszúrjuk üres értékkel a fő táblába
                        $this->db->query("INSERT INTO " . DB_PREFIX . "chat_setting SET `key` = '" . $this->db->escape($key) . "', `value` = '', `store_id` = 0");
                        $chat_setting_id = $this->db->getLastId();
                    }

                    // Most már biztosan van chat_setting_id, jöhet a nyelvi mentés
                    $this->db->query("DELETE FROM " . DB_PREFIX . "chat_setting_description WHERE chat_setting_id = '" . (int)$chat_setting_id . "' AND language_id = '" . (int)$language_id . "'");

                    $this->db->query("INSERT INTO " . DB_PREFIX . "chat_setting_description SET 
                    chat_setting_id = '" . (int)$chat_setting_id . "', 
                    language_id = '" . (int)$language_id . "', 
                    `value` = '" . $this->db->escape($value) . "'");
                }
            }
        }
    }

    public function getSettingDescriptions($key = '') {
        $data = [];
        $sql = "SELECT cs.key, csd.language_id, csd.value 
            FROM " . DB_PREFIX . "chat_setting_description csd 
            LEFT JOIN " . DB_PREFIX . "chat_setting cs ON (csd.chat_setting_id = cs.id)";

        if ($key) {
            $sql .= " WHERE cs.key = '" . $this->db->escape($key) . "'";
        }

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $data[$result['key']][$result['language_id']] = $result['value'];
        }

        return $data;
    }
    public function getCoupons() {

        $sql = "SELECT * FROM " . DB_PREFIX . "coupon 
        WHERE status = '1' 
        AND ((date_start = '0000-00-00' OR date_start <= NOW()) 
        AND (date_end = '0000-00-00' OR date_end >= NOW()))
        ORDER BY name ASC";

        $query = $this->db->query($sql);

        return $query->rows;
    }
}