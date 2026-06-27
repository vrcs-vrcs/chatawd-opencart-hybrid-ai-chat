<?php
namespace Opencart\Catalog\Controller\Extension\chataiwd\Module;

class chataiwdSearch extends \Opencart\System\Engine\Controller {

    private $max = 20;
    private $max_product = 100;
    private $keywords = [];
    private $intent;
    private $attributes;
    private $store_data = [];
    private $method_separator = '';
    private $attributes_articulated = [];
    private $query_vector;

    private const RELEVANCE_LOW = 3;
    private const RELEVANCE_MEDIUM = 8;
    private const RELEVANCE_HIGH = 15;

    public function index(array $args) {
        $this->keywords = $args['keywords'] ?? [];
        $this->query_vector = $args['query_vector'] ?? [];
        $standardized_keywords = [];

        foreach ($this->keywords as $keyword_phrase) {
            $words = explode(' ', trim($keyword_phrase));
            foreach ($words as $word) {
                $word = trim($word);
                if (!empty($word)) {
                    $standardized_keywords[] = mb_strtolower($word, 'UTF-8');
                }
            }
        }
        $this->keywords = array_unique($standardized_keywords);

        if (!empty($args['attributes'])) {
            $this->attributes = array_map(function($attr) {
                return mb_strtolower(trim($attr), 'UTF-8');
            }, (array)$args['attributes']);
        } else {
            $this->attributes = [];
        }

        $this->articulateAttributes();
        $this->intent   = $args['intent'] ?? null;
        $this->method_separator   = $args['method_separator'] ?? null;

        $this->load->model('extension/chataiwd/module/chataiwd');
        $this->load->model('catalog/product');
        $this->load->model('catalog/information');


        switch ($this->intent) {

            case 'Product':
                $this->getProduct();
                $this->getCategory();

                if (empty($this->store_data) && !empty($this->keywords)) {
                    $this->getPromotion();
                }
                break;

            case 'PromotionDiscount':
                if (!$this->getPromotionDiscount()) $this->getPromotion();
                break;

            case 'PromotionFeatured':
                if (!$this->getPromotionFeatured()) $this->getPromotion();
                break;

            case 'PromotionNewest':
                if (!$this->getPromotionNewest()) $this->getPromotion();
                break;

            case 'PromotionPopular':
                if (!$this->getPromotionPopular()) $this->getPromotion();
                break;

            case 'Promotion':
                $this->getPromotion();
                break;

            case 'Category':
                $this->getCategory();
                $this->getProduct();

                break;

            case 'Manufacturer':
                $this->getManufacturer();
                break;

            case 'Shipping':
                $this->getShipping();
                break;

            case 'Payment':
                $this->getPayment();
                break;

            case 'Information':
                $this->getInformation();
                if (empty($this->store_data))  $this->getSitemap();
                break;

            case 'OrderStatus':
                $this->getOrderStatus();
                break;

            case 'Return':
                $this->getReturn();
                break;

            case 'Contact':
                $this->getContact();
                break;

            case 'Account':
                $this->getAccount();
                break;

            case 'Cart':
                $this->getCart();
                break;

            case 'Sitemap':
                $this->getSitemap();
                break;

            default:
                $this->getPromotion();
        }

        if (!empty($this->store_data)) {
            usort($this->store_data, function ($a, $b) {
                $scoreA = $a['relevance_score'] ?? 0;
                $scoreB = $b['relevance_score'] ?? 0;
                return $scoreB <=> $scoreA;
            });
        }
        $this->store_data = array_slice($this->store_data, 0, 20);

        $json = json_encode($this->store_data,JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $json;
    }


    private function getProduct() {
        $this->load->model('catalog/product');

        $data_storage = [];

        // 1. Alap lekérdezés
        if (empty($this->keywords) && empty($this->query_vector)) {
            $filter_data = [
                'sort'  => 'p.date_added',
                'order' => 'DESC',
                'start' => 0,
                'limit' => $this->max,
            ];
            $results = $this->model_catalog_product->getProducts($filter_data);

            foreach ($results as $product) {
                $product['relevance_score'] = self::RELEVANCE_LOW;
                $data_storage[$product['product_id']] = $product;
            }

        } else {
            if (!empty($this->query_vector) && $this->intent === 'Product') {
                foreach ($this->query_vector as $product_id => $v_score) {
                    $product_info = $this->model_catalog_product->getProduct($product_id);
                    if ($product_info) {
                        $product_info['relevance_score'] = $v_score * self::RELEVANCE_MEDIUM;
                        $data_storage[$product_id] = $product_info;
                    }
                }
            }

            foreach ($this->keywords as $keyword) {
                $results = $this->performDatabaseSearch($keyword);

                $attempt = 0;
                $temp_keyword = $keyword;

                while (empty($results) && mb_strlen($temp_keyword) > 4 && $attempt < 3) {
                    $temp_keyword = mb_substr($temp_keyword, 0, -1); // Levágunk egy karaktert a végéről
                    $results = $this->performDatabaseSearch($temp_keyword);
                    $attempt++;
                }
                $keyword = $temp_keyword;

                if (!empty($results)) {
                    foreach ($results as $result) {
                        $penalty = ($attempt > 0) ? 0.7 : 1.0;
                        $relevance_score = self::RELEVANCE_LOW * $penalty;

                        $name = mb_strtolower($result['name'], 'UTF-8');
                        $description = mb_strtolower(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 'UTF-8');

                        // 1. Relevancia pontozás az AKTUÁLIS kulcsszóra
                        if (mb_strpos($name, $keyword) !== false) {
                            $relevance_score += (self::RELEVANCE_MEDIUM * $penalty);
                        }
                        if (mb_strpos($description, $keyword) !== false) {
                            $relevance_score += (self::RELEVANCE_LOW * $penalty);
                        }

                        if (!empty($this->attributes)) {
                            foreach ($this->attributes as $attrKeyword) {
                                $attrKeyword = mb_strtolower($attrKeyword, 'UTF-8');
                                if (mb_strpos($description, $attrKeyword) !== false) {
                                    $relevance_score += self::RELEVANCE_MEDIUM * $penalty;
                                }
                            }

                            foreach ($this->attributes_articulated as $partKeyword) {
                                if (mb_strpos($description, $partKeyword) !== false) {
                                    $relevance_score += self::RELEVANCE_LOW * $penalty;
                                }
                            }
                        }

                        $current_score = $data_storage[$result['product_id']]['relevance_score'] ?? 0;
                        $result['relevance_score'] = $current_score + $relevance_score;

                        // 3. Tárolás (és felülírás)
                        $data_storage[$result['product_id']] = $result;
                    }
                }
            }

            usort($data_storage, function($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });

            $data_storage = array_slice($data_storage, 0, $this->max);
        }

        foreach ($data_storage as &$product) {
            if (!empty($this->attributes)) {
                $attr_score = $this->getProductAttributesScore($product['product_id']);
                $product['relevance_score'] += $attr_score['score'];
                $product['attributes'] = $attr_score['found'];
            }
        }
        unset($product); // Fontos a referencia megszüntetése!

        if (!empty($data_storage)) {
            uasort($data_storage, function($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });
        }

        $final_products = array_slice($data_storage, 0, $this->max);
        foreach ($final_products as $product) {
            $prices = $this->getPrices($product);

            $item = [
                'type' => 'Product',
                'id' => $product['product_id'],
                'link' => 'product/product',
                'path' => 'product_id',
                'title' => $product['name'],
                'description' => $this->formatDescription($product['description']),
                'price' => $prices['price'],
                'special' => $prices['special'],
                'quantity' => $product['quantity'],
                'attributes' => $product['attributes'] ?? [],
                'relevance_score' => $product['relevance_score'] ?? self::RELEVANCE_LOW,
            ];

            $this->store_data[] = $this->storeData($item);
        }
    }


    private function performDatabaseSearch($keyword) {
        $filter_data = [
            'filter_name'         => $keyword,
            'filter_search'       => $keyword,
            'filter_tag'          => $keyword,
            'filter_description'  => $keyword,
            'start'               => 0,
            'limit'               => $this->max_product,
        ];
        return $this->model_catalog_product->getProducts($filter_data);
    }

    private function getPromotionDiscount() {

        $getSpecials = function($filter_data) {
            try {
                return $this->model_catalog_product->getSpecials($filter_data);
            } catch (\Throwable $e) {
                try {
                    return $this->model_catalog_product->getProductSpecials($filter_data);
                } catch (\Throwable $e) {
                    return [];
                }
            }
        };

        $data_storage = [];

        if (empty($this->keywords)) {
            $filter_data = ['start' => 0, 'limit' => $this->max];
            $results = $getSpecials($filter_data);

            foreach ($results as $result) {
                $result['relevance_score'] = self::RELEVANCE_MEDIUM;
                $data_storage[$result['product_id']] = $result;
            }
        } else {
            // 2️⃣ Kulcsszavas keresés
            foreach ($this->keywords as $keyword) {
                $filter_data = [
                    'filter_search' => $keyword,
                    'filter_name' => $keyword,
                    'filter_tag' => $keyword,
                    'filter_description' => $keyword,
                    'start' => 0,
                    'limit' => $this->max,
                ];
                $results = $getSpecials($filter_data);

                foreach ($results as $result) {
                    $current_score = $data_storage[$result['product_id']]['relevance_score'] ?? 0;

                    $result['relevance_score'] = $current_score + self::RELEVANCE_HIGH;
                    $data_storage[$result['product_id']] = $result;
                }
            }
        }

        if (!empty($this->keywords)) {
            uasort($data_storage, function($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });
        }

        $final_products = array_slice($data_storage, 0, $this->max);

        // 5️⃣ Árformázás + storeData
        foreach ($final_products as $product) {
            $prices = $this->getPrices($product);

            $item = [
                'type' => 'Product',
                'id' => $product['product_id'],
                'link' => 'product/product',
                'path' => 'product_id',
                'title' => $product['name'],
                'description' => $this->formatDescription($product['description']),
                'price' => $prices['price'],
                'special' => $prices['special'],
                'quantity' => $product['quantity'],
                'attributes' => $product['attributes'] ?? [],
                'relevance_score' => $product['relevance_score'] ?? self::RELEVANCE_LOW,
            ];

            $this->store_data[] = $this->storeData($item);
        }

        return !empty($data_storage);
    }

    private function getPromotionFeatured() {
        $data_storage = [];

        $results = $this->model_extension_chataiwd_module_chataiwd->getModulesByCode('opencart.featured');
        if (!$results) {
            $results = $this->model_extension_chataiwd_module_chataiwd->getModulesByCode('featured');
        }

        if ($results) {
            foreach ($results as $row) {
                $setting = json_decode($row['setting'], true);
                if (isset($setting['product']) && is_array($setting['product'])) {
                    foreach ($setting['product'] as $product_id) {
                        $product = $this->model_catalog_product->getProduct($product_id);

                        if ($product) {
                            $relevance_score = self::RELEVANCE_MEDIUM; // Alap relevanciát adunk

                            if (!empty($this->keywords)) {
                                foreach ($this->keywords as $keyword) {
                                    if (mb_strpos(mb_strtolower($product['name'], 'UTF-8'), $keyword) !== false) {
                                        $relevance_score += self::RELEVANCE_MEDIUM;
                                    }
                                    if (mb_strpos(mb_strtolower($product['description'], 'UTF-8'), $keyword) !== false) {
                                        $relevance_score += self::RELEVANCE_LOW;
                                    }
                                }
                            }
                            $current_score = $data_storage[$product['product_id']]['relevance_score'] ?? 0;
                            $product['relevance_score'] = $current_score + $relevance_score;

                            $data_storage[$product['product_id']] = $product;
                        }
                    }
                }
            }

            if (!empty($this->keywords)) {
                uasort($data_storage, function($a, $b) {
                    return $b['relevance_score'] <=> $a['relevance_score'];
                });
            }
            $final_products = array_slice($data_storage, 0, $this->max);

            foreach ($final_products as $product) {
                $prices = $this->getPrices($product);

                $item = [
                    'type' => 'Product',
                    'id' => $product['product_id'],
                    'link' => 'product/product',
                    'path' => 'product_id',
                    'title' => $product['name'],
                    'description' => $this->formatDescription($product['description']),
                    'price' => $prices['price'],
                    'special' => $prices['special'],
                    'quantity' => $product['quantity'],
                    'attributes' => $product['attributes'] ?? [],
                    'relevance_score' => $product['relevance_score'] ?? self::RELEVANCE_LOW,
                ];

                $this->store_data[] = $this->storeData($item);
            }
        }

        return !empty($data_storage);
    }

    private function getPromotionNewest() {
        $data_storage = [];

        try {
            $this->load->model('extension/opencart/module/latest');
            $results = $this->model_extension_opencart_module_latest->getLatest($this->max);
        } catch (\Throwable $e) { // Exception és Error egyaránt
            try {
                $results = $this->model_catalog_product->getLatest($this->max);
            } catch (\Throwable $e) {
                try {
                    $results = $this->model_catalog_product->getLatestProducts($this->max);
                } catch (\Throwable $e) {
                    $results = [];
                }
            }
        }

        if (!empty($results)) {

            foreach ($results as $product) {
                $relevance_score = self::RELEVANCE_MEDIUM;

                if (!empty($this->keywords)) {
                    foreach ($this->keywords as $keyword) {
                        if (mb_strpos(mb_strtolower($product['name'], 'UTF-8'), $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_MEDIUM;
                        }
                        if (mb_strpos(mb_strtolower($product['description'], 'UTF-8'), $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_LOW;
                        }
                    }
                }

                $product['relevance_score'] = $relevance_score;

                $data_storage[$product['product_id']] = $product;
            }


            if (!empty($this->keywords)) {
                uasort($data_storage, function($a, $b) {
                    return $b['relevance_score'] <=> $a['relevance_score'];
                });
            }
            $final_products = array_slice($data_storage, 0, $this->max);

            foreach ($final_products as $product) {
                $prices = $this->getPrices($product);

                $item = [
                    'relevance_score' => $product['relevance_score'] ?? self::RELEVANCE_LOW,
                    'type' => 'Product',
                    'id' => $product['product_id'],
                    'link' => 'product/product',
                    'path' => 'product_id',
                    'title' => $product['name'],
                    'description' => $this->formatDescription($product['description']),
                    'price' => $prices['price'],
                    'special' => $prices['special'],
                    'quantity' => $product['quantity'],
                    'attributes' => $product['attributes'] ?? [],
                ];

                $this->store_data[] = $this->storeData($item);
            }
        }

        return !empty($data_storage);
    }

    private function getPromotionPopular() {
        $data_storage = [];

        try {
            $this->load->model('extension/opencart/module/bestseller');
            $results = $this->model_extension_opencart_module_bestseller->getBestSeller($this->max);

        } catch (\Throwable $e) { // Exception és Error egyaránt
            try {
                $this->load->model('extension/opencart/module/bestseller');
                $results = $this->model_extension_opencart_module_bestseller->getBestSellers($this->max);

            } catch (\Throwable $e) {
                try {
                    $results = $this->model_catalog_product->getBestSeller($this->max);
                } catch (\Throwable $e) {
                    try {
                        $results = $this->model_catalog_product->getBestSellerProducts($this->max);
                    } catch (\Throwable $e) {
                        $results = [];
                    }
                }
            }
        }

        if (!empty($results)) {

            foreach ($results as $product) {
                $relevance_score = self::RELEVANCE_MEDIUM; // Alap relevanciát adunk (Legnépszerűbb)

                if (!empty($this->keywords)) {

                    foreach ($this->keywords as $keyword) {
                        if (mb_strpos(mb_strtolower($product['name'], 'UTF-8'), $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_MEDIUM;
                        }
                        if (mb_strpos(mb_strtolower($product['description'], 'UTF-8'), $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_LOW;
                        }
                    }
                }
                $product['relevance_score'] = $relevance_score;

                $data_storage[$product['product_id']] = $product;
            }

            if (!empty($this->keywords)) {
                uasort($data_storage, function($a, $b) {
                    return $b['relevance_score'] <=> $a['relevance_score'];
                });
            }
            $final_products = array_slice($data_storage, 0, $this->max);

            foreach ($final_products as $product) {
                $prices = $this->getPrices($product);

                $item = [
                    'type' => 'Product',
                    'id' => $product['product_id'],
                    'link' => 'product/product',
                    'path' => 'product_id',
                    'title' => $product['name'],
                    'description' => $this->formatDescription($product['description']),
                    'price' => $prices['price'],
                    'special' => $prices['special'],
                    'quantity' => $product['quantity'],
                    'attributes' => $product['attributes'] ?? [],
                    'relevance_score' => $product['relevance_score'] ?? self::RELEVANCE_LOW,
                ];

                $this->store_data[] = $this->storeData($item);
            }
        }

        return !empty($data_storage);
    }

    private function getPromotion() {
        if ($this->getPromotionDiscount()) {
            return 'discount';
        }
        if ($this->getPromotionFeatured()) {
            return 'featured';
        }
        if ($this->getPromotionNewest()) {
            return 'newest';
        }
        if ($this->getPromotionPopular()) {
            return 'popular';
        }

        $this->getProduct();
        return 'default';
    }

    private function getCategory() {
        $categories = $this->model_extension_chataiwd_module_chataiwd->getCategories();

        if (empty($this->keywords) && empty($this->query_vector)) {

            $data = [
                'link' => 'information/sitemap',
                'type' => 'SiteMap',
                'name' => 'SiteMap',
                'title' => 'SiteMap',
                'id' => 'sitemap',
                'path' => 'sitemap',
                'relevance_score' => self::RELEVANCE_LOW,
            ];
            $this->store_data[] = $this->storeData($data);
        }

        $data_storage = [];

        if (!empty($this->query_vector) && $this->intent === 'Category') {
            $id_to_path = array_column($categories, 'path', 'category_id');

            foreach ($this->query_vector as $category_id => $v_score) {
                if (isset($id_to_path[$category_id])) {
                    $path = $id_to_path[$category_id];
                    $data_vector_relevancia[$path] = $v_score * self::RELEVANCE_MEDIUM;
                }

            }
        }

        foreach ($categories as $category) {
            $category_id = $category['path'];
            $relevance_score = self::RELEVANCE_LOW; // Alap pontszám

            if (!empty($this->keywords)) {
                $category_name = mb_strtolower(($category['name'] ?? ''), 'UTF-8');
                $category_description = mb_strtolower(($category['description'] ?? ''), 'UTF-8');

                foreach ($this->keywords as $keyword) {

                    if (mb_strpos($category_name, $keyword) !== false) {
                        $relevance_score += self::RELEVANCE_HIGH;
                    }

                    if (mb_strpos($category_description, $keyword) !== false) {
                        $relevance_score += self::RELEVANCE_MEDIUM;
                    }
                }
            }
            $data_storage[$category_id] = [
                'type' => 'Category',
                'id' => $category_id,
                'path' => 'path',
                'link' => 'product/category',
                'title' => $category['name'],
                'description' => $category['description'],
                'relevance_score' => ($data_vector_relevancia[$category_id] ?? 0) + $relevance_score,
            ];
        }

        if (!empty($this->keywords)) {
            uasort($data_storage, function($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });
        }
        $data_storage = array_slice($data_storage, 0, $this->max);

        foreach ($data_storage as $category) {
            $data = [
                'link' => 'product/category',
                'path' => 'path',
                'type' => 'Category',
                'id' => $category['id'],
                'title' => $category['title'],
                'relevance_score' => $category['relevance_score'],
                'description' => $this->formatDescription($category['description']),
            ];
            $this->store_data[] = $this->storeData($data);
        }

        return !empty($data_storage);
    }

    private function getManufacturer() {
        $manufacturers = $this->model_extension_chataiwd_module_chataiwd->getManufacturers();

        $data_storage = [];

        foreach ($manufacturers as $manufacturer) {
            $manufacturer_id = $manufacturer['manufacturer_id'];
            $manufacturer_name = $manufacturer['name'];
            $relevance_score = self::RELEVANCE_LOW;

            if (!empty($this->keywords)) {
                $manufacturer_lower = mb_strtolower($manufacturer_name, 'UTF-8');

                foreach ($this->keywords as $keyword) {

                    if (mb_strpos($manufacturer_lower, $keyword) !== false) {
                        $relevance_score += self::RELEVANCE_HIGH;
                    }
                }
            }

            $data_storage[$manufacturer_id] = [
                'type' => 'Manufacturer',
                'id' => $manufacturer_id,
                'path' => 'manufacturer_id',
                'link' => 'product/manufacturer' . $this->method_separator . 'info',
                'title' => $manufacturer_name,
                'description' => '',
                'relevance_score' => $relevance_score,
            ];
        }

        if (!empty($this->keywords)) {
            uasort($data_storage, function($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });
        }
        $data_storage = array_slice($data_storage, 0, $this->max);

        foreach ($data_storage as $manufacturer) {
            $item = [
                'link' => $manufacturer['link'],
                'path' => $manufacturer['path'],
                'type' => 'Manufacturer',
                'id' => $manufacturer['id'],
                'title' => $manufacturer['title'],
                'relevance_score' => $manufacturer['relevance_score'],
                'description' => $this->formatDescription($manufacturer['description']), // Bár üres, egységes formátum
            ];
            $this->store_data[] = $this->storeData($item);
        }

        return !empty($data_storage);
    }

    private function getShipping() {
        $data_storage = [];
        $shipping_methods = $this->model_extension_chataiwd_module_chataiwd->getShippingMethods();
        $informations = $this->model_catalog_information->getInformations();
        $shipping_lang = $this->getSearchTerms('shipping');

        if (!empty($shipping_methods)) {
            foreach ($shipping_methods as $method) {
                $method_id = $method['title'] ?: $method['code'];
                $relevance_score = 2 * self::RELEVANCE_HIGH;

                if (!empty($this->keywords)) {
                    $title_lower = mb_strtolower($method['title'], 'UTF-8');

                    foreach ($this->keywords as $keyword) {
                        if (mb_strpos($title_lower, $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_HIGH;
                        }
                    }
                }

                $data_storage[$method_id] = [
                    'relevance_score' => $relevance_score,
                    'type' => 'Shipping Info',
                    'id' => $method_id,
                    'link' => '',
                    'path' => '',
                    'title' => $method_id,
                    'description' => $method['text'] ?? '',
                    'price' => $method['text'] ?? '',
                ];
            }
        }

        if (!empty($informations)) {
            foreach ($informations as $info) {
                $info_id = $info['information_id'];
                $relevance_score = 0;

                $title_lower = mb_strtolower($info['title'], 'UTF-8');
                $desc_lower = mb_strtolower($info['description'], 'UTF-8');

                if (mb_strpos($title_lower, $shipping_lang) !== false) {
                    $relevance_score += self::RELEVANCE_MEDIUM;
                }
                if (mb_strpos($desc_lower, $shipping_lang) !== false) {
                    $relevance_score += self::RELEVANCE_LOW;
                }

                if (!empty($this->keywords)) {
                    foreach ($this->keywords as $keyword) {

                        if (mb_strpos($title_lower, $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_MEDIUM;
                        }
                        if (mb_strpos($desc_lower, $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_LOW;
                        }
                    }
                }

                if ($relevance_score) {
                    $data_storage[$info_id] = [
                        'relevance_score' => $relevance_score,
                        'type' => 'Shipping Info',
                        'id' => $info_id,
                        'link' => 'information/information',
                        'path' => 'information_id',
                        'title' => $info['title'],
                        'description' => $info['description'],
                    ];
                }
            }
        }

        if (!empty($data_storage)) {
            uasort($data_storage, function($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });
        }
        $data_storage = array_slice($data_storage, 0, $this->max);

        foreach ($data_storage as $item) {
            $final_item = [
                'relevance_score' => $item['relevance_score'],
                'type' => 'Shipping Info',
                'id' => $item['id'],
                'link' => $item['link'] ?? '',
                'path' => $item['path'] ?? '',
                'title' => $item['title'],
                'description' => $this->formatDescription($item['description']),
            ];

            if (isset($item['price'])) {
                $final_item['price'] = $item['price'];
            }

            $this->store_data[] = $this->storeData($final_item);
        }

        return !empty($data_storage);
    }

    private function getPayment() {
        $data_storage = [];

        $this->load->model('checkout/payment_method');
        $payment_address = $this->model_extension_chataiwd_module_chataiwd->getPaymentAddress();
        $payment_methods = $this->model_checkout_payment_method->getMethods($payment_address);
        $informations = $this->model_catalog_information->getInformations();
        $payment_lang = $this->getSearchTerms('payment');

        if (!empty($payment_methods)) {
            foreach ($payment_methods as $method) {
                $method_id = $method['code'];

                $relevance_score = 2 * self::RELEVANCE_HIGH;

                if (!empty($this->keywords)) {
                    $title_lower = mb_strtolower($method['title'] ?? ($method['name'] ?? ''), 'UTF-8');

                    foreach ($this->keywords as $keyword) {
                        if (mb_strpos($title_lower, $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_HIGH;
                        }
                    }
                }

                $data_storage[$method_id] = [
                    'relevance_score' => $relevance_score,
                    'type' => 'Payment Info',
                    'id' => $method_id,
                    'link' => '',
                    'path' => '',
                    'title' => $method['name'] ?? ($method['title'] ?? $method_id),
                    'description' => $method['description'] ?? '',
                ];
            }
        }

        if (!empty($informations)) {
            foreach ($informations as $info) {
                $info_id = $info['information_id'];
                $relevance_score = 0;

                $title_lower = mb_strtolower($info['title'] ?? '', 'UTF-8');
                $desc_lower = mb_strtolower($info['description'] ?? '', 'UTF-8');

                if (mb_strpos($title_lower, $payment_lang) !== false || mb_strpos($title_lower, 'payment') !== false) {
                    $relevance_score += self::RELEVANCE_MEDIUM;
                }
                if (mb_strpos($desc_lower, $payment_lang) !== false || mb_strpos($desc_lower, 'payment') !== false) {
                    $relevance_score += self::RELEVANCE_LOW;
                }

                // Pontozás felhasználói kulcsszavakra
                if (!empty($this->keywords)) {
                    foreach ($this->keywords as $keyword) {
                        if (mb_strpos($title_lower, $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_MEDIUM; // Címre közepes pont
                        }
                        if (mb_strpos($desc_lower, $keyword) !== false) {
                            $relevance_score += self::RELEVANCE_LOW; // Leírásra alacsony pont
                        }
                    }
                }

                if ($relevance_score > 0) {
                    $data_storage[$info_id] = [
                        'relevance_score' => $relevance_score,
                        'type' => 'Payment Info',
                        'id' => $info_id,
                        'link' => 'information/information',
                        'path' => 'information_id',
                        'title' => $info['title'],
                        'description' => $info['description'],
                    ];
                }
            }
        }

        if (!empty($data_storage)) {
            uasort($data_storage, function($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });
        }
        $data_storage = array_slice($data_storage, 0, $this->max);

        foreach ($data_storage as $item) {
            $final_item = [
                'relevance_score' => $item['relevance_score'],
                'type' => 'Payment Info',
                'id' => $item['id'],
                'link' => $item['link'] ?? '',
                'path' => $item['path'] ?? '',
                'title' => $item['title'],
                'description' => $this->formatDescription($item['description']),
            ];

            $this->store_data[] = $this->storeData($final_item);
        }

        return !empty($data_storage);
    }

    private function getInformation() {
        if (empty($this->keywords) && empty($this->query_vector)) {
            return false;
        }

        $info_vector_relevancia = [];
        if (!empty($this->query_vector) && $this->intent === 'Information') {
            if (!empty($this->query_vector)) {
                foreach ($this->query_vector as $info_id => $v_score) {
                    $info_vector_relevancia[$info_id] = $v_score * self::RELEVANCE_MEDIUM;
                }
            }
        }

        $data_storage = [];
        $this->load->model('catalog/information');
        $informations = $this->model_catalog_information->getInformations();

        if (empty($informations)) {
            return false;
        }

        foreach ($informations as $info) {
            $info_id = $info['information_id'];
            $description_lower = mb_strtolower($info['description'], 'UTF-8');
            $title_lower = mb_strtolower($info['title'], 'UTF-8');

            $relevance_score = 0;

            foreach ($this->keywords as $keyword) {
                if (mb_strpos($title_lower, $keyword) !== false) {
                    $relevance_score += self::RELEVANCE_HIGH;
                }

                if (mb_strpos($description_lower, $keyword) !== false) {
                    $relevance_score += self::RELEVANCE_MEDIUM;
                }
            }
            $total_relevance = $relevance_score + ($info_vector_relevancia[$info_id] ?? 0);

            if ($total_relevance) {
                $data_storage[$info_id] = [
                    'type' => 'Information',
                    'id' => $info_id,
                    'link' => 'information/information',
                    'path' => 'information_id',
                    'title' => $info['title'],
                    'description' => $info['description'],
                    'relevance_score' => $total_relevance,
                ];
            }
        }

        if (!empty($data_storage)) {
            uasort($data_storage, function($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });
        }
        $data_storage = array_slice($data_storage, 0, $this->max);

        foreach ($data_storage as $value) {
            $final_item = [
                'relevance_score' => $value['relevance_score'],
                'type' => 'Information',
                'id' => $value['id'],
                'link' => $value['link'] ?? '',
                'path' => $value['path'] ?? '',
                'title' => $value['title'],
                'description' => $this->formatDescription($value['description']),
            ];

            $this->store_data[] = $this->storeData($final_item);
        }

        return !empty($data_storage);
    }

    private function getOrderStatus() {
        if (!$this->customer->isLogged()) $desc = "Please log in to view the status of your order.";

         $data = [
             'link' => 'account/order',
             'type' => 'Order',
             'description' => $this->formatDescription(''),
         ];
        if (!$this->customer->isLogged()) $data['description'] = "Please log in to view the status of your order.";

        $this->store_data[] = $this->storeData($data);
    }

    private function getReturn() {
        $result = $this->load->controller('account/return');
        if (mb_strpos($result, 'Error: Could not call') === false) {
            $link = 'account/return';
        } else {
            $link = 'account/returns';
        }

        $data = [
            'link' => $link,
            'type' => 'Return',
            'relevance_score' => self::RELEVANCE_LOW,

        ];
        if (!$this->customer->isLogged()) $data['description'] = "Please log in to view the status of your return.";

        $this->store_data[] = $this->storeData($data);
    }

    private function getContact() {
        $data = [
            'link' => 'information/contact',
            'type' => 'Contact',
            'relevance_score' => self::RELEVANCE_LOW,
        ];
        $this->store_data[] = $this->storeData($data);
    }

    private function getAccount() {
        $data = [
            'link' => 'account/account',
            'type' => 'Account',
            'relevance_score' => self::RELEVANCE_LOW,
        ];
        if (!$this->customer->isLogged()) $data['description'] = "Please log in to your account to view your details. If you don’t have an account yet, please register.";

        $this->store_data[] = $this->storeData($data);
    }

    private function getCart() {
        $products = $this->cart->getProducts();

        // 1. Termékek feldolgozása egyenként
        foreach ($products as $product) {
            $data = [
                'type'            => 'cart_item',
                'id'              => $product['product_id'],
                'title'           => $product['name'],
                'quantity'        => $product['quantity'],
                'price'           => $this->currency->format($product['price'], $this->session->data['currency']),
                'total'           => $this->currency->format($product['total'], $this->session->data['currency']),
                'link'            => 'product/product', // A storeData-nak kell a route
                'path'            => 'product_id',      // A storeData-nak kell a paraméter neve
                'relevance_score' => 100                // A kosár mindig nagyon releváns
            ];

            // A storeData() metódusoddal formázzuk, hogy megkapja a linket és a struktúrát
            $this->store_data[] = $this->storeData($data);
        }

        // 2. Összesítő hozzáadása (akár üres a kosár, akár nem)
        $summary = [
            'type'            => 'cart_summary',
            'id'              => 'total', // Kell egy fix ID az UID generáláshoz
            'title'           => 'Cart summary',
            'price'           => $this->currency->format($this->cart->getTotal(), $this->session->data['currency']),
            'quantity'        => (int)$this->cart->countProducts(),
            'description'     => empty($products) ? 'Your cart is currently empty.' : 'The cart is active.',
            'relevance_score' => 100
        ];

        // Ezt is átfuttatjuk a formázón
        $this->store_data[] = $this->storeData($summary);
    }

    private function getSitemap() {
        $data = [
            'link' => 'information/sitemap',
            'type' => 'Sitemap',
            'relevance_score' => self::RELEVANCE_LOW,
        ];
        $this->store_data[] = $this->storeData($data);
    }

    private function storeData($data) {
        $response = [];

        if (!empty($data['link']) && !empty($data['path']) && !empty($data['id'])) {
            $language = $this->config->get('config_language') ?? 'en-gb';
            $url = $this->url->link($data['link'], 'language=' . $language . '&' . $data['path'] . '=' . $data['id']);

        } elseif (!empty($data['link'])) {
            $language = $this->config->get('config_language') ?? 'en-gb';
            $url = $this->url->link($data['link'], 'language=' . $language);
        }

        if (isset($this->session->data['customer_token']) && !empty($url)) {
            $token_required_links = [
                'account/wishlist',
                'account/account',
                'account/order',
                'account/transaction',
                'account/download',
                'account/edit',
                'account/password',
                'account/address',
                'account/return',
                'account/returns',
            ];
            if (in_array($data['link'], $token_required_links)) {
                $url .= '&customer_token=' . $this->session->data['customer_token'];
            }
        }

        // JSON objektum felépítése
        $response['relevance_score'] = $data['relevance_score'] ?? self::RELEVANCE_LOW;

        $response['type'] = $data['type'] ?? 'default';
        $response['id'] = $data['id'] ?? '';

        if (isset($data['title'])) {
            $response['title'] = $data['title'];
        }
        if (isset($url)) {
            $response['link'] = $url;
        }
        if (isset($data['description'])) {
            $response['description'] = $data['description'];
        }
        if (isset($data['price'])) {
            $response['price'] = $data['price'];
        }
        if (isset($data['special'])) {
            $response['special'] = $data['special'];
        }
        if (isset($data['quantity'])) {
            $response['quantity'] = $data['quantity'];
        }
        if (!empty($data['attributes'])) {
            $response['attributes'] = $data['attributes'];
        }
        if (!empty($data['total'])) {
            $response['total'] = $data['total'];
        }

        return $response;
    }

    private function formatDescription($description, $length = 150) {
        $description = $description ?? '';
        $description = substr(strip_tags(html_entity_decode($description)), 0, $length);
        $description = preg_replace('/\s+/', ' ', $description);
        $description = str_replace(["\r\n", "\r", "\n"], '', $description);

        return $description;
    }

    private function getPrices(array $product): array {
        $price = '';
        $special = '';

        // Adóosztály és Konfiguráció ellenőrzése
        $tax_class_id = $product['tax_class_id'] ?? 0;
        $config_tax = $this->config->get('config_tax');

        // Pénznem ellenőrzése
        $currency_code = $this->session->data['currency'] ?? $this->config->get('config_currency');

        if (!empty($product['price'])) {
            $price = $this->currency->format(
                $this->tax->calculate(
                    $product['price'],
                    $tax_class_id,
                    $config_tax
                ),
                $currency_code
            );
        }

        if (!empty($product['special'])) {
            $special = $this->currency->format(
                $this->tax->calculate(
                    $product['special'],
                    $tax_class_id,
                    $config_tax
                ),
                $currency_code
            );
        }

        return ['price' => $price, 'special' => $special];
    }

    private function getProductAttributesScore($product_id) {
        $score = 0;
        $found = [];

        $attributes = $this->model_catalog_product->getAttributes($product_id);
        foreach ($attributes as $group) {
            foreach ($group['attribute'] as $attr) {
                $name = mb_strtolower($attr['name'], 'UTF-8');
                $text = mb_strtolower($attr['text'], 'UTF-8');

                foreach ($this->attributes as $attrKeyword) {
                    $attrKeyword = mb_strtolower($attrKeyword, 'UTF-8');
                    if (mb_strpos($name, $attrKeyword) !== false) {
                        $found[$attrKeyword] = $attrKeyword;
                        $score += self::RELEVANCE_HIGH;

                    } elseif (mb_strpos($text, $attrKeyword) !== false) {
                        $found[$attrKeyword] = $attrKeyword;
                        $score += self::RELEVANCE_MEDIUM;
                    }
                }

                foreach ($this->attributes_articulated as $partKeyword) {
                    if (mb_strpos($name, $partKeyword) !== false) {
                        $found[$partKeyword] = $partKeyword;
                        $score += self::RELEVANCE_LOW;

                    } elseif (mb_strpos($text, $partKeyword) !== false) {
                        $found[$partKeyword] = $partKeyword;
                        $score += self::RELEVANCE_LOW;
                    }
                }
            }
        }

        $options = $this->model_catalog_product->getOptions($product_id);
        foreach ($options as $opt) {
            foreach ($opt['product_option_value'] as $val) {
                $name = mb_strtolower($val['name'], 'UTF-8');

                foreach ($this->attributes as $attrKeyword) {
                    $attrKeyword = mb_strtolower($attrKeyword, 'UTF-8');

                    if (mb_strpos($name, $attrKeyword) !== false) {
                        $found[$attrKeyword] = $attrKeyword;
                        $score += self::RELEVANCE_MEDIUM;
                    }
                }

                foreach ($this->attributes_articulated as $partKeyword) {
                    if (mb_strpos($name, $partKeyword) !== false) {
                        $found[$partKeyword] = $partKeyword;
                        $score += self::RELEVANCE_LOW;
                    }
                }
            }
        }

        return ['score' => $score, 'found' => $found];
    }

    private function getSearchTerms($key) {
        $languageCode = $this->config->get('config_language');

        $searchTerms = [
            // Európai nyelvek
            'hu-hu' => ['shipping' => 'szállítás', 'payment' => 'fizetés', 'sitemap' => 'oldaltérkép'],
            'en-gb' => ['shipping' => 'shipping', 'payment' => 'payment', 'sitemap' => 'sitemap'],
            'de-de' => ['shipping' => 'versand', 'payment' => 'zahlung', 'sitemap' => 'seitenübersicht'],
            'fr-fr' => ['shipping' => 'expédition', 'payment' => 'paiement', 'sitemap' => 'plan du site'],
            'es-es' => ['shipping' => 'envío', 'payment' => 'pago', 'sitemap' => 'mapa del sitio'],
            'it-it' => ['shipping' => 'spedizione', 'payment' => 'pagamento', 'sitemap' => 'mappa del sito'],
            'pt-pt' => ['shipping' => 'envio', 'payment' => 'pagamento', 'sitemap' => 'mapa do site'],
            'nl-nl' => ['shipping' => 'verzending', 'payment' => 'betaling', 'sitemap' => 'sitemap'],
            'ru-ru' => ['shipping' => 'доставка', 'payment' => 'оплата', 'sitemap' => 'карта сайта'],
            'fi-fi' => ['shipping' => 'toimitus', 'payment' => 'maksu', 'sitemap' => 'sivukartta'],
            'sv-se' => ['shipping' => 'frakt', 'payment' => 'betalning', 'sitemap' => 'webbkarta'],
            'no-no' => ['shipping' => 'frakt', 'payment' => 'betaling', 'sitemap' => 'nettstedskart'],
            'da-dk' => ['shipping' => 'forsendelse', 'payment' => 'betaling', 'sitemap' => 'sitemap'],
            'pl-pl' => ['shipping' => 'wysyłka', 'payment' => 'płatność', 'sitemap' => 'mapa strony'],
            'cs-cz' => ['shipping' => 'doprava', 'payment' => 'platba', 'sitemap' => 'mapa stránek'],
            'sk-sk' => ['shipping' => 'doprava', 'payment' => 'platba', 'sitemap' => 'mapa stránky'],
            'hr-hr' => ['shipping' => 'dostava', 'payment' => 'plaćanje', 'sitemap' => 'karta stranice'],
            'sr-rs' => ['shipping' => 'достава', 'payment' => 'плаћање', 'sitemap' => 'мапа сајта'],
            'bs-ba' => ['shipping' => 'dostava', 'payment' => 'plaćanje', 'sitemap' => 'mapa stranice'],
            'sl-si' => ['shipping' => 'dostava', 'payment' => 'plačilo', 'sitemap' => 'zemljevid mesta'],
            'bg-bg' => ['shipping' => 'доставка', 'payment' => 'плащане', 'sitemap' => 'карта на сайта'],
            'ro-ro' => ['shipping' => 'livrare', 'payment' => 'plată', 'sitemap' => 'hartă site'],
            'el-gr' => ['shipping' => 'αποστολή', 'payment' => 'πληρωμή', 'sitemap' => 'χάρτης ιστοτόπου'],
            'mk-mk' => ['shipping' => 'испорака', 'payment' => 'плаќање', 'sitemap' => 'мапа на сајтот'],
            'sq-al' => ['shipping' => 'dërgesë', 'payment' => 'pagesë', 'sitemap' => 'harta e faqes'],
            'mt-mt' => ['shipping' => 'tbaħħir', 'payment' => 'ħlas', 'sitemap' => 'mappa tas-sit'],
            'ga-ie' => ['shipping' => 'loingseoireacht', 'payment' => 'íocaíocht', 'sitemap' => 'léarscáil an tsuímh'],
            'cy-cy' => ['shipping' => 'cludo', 'payment' => 'taliad', 'sitemap' => 'map o\'r safle'],
            'is-is' => ['shipping' => 'sending', 'payment' => 'greiðsla', 'sitemap' => 'vefkort'],
            'be-by' => ['shipping' => 'дастаўка', 'payment' => 'аплата', 'sitemap' => 'карта сайта'],
            'uk-ua' => ['shipping' => 'доставка', 'payment' => 'оплата', 'sitemap' => 'карта сайту'],
            'li-lu' => ['shipping' => 'versendung', 'payment' => 'bezuelung', 'sitemap' => 'sitemap'],
            // Ázsiai nyelvek
            'ja-jp' => ['shipping' => '配送', 'payment' => '支払い', 'sitemap' => 'サイトマップ'],
            'ko-kr' => ['shipping' => '배송', 'payment' => '지불', 'sitemap' => '사이트맵'],
            'zh-cn' => ['shipping' => '运输', 'payment' => '支付', 'sitemap' => '网站地图'],
            'zh-tw' => ['shipping' => '運送', 'payment' => '付款', 'sitemap' => '網站地圖'],
            'th-th' => ['shipping' => 'จัดส่ง', 'payment' => 'การชำระเงิน', 'sitemap' => 'แผนผังเว็บไซต์'],
            'vi-vn' => ['shipping' => 'vận chuyển', 'payment' => 'thanh toán', 'sitemap' => 'sơ đồ trang web'],
            'id-id' => ['shipping' => 'pengiriman', 'payment' => 'pembayaran', 'sitemap' => 'peta situs'],
            'ms-my' => ['shipping' => 'penghantaran', 'payment' => 'pembayaran', 'sitemap' => 'peta laman web'],
            'hi-in' => ['shipping' => 'शिपिंग', 'payment' => 'भुगतान', 'sitemap' => 'साइटमैप'],
            'bn-bd' => ['shipping' => 'শিপিং', 'payment' => 'পেমেন্ট', 'sitemap' => 'সাইটম্যাপ'],
            'ta-in' => ['shipping' => 'கப்பல்', 'payment' => 'கட்டணம்', 'sitemap' => 'தள வரைபடம்'],
            'ur-pk' => ['shipping' => 'شپنگ', 'payment' => 'ادائیگی', 'sitemap' => 'سائٹ میپ'],
            'fa-ir' => ['shipping' => 'ارسال', 'payment' => 'پرداخت', 'sitemap' => 'نقشه سایت'],
            'ar-sa' => ['shipping' => 'الشحن', 'payment' => 'الدفع', 'sitemap' => 'خريطة الموقع'],
            'tr-tr' => ['shipping' => 'kargo', 'payment' => 'ödeme', 'sitemap' => 'site haritası'],
            // Afrikai nyelvek
            'sw-ke' => ['shipping' => 'usafirishaji', 'payment' => 'malipo', 'sitemap' => 'ramani ya tovuti'],
            'am-et' => ['shipping' => 'መላኪያ', 'payment' => 'ክፍያ', 'sitemap' => 'የጣቢያ ካርታ'],
            'zu-za' => ['shipping' => 'ukuthutha', 'payment' => 'inkokhelo', 'sitemap' => 'imephu yesayithi'],
            'xh-za' => ['shipping' => 'ukuthumela', 'payment' => 'intlawulo', 'sitemap' => 'imephu yesayithi'],
            'yo-ng' => ['shipping' => 'gbigbe', 'payment' => 'owo sisan', 'sitemap' => 'maapu aaye'],
            'ig-ng' => ['shipping' => 'mbupu', 'payment' => 'ịkwụ ụgwọ', 'sitemap' => 'maapu saịtị'],
            'ha-ng' => ['shipping' => 'jigilar kayayyaki', 'payment' => 'biya', 'sitemap' => 'taswirar shafi'],
            // Amerikai nyelvek
            'es-mx' => ['shipping' => 'envío', 'payment' => 'pago', 'sitemap' => 'mapa del sitio'],
            'pt-br' => ['shipping' => 'frete', 'payment' => 'pagamento', 'sitemap' => 'mapa do site'],
            'fr-ca' => ['shipping' => 'expédition', 'payment' => 'paiement', 'sitemap' => 'plan du site'],
            'qu-pe' => ['shipping' => 'apachiy', 'payment' => 'pagy', 'sitemap' => 'llika-kaywan'],
            'ay-bo' => ['shipping' => 'apay', 'payment' => 'pagsu', 'sitemap' => 'pampa-kaywan'],
            // Óceániai nyelvek
            'mi-nz' => ['shipping' => 'tukunga', 'payment' => 'utu', 'sitemap' => 'mahere pae'],
            'sm-ws' => ['shipping' => 'felauaiga', 'payment' => 'totogi', 'sitemap' => 'faasite'],
            'to-to' => ['shipping' => 'fekauʻaki', 'payment' => 'totongi', 'sitemap' => 'kātoanga'],
            // További ázsiai nyelvek
            'pa-in' => ['shipping' => 'ਸ਼ਿਪਿੰਗ', 'payment' => 'ਭੁਗਤਾਨ', 'sitemap' => 'ਸਾਈਟਮੈਪ'],
            'te-in' => ['shipping' => 'షిప్పింగ్', 'payment' => 'చెల్లింపు', 'sitemap' => 'సైట్‌మ్యాప్'],
            'kn-in' => ['shipping' => 'ರವಾನೆ', 'payment' => 'ಪಾವತಿ', 'sitemap' => 'ಸೈಟ್‌ಮ್ಯಾಪ್'],
            'ml-in' => ['shipping' => 'പേയ്മെന്റ്', 'payment' => 'പേയ്മെന്റ്', 'sitemap' => 'സൈറ്റ്മാപ്പ്'],
            'si-lk' => ['shipping' => 'නැව්ගත කිරීම', 'payment' => 'ගෙවීම', 'sitemap' => 'අඩවි සිතියම'],
            'my-mm' => ['shipping' => 'ပို့ဆောင်ရေး', 'payment' => 'ငွေပေးချေမှု', 'sitemap' => 'ဆိုက်မြေပုံ'],
            'km-kh' => ['shipping' => 'ការដឹកជញ្ជូន', 'payment' => 'ការទូទាត់', 'sitemap' => 'ផែនទីគេហទំព័រ'],
            'lo-la' => ['shipping' => 'ການຂົນສົ່ງ', 'payment' => 'ການຊຳລະ', 'sitemap' => 'ແຜນຜັງເວັບໄຊທ໌'],
        ];

        return $searchTerms[$languageCode][$key] ?? 'shipping';
    }

    private function articulateAttributes() {
        if (empty($this->attributes)) return;

        foreach ($this->attributes as $attr) {
            $attr = mb_strtolower(trim($attr), 'UTF-8');

            // Csak akkor bontunk, ha van benne szóköz vagy kötőjel
            $parts = preg_split('/[\s\-_]+/', $attr, -1, PREG_SPLIT_NO_EMPTY);

            if (count($parts) > 1) {
                foreach ($parts as $part) {
                    if (mb_strlen($part) > 2 && !in_array($part, $this->attributes)) {
                        $this->attributes_articulated[] = $part;
                    }
                }
            }
        }
        $this->attributes_articulated = array_unique($this->attributes_articulated);
    }
}

