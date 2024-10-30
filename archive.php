<?php
/**
 * Copyright (C) 2015 freakedout (www.freakedout.de)
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

$archiveDirAbs = ABSPATH . 'archive/';
$archiveDirRel = get_option('home') . '/archive/';

?>
<div class="wrap" id="CXwrap">
    <div id="archive">
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                jQuery("#gotoDashboard").click(function () {
                    window.location = 'admin.php?page=chimpXpressDashboard';
                });
            });

            function editLP() {
                var lpid = document.getElementsByName('lpid[]');
                var isChecked = false;
                for (i = 0; i < lpid.length; i++) {
                    if (lpid[i].checked == true) {
                        isChecked = true;
                    }
                }
                if (!isChecked) {
                    alert('<?php esc_html_e('Please select a landing page from the list!', 'chimpxpress');?>');
                } else {
                    document.forms["wp_chimpxpress"].submit();
                }
            }

            function deleteLP() {
                var lpid = document.getElementsByName('lpid[]');
                var isChecked = false;
                for (i = 0; i < lpid.length; i++) {
                    if (lpid[i].checked == true) {
                        isChecked = true;
                    }
                }
                if (!isChecked) {
                    alert('<?php esc_html_e('Please select one or more landing pages from the list!', 'chimpxpress');?>');
                } else {
                    if (confirm('<?php esc_html_e('Are you sure you want to delete the selected landing pages? There is no undo!', 'chimpxpress');?>')) {
                        var filenames = new Array();
                        for (i = 0; i < lpid.length; i++) {
                            if (lpid[i].checked == true) {
                                filenames.push(lpid[i].value);
                            }
                        }
                        var data = {
                            action: 'archive_deleteLP',
                            _wpnonce: jQuery('#_wpnonce').val(),
                            filenames: filenames
                        };
                        jQuery.post(ajaxurl, data, function () {
                            window.location = 'admin.php?page=chimpXpressArchive';
                        });
                    }
                }
            }
        </script>

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
                </div><?php
            }
            ftp_close($ftpstream);
        }
        ?>
        <div style="display:block;height:3em;"></div>

        <h3><?php esc_html_e('Landing Page Archive', 'chimpxpress'); ?></h3>
        <hr/>
        <br/>
        <form action="admin.php?page=chimpXpressEditLandingPage" method="post" id="wp_chimpxpress">
            <?php
            wp_nonce_field('chimpxpress-archive');

            // get a list of existing archive files
            if (is_dir($archiveDirAbs)) {
                $files = getDirectoryList($archiveDirAbs);
                usort($files, 'cmp');
            }
            if (isset($files[0])){
            ?>
            <table width="100%" class="widefat">
                <thead>
                <tr>
                    <th width="20"></th>
                    <th><?php esc_html_e('Campaign Subject', 'chimpxpress'); ?></th>
                    <th width="150" nowrap="nowrap"
                        style="text-align:center;"><?php esc_html_e('created / modified', 'chimpxpress'); ?></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th colspan="20"></th>
                </tr>
                </tfoot>
                <tbody>
                <?php
                foreach ($files as $f) {
                    ?>
                    <tr>
                        <td style="vertical-align:middle;">
                            <input type="checkbox" name="lpid[]" value="<?php echo esc_html($f['name']); ?>"/>
                        </td>
                        <td>
                            <a href="<?php echo esc_html($archiveDirRel . $f['name']); ?>"
                               target="_blank"><?php echo esc_html($f['name']); ?></a>
                        </td>
                        <td align="center" nowrap="nowrap">
                            <?php echo esc_html(date_i18n(get_option('time_format') . ' ' . get_option('date_format'), $f['created'])); ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <br/>
            <a href="javascript:editLP()" class="button">&nbsp;&nbsp;<?php esc_html_e('Edit', 'chimpxpress'); ?>&nbsp;&nbsp;</a>
            &nbsp;&nbsp;
            <a href="javascript:deleteLP()" class="button"
               style="color:#A50000;float:right;">&nbsp;&nbsp;<?php esc_html_e('Delete', 'chimpxpress'); ?>&nbsp;&nbsp;</a>
            <div style="clear:both;"></div>
            <br/>
            <br/>
        </form>
    <?php
    } else {
        esc_html_e('No landing pages found!', 'chimpxpress');
        echo '<br />';
    } ?>

        <a id="gotoDashboard" class="button" style="float:right;" href="javascript:void(0);"
           title="<?php esc_html_e('Dashboard', 'chimpxpress'); ?>"><?php esc_html_e('Dashboard', 'chimpxpress'); ?></a>
    </div>
    <?php include(WP_PLUGIN_DIR . DS . 'chimpxpress' . DS . 'footer.php'); ?>
</div>


<?php
function getDirectoryList($directory) {
    // create an array to hold directory list
    $results = [];
    $gmtOffset = get_option('gmt_offset');
    $serverOffset = gmdate("O") / 100;
    // create a handler for the directory
    $handler = opendir($directory);

    // open directory and walk through the filenames
    while ($file = readdir($handler)) {
        // if file isn't this directory or its parent, add it to the results
        if ($file != "." && $file != "..") {
            // only return .html files
            if (substr($file, -5) == '.html') {
                $creationTime = ($gmtOffset != $serverOffset) ? (filemtime($directory . $file) + ($gmtOffset * 60 * 60)) : filemtime($directory . $file);
                $results[] = ['name' => $file, 'created' => $creationTime];
            }
        }
    }
    // close the directory handler
    closedir($handler);

    return $results;
}

function cmp($a, $b) {
    if ($a['created'] == $b['created']) {
        return 0;
    }
    return ($a['created'] < $b['created']) ? 1 : -1;
}

