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
defined('ABSPATH') or die('Restricted Access');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !wp_verify_nonce($_REQUEST['_wpnonce'], 'chimpxpress-import')) {
    die('Invalid request!');
}

if (isset($_POST['type'])) {
    $type = $_POST['type'];
} else if (isset($_GET['type'])) {
    $type = $_GET['type'];
} else {
    $type = 1;
}
?>
<div class="wrap" id="CXwrap">

    <?php include(WP_PLUGIN_DIR . DS . 'chimpxpress' . DS . 'loggedInStatus.php'); ?>

    <h1 class="componentHeading">chimpXpress</h1>
    <div class="clr"></div>
    <?php if (!$_SESSION['MCping']) { ?>
        <div class="updated" style="width:100%;text-align:center;padding:10px 0 13px;">
            <a href="options-general.php?page=chimpXpressConfig"><?php esc_html_e('Please connect your Mailchimp account!', 'chimpxpress'); ?></a>
        </div>
    <?php } ?>
    <?php
    global $wp_filesystem;
    if ($wp_filesystem->method != 'direct') {
        $chimpxpress = new chimpxpress;
        $ftpstream = ftp_connect($chimpxpress->settings['ftpHost']);
        $login = ftp_login($ftpstream, $chimpxpress->settings['ftpUser'], $chimpxpress->settings['ftpPasswd']);
        $ftproot = ftp_chdir($ftpstream, $chimpxpress->settings['ftpPath']);
        $adminDir = ftp_chdir($ftpstream, 'wp-admin');
        if ($wp_filesystem->method != 'direct'
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
                <a href="options-general.php?page=chimpXpressConfig"><?php esc_html_e('Direct file access not possible. Please enter valid ftp credentials in the configuration!', 'chimpxpress'); ?></a>
            </div>
        <?php }
        ftp_close($ftpstream);
    }
    ?>
    <div style="display:block;height:3em;"></div>

    <h3><?php esc_html_e('Import', 'chimpxpress'); ?></h3>
    <hr/>
    <br/>

    <script type="text/javascript"
            src="<?php echo esc_attr(plugins_url('js' . DS . 'php.default.min.js', __FILE__)); ?>"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            jQuery("#cancel").click(function () {
                window.location = 'admin.php?page=chimpXpressDashboard';
            });
            <?php /*
jQuery("#gotoArchive").click( function(){
    window.location = 'admin.php?page=chimpXpressArchive';
});
*/ ?>
            jQuery("#next").click(function (force) {
                if (jQuery.trim(jQuery('#fileName').val()) == '') {
                    alert('<?php esc_html_e('Cannot continue as campaign does not seem to have a subject yet!', 'chimpxpress');?>');
                    return;
                }
                if (jQuery('#typePost').is(':checked') || jQuery('#typePage').is(':checked')) {

                    jQuery('#cancelContainer').html('<img src="<?php echo esc_html(plugins_url('/images/ajax-loader.gif', __FILE__));?>" style="position: relative;top: 1px;"/>');

                    if (jQuery('#typePost').is(':checked')) {
                        var type = 'post';
                    } else {
                        var type = 'page';
                    }
                    if (jQuery('#typeHTML').is(':checked')) {
                        var datatype = 'html';
                    } else {
                        var datatype = 'text';
                    }
                    var data = {
                        action: 'import',
                        _wpnonce: jQuery('#_wpnonce').val(),
                        type: type,
                        datatype: datatype,
                        cid: jQuery('#CX_cid').val(),
                        title: campaigns[jQuery('#CX_cid').val()]['title'],
                        subject: campaigns[jQuery('#CX_cid').val()]['subject'],
                        fileName: htmlentities(jQuery('#fileName').val()),
                        force: jQuery('#force').val()
                    };

                    jQuery.post(ajaxurl, data, function (response) {
                        if (response.error == 1) {
                            jQuery('#cancelContainer').html(response.msg);
                        } else {
                            if (type == 'post') {
                                window.location = 'post.php?post=' + response + '&action=edit';
                            } else {
                                jQuery('#lpid').val(htmlentities(jQuery('#fileName').val()) + '.html');
                                document.forms["wp_chimpxpress_import"].submit();
                            }
                        }
                    });
                } else {
                    window.location = 'admin.php?page=chimpXpressImport';
                }
            });

            jQuery('#fileName').val(
                html_entity_decode(
                    campaigns[jQuery('#CX_cid').val()]['subject']
                )
            );

            jQuery('#CX_cid').change(function () {
                jQuery('#fileName').val(html_entity_decode(campaigns[jQuery(this).val()]['subject']));
            });

            if (jQuery('#typePage').is(':checked')) {
                jQuery('#fileNameContainer').css('display', 'block');
                jQuery('#datatypeContainer').css('display', 'none');
            } else {
                jQuery('#fileNameContainer').css('display', 'none');
                jQuery('#datatypeContainer').css('display', 'block');
            }

            jQuery('#typePage').change(function () {
                if (jQuery(this).is(':checked')) {
                    jQuery('#fileNameContainer').css('display', 'block');
                    jQuery('#datatypeContainer').css('display', 'none');
                }
            });
            jQuery('#typePost').change(function () {
                if (jQuery(this).is(':checked')) {
                    jQuery('#fileNameContainer').css('display', 'none');
                    jQuery('#datatypeContainer').css('display', 'block');
                }
            });
        });
    </script>
    <?php

    require_once(WP_PLUGIN_DIR . DS . 'chimpxpress' . DS . 'class-MCAPI.php');
    $MCAPI = new chimpxpressMCAPI;
    if ($type == 1) {
        step1();
    } else if ($type == 'post') {
        typePost();
    } else if ($type == 'page') {
        typePage();
    }

    function step1() {
        $MCAPI = new chimpxpressMCAPI;
        $campaigns = $MCAPI->campaigns(['status' => 'sent', 'count'  => 10, 'offset' => 0]);
        ?>
        <label for="CX_cid" class="bold"><?php esc_html_e('select campaign content to import', 'chimpxpress'); ?></label><br/>
        <select name="cid" id="CX_cid">
            <?php
            $js = "var campaigns = new Array();\n";
            foreach ($campaigns['campaigns'] as $c) {
                $sentDate = date_i18n(get_option('date_format'), strtotime($c['send_time']));
                echo '<option value="' . esc_attr($c['id']) . '">[' . esc_html($sentDate) . '] ' . esc_html($c['settings']['title']) . ' (' . esc_html($c['settings']['subject_line']) . ')</option>';
                $js .= "campaigns['" . $c['id'] . "'] = new Array();\n";
                $js .= "campaigns['" . $c['id'] . "']['title'] = '" . esc_attr($c['settings']['title']) . "';\n";
                $js .= "campaigns['" . $c['id'] . "']['subject'] = '" . esc_attr($c['settings']['subject_line']) . "';\n";
            }
            ?>
        </select>
        <script type="text/javascript"><?php echo $js; ?></script>
        <br/>
        <br/>
        <label class="bold"><?php esc_html_e('create a new ...', 'chimpxpress'); ?></label><br/>
        <input type="radio" name="type" id="typePost" value="post" checked="checked"/>&nbsp;<label
                for="typePost"><?php esc_html_e('blog post', 'chimpxpress'); ?></label>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <input type="radio" name="type" id="typePage" value="page"/>&nbsp;<label
                for="typePage"><?php esc_html_e('landing page', 'chimpxpress'); ?></label>
        <input type="hidden" name="force" id="force" value="0" />
        <?php wp_nonce_field('chimpxpress-import'); ?>
        <br/>
        <br/>

        <div id="datatypeContainer">
            <label class="bold"><?php esc_html_e('import as ...', 'chimpxpress'); ?></label><br/>
            <input type="radio" name="datatype" id="typeText" value="text" checked="checked"/>&nbsp;<label
                    for="typeText"><?php esc_html_e('text', 'chimpxpress'); ?></label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" name="datatype" id="typeHTML" value="html"/>&nbsp;<label
                    for="typeHTML"><?php esc_html_e('HTML', 'chimpxpress'); ?></label>
        </div>

        <div id="fileNameContainer" style="display:none;">
            <label class="bold"><?php esc_html_e('page title', 'chimpxpress'); ?></label><br/>
            <input type="text" name="fileName" id="fileName" value="" size="45"/>
        </div>

        <br/>
        <br/>
        <br/>
        <table style="vertical-align:middle;">
            <tr>
                <td>
                    <a class="button" id="next" href="javascript:void(0);"
                       title="<?php esc_html_e('next &raquo;', 'chimpxpress'); ?>"><?php esc_html_e('next &raquo;', 'chimpxpress'); ?></a>
                </td>
                <td>
                    <span id="cancelContainer">
                        <a id="cancel" class="grey" style="position: relative;top: -1px;" href="javascript:void(0);"
                        title="<?php esc_html_e('cancel', 'chimpxpress'); ?>"><?php esc_html_e('cancel', 'chimpxpress'); ?></a>
                    </span>
                </td>
            </tr>
        </table>
        <div id="gotoarchive" style="display:none;">
            <form action="admin.php?page=chimpXpressEditLandingPage" method="post" id="wp_chimpxpress_import">
                <input type="hidden" name="lpid[]" id="lpid" value=""/>
            </form>
        </div>
        <?php
    }

    ?>
    <?php include(WP_PLUGIN_DIR . DS . 'chimpxpress' . DS . 'footer.php'); ?>
</div>
<?php
$MCAPI->showMessages();
