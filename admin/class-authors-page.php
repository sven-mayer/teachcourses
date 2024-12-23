<?php
/**
 * This class contains all functions for the authors page in the admin menu
 * @since 8.1
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */
class tc_Authors_Page {
    
    /**
     * Prints the page
     * @since 8.1
     */
    public static function init() {
        $action = '';
        if ( isset( $_GET['action1'] ) && $_GET['action1'] !== '' ) {
            $action = htmlspecialchars($_GET['action1']);
        }
        if ( isset( $_GET['action2'] ) && $_GET['action2'] !== '' ) {
            $action = htmlspecialchars($_GET['action2']);
        }
        if ( isset( $_GET['action'] ) && $_GET['action'] !== '' ) {
            $action = htmlspecialchars($_GET['action']);
        }
        tc_Authors_Page::get_page($action);
    }


    /**
     * This function executes all actions for this page
     * @param string $action        The current action
     * @param array $checkbox       The checkbox array
     * @param string $page          The current page 
     * @param string $search        The search string
     * @param int $curr_page        The current page in the page menu
     * @since 6.0.0
     * @access private
     */
    private static function actions ($action, $checkbox, $page, $search, $curr_page) {
        // Delete tags - part 1
        if ( $action === 'delete' ) {
            echo '<div class="teachcourses_message teachcourses_message_orange">
                <p class="teachcourses_message_headline">' . __('Do you want to delete the selected items?','teachcourses') . '</p>
                <p><input name="delete_ok" type="submit" class="button-secondary" value="' . __('Delete','teachcourses') . '"/>
                <a href="admin.php?page=' . $page . '&search=' . $search . '&amp;limit=' . $curr_page . '"> ' . __('Cancel','teachcourses') . '</a></p>
                </div>';
        }
        // delete tags - part 2
        if ( isset( $_GET['delete_ok'] ) ) {
            tc_Authors::delete_authors($checkbox);
            get_tc_message( __('Removing successful','teachcourses') );
        }
    }
    
    /**
     * This function prints the page
     * @param string $action
     * @since 6.0.0
     */
    public static function get_page ($action) {
        $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
        $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : array();
        $filter = isset( $_GET['filter'] ) ? htmlspecialchars($_GET['filter']) : '';
        $only_zero = ( $filter === 'only_zero' ) ? true : false;
        $page = 'teachcourses/authors.php';
        
        /** 
         * Screen options 
         */
        // items per page
        $user = get_current_user_id();
        $screen = get_current_screen();
        $screen_option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $screen_option, true);
        if ( empty ( $per_page) || $per_page < 1 ) {
            $per_page = $screen->get_option( 'per_page', 'default' );
        }
        // sorting
        $option = get_user_meta($user, 'tc_authors_sorting', true);
        $order = 'a.sort_name ASC, a.name ASC';
        
        if ( $option == 'firstname' ) {
            $order = 'a.name ASC, a.sort_name ASC';
        }

        // Handle limits
        $number_messages = $per_page;
        if (isset($_GET['limit'])) {
            $curr_page = intval($_GET['limit']) ;
            if ( $curr_page <= 0 ) {
                $curr_page = 1;
            }
            $entry_limit = ( $curr_page - 1 ) * $number_messages;
        }
        else {
            $entry_limit = 0;
            $curr_page = 1;
        }
        
        echo '<div class="wrap" style="max-width:900px;">';
        echo '<h2>' . __('Authors','teachcourses') . '</h2>';
        echo '<form id="form1" name="form1" method="get" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        echo '<input name="page" type="hidden" value="' . $page . '" />';

        // actions
        self::actions($action, $checkbox, $page, $search, $curr_page);

        // Searchbox
        echo '<div id="tc_searchbox">';
        if ($search != "") {
            echo '<a href="admin.php?page=teachcourses/authors.php" class="tc_search_cancel" title="' . __('Cancel the search','teachcourses') . '">X</a>';
        }
        echo '<input type="search" name="search" id="pub_search_field" value="' . stripslashes($search) . '"/>';
        echo '<input type="submit" name="button" id="button" value="' . __('Search','teachcourses') . '" class="button-secondary"/>';
        echo '</div>';
        // END Searchbox
        
        // Tablenav actions
        echo '<div class="tablenav" style="padding-bottom:5px;">';
        echo '<div class="alignleft actions">';
        echo '<select name="action1">';
        echo '<option value="">- ' . __('Bulk actions','teachcourses') . ' -</option>';
        echo '<option value="delete">' . __('Delete','teachcourses') . '</option>';
        echo '</select>';
        echo '<select name="filter">';
        echo '<option>- ' . __('Select filter','teachcourses') . ' -</option>';
        $selected = ( $only_zero === true ) ? 'selected="selected"' : '';
        echo '<option value="only_zero"' . $selected . '>' . __('Occurence = 0','teachcourses') . '</option>';
        echo '</select>';
        echo '<input name="OK" value="OK" type="submit" class="button-secondary"/>';
        echo '</div>';
        $test = tc_Authors::get_authors_occurence( array( 
                    'count'         => true, 
                    'search'        => $search, 
                    'only_zero'     => $only_zero
        ));
        $args = array(
                    'number_entries'    => $test,
                    'entries_per_page'  => $number_messages,
                    'current_page'      => $curr_page,
                    'entry_limit'       => $entry_limit,
                    'page_link'         => "admin.php?page=$page&amp;",
                    'link_attributes'   => "search=$search");
        echo tc_page_menu($args);
        echo '</div>';
        // END Tablenav actions

        // Main table
        echo '<table class="widefat">';
        echo '<thead id="tc_authors_table_header">';
        echo '<td class="check-column"><input name="tc_check_all" id="tc_check_all" type="checkbox" value="" /></td>';
        echo '<th>' . __('Name','teachcourses') . '</th>';
        echo '<th>' . __('ID','teachcourses') . '</th>';
        echo '<th>' . __('Number publications','teachcourses') . '</th>';
        echo '</thead>';
        echo '<tbody id="tc_authors_table_content">';
        if ( intval($test) === 0 ) {
            echo '<tr><td colspan="4"><strong>' . __('Sorry, no entries matched your criteria.','teachcourses') . '</strong></td></tr>';
            }
        else {
            
            $link = 'admin.php?page=' . $page . '&amp;search=' . $search . '&amp;limit=' . $curr_page . '&amp;action=delete&amp;filter=' . $filter;
            $results = tc_Authors::get_authors_occurence(array(
                    'search'        => $search,
                    'limit'         => $entry_limit . ',' . $number_messages,
                    'order'         => $order,
                    'only_zero'     => $only_zero,
            ));
            self::get_table($results, $action, $checkbox, $link);
        }
        echo '</tbody>';
        echo '</table>';
        // END Main Table

        // Tablenav actions
        echo '<div class="tablenav bottom">';
        echo '<div class="tablenav-pages" style="float:right;">'; 
        if ( $test > $number_messages ) {
            $args = array(
                        'number_entries'      => $test,
                        'entries_per_page'    => $number_messages,
                        'current_page'        => $curr_page,
                        'entry_limit'         => $entry_limit,
                        'page_link'           => "admin.php?page=$page&amp;",
                        'link_attributes'     => "search=$search",
                        'mode'                => 'bottom');
            echo tc_page_menu($args);
        } 
        else {
           if ( $test === 1 ) {
              echo $test . ' ' . __('entry','teachcourses');
           }
           else {
              echo $test . ' ' . __('entries','teachcourses');
           }
        }
        echo '</div>';
        echo '</div>';
        // END Tablenav actions

        echo '</form>';
        self::print_scripts();
        echo '</div>';
    }
    
    /**
     * Prints the authors table
     * @param array $results
     * @param string action
     * @param array $checkbox
     * @param string link
     * @since 6.0.0
     * @access private 
     */
    private static function get_table ($results, $action, $checkbox, $link) {
        $class_alternate = true;
        
        foreach ( $results as $row ) {
            // Alternate line style
            if ( $class_alternate === true ) {
                $tr_class = 'alternate';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            
            $checked = '';
            if ( $action === "delete") { 
                $checked = in_array($row['author_id'], $checkbox ) ? 'checked="checked"' : '';
            }
            
            tc_HTML::line('<tr class="' . $tr_class . '" id="resultbox_' . $row['author_id'] . '">');
            tc_HTML::line('<th class="check-column"><input name="checkbox[]" class="tc_checkbox" type="checkbox" ' . $checked . ' value="' . $row['author_id'] . '"></th>');
            tc_HTML::line('<td><a class="tc_show_pub_info" author_id="' . $row['author_id'] . '" title="' . __('Show publications','teachcourses') . '" style_class="' . $tr_class . '" style="cursor:pointer;"><b>' . tc_Bibtex::parse_author_simple($row['name']) . '</b></a>');
            
            // Row actions
            tc_HTML::line('<div class="tc_row_actions">');
            tc_HTML::line('<a class="tc_row_delete" href="' . $link . '&amp;checkbox%5B%5D=' . $row['author_id'] . '" title="' . __('Delete','teachcourses') . '">' . __('Delete', 'teachcourses') . '</a>');
            tc_HTML::line('</div>');
            // END Row actions
            
            tc_HTML::line('</td>');
            tc_HTML::line('<td>' . $row['author_id'] . '</td>');
            tc_HTML::line('<td>' . $row['count'] . '</td>');
            tc_HTML::line('<tr>');
        }
    }
    
    /**
     * Adds the screen options (items per page, authors_sorting)
     * @global string $tc_admin_show_authors_page
     * @since 8.1
     */
    public static function add_screen_options() {
        global $tc_admin_show_authors_page;
        $screen = get_current_screen();

        if( !is_object($screen) || $screen->id != $tc_admin_show_authors_page ) {
            return;
        }

        $args = array(
            'label' => __('Items per page', 'teachcourses'),
            'default' => 50,
            'option' => 'tc_authors_per_page'
        );
        add_screen_option( 'per_page', $args );

        $args = array(
            'default' => 'lastname',
            'option' => 'tc_authors_sorting'
        );
        add_screen_option( 'tc_authors_sorting', $args );
    }
    
    /**
     * Prints the custom screen options fo this page
     * @since 8.1
     */
    public static function print_screen_options() {
        $user = get_current_user_id();
        $option = get_user_meta($user, 'tc_authors_sorting', true);
        $value = ( $option ) ? $option : 'lastname';
        
        // Available options
        $options[] = array(
            'key'   => 'firstname',
            'label' => __('First name', 'teachcourses')
        );
        $options[] = array(
            'key'   => 'lastname',
            'label' => __('Last name', 'teachcourses')
        );
        
        $r = '<label for="tc_authors_sorting"><b>' . __('Sorting', 'teachcourses') . '</b></label><br/>';
        $r .= '<select name="tc_authors_sorting" id="tc_authors_sorting">';
        foreach ( $options as $row ) {
            $selected = ( $row['key'] == $value ) ? 'selected="selected"' : '';
            $r .= '<option value="' . $row['key'] . '" ' . $selected . '>' . $row['label'] . '</option>';
        }
        $r .= '</select>';
        return $r;
        
    }
    
    /**
     * Saves the custom screen options
     * @since 8.1
     */
    public static function save_screen_options () {
        $sorting = htmlspecialchars($_POST['tc_authors_sorting']);
        if ( $sorting === 'firstname' || $sorting === 'lastname' ) {
            update_user_meta(get_current_user_id(), 'tc_authors_sorting', $sorting);
        }
    }


    /**
     * Prints js scripts for the page
     * @since 6.0.0
     */
    private static function print_scripts () {
        ?>
        <script type="text/javascript" charset="utf-8">
            jQuery(document).ready(function($) {
                $(".tc_show_pub_info").click( function(){
                    var author_id = $(this).attr("author_id");
                    var tr_class = $(this).attr("style_class");
                    var tr = '#resultbox_' + author_id;
                    $.get(ajaxurl + "?action=teachcourses&author_id=" + author_id, 
                    function(text){
                        var ret;
                        var current = $(tr).next();
                        current.attr('class', tr_class);
                        current.attr('id', 'pub_info_' + author_id);
                        ret = ret + '<td id="pub_details_' + author_id + '" colspan="4" style="padding-left:40px;"><b><?php echo __('Publications','teachcourses') ;?></b><br />' + text + '</td>';
                        current.html(ret);
                        $('<a class="button-secondary" style="cursor:pointer;" onclick="javascript:teachcourses_del_node(' + "'#pub_info_" + author_id + "'" + ');">Close</a>').appendTo('#pub_details_' + author_id);

                    });
                });

            });
        </script>
        <?php
    }
}