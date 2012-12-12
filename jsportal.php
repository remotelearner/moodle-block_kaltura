<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/ajax/ajaxlib.php');

class kaltura_jsportal extends jsportal {

    /**
     * Print javascript that initializes variables in the main class
     *
     * @param array $params - An array of parameters to add/initialize.
     * Array key
     * @param string $funccall - Adds a javascript function call if needed
     * @param bool $return - true to return the javascript or false to echo it
     * to the screen
     */
    function print_javascript($params = array(), $funccall = '', $return = false) {
        global $CFG, $USER;

        // TODO: Add wwwroot, sesskey, userid by default
        $output = '';

        $output .= "\n<script type=\"text/javascript\">\n";

        foreach ($params as $key => $param) {
            $output .= 'main.params[\'' . $key . '\'] = "' . $param . '";'. "\n";
        }

        $output .= "$funccall"."\n";
        $output .= "</script>\n";

        if ($return) {
            return $output;
        } else {
            echo $output;
            return '';
        }
    }
}
?>