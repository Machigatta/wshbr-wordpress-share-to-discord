<?php

/* xTech Labs WebHook Client */

namespace xTechLabs\Hooks;

use xTechLabs\Hooks\Parts\WebHook;

class Discord {

	/* Main WebHook URL */
	public $HookUrl = NULL;


	/* WebHook Name */
	public $BotName = NULL;


	/* Message Content */
	public $Message = NULL;


	/* Enable TTS */
	public $TTS = false;


	/* WebHook Avatar URL */
	public $Avatar = NULL;


	/* Thumbnail URL */
	public $ThumbUrl = NULL;


	/* Thumbnail Height */
	public $ThumbHeight = 16;


	/* Thumbnail Width */
	public $ThumbWidth = 16;


	/* Images Collection */
	public $Images = array();


	/* Link URL */
	public $LinkUrl = NULL;


	/* Link Title */
	public $LinkTitle = NULL;


	/* Link Description */
	public $LinkDesc = NULL;

	/* changes embed border color */
	public $embedColor = NULL;



	public function Post() {
		// Collect needed variables.
		$sys = $this->initUser();
		if(! empty($this->Images)) {
			$n=0;
			foreach($this->Images as $img) {
				$e[$n] = array("image" => $img);
				$n++;
			}
			$sys["embeds"] = $e;
		}
		if(isset($this->embedColor)) {
			if(strpos($this->embedColor, "#") > -1) {
	            $c=str_replace("#", "", $this->embedColor);
	            if (!preg_match("/#([a-fA-F0-9]{3}){1,2}\b/", $c)) {
	            	$color = hexdec( strtolower($c) );
	        	}
        	}
		} else {
			$color = 0;
		}
		// For now only allow one link to be displayed. - Later i will Rewrite this to allow multiple link embeds.
		if(! empty($this->LinkUrl)) {
			$sys = $this->initUser();
			$n=0;
			if(! isset($this->ThumbUrl)) {
				$e = array("url" => $this->LinkUrl, "title" => $this->LinkTitle, "description" => $this->LinkDesc, "color" => $color);
			} else {
				$th = array("url" => $this->ThumbUrl, "height" => $this->ThumbHeight, "width" => $this->ThumbWidth);
				$e = array("url" => $this->LinkUrl, "title" => $this->LinkTitle, "description" => $this->LinkDesc, "thumbnail" => $th, "color" => $color);
			}

			$e["image"] = $this->Images[0];
			
			$sys["embeds"] = array(0 => $e);
		}

		$webhook = new WebHook($this->HookUrl);
		// Check main variables. Throw error if they're NULL.
		$webhook->checkState();
		// Send the Data to Discord.
		$webhook->Connect($sys);
		
	}

	// Test function
	public function Test() {
		echo "Test has passed";
	}

	public function initUser() {
		$user = array();
		$user["username"] = $this->BotName;
		$user["avatar_url"] = $this->Avatar;
		$user["content"] = $this->Message;
		return $user;
	}
}
