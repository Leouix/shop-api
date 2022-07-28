<?php

//header('Access-Control-Allow-Origin: true-domain.ru');
header('Access-Control-Allow-Methods: POST');

if( !isset($_POST['key']) || $_POST['key'] !== 'correct') {
    throw new Exception('Неизвестная ошибка');
}

try {

    $data_json = $_POST['data'];
    if(empty($data_json)) {
        throw new Exception('Данные о ценах отсутствуют');
    }

    $data = json_decode($data_json);
    if(empty($data)) {
        throw new Exception('Неверный формат поступивших данных');
    }
} catch ( \Exception $e ) {
    echo $e->getMessage();
    die();
}

$db_config = require_once __DIR__ . '/db-config.php';
include_once 'DB.php';

$db = new DB($db_config);

$data = json_decode($data_json);
$response = [];

foreach( $data as $data_product) {

    $product_id = $data_product->product_id;
    $regions_prices = $data_product->prices;

    foreach($regions_prices as $region_id => $prices) {
        if( $product_prices_db = $db->issetThisProductInDb($product_id, $region_id) ) {

            if($product_prices_db['price_purchase'] != $prices->price_purchase) {
                $result = $db->updatePrice($product_id, $region_id, 'price_purchase', $prices->price_purchase);
                $response[$product_id][$result][$region_id] = ['price_purchase' => $prices->price_purchase];
            }

            if($product_prices_db['price_selling'] != $prices->price_selling) {
                $result = $db->updatePrice($product_id, $region_id, 'price_selling', $prices->price_selling);
                $response[$product_id][$result][$region_id] = ['price_selling' => $prices->price_selling];
            }

            if($product_prices_db['price_discount'] != $prices->price_discount) {
                $result = $db->updatePrice($product_id, $region_id, 'price_discount', $prices->price_discount);
                $response[$product_id][$result][$region_id] = ['price_discount' => $prices->price_discount];
            }

        }
        else {
            $result = $db->addPriceProduct($product_id, $region_id, $prices->price_purchase, $prices->price_selling, $prices->price_discount);
            $response[$product_id][$result][$region_id] = [
                'price_purchase' => $prices->price_purchase,
                'price_selling' => $prices->price_selling,
                'price_discount' => $prices->price_discount
            ];
        }
    }

}

echo json_encode($response);
