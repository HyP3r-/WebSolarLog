<?php
class FacebookAddon {
	/**
	 * Start the job
	 * @param mixed $args
	 */

	function __construct() {
		
		include('../classes/Social/hybridauth/Hybrid/Auth.php');
		$this->config = Session::getConfig();
		
		$url = Util::getCurrentUrl();
		$url = str_replace("admin/admin-server.php","",$url);
		
		$this->configHybridAuth =  array(
				"base_url" => $url."classes/Social/hybridauth/",
				"providers" => array (
						"Facebook" => array (
								"enabled" => true,
								"keys"    => array ( "id" => "506888969354415", "secret" => "6627e0acc406682cfc35934a2413331b" ),
						)
					)
				);
		
		
		
		$this->adapter = PDODataAdapter::getInstance();
		
		$this->hybridauth = new Hybrid_Auth( $this->configHybridAuth );
		$this->type = 'Facebook';

		$this->current_user_id = 1;
		$this->hybridauth_session_data = $this->adapter->get_hybridauth_session( $this->current_user_id,$type  );
		//var_dump($this->hybridauth_session_data);
	}

	function __destruct() {
		$this->config = null;
		$this->adapter = null;
	}


	function attachFacebook(){
		HookHandler::getInstance()->fire("onError", 'Fire(AttachFacebook)');
		if($this->hybridauth_session_data){
			$data['message'] = 'Connected';
		}else{
			echo "1";
			$facebook = $this->hybridauth->authenticate( "Facebook" );
			echo "2";
			$hybridauth_session_data = $this->hybridauth->getSessionData();
			
			$twitter_user_profile = $facebook->getUserProfile();
			$this->adapter->save_hybridauth_session( $this->current_user_id, $hybridauth_session_data,$twitter_user_profile, $this->type );
			$hybridauth_session_data = $this->adapter->get_hybridauth_session( $this->current_user_id, $this->type );
	
			if($hybridauth_session_data){
				$data['message'] = 'Connected';
			}
			$facebook->logout();
		}
	}


	function detachFacebook(){
		HookHandler::getInstance()->fire("onError", 'Fire(DetachFacebook)');

			$twitter = $this->hybridauth->authenticate( "Facebook" );
			$hybridauth_session_data = $this->hybridauth->getSessionData();
	
			$twitter_user_profile = $twitter->getUserProfile();
			
			$detached = $this->adapter->remove_hybridauth_session( $this->current_user_id,$this->type);

			if($detached){
				$data['message'] = 'Detached';
			}
			$twitter->logout();
	}
	
	function sendFacebook(){
		HookHandler::getInstance()->fire("onDebug", 'Fire(sendFacebook)');
		if($this->hybridauth_session_data){
			HookHandler::getInstance()->fire("onDebug", 'Found session data, lets try to Tweet');
			$data = $this->hybridauth->restoreSessionData( $this->hybridauth_session_data['hybridauth_session'] );
			try{
				$twitter = $this->hybridauth->getAdapter( "Facebook" );
				$indexValues = $this->adapter->readPageIndexData($this->config->hybridAuth);
				$url = Common::getShortUrl($this->config->url);
				$twitter->setUserStatus("Today we generated ". $indexValues['summary']['totalEnergyToday'][0]['KWH']." kWh. Check out: " . $url );
				$twitter->logout();
				$data['message']='Facebook send';
				$data['tweetSend']=1;
				HookHandler::getInstance()->fire("onDebug", 'It looks like we Tweeted for you :D');
			}
			catch( Exception $e ){
				HookHandler::getInstance()->fire("onError", $e->getMessage());
			}
		}else{
			$data['facebookSend']=0;
			$data['message']= "No credentials available, so Twitter doesn't no how you are and you may not Tweet :| ";
		}
	}
}