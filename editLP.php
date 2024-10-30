<?php
/**
 * Copyright (C) 2015  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 * or write to the Free Software Foundation, Inc., 51 Franklin St,
 * Fifth Floor, Boston, MA  02110-1301  USA
**/

// no direct access
defined( 'ABSPATH' ) or die( 'Restricted Access' );

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !wp_verify_nonce($_REQUEST['_wpnonce'], 'chimpxpress-editLP')) {
    die('Invalid request!');
}

if (isset($_POST['task']) && $_POST['task'] == 'saveLP') {

	$content = $_POST['head'] . $_POST['LPcontent'] . '</body></html>';
	$content = str_replace( array('\"', "\'"), array('"', "'"), $content);

	// write landing page html file
	global $wp_filesystem;
	if ($wp_filesystem->method == 'direct') {
	    $wp_filesystem->put_contents( ABSPATH . 'archive' . DS . $_POST['lpid'], $content);
	} else {
	    $chimpxpress = new chimpxpress;
	    $ftpstream = ftp_connect($chimpxpress->settings['ftpHost'] );
	    $login = ftp_login($ftpstream, $chimpxpress->settings['ftpUser'], $chimpxpress->settings['ftpPasswd']);
	    $ftproot = ftp_chdir($ftpstream, $chimpxpress->settings['ftpPath'] );
	    $temp = tmpfile();
        $wp_filesystem->put_contents($temp, $content);
	    rewind($temp);
	    ftp_fput($ftpstream, 'archive' . DS . $_POST['lpid'], $temp, FTP_ASCII);
	    ftp_close($ftpstream);
	}

	echo '<script type="text/javascript">window.location = "'.esc_attr(get_option('home')) . '/wp-admin/admin.php?page=chimpXpressArchive";</script>';

} else {

	add_action('admin_init', 'editor_admin_init');
	add_action('admin_head', 'editor_admin_head');
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'jquery-color' );
	wp_print_scripts('editor');
	if (function_exists('add_thickbox')) add_thickbox();
	wp_print_scripts('media-upload');
	wp_enqueue_script('utils');
	wp_admin_css();
	do_action("admin_print_styles-post-php");
	do_action('admin_print_styles');
?>
<div class="wrap" id="CXwrap">

    <?php include(WP_PLUGIN_DIR . DS . 'chimpxpress' . DS . 'loggedInStatus.php'); ?>

	<h1 class="componentHeading">chimpXpress</h1>
	<div class="clr"></div>
	<?php if (!$_SESSION['MCping']) { ?>
	<div class="updated" style="width:100%;text-align:center;padding:10px 0 13px;">
		<a href="options-general.php?page=chimpXpressConfig"><?php esc_html_e('Please connect your Mailchimp account!', 'chimpxpress');?></a>
	</div>
	<?php }?>
	<?php
	global $wp_filesystem;
	if ($wp_filesystem->method != 'direct' ){
        $chimpxpress = new chimpxpress;
        $ftpstream = ftp_connect($chimpxpress->settings['ftpHost'] );
        $login = ftp_login($ftpstream, $chimpxpress->settings['ftpUser'], $chimpxpress->settings['ftpPasswd']);
        $ftproot = ftp_chdir($ftpstream, $chimpxpress->settings['ftpPath'] );
        $adminDir = ftp_chdir($ftpstream, 'wp-admin' );
        if (   $wp_filesystem->method != 'direct'
            && (
            !$chimpxpress->settings['ftpHost']
            || !$chimpxpress->settings['ftpUser']
            || !$chimpxpress->settings['ftpPasswd']
            || !$ftpstream
            || !$login
            || !$ftproot
            || !$adminDir
            )
         ) { ?>
            <div class="updated" style="width:100%;text-align:center;padding:10px 0 13px;">
                <a href="options-general.php?page=chimpXpressConfig"><?php esc_html_e('Direct file access not possible. Please enter valid ftp credentials in the configuration!', 'chimpxpress');?></a>
            </div><?php
        }
	    ftp_close($ftpstream);
	}
	?>
	<div style="display:block;height:3em;"></div>

	<link media="all" type="text/css" href="<?php echo esc_attr(get_option('home'));?>/wp-admin/css/colors-fresh.css" id="colors-css" rel="stylesheet">
	<h3><?php esc_html_e('Edit Landing Page', 'chimpxpress');?></h3>
	<hr />
	<br />
	<?php
	if (!isset($_POST['lpid'])) {
		echo esc_html__('No landing page selected!', 'chimpxpress');
		echo ' <a href="admin.php?page=chimpXpressArchive">'. esc_html__('Landing Page Archive', 'chimpxpress').'</a>';
	} else {
		$filename = sanitize_title( $_POST['lpid'][0] );
		$filename = str_replace('-html', '.html', $filename);
	?>
	<h3 style="float:left;"><?php echo esc_html($filename);?></h3>
	<?php
	$archiveDirAbs = ABSPATH . 'archive/';
	if( is_file(  $archiveDirAbs . $filename ) ){
        $content = $wp_filesystem->get_contents($archiveDirAbs . $filename);
        preg_match('#.*<body[^>]*>#is', $content, $head);

        $head = (isset($head[0])) ? str_replace('"',"'",$head[0]) : '';
        preg_match('%<body[^>]*>(.*?)</body>%s', $content, $body);

		$bodyContent = $body[0] ?? '';
		$bodyContent = preg_replace('/<body[^>]*>/', '', $bodyContent);
		$bodyContent = preg_replace('%</body>%', '', $bodyContent);
		$bodyContent = preg_replace('%<style[^>]*>(.*?)</style>%s', '', $bodyContent);
	?>

	<div style="float:right;">
		<a href="javascript:document.forms['wp_chimpxpress'].submit()" class="button">&nbsp;<?php esc_html_e('Save', 'chimpxpress');?>&nbsp;</a>
		<a href="javascript:cancelEdit()" class="button"><?php esc_html_e('Cancel', 'chimpxpress');?></a>
		<script type="text/javascript">
		function cancelEdit(){
			if (confirm( '<?php esc_html_e('Are you sure you want to cancel this operation?', 'chimpxpress');?>')) {
				window.location = 'admin.php?page=chimpXpressArchive';
			}
		}
		</script>
		</div>
		<div style="clear:both;"></div>
		<br />
		<form action="admin.php?page=chimpXpressEditLandingPage" method="post" id="wp_chimpxpress">
            <div id="poststuff" class="postarea">
                <?php wp_editor($bodyContent, 'LPcontent'); ?>
                <input type="hidden" name="lpid" value="<?php echo esc_attr($filename);?>" />
                <input type="hidden" name="task" value="saveLP" />
                <input type="hidden" name="head" value="<?php echo esc_attr($head); ?>" />
                <?php wp_nonce_field('chimpxpress-editLP'); ?>
            </div>
		</form>
		</div>
		<?php

	} else {
		?>
		<div style="clear:both;"></div>
		<br />
		<b><?php esc_html_e('Landing page not found! Please make sure the directory "archive" exists in your wordpress root and is writable.', 'chimpxpress');?></b>
		<?php
	}
?>
</div>
<?php }
}
