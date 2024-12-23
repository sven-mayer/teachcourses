<?php
/**
 * This file contains the shortcode functions
 * 
 * @package teachcourses\core\shortcodes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * This class contains all shortcode helper functions
 * @since 5.0.0
 * @package teachcourses\core\shortcodes
 */
class tc_Shortcodes {

    /** 
     * Show all courses of the latest visbile term grouped by type
     */
    public static function tc_courselist_shortcode($atts) {
        
        $param = shortcode_atts(array(
            "headline"     => 1,
            'term_id'      => get_tc_option('active_term')
        ), $atts);
        $headline = intval($param['headline']);
        $term_id = intval($param['term_id']);
        $rtn = '<div id="tpcourselist">';
        if ($headline === 1) {
             $rtn .= '<h2>' . __('Courses for the','teachcourses') . ' ' . stripslashes(TC_Terms::get_term(array("term_id"=>$term_id))->name) . '</h2>';
        }

        $types = get_tc_options('course_type', '`value` ASC');  

        foreach ( $types as $type ) {
            $courses = TC_Courses::get_courses( array('term_id' => $term_id, 'type' => $type->value, 'visibility' => '1') );

            $rtn .= '<h3>' . $type->value . '</h3>';
            $rtn .= '<ul>';
            foreach ( $courses as $row ) {
                $rtn .= '<li><a href="./'.$row->term_slug.'/'.$row->slug.'">' . $row->name . '</a></li>';
            }
            $rtn .= '</ul>';
        }
        $rtn .= '</div>';

        $rtn .= '<h3>' . __('All available terms','teachcourses') . '</h3>';
        $rtn .= tc_Shortcodes::get_list_of_terms();
        return $rtn;
    }

    /** 
     * Get List of all available and visbile terms
     */
    static function get_list_of_terms() {
        $terms = TC_Terms::get_terms();
        $rtn = '<ul>';
        foreach ( $terms as $term ) {
            $rtn .= '<li><a href="./'.$term->slug.'">' . $term->name . '</a></li>';
        }
        $rtn .= '</ul>';
        return $rtn;
    }

    /**
     * Returns a table headline for a course document list
     * @param array $row        An associative array of document data (i.e. name)
     * @param int $numbered     Display a numbered list (1) or not (0)
     * @param int $show_date    Display the upload date (1) or not (0)
     * @return string
     * @since 5.0.0
     */
    public static function get_coursedocs_headline ($row, $numbered, $show_date) {
        $span = 1;
        if ( $numbered === 1 ) {
            $span++;
        }
        if ( $show_date === 1 ) {
            $span++;
        }
        $colspan = ( $span > 1 ) ? 'colspan="' . $span . '"' : '';
        return '<th class="tc_coursedocs_headline" ' . $colspan . '>' . stripcslashes($row['name']) . '</th>';
    }
    
    /**
     * Returns a single table line for the function tc_courselist()
     * @param array $row            An associative array of document data (i.e. name, added)
     * @param array $upload_dir     An associative array of upload dir data
     * @param string $link_class    The link class
     * @param string $date_format   A typical date format string like d.m.Y
     * @param int $numbered         Display a numbered list (1) or not (0)
     * @param int $num              The current position in a numbered list
     * @param int $show_date        Display the upload date (1) or not (0)
     * @return string
     * @since 5.0.0
     */
    public static function get_coursedocs_line ($row, $upload_dir, $link_class, $date_format, $numbered, $num, $show_date) {
        $return = '';
        $date = date( $date_format, strtotime($row['added']) );
        
        if ( $numbered === 1 ) {
            $return .= '<td>' . $num . '</td>';
        }
        
        if ( $show_date === 1 ) {
            $return .= '<td><span title="' . __('Published on','teachcourses') . ' ' . $date . '">' . $date . '</span></td>';
        }
        
        $return .= '<td><a href="' . $upload_dir['baseurl'] . $row['path'] . '" class="' . $link_class . '">' . stripcslashes($row['name']) . '</a></td>';
        return $return;
    }
    
    /**
     * Generates and returns filter for the shortcodes (jumpmenus)
     * @param array $row                The array of select options
     * @param string $id                name/id of the form field
     * @param string $index             year/type/author_id/user/tag_id
     * @param string $title             The title for the default value
     * @param array $filter_parameter   An array with the user input. The keys are: year, type, author, user
     * @param array $settings           An array with SQL search parameter (user, type, exclude, exclude_tags, order)
     * @param int tabindex              The tabindex fo the form field
     * @param string $mode              year/type/author/user/tag
     * @return string
     * @since 7.0.0
     */
    private static function generate_filter_jumpmenu($row, $id, $index, $title, $filter_parameter, $settings, $tabindex, $mode) {
        $options = '';
        
        // generate option
        foreach ( $row as $row ){
            // Set the values for URL parameters
            $current = ( $row[$index] == $filter_parameter[$mode] && $filter_parameter[$mode] != '0' ) ? 'selected="selected"' : '';
            $tag = ( $mode === 'tag' ) ? $row['tag_id'] : $filter_parameter['tag'] ;
            $year = ( $mode === 'year' ) ? $row['year'] : $filter_parameter['year'];
            $type = ( $mode === 'type' ) ? $row['type'] : $filter_parameter['type'];
            $user = ( $mode === 'user' ) ? $row['user'] : $filter_parameter['user'];
            $author = ( $mode === 'author' ) ? $row['author_id'] : $filter_parameter['author'];
            
            // Set the label for each select option
            if ( $mode === 'type' ) {
                $text = tc_translate_pub_type($row['type'], 'pl');
            }
            else if ( $mode === 'author' ) {
                $text = tc_Bibtex::parse_author($row['name'], '', $settings['author_name']);
            }
            else if ( $mode === 'user' ) {
                $user_info = get_userdata( $row['user'] );
                if ( $user_info === false ) {
                    continue;
                }
                $text = $user_info->display_name;
            }
            else if ( $mode === 'tag' ) {
                $text = $row['name'];
            }
            else {
                $text = $row[$index];
            }
            
            // Write the select option
            $options .= '<option value = "tgid=' . $tag. '&amp;yr=' . $year . '&amp;type=' . $type . '&amp;usr=' . $user . '&amp;auth=' . $author . $settings['html_anchor'] . '" ' . $current . '>' . stripslashes(urldecode($text)) . '</option>';
        }

        // clear filter_parameter[$mode]
        $filter_parameter[$mode] = '';
        
        // return filter menu
        return '<select class="' . $settings['filter_class'] . '" name="' . $id . '" id="' . $id . '" tabindex="' . $tabindex . '" onchange="teachcourses_jumpMenu(' . "'" . 'parent' . "'" . ',this, ' . "'" . stripslashes(urldecode($settings['permalink'])) . "'" . ')">
                   <option value="tgid=' . $filter_parameter['tag'] . '&amp;yr=' . $filter_parameter['year'] . '&amp;type=' . $filter_parameter['type'] . '&amp;usr=' . $filter_parameter['user'] . '&amp;auth=' . $filter_parameter['author'] . '' . $settings['html_anchor'] . '">' . $title . '</option>
                   ' . $options . '
                </select>';
    }
    
    /**
     * Generates and returns filter for the shortcodes (selectmenus)
     * @param array $row                The array of select options
     * @param string $id                name/id of the form field
     * @param string $index             year/type/author_id/user/tag_id
     * @param string $title             The title for the default value
     * @param array $filter_parameter   An array with the user input. The keys are: year, type, author, user
     * @param array $settings           An array with SQL search parameter (user, type, exclude, exclude_tags, order)
     * @param int tabindex              The tabindex fo the form field
     * @param string $mode              year/type/author/user/tag
     * @return string
     * @since 7.0.0
     */
    private static function generate_filter_selectmenu($row, $id, $index, $title, $filter_parameter, $settings, $tabindex, $mode ) {
        $options = '';
        
        // generate option
        foreach ( $row as $row ){
            $current = ( $row[$index] == $filter_parameter[$mode] && $filter_parameter[$mode] != '0' ) ? 'selected="selected"' : '';
            $value = '';
            
            // Set the label for each select option
            if ( $mode === 'type' ) {
                $text = tc_translate_pub_type($row['type'], 'pl');
                $value = $row['type'];
            }
            else if ( $mode === 'author' ) {
                $text = tc_Bibtex::parse_author($row['name'], '', $settings['author_name']);
                $value = $row['author_id'];
            }
            else if ( $mode === 'user' ) {
                $user_info = get_userdata( $row['user'] );
                if ( $user_info === false ) {
                    continue;
                }
                $text = $user_info->display_name;
                $value = $row['user'];
            }
            else if ( $mode === 'tag' ) {
                $text = $row['name'];
                $value = $row['tag_id'];
            }
            else {
                $text = $row[$index];
                $value = $row[$index];
            }
            
            // Write the select option
            $options .= '<option value="' . $value. '" ' . $current . '>' . stripslashes($text) . '</option>';
        }

        // clear filter_parameter[$mode]
        $filter_parameter[$mode] = '';
        
        // return filter menu
        return '<select class="' . $settings['filter_class'] . '" name="' . $id . '" id="' . $id . '" tabindex="' . $tabindex . '">
                   <option value="">' . $title . '</option>
                   ' . $options . '
                </select>';
    }
    
    /**
     * Generates the pagination limits for lists
     * @param int $pagination           0 or 1 (pagination is used or not)
     * @param int $entries_per_page     Number of entries per page
     * @param int $form_limit           Current position in the list, which is set by a form 
     * @return array
     * @since 6.0.0
     */
    public static function generate_pagination_limits($pagination, $entries_per_page, $form_limit) {
        
        // Define page variables
        if ( $form_limit != '' ) {
            $current_page = $form_limit;
            if ( $current_page <= 0 ) {
                $current_page = 1;
            }
            $entry_limit = ( $current_page - 1 ) * $entries_per_page;
        }
        else {
            $entry_limit = 0;
            $current_page = 1;
        }
        
        // Define SQL limit
        if ( $pagination === 1 ) {
            $limit = $entry_limit . ',' .  $entries_per_page;
        }
        else {
            $limit = ( $entries_per_page > 0 ) ? $entry_limit . ',' .  $entries_per_page : '';
        }
        
        return array(
            'entry_limit' => $entry_limit,
            'current_page' => $current_page,
            'limit' => $limit
        );
        
    }
    
    /**
     * Generates the list of publications for [tplist], [tpcloud], [tpsearch]
     * @param array $tparray    The array of publications
     * @param object $template  The template object
     * @param array $args       An associative array with options (headline,...)
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function generate_pub_table($tparray, $template, $args ) {
        $headlines = array();
        if ( $args['headline'] == 1 ) {
            foreach( $args['years'] as $row ) {
                $headlines[$row['year']] = '';
            }
            $pubs = tc_Shortcodes::sort_pub_table( $tparray, $template, $headlines , $args );
        }
        elseif ( $args['headline'] == 2 ) {
            $pub_types = tc_Publications::get_used_pubtypes( array('user' => $args['user'] ) );
            foreach( $pub_types as $row ) {
                $headlines[$row['type']] = '';
            }
            $pubs = tc_Shortcodes::sort_pub_table( $tparray, $template, $headlines, $args );
        }
        else {
            $pubs = tc_Shortcodes::sort_pub_table( $tparray, $template, '', $args );
        }
        return $template->get_body($pubs, $args);
    }

    
    /**
     * Sort the table lines of a publication table
     * @param array $tparray        Array of publications
     * @param object $template      The template object
     * @param array $headlines      Array of headlines
     * @param array $args           Array of arguments
     * @return string 
     * @since 5.0.0
     * @access public
     */
    public static function sort_pub_table($tparray, $template, $headlines, $args) {
        $publications = '';
        $tpz = $args['number_publications'];

        // with headlines
        if ( $args['headline'] === 1 || $args['headline'] === 2 ) {
            $publications = tc_Shortcodes::sort_pub_by_type_or_year($tparray, $template, $tpz, $args, $headlines);
        }
        // with headlines grouped by year then by type
        else if ($args['headline'] === 3) {
            $publications = tc_Shortcodes::sort_pub_by_year_type($tparray, $template, $tpz, $args);
        }
        // with headlines grouped by type then by year
        else if ($args['headline'] === 4) {
            $publications = tc_Shortcodes::sort_pub_by_type_year($tparray, $template, $tpz, $args);
        }
        // without headlines
        else {
            for ($i = 0; $i < $tpz; $i++) {
                $publications .= $tparray[$i][1];
            }
        }

        return $publications;
    }
    
    /**
     * Sorts the publications by type or by year. This is the default sort function
     * @param array $tparray    The numeric publication array
     * @param object $template  The template object
     * @param int $tpz          The length of $tparray
     * @param array $args       An associative of arguments (colspan)
     * @return string
     * @access private
     * @since 5.0.0
     */
    private static function sort_pub_by_type_or_year($tparray, $template, $tpz, $args, $headlines){
        $return = '';
        $field = ( $args['headline'] === 2 ) ? 2 : 0;
        for ( $i = 0; $i < $tpz; $i++ ) {
            $key = $tparray[$i][$field];
            $headlines[$key] .= $tparray[$i][1];
        }
        
        // custom sort order
        if ( $args['sort_list'] !== '' ) {
            $args['sort_list'] = str_replace(' ', '', $args['sort_list']);
            $sort_list = explode(',', $args['sort_list']);
            $max = count($sort_list);
            $sorted = array();
            for ($i = 0; $i < $max; $i++) {
                if ( array_key_exists($sort_list[$i], $headlines) ) {
                    $sorted[$sort_list[$i]] = $headlines[$sort_list[$i]];
                }
            }
            $headlines = $sorted;
        }
        
        // set headline
        foreach ( $headlines as $key => $value ) {
            if ( $value != '' ) {
                $line_title = ( $args['headline'] === 1 ) ? $key : tc_translate_pub_type($key, 'pl');
                $return .=  $template->get_headline($line_title, $args);
                $return .=  $value;
            }
        }
        return $return;
    }
    
    /**
     * Sorts the publications by type and by year (used for headline type 4)
     * @param array $tparray    The numeric publication array
     * @param object $template  The template object
     * @param int $tpz          The length of $tparray
     * @param array $args       An associative of arguments (colspan)
     * @return string
     * @access private
     * @since 5.0.0
     */
    private static function sort_pub_by_type_year($tparray, $template, $tpz, $args) {
        $return = '';
        $typeHeadlines = array();
        for ($i = 0; $i < $tpz; $i++) {
            $keyYear = $tparray[$i][0];
            $keyType = $tparray[$i][2];
            $pubVal  = $tparray[$i][1];
            if(!array_key_exists($keyType, $typeHeadlines)) {
                $typeHeadlines[$keyType] = array($keyYear => $pubVal); 
            }
            else if(!array_key_exists($keyYear, $typeHeadlines[$keyType])) {
                $typeHeadlines[$keyType][$keyYear] = $pubVal;
            }
            else {
                $typeHeadlines[$keyType][$keyYear] .= $pubVal;
            }
        }
        foreach ( $typeHeadlines as $type => $yearHeadlines ) {
            $return .= $template->get_headline( tc_translate_pub_type($type, 'pl'), $args );
            foreach($yearHeadlines as $year => $pubValue) {
                if ($pubValue != '' ) {
                    $return .= $template->get_headline( $year, $args );
                    $return .= $pubValue;
                }
            }
        }
        return $return;
    }
    
    /**
     * Sorts the publications by year and by type (used for headline type 3)
     * @param array $tparray    The numeric publication array
     * @param object $template  The template object
     * @param int $tpz          The length of $tparray
     * @param array $args       An associative of arguments (colspan)
     * @return string
     * @access private
     * @since 5.0.0
     */
    private static function sort_pub_by_year_type ($tparray, $template, $tpz, $args) {
        $return = '';
        $yearHeadlines = array();
        for ($i = 0; $i < $tpz; $i++) {
            $keyYear = $tparray[$i][0];
            $keyType = $tparray[$i][2];
            if(!array_key_exists($keyYear, $yearHeadlines)) {
                $yearHeadlines[$keyYear] = array($keyType => '');
            }
            else if(!array_key_exists($keyType, $yearHeadlines[$keyYear])) {
                $yearHeadlines[$keyYear][$keyType] = '';
            }
            $yearHeadlines[$keyYear][$keyType] .= $tparray[$i][1];
        }

        foreach ( $yearHeadlines as $year => $typeHeadlines ) {
            $return .= $template->get_headline($year, $args);
            foreach($typeHeadlines as $type => $value) {
                if ($value != '' ) {
                    $return .= $template->get_headline( tc_translate_pub_type($type, 'pl'), $args );
                    $return .=  $value;
                }
            }
        }
        return $return;
    }
    
    /**
     * Sets the colspan for the rows of publication list headlines
     * @param array $settings
     * @return string
     * @since 7.0.0
     */
    public static function set_colspan ($settings) {
        $count = 1;
        
        // if there is a numbered style
        if ( $settings['style'] === 'numbered' || $settings['style'] === 'numbered_desc' ) {
            $count++;
        }
        
        // if there is an image left or right
        if ( $settings['image']== 'left' || $settings['image']== 'right' ) {
            $count++;
        }
        
        // if there is an altmetric donut
        if ( $settings['show_altmetric_donut']  ) {
            $count++;
        }
        
        if ( $count < 2 ) {
            return '';
        }
        return ' colspan="' . $count . '"';
    }
}

/** 
 * Shows an overview of courses
 * 
 * @param array $atts {
 *      @type string image      left, right, bottom or none, default: none
 *      @type int image_size    default: 0
 *      @type int headline      0 for hide headline, 1 for show headline (default:1)
 *      @type string text       a custom text under the headline
 *      @type string term       the term/semester you want to show
 * }
 * @param string $semester (GET)
 * @return string
 * @since 2.0.0
*/
function tc_courselist_shortcode($atts) {	
    $param = shortcode_atts(array(
       'headline'   => 1,
       'text'       => '',
       'term'       => ''
    ), $atts);
    $term = htmlspecialchars($param['term']);
    $headline = intval($param['headline']);

    $url = array(
        'post_id' => get_the_id()
    );

    // hanlde permalinks
    if ( !get_option('permalink_structure') ) {
        $page = ( is_page() ) ? 'page_id' : 'p';
        $page = '<input type="hidden" name="' . $page . '" id="' . $page . '" value="' . $url["post_id"] . '"/>';
    }
    else {
        $page = '';
    }
    
    // define term
    if ( isset( $_GET['semester'] ) ) {
        $sem = htmlspecialchars($_GET['semester']);
    }
    elseif ( $term != '' ) {
        $sem = $term;
    }
    else {
        $sem = get_tc_option('active_term');
    }
   
    $rtn = '<div id="tpcourselist">';
    if ($headline === 1) {
         $rtn .= '<h2>' . __('Courses for the','teachcourses') . ' ' . stripslashes($sem) . '</h2>';
    }
    $rtn .= '' . $text . '
               <form name="lvs" method="get" action="' . esc_url($_SERVER['REQUEST_URI']) . '">
               ' . $page . '		
               <div class="tc_auswahl"><label for="semester">' . __('Select the term','teachcourses') . '</label> <select name="semester" id="semester" title="' . __('Select the term','teachcourses') . '">';
    $rowsem = get_tc_options('semester');
    foreach( $rowsem as $rowsem ) { 
        $current = ($rowsem->value == $sem) ? 'selected="selected"' : '';
        $rtn .= '<option value="' . $rowsem->value . '" ' . $current . '>' . stripslashes($rowsem->value) . '</option>';
    }
    $rtn .= '</select>
           <input type="submit" name="start" value="' . __('Show','teachcourses') . '" id="teachcourses_submit" class="button-secondary"/>
    </div>';
    $rtn2 = '';
    $row = TC_Courses::get_courses( array('semester' => $sem, 'parent' => 0, 'visibility' => '1,2') );
    if ( count($row) != 0 ){
        foreach($row as $row) {
            $rtn2 .= tc_Shortcodes::get_courselist_line ($row, $image, $image_size, $sem);
        } 
    }
    else {
        $rtn2 = '<tr><td class="teachcourses_message">' . __('Sorry, no entries matched your criteria.','teachcourses') . '</td></tr>';
    }
    $rtn2 = '<table class="teachcourses_course_list">' . $rtn2 . '</table>';
    $rtn3 = '</form></div>';
    return $rtn . $rtn2 . $rtn3;
}

/**
 * Displays the attached documents of a course
 * 
 * @param array $atts {
 *      @type int id                ID of the course 
 *      @type string linkclass      The name of the html class for document links, default is: linksecure
 *      @type string date_format    Default: d.m.Y
 *      @type int show_date         1 (date is visible) or 0, default is: 1
 *      @type int numbered          1 (use numbering) or 0, default is: 0
 *      @type int headline          1 (display headline) or 0, default is: 1
 * }
 * @since 5.0.0
 */
function tc_coursedocs_shortcode($atts) {
    $param = shortcode_atts(array(
       'id'             => '',
       'link_class'     => 'linksecure',
       'date_format'    => 'd.m.Y',
       'show_date'      => 1,
       'numbered'       => 0,
       'headline'       => 1
    ), $atts);
    $course_id = intval($param['id']);
    $headline = intval($param['headline']);
    $link_class = htmlspecialchars($param['link_class']);
    $date_format = htmlspecialchars($param['date_format']);
    $show_date = intval($param['show_date']);
    $numbered = intval($param['numbered']);
    $upload_dir = wp_upload_dir();
    $documents = tc_Documents::get_documents($course_id);
    
    if ( $headline === 1 ) {
        $a = '<div class="tc_course_headline">' . __('Documents','teachcourses') . '</div>';
    }
    
    if ( count($documents) === 0 ) {
        return $a;
    }
    
    $num = 1;
    $body = '<table class="tc_coursedocs">';
    foreach ( $documents as $row ) {
        $body .= '<tr>';
        if ( $row['path'] === '' ) {
            $body .= tc_Shortcodes::get_coursedocs_headline($row, $numbered, $show_date);
            $num = 1;
        }
        else {
            $body .= tc_Shortcodes::get_coursedocs_line($row, $upload_dir, $link_class, $date_format, $numbered, $num, $show_date);
            $num++;
        }
        $body .= '</tr>';
    }
    $body .= '</table>';
    return $a . $body;
}

/** 
 * Displays information about a single course and his childs
 * 
 * @param array $atts {
 *       @type int id           ID of the course 
 *       @type int show_meta    Display course meta data (1) or not (0), default is 1
 * }
 * @return string
 * @since 5.0.0
*/
function tc_courseinfo_shortcode($atts) {
    $param = shortcode_atts(array(
       'id'         => 0,
       'show_meta'  => 1
    ), $atts);
    $id = intval($param['id']);
    $show_meta = intval($param['show_meta']);
    
    if ( $id === 0 ) {
        return;
    }
    
    $course = TC_Courses::get_course($id);
    $fields = get_tc_options('teachcourses_courses','`setting_id` ASC', ARRAY_A);
    $v_test = $course->name;
    $body = '';
    $head = '<div class="tc_course_headline">' . __('Date(s)','teachcourses') . '</div>';
    $head .= '<table class="tc_courseinfo">';
    
    $head .= '<tr>';
    $head .= '<td class="tc_courseinfo_type"><strong>' . stripslashes($course->type) . '</strong></td>';
    $head .= '<td class="tc_courseinfo_main">';
    $head .= '<p>' . stripslashes($course->date) . ' ' . stripslashes($course->room) . '</p>';
    $head .= '<p>' . stripslashes(nl2br($course->comment)) . '</p>';
    $head .= '</td>';
    $head .= '<td clas="tc_courseinfo_lecturer">' . stripslashes($course->lecturer) . '</td>';
    $head .= '</tr>';
    
    // Search the child courses
    $row = TC_Courses::get_courses( array('parent' => $id, 'visible' => '1,2', 'order' => 'name, course_id') );
    foreach($row as $row) {
        // if parent name = child name
        if ($v_test == $row->name) {
            $row->name = $row->type;
        }
        $body .= '<tr>';
        $body .= '<td class="tc_courseinfo_type"><strong>' . stripslashes($row->name) . '</strong></td>';
        $body .= '<td class="tc_courseinfo_meta">';
        $body .= '<p>' . stripslashes($row->date) . ' ' . stripslashes($row->room) . '</p>';
        $body .= '<p>' . stripslashes($row->comment) . '</p>';
        if ( $show_meta === 1 ) {
            $body .= tc_Shortcodes::get_coursemeta_line($id, $fields);
        }
        $body .= '</td>';
        $body .= '<td class="tc_courseinfo_lecturer">' . stripslashes($row->lecturer) . '</td>';
        $body .= '</tr>';
    } 
    return $head . $body . '</table>';
}

/**
 * Prints the references

 * @param array $atts {
 *      @type string author_name        last, initials or old, default: simple
 *      @type string editor_name        last, initials or old, default: initials
 *      @type string author_separator   The separator for author names, default: ;
 *      @type string editor_separator   The separator for editor names, default: ;
 *      @type string date_format        The format for date; needed for the types: presentations, online; default: d.m.Y
 *      @type int show_links            0 (false) or 1 (true), default: 0
 * }
 * @return string
 * @since 6.0.0
 */
function tc_ref_shortcode($atts) {
    global $tc_cite_object;
    
    // shortcode parameter defaults
    $param = shortcode_atts(array(
       'author_name'        => 'simple',
       'editor_name'        => 'initials',
       'author_separator'   => ',',
       'editor_separator'   => ';',
       'date_format'        => 'd.m.Y',
       'show_links'         => 0
    ), $atts);
    
    // define settings
    $settings = array(
       'author_name'        => htmlspecialchars($param['author_name']),
       'editor_name'        => htmlspecialchars($param['editor_name']),
       'author_separator'   => htmlspecialchars($param['author_separator']),
       'editor_separator'   => htmlspecialchars($param['editor_separator']),
       'date_format'        => htmlspecialchars($param['date_format']),
       'style'              => 'simple',
       'title_ref'          => 'links',
       'link_style'         => ($param['show_links'] == 1) ? 'direct' : 'none',
       'meta_label_in'      => __('In','teachcourses') . ': ',
       'use_span'           => false
    );
    
    // define reference part
    $references = isset($tc_cite_object) ? $tc_cite_object->get_ref() : array();
    
    // If there is no reference to show
    if ( empty($references) ) {
        return;
    }
    
    $ret = '<h3 class="teachcourses_ref_headline">' . __('References','teachcourses') . '</h3>';
    $ret .= '<ol>';
    foreach ( $references as $row ) {
        $ret .= '<li id="tc_cite_' . $row['pub_id'] . '" class="tc_cite_entry"><span class="tc_single_author">' . stripslashes($row['author']) . '</span><span class="tc_single_year"> (' . $row['year'] . ')</span>: <span class="tc_single_title">' . tc_HTML_Publication_Template::prepare_publication_title($row, $settings, 1) . '</span>. <span class="tc_single_additional">' . tc_HTML_Publication_Template::get_publication_meta_row($row, $settings) . '</span></li>';
    }
    $ret .= '</ol>';
    return $ret;
}

/**
 * General interface for [tccloud], [tclist] and [tcsearch]
 * 
 * Parameters from $_GET: 
 *      $yr (INT)               Year 
 *      $type (STRING)          Publication type 
 *      $auth (INT)             Author ID
 *      $tgid (INT)             Tag ID
 *      $usr (INT)              User ID
 *      $tsr (STRING)           Full text search
 * 
 * 
 * @param array $atts {
 *      @type string user                  the WordPress IDs of on or more users (separated by comma)
 *      @type string tag                   tag IDs (separated by comma)
 *      @type string type                  the publication types you want to show (separated by comma)
 *      @type string author                author IDs (separated by comma)
 *      @type string year                  one or more years (separated by comma)
 *      @type string exclude               one or more IDs of publications you don't want to show (separated by comma)
 *      @type string include               one or more IDs of publications you want to show (separated by comma)
 *      @type string include_editor_as_author  0 (false) or 1 (true), default: 1
 *      @type string order                 title, year, bibtex or type, default: date DESC
 *      @type int headline                 show headlines with years(1), with publication types(2), with years and types (3), with types and years (4) or not(0), default: 1
 *      @type int maxsinze                 maximal font size for the tag cloud, default: 35
 *      @type int minsize                  minimal font size for the tag cloud, default: 11
 *      @type int tag_limit                number of tags, default: 30
 *      @type string hide_tags             ids of the tags you want to hide from your users (separated by comma)
 *      @type string exclude_tags          similar to hide_tags but with influence on publications; if exclude_tags is defined hide_tags will be ignored
 *      @type string exclude_types         name of the publication types you want to exclude (separated by comma)
 *      @type string image                 none, left, right or bottom, default: none 
 *      @type int image_size               max. Image size, default: 0
 *      @type string image_link            none, self, rel_page or external (defalt: none)
 *      @type string author_name           Author name style options: simple, last, initials, short or old, default: initials
 *      @type string editor_name           Editor name style options: simple, last, initials, short or old, default: initials
 *      @type string author_separator      The separator for author names
 *      @type string editor_separator      The separator for author names
 *      @type string style                 List style options: numbered, numbered_desc or none, default: none
 *      @type string template              The key of the used template, default: tc_template_2021
 *      @type string title_ref             Defines the target for the title link. Options: links or abstract, default: links
 *      @type string link_style            Defines the style of the publication links. Options: inline, direct or images, default: inline
 *      @type string date_format           The format for date, needed for the types: presentations, online; default: d.m.Y
 *      @type int pagination               Activates pagination (1) or not (0), default: 1
 *      @type int entries_per_page         Number of publications per page (pagination must be set to 1), default: 50
 *      @type string sort_list             A list of publication types (separated by comma) which overwrites the default sort order for headline = 2
 *      @type string show_tags_as          Style option for the tags: cloud, pulldown, plain or none, default: cloud
 *      @type int show_author_filter       0 (false) or 1 (true), default: 1
 *      @type string show_in_author_filter Can be used to manage the visisble authors in the author filter. Uses the author IDs (separated by comma)
 *      @type int show_type_filter         0 (false) or 1 (true), default: 1
 *      @type int show_user_filter         0 (false) or 1 (true), default: 1
 *      @type int show_search_filter       0 (false) or 1 (true), default: 1
 *      @type int show_year_filter         0 (false) or 1 (true), default: 1
 *      @type int show_bibtex              Show bibtex container under each entry (1) or not (0), default: 1
 *      @type string container_suffix      a suffix which can optionally set to modify container IDs in publication lists. It's not set by default.
 *      @type string filter_class          The CSS class for filter/select menus, default: default
 *      @type int show_altmetric_donut     0 (false) or 1 (true), default: 0
 *      @type int show_altmetric_entrx     0 (false) or 1 (true), default: 0
 *      @type int use_jumpmenu             Use filter as jumpmenu (1) or not (0), default: 1
 *      @type int use_as_filter            Show all entries by default (1) o not (0), default 1
 * }
 * @return string
 * @since 7.0.0
 */
function tc_publist_shortcode ($atts) {
    $atts = shortcode_atts(array(
        'user'                  => '',
        'tag'                   => '',
        'type'                  => '',
        'author'                => '',
        'year'                  => '',
        'exclude'               => '',
        'include'               => '',
        'include_editor_as_author' => 1,
        'order'                 => 'date DESC',
        'headline'              => 1,
        'maxsize'               => 35,
        'minsize'               => 11,
        'tag_limit'             => 30,
        'hide_tags'             => '',
        'exclude_tags'          => '',
        'exclude_types'         => '',
        'image'                 => 'none',
        'image_size'            => 0,
        'image_link'            => 'none',
        'anchor'                => 1,                   
        'author_name'           => 'initials',
        'editor_name'           => 'initials',
        'author_separator'      => ';',
        'editor_separator'      => ';',
        'style'                 => 'none',
        'template'              => 'tc_template_2021',
        'title_ref'             => 'links',
        'link_style'            => 'inline',
        'date_format'           => 'd.m.Y',
        'pagination'            => 1,
        'entries_per_page'      => 50,
        'sort_list'             => '',
        'show_tags_as'          => 'cloud',
        'show_author_filter'    => 1,
        'show_in_author_filter' => '',
        'show_type_filter'      => 1,
        'show_user_filter'      => 1,
        'show_search_filter'    => 1,
        'show_year_filter'      => 1,
        'show_bibtex'           => 1,
        'container_suffix'      => '',
        'filter_class'          => 'default',
        'show_altmetric_donut'  => 0,
        'show_altmetric_entry'  => 0,
        'use_jumpmenu'          => 1,
        'use_as_filter'         => 1
    ), $atts);
    
    $settings = array(
        'author_name'           => htmlspecialchars($atts['author_name']),
        'editor_name'           => htmlspecialchars($atts['editor_name']),
        'author_separator'      => htmlspecialchars($atts['author_separator']),
        'editor_separator'      => htmlspecialchars($atts['editor_separator']),
        'headline'              => intval($atts['headline']),
        'style'                 => htmlspecialchars($atts['style']),
        'template'              => htmlspecialchars($atts['template']),
        'image'                 => htmlspecialchars($atts['image']),
        'image_link'            => htmlspecialchars($atts['image_link']),
        'link_style'            => htmlspecialchars($atts['link_style']),
        'title_ref'             => htmlspecialchars($atts['title_ref']),
        'html_anchor'           => ( $atts['anchor'] == '1' ) ? '#tppubs' . htmlspecialchars($atts['container_suffix']) : '',
        'date_format'           => htmlspecialchars($atts['date_format']),
        'permalink'             => ( get_option('permalink_structure') ) ? get_permalink() . "?" : get_permalink() . "&amp;",
        'convert_bibtex'        => ( get_tc_option('convert_bibtex') == '1' ) ? true : false,
        'pagination'            => intval($atts['pagination']),
        'entries_per_page'      => intval($atts['entries_per_page']),
        'sort_list'             => htmlspecialchars($atts['sort_list']),
        'show_author_filter'    => ( $atts['show_author_filter'] == '1' ) ? true : false,
        'show_type_filter'      => ( $atts['show_type_filter'] == '1' ) ? true : false,
        'show_user_filter'      => ( $atts['show_user_filter'] == '1' ) ? true : false,
        'show_year_filter'      => ( $atts['show_year_filter'] == '1' ) ? true : false,
        'show_search_filter'    => ( $atts['show_search_filter'] == '1' ) ? true : false,
        'show_bibtex'           => ( $atts['show_bibtex'] == '1' ) ? true : false,
        'show_tags_as'          => htmlspecialchars($atts['show_tags_as']),
        'container_suffix'      => htmlspecialchars($atts['container_suffix']),
        'filter_class'          => htmlspecialchars($atts['filter_class']),
        'show_altmetric_entry'  => ($atts['show_altmetric_entry'] == '1') ? true : false,
        'show_altmetric_donut'  => ($atts['show_altmetric_donut'] == '1') ? true : false,
        'use_jumpmenu'          => ( $atts['use_jumpmenu'] == '1' ) ? true : false
    );

    // Settings for the tag cloud
    $cloud_settings = array (
        'tag_limit'             => intval($atts['tag_limit']),
        'hide_tags'             => htmlspecialchars($atts['hide_tags']),
        'maxsize'               => intval($atts['maxsize']),
        'minsize'               => intval($atts['minsize'])
    );
    
    // Settings for and from form fields
    $filter_parameter = array(
        'tag'                   => ( isset ($_GET['tgid']) && $_GET['tgid'] != '' ) ? tc_convert_input_to_string($_GET['tgid'], 'int') : '',
        'year'                  => ( isset ($_GET['yr']) && $_GET['yr'] != '' ) ? intval($_GET['yr']) : '',
        'type'                  => isset ($_GET['type']) ? htmlspecialchars( $_GET['type'] ) : '',
        'author'                => ( isset ($_GET['auth']) && $_GET['auth'] != '' ) ? intval($_GET['auth']) : '',
        'user'                  => ( isset ($_GET['usr']) && $_GET['usr'] != '' ) ? intval($_GET['usr']) : '',
        'search'                => isset ($_GET['tsr']) ? htmlspecialchars( $_GET['tsr'] ) : '',
        'show_in_author_filter' => htmlspecialchars($atts['show_in_author_filter']),
        'tag_preselect'         => htmlspecialchars($atts['tag']),
        'year_preselect'        => htmlspecialchars($atts['year']),
        'author_preselect'      => htmlspecialchars($atts['author']),
        'type_preselect'        => htmlspecialchars($atts['type']),
        'user_preselect'        => htmlspecialchars($atts['user']),
    );
    
    /*
     * Settings for data selection
     * 
     * Default values are from the shortcode parameters
     * Can be overwritten with filter_parameter
     */
    $sql_parameter = array (
        'user'          => ( $filter_parameter['user'] !== '' ) ? $filter_parameter['user'] : htmlspecialchars($atts['user']),
        'type'          => ( $filter_parameter['type'] !== '' ) ? $filter_parameter['type'] : htmlspecialchars($atts['type']),
        'author'        => ( $filter_parameter['author'] !== '' ) ? $filter_parameter['author'] : htmlspecialchars($atts['author']),
        'year'          => ( $filter_parameter['year'] !== '' ) ? $filter_parameter['year'] : htmlspecialchars($atts['year']),
        'tag'           => ( $filter_parameter['tag'] !== '' ) ? $filter_parameter['tag'] : htmlspecialchars($atts['tag']),
        'exclude'       => htmlspecialchars($atts['exclude']),
        'exclude_tags'  => htmlspecialchars($atts['exclude_tags']),
        'exclude_types' => htmlspecialchars($atts['exclude_types']),
        'order'         => htmlspecialchars($atts['order']),
    );
   
    // Handle limits for pagination   
    $form_limit = ( isset($_GET['limit']) ) ? intval($_GET['limit']) : '';
    $pagination_limits = tc_Shortcodes::generate_pagination_limits($settings['pagination'], $settings['entries_per_page'], $form_limit);

    // ignore hide_tags if exclude_tags is given 
    if ( $sql_parameter['exclude_tags'] != '' ) {
        $atts['hide_tags'] = $sql_parameter['exclude_tags'];
    }

    /*************/
    /* Tag cloud */
    /*************/
    $tag_cloud = '';
    if ( $settings['show_tags_as'] === 'cloud' ) {
        $tag_cloud = tc_Shortcodes::generate_tag_cloud($atts['user'], $cloud_settings, $filter_parameter, $sql_parameter, $settings);
    }
    
    /****************/
    /* Search Field */
    /****************/
    
    $searchbox = '';
    
    if ( $settings['show_search_filter'] === true ) {
        if ( !get_option('permalink_structure') ) {
            $searchbox .= '<input type="hidden" name="p" id="page_id" value="' . get_the_id() . '"/>';
        }

        $searchbox .= '<input name="tsr" id="tc_search_input_field" type="search" placeholder="' . __('Enter search word','teachcourses') .'" value="' . stripslashes($filter_parameter['search']) . '" tabindex="1"/>';
        $searchbox .= '<input name="tps_button" class="tc_search_button" type="submit" tabindex="7" value="' . __('Search', 'teachcourses') . '"/>';

    }
    
    /**********/ 
    /* Filter */
    /**********/
    $filter = '';
    
    // Filter year
    if ( ( $atts['year'] == '' || strpos($atts['year'], ',') !== false ) && 
            $settings['show_year_filter'] === true ) {
        $filter .= tc_Shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 2,'year');
    }

    // Filter type
    if ( ( $atts['type'] == '' || strpos($atts['type'], ',') !== false ) && 
            $settings['show_type_filter'] === true ) {
        $filter .= tc_Shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 3, 'type');
    }
    
    // Filter tag
    if ( $settings['show_tags_as'] === 'pulldown' ) {
        $filter .= tc_Shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 4,'tag');
    }

    // Filter author
    if ( ( $atts['author'] == '' || strpos($atts['author'], ',') !== false ) && 
            $settings['show_author_filter'] === true ) {
        $filter .= tc_Shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 5,'author');
    }
    
    // Filter user
    if ( ( $atts['user'] == '' || strpos($atts['user'], ',') !== false ) &&
            $settings['show_user_filter'] === true ) {
        $filter .= tc_Shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 6,'user');
    }

    // Show all link
    if ( ( $filter_parameter['year'] == '' || $filter_parameter['year'] == $atts['year'] ) && 
         ( $filter_parameter['type'] == '' || $filter_parameter['type'] == $atts['type'] ) && 
         ( $filter_parameter['user'] == '' || $filter_parameter['user'] == $atts['user'] ) && 
         ( $filter_parameter['author'] == '' || $filter_parameter['author'] == $atts['author'] ) && 
         ( $filter_parameter['tag'] == '' || $filter_parameter['tag'] == $atts['tag'] ) && 
           $filter_parameter['search'] == '' 
        ) {
        $showall = '';
    }
    else {
        $showall = '<a rel="nofollow" href="' . $settings['permalink'] . $settings['html_anchor'] . '" title="' . __('Show all','teachcourses') . '">' . __('Show all','teachcourses') . '</a>';
    }
    
    /***********************/ 
    /* Complete the header */
    /***********************/
    
    $part1 = '';
    
    // anchor
    $part1 .= '<a name="tppubs" id="tppubs"' . $settings['container_suffix'] . '></a>';
    
    // tag cloud
    if ( $tag_cloud !== '' ) {
        $part1 .= '<div class="teachcourses_cloud">' . $tag_cloud . '</div>';
    }
    
    // search
    if ( $searchbox !== '' ) {
        $part1 .= '<div class="teachcourses_search_input">' . $searchbox . '</div>';
    }
    
    // filter
    if ( $filter !== '' ) {
        $part1 .= '<div class="teachcourses_filter">' . $filter . '</div>';
    }
    
    // show all button
    if ( $showall !== '' ) {
        $part1 .= '<p style="text-align:center">' . $showall . '</p>';
    }
    
    // Form
    $part1 = '<form name="tppublistform" method="get">' . $part1 . '</form>';
    
    // Return if we don't want so display the publications fo default
    if ( intval($atts['use_as_filter']) === 0 && $showall === '' ) {
        return '<div class="teachcourses_pub_list">' . $part1 . '</div>';
    }
    
    // For debugging only:
    // print_r($pagination_limits);
    // print_r($settings);
    // print_r($filter_parameter);
    
    // Return
    return '<div class="teachcourses_pub_list">' . $part1 . $part2 . '</div>';
}

/** 
 * Shortcode for displaying a publication list with tag cloud
 * This is just a preset for tc_publist_shortcode()
 * 
 * Parameters from $_GET: 
 *      $yr (INT)              Year 
 *      $type (STRING)         Publication type 
 *      $auth (INT)            Author ID
 *      $tgid (INT)            Tag ID
 *      $usr (INT)             User ID
 * 
 * @param array $atts
 * @return string
 * @since 0.10.0
*/
function tc_cloud_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user'                      => '',
        'tag'                       => '',
        'type'                      => '',
        'author'                    => '',
        'year'                      => '',
        'exclude'                   => '', 
        'include'                   => '',
        'include_editor_as_author'  => 1,
        'order'                     => 'date DESC',
        'headline'                  => 1, 
        'maxsize'                   => 35,
        'minsize'                   => 11,
        'tag_limit'                 => 30,
        'hide_tags'                 => '',
        'exclude_tags'              => '',
        'exclude_types'             => '',
        'image'                     => 'none',
        'image_size'                => 0,
        'image_link'                => 'none',
        'anchor'                    => 1,
        'author_name'               => 'initials',
        'editor_name'               => 'initials',
        'author_separator'          => ';',
        'editor_separator'          => ';',
        'style'                     => 'none',
        'template'                  => 'tc_template_2021',
        'title_ref'                 => 'links',
        'link_style'                => 'inline',
        'date_format'               => 'd.m.Y',
        'pagination'                => 1,
        'entries_per_page'          => 50,
        'sort_list'                 => '',
        'show_tags_as'              => 'cloud',
        'show_author_filter'        => 1,
        'show_in_author_filter'     => '',
        'show_type_filter'          => 1,
        'show_user_filter'          => 1,
        'show_search_filter'        => 0,
        'show_year_filter'          => 1, 
        'show_bibtex'               => 1,
        'container_suffix'          => '',
        'show_altmetric_donut'      => 0,
        'show_altmetric_entry'      => 0,
        'use_jumpmenu'              => 1,
        'use_as_filter'             => 1,
        'filter_class'              => 'default'
    ), $atts);
   
    return tc_publist_shortcode($atts);

}

/** 
 * Shortcode for displaying a publication list without filters
 * This is just a preset for tc_publist_shortcode()
 * 
 * @param array $atts
 * @return string
 * @since 0.12.0
*/
function tc_list_shortcode($atts){
    $atts = shortcode_atts(array(
       'user'                       => '',
       'tag'                        => '',
       'type'                       => '',
       'author'                     => '',
       'year'                       => '',
       'exclude'                    => '',
       'include'                    => '',
       'include_editor_as_author'   => 1,
       'exclude_tags'               => '',
       'exclude_types'              => '',
       'order'                      => 'date DESC',
       'headline'                   => 1,
       'image'                      => 'none',
       'image_size'                 => 0,
       'image_link'                 => 'none',
       'anchor'                     => 1,
       'author_name'                => 'initials',
       'editor_name'                => 'initials',
       'author_separator'           => ';',
       'editor_separator'           => ';',
       'style'                      => 'none',
       'template'                   => 'tc_template_2021',
       'title_ref'                  => 'links',
       'link_style'                 => 'inline',
       'date_format'                => 'd.m.Y',
       'pagination'                 => 1,
       'entries_per_page'           => 50,
       'sort_list'                  => '',
       'show_bibtex'                => 1,
       'show_type_filter'           => 0,
       'show_author_filter'         => 0,
       'show_in_author_filter'      => '',
       'show_search_filter'         => 0,
       'show_user_filter'           => 0, 
       'show_year_filter'           => 0, 
       'show_tags_as'               => 'none',
       'container_suffix'           => '',
       'show_altmetric_donut'       => 0,
       'show_altmetric_entry'       => 0,
       'use_jumpmenu'               => 1,
       'use_as_filter'              => 1,
       'filter_class'               => 'default'
    ), $atts);

    return tc_publist_shortcode($atts);
}

/**
 * Shortcode for frontend search function for publications
 * This is just a preset for tc_publist_shortcode()
 * 
 * @param array $atts
 * @return string
 * @since 4.0.0
 */
function tc_search_shortcode ($atts) {
    $atts = shortcode_atts(array(
       'user'                       => '',
       'tag'                        => '',
       'type'                       => '',
       'author'                     => '',
       'year'                       => '',
       'exclude'                    => '',
       'include'                    => '',
       'include_editor_as_author'   => 1,
       'order'                      => 'date DESC',
       'headline'                   => 0,
       'exclude_tags'               => '',
       'exclude_types'              => '',
       'image'                      => 'none',
       'image_size'                 => 0,
       'image_link'                 => 'none',
       'anchor'                     => 0,
       'author_name'                => 'initials',
       'editor_name'                => 'initials',
       'author_separator'           => ';',
       'editor_separator'           => ';',
       'style'                      => 'numbered',
       'template'                   => 'tc_template_2021',
       'title_ref'                  => 'links',
       'link_style'                 => 'inline',
       'date_format'                => 'd.m.Y',
       'pagination'                 => 1,
       'entries_per_page'           => 20,
       'sort_list'                  => '',
       'show_bibtex'                => 1,
       'show_tags_as'               => 'none',
       'show_author_filter'         => 0,
       'show_in_author_filter'      => '',
       'show_type_filter'           => 0,
       'show_user_filter'           => 0,
       'show_search_filter'         => 1,
       'show_year_filter'           => 0,
       'container_suffix'           => '',
       'show_altmetric_donut'       => 0,
       'show_altmetric_entry'       => 0,
       'use_jumpmenu'               => 0,
       'use_as_filter'              => 1,
       'filter_class'               => 'block'
    ), $atts); 
    
    return tc_publist_shortcode($atts);
}

/** 
 * Private Post shortcode  
 * 
 * @param array $atts  {
 *      @type int id        The id of the course
 * }
 * @param string $content   The content you want to display
 * @return string
 * @since 2.0.0
*/
function tc_post_shortcode ($atts, $content) {
    $param = shortcode_atts(array('id' => 0), $atts);
    $id = intval($param['id']);
    return $content;
}


/**
 * Shortcode for displaying a publication list with tag cloud
 * This is just a preset for tc_publist_shortcode()
 * 
 * Parameters from $_GET: 
 *      $yr (INT)              Year 
 *      $type (STRING)         Publication type 
 *      $auth (INT)            Author ID
 *      $tgid (INT)            Tag ID
 *      $usr (INT)             User ID
 * 
 * @param array $atts
 * @return string
 * @since 0.0.1
*/
function tc_course_list_shortcode ($atts) {
    
    return tc_Shortcodes::tc_courselist_shortcode($atts);
}
