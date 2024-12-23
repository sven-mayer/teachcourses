<?php
/**
 * This file contains general core functions
 * 
 * @package teachcourses\core
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/** 
 * teachcourses Page Menu
 *      
 * @param array $atts {
 *      @type int number_entries       Number of all available entries
 *      @type int entries_per_page     Number of entries per page
 *      @type int current_page         current displayed page
 *      @type string entry_limit       SQL entry limit
 *      @type string page_link         the name of the page you will insert the menu
 *      @type string link_atrributes   the url attributes for get parameters
 *      @type string container_suffix  The optional suffix from the shortcode container 
 *      @type string mode              top or bottom, default: top
 * }
 * @return string
 * @since 5.0.0
*/
function tc_page_menu ($atts) {
    $atts = shortcode_atts(array(
       'number_entries'     => 0,
       'entries_per_page'   => 50,
       'current_page'       => 1,
       'entry_limit'        => 0,
       'page_link'          => '',
       'link_attributes'    => '',
       'container_suffix'   => '',
       'mode'               => 'top',
       'class'              => 'tablenav-pages',
       'before'             => '',
       'after'              => ''
    ), $atts);
    
    $number_entries = intval($atts['number_entries']);
    $entries_per_page = intval($atts['entries_per_page']);
    $current_page = intval($atts['current_page']);
    $entry_limit = intval($atts['entry_limit']);
    $limit_name = 'limit' . $atts['container_suffix'];
    
    // If we can show all entries on a page, do nothing
    if ( $number_entries <= $entries_per_page ) {
        return;
    }

    $page_link = $atts['page_link'] . $limit_name;
    $num_pages = floor (($number_entries / $entries_per_page));
    $mod = $number_entries % $entries_per_page;
    if ($mod != 0) {
        $num_pages = $num_pages + 1;
    }
    
    // Defaults
    $page_input = ' <input name="' . $limit_name . '" type="text" size="2" value="' .  $current_page . '" style="text-align:center;" /> ' . __('of','teachcourses') . ' ' . $num_pages . ' ';
    $entries = '<span class="displaying-num">' . $number_entries . ' ' . __('entries','teachcourses') . '</span> ';
    $back_links = '<a class="page-numbers button disabled">&laquo;</a> <a class="page-numbers button disabled">&lsaquo;</a> ';
    $next_links = '<a class="page-numbers button disabled">&rsaquo;</a> <a class="page-numbers button disabled">&raquo;</a> ';

    // first page / previous page
    if ( $entry_limit != 0 ) {
        $first_page = '<a href="' . $page_link . '=1&amp;' . $atts['link_attributes'] . '" title="' . __('first page','teachcourses') . '" class="page-numbers button">&laquo;</a>';
        $prev_page = ' <a href="' . $page_link . '=' . ($current_page - 1) . '&amp;' . $atts['link_attributes'] . '" title="' . __('previous page','teachcourses') . '" class="page-numbers button">&lsaquo;</a> ';
        $back_links = $first_page . $prev_page;
    }

    // next page/ last page
    if ( ( $entry_limit + $entries_per_page ) <= ($number_entries)) { 
        $next_page = '<a href="' . $page_link . '=' . ($current_page + 1) . '&amp;' . $atts['link_attributes'] . '" title="' . __('next page','teachcourses') . '" class="page-numbers button">&rsaquo;</a>';
        $last_page = ' <a href="' . $page_link . '=' . $num_pages . '&amp;' . $atts['link_attributes'] . '" title="' . __('last page','teachcourses') . '" class="page-numbers button">&raquo;</a> ';
        $next_links = $next_page . $last_page;
    }

    // return
    if ($atts['mode'] === 'top') {
        return $atts['before'] . '<div class="' . $atts['class'] . '">' . $entries . $back_links . $page_input . $next_links . '</div>' . $atts['after'];
    }
    
    return $atts['before'] . '<div class="' . $atts['class'] . '">' . $entries . $back_links . $current_page . ' ' . __('of','teachcourses') . ' ' . $num_pages . ' ' . $next_links . '</div>' . $atts['after'];

}

/** 
 * Print message
 * @param string $message   The html content of the message
 * @param string $color     green (default), orange, red
 * @version 2
 * @since 5.0.0
*/ 
function get_tc_message($message, $color = 'green') {
    tc_HTML::line('<div class="teachcourses_message teachcourses_message_' . esc_attr( $color ) . '">');
    tc_HTML::line('<strong>' . $message . '</strong>');
    tc_HTML::line('</div>');
}

/** 
 * Split a timestamp
 * @param datetime $date_string
 * @return array
 * @since 0.20.0
 *
 * $split[0][0] => Year
 * $split[0][1] => Month 
 * $split[0][2] => Day
 * $split[0][3] => Hour 
 * $split[0][4] => Minute 
 * $split[0][5] => Second
*/ 
function tc_datesplit($date_string) {
    $preg = '/[\d]{2,4}/'; 
    $split = array(); 
    preg_match_all($preg, $date_string, $split); 
    return $split; 
}

/**
 * Translate a publication type
 * @param string $pub_slug  The publication type
 * @param string $num       sin (singular) or pl (plural)
 * @return string
 * @since 2.0.0
 */
function tc_translate_pub_type($pub_slug, $num = 'sin') {
    global $tc_publication_types;
    $types = $tc_publication_types->get();
    
    if ( isset( $types[$pub_slug] ) ) {
        if ( $num == 'sin' ) {
            return $types[$pub_slug]['i18n_singular'];
        }
        else {
            return $types[$pub_slug]['i18n_plural'];
        }
    }
    else {
        return $pub_slug;
    }
}

/** 
 * Get publication types
 * @param string $selected  --> 
 * @param string $mode      --> sng (singular titles) or pl (plural titles)
 * 
 * @version 3
 * @since 4.1.0
 * 
 * @return string
*/
function get_tc_publication_type_options ($selected, $mode = 'sng') {
    global $tc_publication_types;
    $types = '';
    $pub_types = $tc_publication_types->get();
    usort($pub_types, 'sort_tc_publication_type_options');
    foreach ( $pub_types as $row ) {
        $title = ($mode === 'sng') ? $row['i18n_singular'] : $row['i18n_plural'];
        $current = ( $row['type_slug'] == $selected && $selected != '' ) ? 'selected="selected"' : '';
        $types = $types . '<option value="' . $row['type_slug'] . '" ' . $current . '>' . $title . '</option>';  
    }
   return $types;
}

/**
 * Sort function helper for get_tc_publication_type_options()
 * Sorts the publication types after the i18n_singular string
 * @param string $a
 * @param string $b
 * @return int
 * @since 8.0.0
 */
function sort_tc_publication_type_options ($a, $b) {
    return strcmp($a['i18n_singular'], $b['i18n_singular']);
}

/**
 * Get the array structure for a parameter
 * @return array 
 */
function get_tc_var_types() {
    return array( 
        'course_id'         => '',
        'name'              => '',
        'slug'              => '',
        'type'              => '',
        'term_id'           => '',
        'lecturer'          => '',
        'assistant'         => '',
        'credits'           => '',
        'hours'             => '',
        'module'            => '',
        'links'             => '',
        'language'          => '',
        'description'       => '',
        'visible'           => '',
        'image_url'         => '',
    );
}

/** 
 * Define who can use teachcourses
 * @param array $roles
 * @param string $capability
 * @since 1.0
 * @version 2
 */
function tc_update_userrole($roles, $capability) {
    global $wp_roles;

    if ( empty($roles) || ! is_array($roles) ) { 
        $roles = array(); 
    }
    $who_can = $roles;
    $who_cannot = array_diff( array_keys($wp_roles->role_names), $roles);
    foreach ($who_can as $role) {
        $wp_roles->add_cap($role, $capability);
    }
    foreach ($who_cannot as $role) {
        $wp_roles->remove_cap($role, $capability);
    }
}

/**
 * Returns a message with the current real amount of memory allocated to PHP
 * @uses memory_get_usage() This function is used with the flag $real_usage = true
 * @return string
 * @since 5.0.0
 */
function tc_get_memory_usage () {
    return 'Current real amount of memory: ' . tc_convert_file_size( memory_get_usage(true) ) . '<br/>';
}

/**
 * Converts a file size in bytes into kB, MB or GB
 * @param int $bytes
 * @return string
 * @since 5.0.0
 */
function tc_convert_file_size ($bytes) {
    $bytes = floatval($bytes);
    if ( $bytes >= 1099511627776 ) {
        return number_format($bytes / 1099511627776, 2) . ' TB';
    }
    if ( $bytes >= 1073741824 ) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    }
    if ( $bytes >= 1048576 ) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    if ( $bytes >= 1024 ) {
        return number_format($bytes / 1024, 2) . ' kB';
    }
    if ( $bytes > 1 ){
        return $bytes . ' bytes';
    }
    if ( $bytes === 1 ){
        return $bytes . ' byte';
    }
    return '0 bytes';
}

/**
 * Converts an input(array or comma separated string) in a secured comma separated string
 * 
 * The method uses intval, floatval or htmlspecialchars for each element depending on the given
 * $type (string, int, float)
 * @param array|string $input
 * @param string $type  The type of the elements: string, int, float
 * @return string
 * @since 8.0.0
 */
function tc_convert_input_to_string($input, $type = 'string') {
    // if we have an array already
    if ( is_array($input) ) {
        $array = $input;
    }
    else {
        // If we have a comma separated string
        if ( strpos ($input, ',') !== false ) {
            $array = explode(',',$input);
        }
        // If we don't know what we have, so we create an array
        else {
            $array[] = $input;
        }
    }

    $max = count( $array );
    $string = '';
    
    for( $i = 0; $i < $max; $i++ ) {
        // Prepare element
        switch ( $type ) :
            case 'int':
                $element = intval($array[$i]);
                break;
            case 'float':
                $element = floatval($array[$i]);
                break;
            default:
                $element = htmlspecialchars($array[$i]);
        endswitch;
        $string = ( $string === '' ) ? $element : $string . ',' . $element;
    }
    return $string;
}

/**
 * Writes data for the teachcourses tinyMCE plugin in Javascript objects
 * @since 5.0.0
 */
function tc_write_data_for_tinymce () {
    
    // Only write the data if the page is a page/post editor
    if ( $GLOBALS['current_screen']->base !== 'post' ) {
        return;
    }
    
    // List of courses
    $course_list = array();
    $course_list[] = array( 'text' => '=== SELECT ===' , 'value' => 0 );


    // List of semester/term
    $term_list = array();
    $term_list[] = array( 'text' => __('Default','teachcourses') , 'value' => '' );

    $terms = TC_Terms::get_terms(); // get_tc_options('semester', '`setting_id` DESC');
    foreach ( $terms as $term ) {
        $courses = TC_Courses::get_courses( array('term_id' => $term->term_id) );
        $term_list[] = array( 'text' => $term->slug , 'value' => $term->term_id );

        foreach ($courses as $course) {
            $course_list[] = array( 'text' => $course->name . ' (' . $term->slug . ')' , 'value' => $course->course_id );
        }
        if ( count($courses) > 0 ) {
            $course_list[] = array( 'text' => '====================' , 'value' => 0 );
        }
    }

    // Current post id
    $post_id = ( isset ($_GET['post']) ) ? intval($_GET['post']) : 0;
    
    // Write javascript
    ?>
    <script type="text/javascript">
        var teachcourses_courses = <?php echo json_encode($course_list); ?>;
        var teachcourses_semester = <?php echo json_encode($term_list); ?>;
        var teachcourses_editor_url = '<?php echo admin_url( 'admin-ajax.php' ) . '?action=teachcoursesdocman&post_id=' . $post_id; ?>';
        var teachcourses_cookie_path = '<?php echo SITECOOKIEPATH; ?>';
        var teachcourses_file_link_css_class = '<?php echo TEACHCOURSES_FILE_LINK_CSS_CLASS; ?>';
    </script>
    <?php
}
