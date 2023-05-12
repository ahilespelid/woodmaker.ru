<?php 
if ($f == 'get_notifications') {
    if ($s == 'get_more_notifications') {
		$data             = array(
			'status' => 200,
			'html' => ''
		);
		$last_id = $_POST['after_notification_id'];
		if (!empty($last_id)) {
			$params  = array(
				'html' => ''
			);
			$params['offset'] = $last_id;
			$notifications    = Wo_GetMoreNotifications($params);
		} else {
			$notifications    = Wo_GetNotifications();
		}
		$notification_ids = array();
		if (count($notifications) > 0) {
			foreach ($notifications as $wo['notification']) {
				$data['html'] .= Wo_LoadPage('header/notifecation');
				if ($wo['notification']['seen'] == 0) {
					$notification_ids[] = $wo['notification']['id'];
				}
			}
			if (!empty($notification_ids)) {
				$query_where = '\'' . implode('\', \'', $notification_ids) . '\'';
				$query       = "UPDATE " . T_NOTIFICATION . " SET `seen` = " . time() . " WHERE `id` IN ($query_where)";
				$sql_query   = mysqli_query($sqlConnect, $query);
			}
		} else {
			$data['message'] = $wo['lang']['no_new_notification'];
		}
		if (empty($html)) {
			$data['message'] = $wo['lang']['no_more_notifications'];
		}
		header("Content-type: application/json");
		echo json_encode($data);
		exit();
		
	} else {
		$data             = array(
			'status' => 200,
			'html' => ''
		);
		$notifications    = Wo_GetNotifications();
		$notification_ids = array();
		if (count($notifications) > 0) {
			foreach ($notifications as $wo['notification']) {
				$data['html'] .= Wo_LoadPage('header/notifecation');
				if ($wo['notification']['seen'] == 0) {
					$notification_ids[] = $wo['notification']['id'];
				}
			}
			if (!empty($notification_ids)) {
				$query_where = '\'' . implode('\', \'', $notification_ids) . '\'';
				$query       = "UPDATE " . T_NOTIFICATION . " SET `seen` = " . time() . " WHERE `id` IN ($query_where)";
				$sql_query   = mysqli_query($sqlConnect, $query);
			}
		} else {
			$data['message'] = $wo['lang']['no_new_notification'];
		}
		header("Content-type: application/json");
		echo json_encode($data);
		exit();
	}
}
