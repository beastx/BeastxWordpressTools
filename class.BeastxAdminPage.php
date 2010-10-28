<?php
/*
Name: BeastxAdminPage Helper Class
Description: Several functions/tools to make the plugins devlopment more easy.
Version: 1.0
Author: Beastx
Author URI: http://www.beastxblog.com/
*/

/*
    Copyright 2010 Beastx (Leandro Asrilevich) (http://beastxblog.com/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('BeastxAdminPage')) {

Class BeastxAdminPage extends BeastxPlugin {
    
    public function __construct() {
        parent::__construct();
        $this->templatePath = $this->pluginBasePath . '/templates/';
        if (method_exists($this, 'saveFormAction') || method_exists($this, 'resetFormAction')) {
            $this->captureFormsActions();
        }
    }
    
    private function captureFormsActions() {
        $postVars = $_POST;
        if (!empty($postVars)) {
            $this->checkSecurity();
            foreach ($postVars as $var => $val) {
                if (substr($var, 0, 7) == 'action_') {
                    $action = substr($var, 7);
                    if (method_exists($this, $action . 'FormAction')) {
                        call_user_func_array(array(&$this, $action. 'FormAction'), array($postVars));
                    }
                }
            }
        }
    }
    
    public function printHeader() {
        echo '<div class="wrap BeastxAdminPage" id="BeastxAdminPageContainer">
        <div class="icon32" id="' . (!empty($this->iconClass) ? $this->iconClass : 'icon-plugins') . '"><br/></div>
        <h2>' . $this->pluginName . ' - ' . (!empty($this->pageTitle) ? $this->pageTitle : __('Admin Page')) . '</h2>
        </div>';
    }
    
    public function printSaveOrErrorMessage() {
        if (!empty($this->saveMsg)) {
            echo '<div id="message" class="updated fade"><p>' . $this->saveMsg . '</p></div>';
        }
    }
    
    public function printFormNonce() {
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field('beastxPlugin-settings');
        }
    }
    
    function checkSecurity() {
        if (!current_user_can('manage_options')) {
            die(__('You cannot edit the ' . $this->pluginName . ' settings.'));
        }
        check_admin_referer('beastxPlugin-settings');
    }
    
    function makeBox($id, $title, $description = null, $rows, $dontUseFormTable = false) {
        echo '<div class="postbox" id="' . $id . '">';
        echo '    <div title="Click to toggle" class="handlediv"><br></div>';
        echo '    <h3 class="hndle"><span>' . $title . '</span></h3>';
        echo '    <div class="inside">';
        if (!empty($description)) {
            echo '    <p class="BoxDescription">';
            echo $description;
            echo '    </p>';
        }
        if (!$dontUseFormTable) {
            echo '        <table class="form-table">';
            echo '            <tbody>';
        }
        for ($i = 0; $i < count($rows); ++$i) {
            echo $rows[$i];
        }
        if (!$dontUseFormTable) {
            echo '            </tbody>';
            echo '        </table>';
        }
        echo '    </div>';
        echo '</div>';
    }
    
    function makeBoxRow($title, $description = null, $input) {
        $returnValue = '<tr>';
        $returnValue.= '    <th valign="top" scrope="row">';
        $returnValue.= '       <label>' . $title . '</label><br>';
        if (!empty($description)) {
            $returnValue.= '<small>' . $description . '</small>';
        }
        $returnValue.= '    </th>';
        $returnValue.= '    <td valign="top">';
        $returnValue.= BeastxInputs::getInputByInputType($input);
        $returnValue.= '    </td>';
        $returnValue.= '</tr>';
        return $returnValue;
    }
    
    public function getTemplate($templateName) {
        $fileName = $this->templatePath . '/' . $templateName . '.php';
        ob_start();
        include($fileName);
        $template = ob_get_contents();
        ob_end_clean();
        return $template;
    }
    
    public function printTemplate($templateName, $vars = null) {
        echo $this->getTemplate($templateName, $vars);
    }
    
    
    
    function saveAndResetBox() {
        $this->makeBox(
            'saveAndReset', 
            __('Included Widgets and Plugins', 'beastxTheme'),
            null,
            array(
                '<p>',
                __('If for whatever reason you want to "clean up" the settings set here or want to use another theme, click the <em>Reset Settings</em> button below.  To completely remove the theme, make sure to delete the <em>/beastx/</em> folder in the<em>/wp-content/themes/</em> directory.', 'beastxTheme'),
                '</p><input type="submit" name="action_reset" class="button" value="' . __('Reset', $this->textDomain) . '" />',
                '<div class="submit">',
                '<input type="submit" value="'.  __('Update configurations', $this->textDomain) . '" name="action_save" class="button-primary">',
                '</div>'
            ),
            true
        );
    }

    function likeThisPluginBox() {
        $this->makeBox(
            'saveAndReset', 
            __('Like this plugin?', 'beastxTheme'),
            null,
            array(
                '<p>Why not do any or all of the following:</p>',
                '<ul>',
                '<li><a href="http://yoast.com/wordpress/blog-icons/">Link to it so other folks can find out about it.</a></li>',
                '<li><a href="http://wordpress.org/extend/plugins/blog-icons/">Give it a good rating on WordPress.org.</a></li>',
                '<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=2017947">Donate a token of your appreciation.</a></li>',
                '</ul>'
            ),
            true
        );
    }

    function needSupportBox() {
        $this->makeBox(
            'saveAndReset', 
            __('Need support?', 'beastxTheme'),
            null,
            array(
                __('<p>If you have any problems with this theme or good ideas for improvements or new features, please talk about them in the <a href="http://wordpress.org/tags/blog-icons">Support forums</a>.</p>')
            ),
            true
        );
    }

    function aboutBeastxBox() {
        $this->makeBox(
            'saveAndReset', 
            __('Latest news from Yoast', 'beastxTheme'),
            null,
            array(
                '<ul>',
                '<li class="rss"><a href="http://www.beastxblog.com/" target="_blank">' . __('Go to Beastx\'s Blog', 'beastxTheme') . '</a></li>',
                '<li class="rss"><a href="http://www.beastxblog.com/feed/" target="_blank">' . __('Subscribe with RSS', 'beastxTheme') . '</a></li>',
                '<li class="rss"><a href="mailto:beastx@beastxblog.com">' . __('Send me an Email', 'beastxTheme') . '</a></li>',
                '</ul>'
            ),
            true
        );
    }
    
}

}

?>