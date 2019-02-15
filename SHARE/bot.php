<?php 
    class newsBot
    {
        private $retArray = array(
            "retNumber" => 0,
            "retMessage" => ""
        );
        private $hooks;


        public function __construct(){
            
        }

        private function setHeader(){
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json');
        }

        private function initHooks(){

            //TEST
            $this->hooks = explode(",",$_POST["webhooks"]);
        }

        public function executeOrderSixtieSix(){
            if(count($_POST) != 0){
                $this->setHeader();
                $this->initHooks();
                $this->startAction();
            }else{
                echo json_encode($this->retArray);
            }
        }

        private function startAction(){

            
                // Basic Includes to build up the main functionality
                include "xTechLabs/WebHookExceptions.php";
                include "xTechLabs/Parts/WebHook.php";
                include "xTechLabs/Discord.php";

                //ini-sets
                ini_set('display_errors', 1);
                error_reporting(E_ERROR);

                //get data
                $title = html_entity_decode ($_POST["title"]);
                $thumbnail = $_POST["thumbnailurl"];
                $short = html_entity_decode ($_POST["short"]);
                $url = $_POST["url"];
            
                //build our class-construct
                $xtc = new xTechLabs\Hooks\Discord;
                $xtc->BotName = "wshbr.de - News";
                $xtc->Avatar = "https://scontent-frt3-1.xx.fbcdn.net/v/t1.0-9/16105789_1096149603864642_226352031125578799_n.png?oh=e42f578812c4e507733ea3aab1d2a27c&oe=590117FC";

                // Set a single link. (Multiple links aren't supported in this version)
                $xtc->LinkUrl = $url;
                $xtc->LinkTitle = $title;
                $xtc->LinkDesc = $short;
                
                // Set a thumbnail. Even though Discord offers the option to change thumb dimensions. it appears to not work.
                $xtc->ThumbUrl = "https://wshbr.de/wp-content/uploads/2017/01/main-icon.png";
                $xtc->ThumbHeight = 16;
                $xtc->ThumbWidth = 16;
                $xtc->embedColor = "#06b48f";
                $xtc->Images[0] = array("url" => $thumbnail,"height" => 800, "width" => 600);
                //$xtc->Images[1] = array("url" => "https://i0.wp.com/wshbr.de/wp-content/uploads/2017/01/fategrand_thumb.pngg", "height" => 800, "width" => 600);
                try {
                    foreach ($this->hooks as $hook) {
                        $xtc->HookUrl = $hook;
                        $xtc->Post();
                    }
                    
                } catch(\Exception $e) {
                    $this->retArray["retMessage"] .= $e->getMessage() . "\r\n";
                    $this->retArray["retNumber"] = 1;
                }
            

            echo json_encode($this->retArray);
        }
        
    }

    $newsBot = new newsBot();
    $newsBot->executeOrderSixtieSix();
?>