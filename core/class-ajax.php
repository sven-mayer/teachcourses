<?php
/**
 * This file contains all functions which are used in ajax calls
 * @package teachcourses\core\ajax
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/**
 * This class contains all functions which are used in ajax calls
 * @package teachcourses\core\ajax
 * @since 5.0.0
 */
class tc_Ajax {
    /**
     * Adds a document headline
     * @param string $doc_name      The name of the document
     * @param int $course_id        The course ID
     * @since 5.0.0
     * @access public
     */
    public static function add_document_headline( $doc_name, $course_id ) {
        $file_id = tc_Documents::add_document($doc_name, '', 0, $course_id);
        echo $file_id;
    }
    
    /**
     * Changes the name of a document
     * @param int $doc_id          The document ID
     * @param string $doc_name     The name of the document
     * @since 5.0.0
     * @access public
     */
    public static function change_document_name( $doc_id, $doc_name ) {
        tc_Documents::change_document_name($doc_id, $doc_name);
        echo $doc_name;
    }
    
    /**
     * Deletes a document
     * @param int $doc_id           The document ID
     * @return boolean
     * @since 5.0.0
     * @access public
     */
    public static function delete_document( $doc_id ) {
        $doc_id = intval($doc_id);
        $data = tc_Documents::get_document($doc_id);
        if ( $data['path'] !== '' ) {
            $uploads = wp_upload_dir();
            $test = @ unlink( $uploads['basedir'] . $data['path'] );
            //echo $uploads['basedir'] . $data['path'];
            if ( $test === false ) {
                echo 'false';
                return false;
            }
        }
        tc_Documents::delete_document($doc_id);
        echo 'true';
        return true;
    }
    
    /**
     * Gets the artefact info screen. The info screen is used in the assessment menu of teachcourses.
     * @param int $artefact_id      The artefact ID
     * @since 5.0.0
     * @access public
     */
    public static function get_artefact_screen($artefact_id) {
        $artefact = tc_Artefacts::get_artefact($artefact_id);
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachcourses - Assessment details</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="tc_artefact_id" type="hidden" value="' . $artefact_id . '"/>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<td>' . __('Title','teachcourses') . '</td>';
        echo '<td><input name="tc_artefact_title" cols="50" value="' . stripslashes($artefact['title']) . '"/></td>';
        echo '</tr>';
        echo '</table>';
        echo '<p><input name="tc_save_artefact" type="submit" class="button-primary" value="' . __('Save') . '"/> <input name="tc_delete_artefact" type="submit" class="button-secondary" value="' . __('Delete','teachcourses') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets a list of publications of a single author. This function is used for teachcourses/admin/show_authors.php
     * @param int $author_id        The authur ID
     * @since 5.0.0
     * @access public
     */
    public static function get_author_publications( $author_id ) {
        $author_id = intval($author_id);
        $pubs = tc_Authors::get_related_publications($author_id, ARRAY_A);
        echo '<ol>';
        foreach ( $pubs as $pub) {
            echo '<li style="padding-left:10px;">';
            echo '<a target="_blank" title="' . __('Edit publication','teachcourses') .'" href="admin.php?page=teachcourses/addpublications.php&pub_id=' . $pub['pub_id'] . '">' . tc_HTML::prepare_title($pub['title'], 'decode') . '</a>, ' . stripslashes($pub['type']) . ', ' . $pub['year'];
            if ( $pub['is_author'] == 1 ) {
                echo ' (' . __('as author','teachcourses') . ')';
            }
            if ( $pub['is_editor'] == 1 ) {
                echo ' (' . __('as editor','teachcourses') . ')';
            }
            echo '</li>';
        }
        echo '</ol>';
    }

    /**
     * Gets the name of a document
     * @param int $doc_id       The ID of the document
     * @since 5.0.0
     * @access public
     */
    public static function get_document_name( $doc_id ) {
        $doc_id = intval($doc_id);
        $data = tc_Documents::get_document($doc_id);
        echo stripslashes($data['name']);
    }
    
    /**
     * Gets the meta field screen for the settings panel
     * @param int $meta_field_id        The meta field ID
     * @since 6.0.0
     * @access public
     */
    public static function get_meta_field_screen ( $meta_field_id ) {
        if ( $meta_field_id === 0 ) {
            $data = array(
                'name' => '',
                'title' => '',
                'type' => '',
                'min' => '',
                'max' => '',
                'step' => '',
                'visibility' => '',
                'required'
            );
        }
        else {
            $field = tc_Options::get_option_by_id($meta_field_id);
            $data = tc_DB_Helpers::extract_column_data($field['value']);
        }
        
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachcourses - Meta Field Screen</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="field_edit" type="hidden" value="' . $meta_field_id . '">';
        echo '<table class="form-table">';
        
        // field name
        if ( $meta_field_id === 0 ) {
            echo '<tr>';
            echo '<td><label for="field_name">' . __('Field name','teachcourses') . '</label></td>';
            echo '<td><input name="field_name" type="text" id="field_name" size="30" title="' . __('Allowed chars','teachcourses') . ': A-Z,a-z,0-9,_" value="' . $data['name'] . '"/></td>';
            echo '</tr>';
        }
        else {
            echo '<input name="field_name" id="field_name" type="hidden" value="' . $data['name'] . '">';
        }
        
        // label
        echo '<tr>';
        echo '<td><label for="field_label">' . __('Label','teachcourses') . '</label></td>';
        echo '<td><input name="field_label" type="text" id="field_label" size="30" title="' . __('The visible name of the field','teachcourses') . '" value="' . $data['title'] . '" /></td>';
        echo '</tr>';
        
        // field type
        $field_types = array('TEXT', 'TEXTAREA', 'INT', 'DATE', 'SELECT', 'CHECKBOX', 'RADIO');
        echo '<tr>';
        echo '<td><label for="field_type">' . __('Field type','teachcourses') . '</label></td>';
        echo '<td>';
        echo '<select name="field_type" id="field_type">';
        foreach ( $field_types as $type ) {
            $selected = ( $data['type'] === $type ) ? 'selected="selected"' : '';
            echo '<option value="' . $type . '" ' . $selected . '>' . $type . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        // min
        $min = ( $data['min'] === 'false' ) ? '' : intval($min);
        echo '<tr>';
        echo '<td><label for="number_min">' . __('Min','teachcourses') . ' (' . __('Only for INT fields','teachcourses') . ')</label></td>';
        echo '<td><input name="number_min" id="number_min" type="number" size="10" value="' . $min . '"/></td>';
        echo '</tr>';
        
        // max
        $max = ( $data['max'] === 'false' ) ? '' : intval($max);
        echo '<tr>';
        echo '<td><label for="number_max">' . __('Max','teachcourses') . ' (' . __('Only for INT fields','teachcourses') . ')</label></td>';
        echo '<td><input name="number_max" id="number_max" type="number" size="10" value="' . $max . '"/></td>';
        echo '</tr>';
        
        // step
        $step = ( $data['step'] === 'false' ) ? '' : intval($step);
        echo '<tr>';
        echo '<td><label for="number_step">' . __('Step','teachcourses') . ' (' . __('Only for INT fields','teachcourses') . ')</label></td>';
        echo '<td><input name="number_step" id="number_step" type="text" size="10" value="' . $step . '"/></td>';
        echo '</tr>';
        
        // visibility
        echo '<tr>';
        echo '<td><label for="visibility">' . __('Visibility','teachcourses') . '</label></td>';
        echo '<td>';
        echo '<select name="visibility" id="visibility">';
        
        // normal
        $vis_normal = ( $data['visibility'] === 'normal' ) ? 'selected="selected"' : '';
        echo '<option value="normal" ' . $vis_normal . '>' . __('Normal','teachcourses') . '</option>';

        // admin
        $vis_admin = ( $data['visibility'] === 'admin' ) ? 'selected="selected"' : '';
        echo '<option value="admin" ' . $vis_admin . '>' . __('Admin','teachcourses') . '</option>';

        // hidden
        $vis_hidden = ( $data['visibility'] === 'hidden' ) ? 'selected="selected"' : '';
        echo '<option value="hidden" ' . $vis_hidden . '>' . __('Hidden','teachcourses') . '</option>';
        
        echo '</select>';
        echo '</td>';
        echo '</tr>'; 
        
        // required
        $req = ( $data['required'] === 'true' ) ? 'checked="checked"' : '';
        echo '<tr>';
        echo '<td colspan="2"><input type="checkbox" name="is_required" id="is_required" ' . $req . '/> <label for="is_required">' . __('Required field','teachcourses') . '</label></td>';
        echo '</tr>';
           
        echo '</table>';
        echo '<p><input type="submit" name="add_field" class="button-primary" value="' . __('Save','teachcourses') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets the url of a mimetype image
     * @param string $filename      The filename or the url
     * @since 5.0.0
     * @access public
     */
    public static function get_mimetype_image( $filename ) {
        echo tc_Icons::get_class($filename);
    }

    /**
     * Saves the order of a document list
     * @param array $array      A numeric array which represents the sort order of course documents
     * @since 5.0.0
     * @access public
     */
    public static function set_sort_order( $array ) {
        $i = 0;
        foreach ($array as $value) {
            tc_Documents::set_sort($value, $i);
            $i++;
        }
    }
}
