<?php 
define("HOST", "https://zeenshare.com");


class zs_user {
	var $email;
	var $role;
	var $firstname;
	var $lastname;
}

class zs_team {
	var $uniqueId;
	
	var $siteUrl;
	var $loginUrl;
	var $logoutUrl;
}


class zs_session {
	var $sessionId;
	var $accessUrl;
}



function zs_create_user($zs_user) {
	try {
		
		$zs_domain_name =  get_option('zs_domain_name');
		$zs_domain_private_key =  get_option('zs_domain_private_key');
		
		$url = HOST."/api/team/account/create";
		$url .= "?teamName=".$zs_domain_name;
		$url .= "&teamPrivateKey=".$zs_domain_private_key;

		$data_string = json_encode($zs_user);


		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string))
		);

		$content = curl_exec( $ch );
		$info = curl_getinfo( $ch );

		curl_close($ch);

		if($info['http_code'] == 200) {
			$response = json_decode($content, false);
			$api_header = $response->apiHeader;
			if($api_header->status == "OK") {
				$fsk_user = $response->result;
				return $fsk_user;
			} else {
				error_log("Failed to create user with id {".
						$zs_user->email."}on ZeenShare. ZeenShare response: ".$content);
			}
		} else {
			error_log("Failed to request ZeenShare server. Request response code: ".$r->getResponseCode());
		}
	} catch(Exception $e) {
		error_log("Failed to create user.".$e);
	}
}


function zs_update_user($zs_user) {
	try {
		$zs_domain_name =  get_option('zs_domain_name');
		$zs_domain_private_key =  get_option('zs_domain_private_key');

		$url = HOST."/api/team/account/update";
		$url .= "?teamName=".$zs_domain_name;
		$url .= "&teamPrivateKey=".$zs_domain_private_key;

		$data_string = json_encode($zs_user);


		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string))
		);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		curl_close($ch);
	} catch(Exception $e) {
		error_log("Failed to update user.".$e);
	}
}

function zs_update_team($zs_team) {
	try {
		$zs_domain_name =  get_option('zs_domain_name');
		$zs_domain_private_key =  get_option('zs_domain_private_key');
	
		$url = HOST."/api/team/update";
		$url .= "?teamName=".$zs_domain_name;
		$url .= "&teamPrivateKey=".$zs_domain_private_key;
	
		$data_string = json_encode($zs_team);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string))
		);
		curl_exec($ch);
		curl_close($ch);
	} catch(Exception $e) {
		error_log("Failed to update team.".$e);
	}
	
}


function zs_open_user_session($zs_user) {
	zs_update_user($zs_user);
	try {
		$zs_domain_name =  get_option('zs_domain_name');
		$zs_domain_private_key =  get_option('zs_domain_private_key');
		
		$url = HOST."/api/team/auth";
		$url .= "?teamName=".$zs_domain_name;
		$url .= "&teamPrivateKey=".$zs_domain_private_key;
		$url .= "&login=".$zs_user->email;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);


		$content = curl_exec( $ch );
		$info = curl_getinfo( $ch );
		curl_close($ch);

		if($info['http_code'] == 200) {
			$response = json_decode($content, false);
			$api_header = $response->apiHeader;
			if($api_header->status == "OK") {
				$session = $response->result;
				return $session;
			} else {
				error_log("Failed to open session for user with id {".
						$zs_user->email."} on ZeenShare. ZeenShare response: ".$content);
			}
		}
	} catch(Exception $e) {
		error_log("Failed to open session.".$e);
	}
}

function zs_close_user_session($sessionId) {
	try {
		$zs_domain_name =  get_option('zs_domain_name');
		$zs_domain_private_key =  get_option('zs_domain_private_key');
	
		$url = HOST."/api/team/close_session";
		$url .= "?teamName=".$zs_domain_name;
		$url .= "&teamPrivateKey=".$zs_domain_private_key;
		$url .= "&sid=".$sessionId;
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		curl_close($ch);
	} catch(Exception $e) {
		error_log("Failed to close session.".$e);
	}
}

function zs_upload_new_document($zs_file) {
	try {
		if(isset($_COOKIE['zs_uid']) && isset($_COOKIE['zs_sid'])) {
			$zs_sid = $_COOKIE['zs_sid'];
			$url = HOST."/api/document/upload?sid=".$zs_sid;
				
			$post_data['file'] = $zs_file;;
				
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type:  multipart/form-data'
			));
			curl_setopt($ch, CURLOPT_HEADER, 0);
				
			// Execute the request
			$content = curl_exec( $ch );
			$info = curl_getinfo( $ch );
			curl_close($ch);
				
			if($info['http_code'] == 200) {
				$response = json_decode($content, false);
				$api_header = $response->apiHeader;
				if($api_header->status == "OK") {
					$session = $response->result;
					return $session;
				} else {
					error_log("Failed to upload document for user session id {".
							$zs_sid."} on ZeenShare. ZeenShare response: ".$content);
				}
			}
		}
	} catch(Exception $e) {
		error_log("Failed to upload document.".$e);
	}

}

?>
