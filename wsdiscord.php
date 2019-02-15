<?php
/*
Plugin Name: wshbr-wordpress-share-to-discord
Plugin URI: https://github.com/Machigatta/wshbr-wordpress-share-to-discord
Description: Share your posts to discord hooks!
Author: Machigatta
Author URI: https://machigatta.com/
Version: 0.1
Stable Tag: 0.1
 */
class wsdiscrod
{
    public function __construct()
    {

        $this->settings_url = urlencode(admin_url('options-general.php?page=wsdiscrod_options'));
        $this->file_url = basename(__FILE__);
        $this->folder_url = basename(dirname(__FILE__));

        //Action-Binds for Wordpress-Frontend
        add_action('the_content', array($this,'addContent'));
        add_action('the_excerpt', array($this, 'disablePlugin'));

        //Action-Binds for Wordpress-Backend
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_init', array($this, 'registerSettings'));

        //Add Something to the scripts-loader
        add_action('wp_enqueue_scripts', array($this, 'addStylesAndScripts'));
    }

    //Add Menu-Point to settings
    public function addMenu()
    {
        add_options_page('Wshbr-Share To Discord', 'Wshbr-Share-to-Discord', 'manage_options', 'wsdiscrod_options', array($this, 'renderOptionsPage'));
    }

    //Options-pAge
    public function renderOptionsPage()
    {
        $settings_url = urlencode(admin_url('options-general.php?page=wsdiscrod_options'));
        $options = get_option('wsdiscrod_settings');
        ?>
            <form action='options.php' method='post'>
                <img src="<?php echo trailingslashit(plugin_dir_url(__FILE__)) . 'assets/img/logo.png' ?>">
                <hr>
                <?php
                    settings_fields('wsdiscrod_options');
                    ?>
                <?php
                    do_settings_sections('wsdiscrod_options');
                    ?>
                </div>
                <?php submit_button();?>
            </form>
            <?php
    }
    
    //Add Content to page
    function addContent($content) {
        $options = get_option('wsdiscrod_settings');
        $post_id = get_the_ID();

        $post_object = $this->getPostObject($post_id);
        $plugin = $this->renderPlugin($options, $post_object);
        if(is_single()){
            $content = $plugin . $content;
        }
    
        return $content;
    }

    //Register Settings for plugin
    public function registerSettings()
    {
        $options = get_option('wsdiscrod_settings');
        register_setting('wsdiscrod_options', 'wsdiscrod_settings');
        add_settings_field('wsdiscrod_options-webhooks', "Discord-Webhooks:", array($this, 'renderTextarea'), 'wsdiscrod_options', 'wsdiscrod_enable', array("key"=>"webhooks"));
        add_settings_section('wsdiscrod_enable', '', array($this, ''), 'wsdiscrod_options');

    }

    function disablePlugin($excerpt) {
		return preg_replace('/<article>.*<\/article>/', '', $excerpt);
	}

    //Add Styles and Scripts to the plugin in the right version
    public function addStylesAndScripts()
    {
        $options = get_option('wsdiscrod_settings');
        wp_enqueue_style('wsdiscrod-font', 'https://fonts.googleapis.com/css?family=Open+Sans');
        wp_enqueue_style('wsdiscrod-style', trailingslashit(plugin_dir_url(__FILE__)) . 'assets/css/style.css', array(), "0.0.2");
        if (is_user_logged_in() && current_user_can('edit_posts')){
            wp_enqueue_script('wsdiscrod-script', trailingslashit(plugin_dir_url(__FILE__)) . 'assets/js/wsdiscord.js', array('jquery'), "0.0.4");
        }
    }

    //simple render-function for textareas
    public function renderTextarea($args)
    {
        $options = get_option('wsdiscrod_settings');
        $value = $options[$args["key"]];
        
        ?>
            <textarea style="width: 100%;min-height: 150px;" type="checkbox" name="wsdiscrod_settings[webhooks]"><?php echo $value ?></textarea>
        <?php
    }

    //Draw the plugin
    public function renderPlugin($options, $post_object)
    {
        $ret = "";
        if(is_user_logged_in() && current_user_can('edit_posts')){
            $ret .= '<article><div id="sidebar_post_buttons"><button type="submit" class="btn btn-default" name="dc_share" onClick="shareDiscord();" disabled><i class="fa fa-share-alt"></i> Discord-Share</button></form></div>';
            $ret .= '<script> var wshbr_hookurls = '.json_encode(explode("\r\n",get_option('wsdiscrod_settings')["webhooks"])).';</script></article>';
        }
        return $ret;
	}

    //Taken from a gist to retrieve a usable post-object for other reasons
    public function getPostObject($post_id)
    {
        $post_url = get_permalink($post_id);
        $title = strip_tags(get_the_title($post_id));
        $tagObjects = get_the_tags($post_id);
        $single = is_single();
        $tags = "";
        if (!empty($tagObjects)) {
            $tags .= $tagObjects[0]->name;
            for ($i = 1; $i < count($tagObjects); $i++) {
                $tags .= "," . $tagObjects[$i]->name;
            }
        }
        $category = get_the_category($post_id);
        $categories = "";
        if (!empty($category)) {
            $categories .= $category[0]->name;
            for ($i = 1; $i < count($category); $i++) {
                $categories .= "," . $category[$i]->name;
            }
        }
        $author = get_the_author();
        $date = get_the_date('U', $post_id) * 1000;
        $comments = get_comments_number($post_id);

        $post_object = array(
            'id' => $post_id,
            'url' => $post_url,
            'title' => $title,
            'tags' => $tags,
            'categories' => $categories,
            'comments' => $comments,
            'date' => $date,
            'author' => $author,
            'single' => $single,
            'img' => get_the_post_thumbnail_url($post_id),
        );
        return $post_object;
    }
}
//base init
new wsdiscrod();
?>