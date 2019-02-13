<?php
/*
Plugin Name: hotStuff
Description: wshbr.de - main config plugin for some extras
Author: Machigatta
Version: 1.0
*/

/* Load translation, if it exists */
function hotStuff_init() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'hotStuff', null, $plugin_dir.'/languages/' );
}
add_action('plugins_loaded', 'hotStuff_init');

add_action('admin_menu','wshbr_register');
function wshbr_register(){
	add_menu_page('wshbrde', 'WSHBR.DE', 'edit_posts', 'wshbrde', 'config_ausgeben', 'dashicons-admin-multisite', 3);
}


function theme_slug_filter_the_title( $content ) {

	if(isset($_GET["preview"])){
		if($_GET["preview"] == "true"){
			return $content;
		}
	}

	global $wp;
	if(is_user_logged_in()){
		$new_content .= '<script> var hookurls = '.json_encode(explode(";",get_option('webhook'))).';</script>';
	}
	
	$new_content .= $content;
	return $new_content;
}
add_filter( 'the_content', 'theme_slug_filter_the_title' );


function config_ausgeben(){
?>
	<div class="wrap">
		<form methong="post" action="options.php">
		<h2>Post-publish webhook (for discord etc.) - format JSON (; seperated)</h2>
			<input id="webhookId" type="text" name="webhook" value="<?php echo get_option('webhook') ?>" style="width:100%;">
			<p style='color:gray;'>Alter webhooks: <ul><?php 
				foreach(explode(";",get_option('webhook')) as $url){
					echo "<li>".$url."</li>";
				}
			 ?></ul>
			</p>
		<hr>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
		<form>
	</div>
<?php
}


/**
 * Adds hooks to get the meta box added to pages and custom post types
 */
function hotStuff_meta_custom() {
	$custom_post_types = get_post_types();
	array_push($custom_post_types,'page');
	foreach ($custom_post_types as $t) {
		$defaults = get_option('hotStuffDefaults'.ucfirst($t));
		if (!isset($defaults['activeMetaBox']) || $defaults['activeMetaBox'] == 'active') {
			add_meta_box('hotstuffdiv', __('Slider','post-expirator'), 'hotStuff_meta_box', $t, 'side', 'core');
			add_meta_box('hotstuffdiv_imdb', __('IMDB','post-expirator'), 'hotStuff_imdb_box', $t, 'side', 'core');
			add_meta_box('hotstuffreview', __('Review','post-expirator'), 'hotStuff_review_meta_box', $t, 'normal', 'core');
			add_meta_box('hotstuffsources', __('Quellen','post-expirator'), 'hotStuff_quellen_meta_box', $t, 'normal', 'core');
		}
	}
	
	
}
add_action ('add_meta_boxes','hotStuff_meta_custom');

function hotStuff_quellen_meta_box($post){
	echo "<h3>Kommaseperiert: Link1, Link2, Link3</h3>";
	$content = get_post_meta($post->ID,"quellenAngaben",true);
	$editor_id = "quellenAngaben";
	$settings = array( 
	'tinymce' => array( 'buttons' => 'a'),
	"textarea_rows"=>"2",
	'media_buttons' => false
	);
	wp_editor( $content, $editor_id, $settings );

	echo "<textarea id='quellePost' name='quellePost' style='display:none;'>".$content."</textarea>";

}

function hotStuff_review_meta_box($post) { 
	
	$isReview = get_post_meta($post->ID,"isReview",true);
	// Get default month
	echo "<div>
	<input id='isReview' type=\"checkbox\" name='isReview' value='true'";
	echo ($isReview == "1") ? "checked='checked'" : "";
	echo ">markiert als Review
	<div id='reviewOptions'>
	<hr>
	<h4>Wertung (bis 10, bsp. 5,6,7,5.5,8.5)</h4>
	<input type='text' name='reviewValue' value='".get_post_meta($post->ID,"reviewValue")[0] ."' style='width:100%'>
	<h4>Kurzbeschreibung</h4>
	<textarea rows=\"10\" cols=\"30\" name=\"reviewShort\" style='width:100%'>".get_post_meta($post->ID,"reviewShort",true)."</textarea></div>
	</div>";
}

/**
 * Actually adds the meta box
 */
function hotStuff_meta_box($post) { 
	// Get default month
	wp_nonce_field( plugin_basename( __FILE__ ), 'hotStuff_nonce' );
	$isSlider = get_post_meta($post->ID,"isSlider",true);
	

	wp_enqueue_media();	
	echo "<div>
	<h3><span class=\"dashicons dashicons-controls-repeat\"></span> AutoShared</h3>
	<button class='button' disabled='disabled' style='width:100%;";
	if(get_post_meta($post->ID,"dcShared")[0] == "1"){
		echo "background-color:green !important;color:white !important;";
	}else{
		echo "background-color:red !important;color:white !important;";
	}
	echo"'>Shared</button></div>";
	echo "<div><h3><span class=\"dashicons dashicons-format-gallery\"></span> Slider</h3>
	<input id='isNoSlider' type=\"radio\" name='isSlider' value='false'";
	echo ($isSlider == "0" || $isSlider == "") ? "checked" : "";
	echo ">Nein<br><input id='isYesSlider' type=\"radio\" name='isSlider' value='true'";
	echo ($isSlider == "1") ? "checked" : "";
	
	echo ">Ja
	<div id='sliderOptions' >
	<hr>
	<h5>Slider-Caption</h5>
	<input type=\"text\" name=\"sliderCaption\" value=\"".get_post_meta($post->ID,"sliderCaption",true)."\" alt=\"Caption\" style='width:100%;'>
	<h5>Slider-Image (360 px X 200 px)</h5>
	<center><div class='image-preview-wrapper' style='width: 190px;height: 100px;overflow: hidden;position: relative;'>
		<img id='image-preview' src='".wp_get_attachment_url( get_post_meta( $post->ID,"sliderImage",true ) )."' style='left: 50%;top: 50%;transform: translate(-50%, -50%);height: 100%;position: absolute;width: auto;'>
	</div>
	<br>
	<input id=\"upload_image_button\" type=\"button\" class=\"button\" value=\"";
	_e( 'Upload image' ); 
	echo "\" /><input type='hidden' name='image_attachment_id' id='image_attachment_id' value='".get_post_meta( $post->ID,"sliderImage",true )."'></center></div></div>";
}


function hotStuff_imdb_box($post) { 
	// Get default month
	$imdbvalue = get_post_meta($post->ID,"imdb_id",true);
	
	echo "<div style='width:100%'>
			<p>
				Hier den Movie-DB-Link eintragen:
			</p>
			<input type='text' name='imdb_id' id='imdb_id' style='width:100%' value='".$imdbvalue."'>
			<p>
				Bsp: - https://www.themoviedb.org/tv/61555-the-missing
			</p>
		</div>";
}

add_action( 'save_post', 'hotStuff_imdb_data' );
add_action( 'save_post', 'hotStuff_field_data' );

function hotStuff_imdb_data($post_id){
    // check if this isn't an auto save
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    // security check
    if ( !wp_verify_nonce( $_POST['hotStuff_nonce'], plugin_basename( __FILE__ ) ) )
        return;
	
	if ( isset( $_POST['imdb_id'] ) ) :
		update_post_meta( $post_id, 'imdb_id', $_POST['imdb_id'] );
	endif;        
}

function hotStuff_field_data($post_id) {
	    // check if this isn't an auto save
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    // security check
    if ( !wp_verify_nonce( $_POST['hotStuff_nonce'], plugin_basename( __FILE__ ) ) )
        return;
	
	if ( isset( $_POST['image_attachment_id'] ) ) :
		update_post_meta( $post_id, 'sliderImage', absint( $_POST['image_attachment_id'] ) );
	endif;
	
	

    // further checks if you like, 
    // for example particular user, role or maybe post type in case of custom post types
	if(get_post_meta($post_id,"dcShared",true) != "1"){
		update_post_meta( $post_id, 'dcShared', "0");
	}
    
    // now store data in custom fields based on checkboxes selected
    if ( isset( $_POST['isSlider'] )){
		if($_POST['isSlider'] == "true"){
			update_post_meta( $post_id, 'isSlider', "1" );
		}else{
			update_post_meta( $post_id, 'isSlider', "0" );
		}
	}

	if ( isset( $_POST['sliderCaption'] ) ) :
		update_post_meta( $post_id, 'sliderCaption', $_POST['sliderCaption']);
	endif;

	if ( isset( $_POST['quellePost'] ) ) :
		update_post_meta( $post_id, 'quellenAngaben', $_POST['quellePost']);
	endif;

	if ( isset( $_POST['isReview'] ) ) :
		if($_POST['isReview'] == "true"){
			update_post_meta( $post_id, 'isReview', "1");
			update_post_meta( $post_id, 'reviewShort', $_POST['reviewShort']);
			update_post_meta( $post_id, 'reviewValue', $_POST['reviewValue']);
		}else{
			update_post_meta( $post_id, 'isReview', "0");	
			update_post_meta( $post_id, 'reviewShort', $_POST['reviewShort']);
			update_post_meta( $post_id, 'reviewValue', $_POST['reviewValue']);
		}
	endif;
        
}

add_action('admin_init', 'wshbr_config_init');
function wshbr_config_init(){
	add_option('webhook');
	add_option('slider_option');
	wp_register_style('mainCss', plugins_url('hotStuff.css',__FILE__ ));
	wp_enqueue_style('mainCss');
}

add_action( 'admin_footer', 'media_selector_print_scripts' );

function media_selector_print_scripts() {

	$my_saved_attachment_post_id = get_option( 'media_selector_attachment_id', 0 );

	?><script type='text/javascript'>
		jQuery( document ).ready( function( $ ) {
			if(jQuery('#isNoSlider').is(':checked')) { jQuery('#sliderOptions').hide(); }
			jQuery('input[type=radio][name=isSlider]').change(function() {
				if (this.value == 'false') {
					jQuery('#sliderOptions').hide();
				}
				else if (this.value == 'true') {
					jQuery('#sliderOptions').show();
				}
			});

			setTimeout(function() {
				jQuery("#hotstuffsources").find("div[role='button']").not("div[aria-label='Insert/edit link'], div[aria-label='Remove link']").hide();
				jQuery("#hotstuffsources").find("#wp-quellenAngaben-editor-tools").hide();
				jQuery("#hotstuffsources").find("div[role=\"toolbar\"]:eq(1)").hide();

				tinymce.get("quellenAngaben").on('change', function(e) {
					jQuery("#quellePost").html(tinyMCE.get("quellenAngaben").getContent());
				});
				
			}, 2000);
			

			if(jQuery('#isReview').is(':checked')) { jQuery('#reviewOptions').show(); }else{ jQuery('#reviewOptions').hide(); }
			jQuery('input[type=checkbox][name=isReview]').change(function() {
				console.log(this.value);
				if ($(this).is(':checked')) {
					jQuery('#reviewOptions').show();
				}
				else{
					jQuery('#reviewOptions').hide();
				}
			});
			// Uploading files
			var file_frame;
			if(wp.media){
				var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
				var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this
			}
			
			jQuery('#upload_image_button').on('click', function( event ){
				event.preventDefault();
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					// Set the post ID to what we want
					file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					// Open frame
					file_frame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = set_to_post_id;
				}
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select a image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();
					// Do something with attachment.id and/or attachment.url here
					$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
					$( '#image_attachment_id' ).val( attachment.id );
					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
				});
					// Finally, open the modal
					file_frame.open();
			});
			// Restore the main ID when the add media button is pressed
			jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
			});
		});
	</script><?php
}