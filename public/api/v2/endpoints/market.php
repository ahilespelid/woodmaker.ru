<?php

if ($_POST['type'] == 'add_cart') {

	try {

		marketAddCartValidation();

		$qty = 1;
        if (!empty($_POST['qty']) && is_numeric($_POST['qty']) && $_POST['qty'] > 0) {
            $qty = Wo_Secure($_POST['qty']);
        }
        $db->insert(T_USERCARD,array('user_id' => $wo['user']['user_id'],
                                 'units' => $qty,
                                 'product_id' => Wo_Secure($_POST['product_id'])));
        $response_data = array(
	        'api_status' => 200,
	        'type' => 'added',
	        'count' => $db->where('user_id',$wo['user']['user_id'])->getValue(T_USERCARD,'COUNT(*)')
	    );
		
	} catch (Exception $e) {
		$error_code    = 5;
	    $error_message = $e->getMessage();
	}
}
elseif ($_POST['type'] == 'change_qty') {
	try {
		
		marketChangeQtyValidation();

		$qty = Wo_Secure($_POST['qty']);
        $db->where('product_id',$wo['product']['id'])->where('user_id',$wo['user']['user_id'])->update(T_USERCARD,array('units' => $qty));

        $response_data = array(
	        'api_status' => 200,
	        'message' => 'qty changed successfully'
	    );

	} catch (Exception $e) {
		$error_code    = 5;
	    $error_message = $e->getMessage();
	}
}
elseif ($_POST['type'] == 'remove_cart') {

	try {

		marketRemoveCartValidation();
		
		$db->where('product_id',Wo_Secure($_POST['product_id']))->where('user_id',$wo['user']['user_id'])->delete(T_USERCARD);
		$response_data = array(
	        'api_status' => 200,
	        'count' => $db->where('user_id',$wo['user']['user_id'])->getValue(T_USERCARD,'COUNT(*)')
	    );

	} catch (Exception $e) {
		$error_code    = 5;
	    $error_message = $e->getMessage();
	}
}
elseif ($_POST['type'] == 'buy') {

	try {
		
		marketBuyValidation();

		foreach ($wo['insert'] as $key => $value) {
            $hash_id = uniqid(rand(11111,999999));
            $total = 0;
            $total_commission = 0;
            $total_final_price = 0;
            foreach ($value as $key2 => $value2) {
                $db->where('id',$value2['product_id'])->update(T_PRODUCTS,array('units' => $db->dec($value2['units'])));
                $store_commission = 0;
                if (!empty($wo['config']['store_commission'])) {
                    $store_commission = round((($wo['config']['store_commission'] * ($value2['price'] * $value2['units'])) / 100), 2);
                }
                $total += ($value2['price'] * $value2['units']);
                $total_commission += $store_commission;
                $total_final_price += ($value2['price'] * $value2['units']) - $store_commission;
                    
                $db->insert(T_USER_ORDERS,array('user_id' => $wo['user']['user_id'],
                                           'product_owner_id' => $key,
                                           'product_id' => $value2['product_id'],
                                           'price' => ($value2['price'] * $value2['units']),
                                           'commission' => $store_commission,
                                           'final_price' => ($value2['price'] * $value2['units']) - $store_commission,
                                           'hash_id' => $hash_id,
                                           'units' => $value2['units'],
                                           'status' => 'placed',
                                           'address_id' => $wo['address']->id,
                                           'time' => time()));
            }
            $db->where('user_id',$wo['user']['user_id'])->update(T_USERS,array('wallet' => $db->dec($total)));

            cache($wo['user']['user_id'], 'users', 'delete');
            //$db->where('user_id',$key)->update(T_USERS,array('balance' => $db->inc($total_final_price)));
            $notes = $wo['lang']['product_purchase'];
            $notes_2 = $wo['lang']['product_sale'];
            mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENT_TRANSACTIONS . " (`userid`, `kind`, `amount`, `notes`) VALUES ({$wo['user']['user_id']}, 'PURCHASE', {$total}, '{$notes}')");
            mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENT_TRANSACTIONS . " (`userid`, `kind`, `amount`, `notes`) VALUES ({$key}, 'SALE', {$total_final_price}, '{$notes_2}')");
            $db->insert(T_PURCHAES,array('user_id' => $wo['user']['user_id'],
                                             'order_hash_id' => $hash_id,
                                             'price' => $total,
                                             'data' => json_encode(array('name' => !empty($wo['main_product']) && !empty($wo['main_product']['name']) ? $wo['main_product']['name'] : '')),
                                             'commission' => $total_commission,
                                             'final_price' => $total_final_price,
                                             'time' => time()));
            $notification_data_array = array(
                'notifier_id' => $wo['user']['user_id'],
                'recipient_id' => $key,
                'type' => 'new_orders',
                'url' => 'index.php?link1=orders',
                'time' => time()
            );
            $db->insert(T_NOTIFICATION,$notification_data_array);
        }

        $db->where('user_id',$wo['user']['user_id'])->delete(T_USERCARD);

        $response_data = array(
	        'api_status' => 200,
	        'message' => 'order placed successfully'
	    );

	} catch (Exception $e) {
		$error_code    = 5;
	    $error_message = $e->getMessage();
	}
}
elseif ($_POST['type'] == 'checkout') {
	$wo['items'] = $db->where('user_id', $wo['user']['id'])->get(T_USERCARD);
	$wo['total'] = 0;
	$data = [];
	if (!empty($wo['items'])) {
	    foreach ($wo['items'] as $key => $wo['item']) {
	        $wo['product'] = Wo_GetProduct($wo['item']->product_id);
	        if (!empty($wo['currencies']) && !empty($wo['currencies'][$wo['product']['currency']]) && $wo['currencies'][$wo['product']['currency']]['text'] != $wo['config']['currency'] && !empty($wo['config']['exchange']) && !empty($wo['config']['exchange'][$wo['currencies'][$wo['product']['currency']]['text']])) {
	            $wo['total'] += (($wo['product']['price'] / $wo['config']['exchange'][$wo['currencies'][$wo['product']['currency']]['text']]) * $wo['item']->units);
	        } else {
	            $wo['total'] += ($wo['product']['price'] * $wo['item']->units);
	        }
	        $data[] = $wo['product'];
	    }
	}
	$response_data = array(
        'api_status' => 200,
        'data' => $data,
        'total' => $wo['total']
    );
}
elseif ($_POST['type'] == 'purchased') {

	$offset = (!empty($_POST['offset']) && is_numeric($_POST['offset']) && $_POST['offset'] > 0 ? Wo_Secure($_POST['offset']) : 0);
    $limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 20);

    if (!empty($offset)) {
    	$db->where('id', $offset,'<');
    }

    $wo['purchased'] = $db->where('user_id', $wo['user']['user_id'])->orderBy('id', 'DESC')->get(T_PURCHAES, $limit);

    $purchased = array_map(function ($purchase) use ($wo)
    {
    	$purchase->data = json_decode($purchase->data, true);
        $purchase->type = $wo['lang']['order'];
        $purchase->date = Wo_Time_Elapsed_String($purchase->time);
        $purchase->url  = Wo_SeoLink('index.php?link1=customer_order&id=' . $purchase->order_hash_id);
        return $purchase;
    }, $wo['purchased']);

    $response_data = array(
        'api_status' => 200,
        'data' => $purchased
    );
}