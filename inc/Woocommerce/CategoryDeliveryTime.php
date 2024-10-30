<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use HorekaCore\Base\BaseController;

/**
* 
*/
class CategoryDeliveryTime extends BaseController
{

    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }
        
        add_action( 'product_cat_edit_form_fields', array( $this, 'displayCategoryDeliveryTimeWithMeta' ) );
        add_action( 'edited_product_cat', array( $this, 'saveCategoryMeta' ) );
        add_action( 'create_product_cat', array( $this, 'saveCategoryMeta' ) );
        add_action( 'admin_footer', array( $this, 'multiDatesPickerScript' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ), 99 );
    }

    public function displayCategoryDeliveryTimeWithMeta( $term )
    {
        // We apply the logic only for the parent categories
        if( $term->parent != 0 ) {
            return;
        }
        
        $term_meta = get_term_meta($term->term_id);

        echo '<tr class="form-field" id="category_delivery_time">
                <th scope="row" valign="top">
                    <label for="category_delivery_time_select">' . esc_html__('Delivery time', 'rpd-restaurant-solution') . '</label>
                </th>
                <td>
                    <select name="category_delivery_time_select" id="category_delivery_time_select" value="">
                        <option value="0" ' . sanitize_text_field( $term_meta['category_delivery_time'][0] == '0' ? 'selected' : '' ) . ' >' . esc_html__('Default', 'rpd-restaurant-solution') . '</option>
                        <option value="1" ' . sanitize_text_field( $term_meta['category_delivery_time'][0] == '1' ? 'selected' : '' ) . ' >' . esc_html__('Next day', 'rpd-restaurant-solution') . '</option>
                        <option value="2" ' . sanitize_text_field( $term_meta['category_delivery_time'][0] == '2' ? 'selected' : '' ) . ' >' . esc_html__('Daily', 'rpd-restaurant-solution') . '</option>
                        <option value="3" ' . sanitize_text_field( $term_meta['category_delivery_time'][0] == '3' ? 'selected' : '' ) . ' >' . esc_html__('Custom dates', 'rpd-restaurant-solution') . '</option>
                        <option value="4" ' . sanitize_text_field( $term_meta['category_delivery_time'][0] == '4' ? 'selected' : '' ) . ' >' . esc_html__('Exclude today', 'rpd-restaurant-solution') . '</option>
                    </select>
                </td>
            </tr>';

            echo '<tr class="form-field" id="category_delivery_time_custom_dates">
                <th scope="row" valign="top">
                    <label for="category_delivery_time">' . esc_html__('Select all the custom dates', 'rpd-restaurant-solution') . '</label>
                </th>
                <td>
                    <input autocomplete="off" type="text" id="custom_dates" name="custom_dates" value="' . sanitize_text_field( $term_meta['custom_dates'][0] ? $term_meta['custom_dates'][0] : '' ) . '">
                </td>
            </tr>';

            echo '<tr class="form-field" id="category_delivery_time_next_day">
                <th scope="row" valign="top">
                    <label for="category_delivery_time">' . esc_html__('Time slot', 'rpd-restaurant-solution') . '</label>
                </th>
                <td>
                    <input autocomplete="off" type="text" id="start_hour" name="start_hour" value="' . sanitize_text_field( $term_meta['start_hour'][0] ? $term_meta['start_hour'][0] : '' ) . '">
                    <input autocomplete="off" type="text" id="end_hour" name="end_hour" value="' . sanitize_text_field( $term_meta['end_hour'][0] ? $term_meta['end_hour'][0] : '' ) . '">
                    <input autocomplete="off" type="number" id="time_slot" name="time_slot" value="' . sanitize_text_field( $term_meta['time_slot'][0] ? $term_meta['time_slot'][0] : '' ) . '" placeholder="' . __('Lapse', 'rpd-restaurant-solution') . '">
                </td>
            </tr>';
            echo '<tr class="form-field" id="category_delivery_time_intervals">
                <th scope="row" valign="top">
                    <label for="custom_interval_time">' . esc_html__('Custom interval time', 'rpd-restaurant-solution') . '</label>
                </th>
                <td>
                    <input autocomplete="off" type="checkbox" id="custom_interval_time" name="custom_interval_time" ' . sanitize_text_field( $term_meta['custom_interval_time'][0] == 'on'  ? 'checked' : '' ) . '>
                </td>
            </tr>';
    }

    public function saveCategoryMeta( $term_id )
    {   
        if ( isset( $_POST['category_delivery_time_select'] ) ) {
            update_term_meta($term_id, 'category_delivery_time', sanitize_text_field($_POST['category_delivery_time_select']));
        } else {
            update_term_meta($term_id, 'category_delivery_time', '0');
        }

        if ( isset( $_POST['start_hour'] ) ) {
            update_term_meta($term_id, 'start_hour', sanitize_text_field($_POST['start_hour']));
        } else {
            update_term_meta($term_id, 'start_hour', '');
        }

        if ( isset( $_POST['end_hour'] ) ) {
            update_term_meta($term_id, 'end_hour', sanitize_text_field($_POST['end_hour']));
        } else {
            update_term_meta($term_id, 'end_hour', '');
        }

        if ( isset( $_POST['time_slot'] ) ) {
            update_term_meta($term_id, 'time_slot', sanitize_text_field($_POST['time_slot']));
        } else {
            update_term_meta($term_id, 'time_slot', '');
        }

        if ( isset( $_POST['custom_dates'] ) ) {
            update_term_meta($term_id, 'custom_dates', sanitize_text_field(str_replace(' ', '', $_POST['custom_dates'])));
        } else {
            update_term_meta($term_id, 'custom_dates', '');
        }

        if ( isset( $_POST['custom_interval_time'] ) ) {
            update_term_meta($term_id, 'custom_interval_time', sanitize_text_field($_POST['custom_interval_time']));
        } else {
            update_term_meta($term_id, 'custom_interval_time', '');
        }
    }

    public function enqueueScripts()
    {
        wp_enqueue_style( 'jquery-timepicker-css', $this->plugin_url . 'assets/css/woocommerce/jquery.timepicker.min.css' );
        wp_enqueue_script( 'jquery-timepicker-js', $this->plugin_url . 'assets/js/woocommerce/jquery.timepicker.min.js' );
        wp_enqueue_style( 'jquery-multidatespicker-css', $this->plugin_url . 'assets/css/woocommerce/jquery-ui.multidatespicker.css' );
        wp_enqueue_script( 'jquery-multidatespicker-js', $this->plugin_url . 'assets/js/woocommerce/jquery-ui.multidatespicker.js' );
        wp_enqueue_script( 'timepicker-admin-code', $this->plugin_url . 'assets/js/woocommerce/timepicker-admin-code.min.js' );
    }

    public function multiDatesPickerScript()
    {
        if( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'product_cat' && isset( $_GET['tag_ID'] ) && (int) $_GET['tag_ID'] > 0 ) {
            $dates = array();
            $tag_ID = $_GET['tag_ID'];
            $term_meta = get_term_meta($tag_ID);
            if( !empty( $term_meta['custom_dates'] ) ) {
                if( $term_meta['custom_dates'][0] != "" ) {
                    $dates = explode( ',', $term_meta['custom_dates'][0] );
                }
            } 
            ?>
                <script>
                    jQuery(document).ready(function( $ ) {
                        $('#custom_dates').multiDatesPicker({
                            dateFormat: 'dd/mm/yy',
                            minDate: 0,
	                        <?php echo !empty($dates) && $dates != NULL ? 'addDates: ' . json_encode($dates) : ''; ?>
                        });
                    });                    
                </script>
            <?php 
        }
    }

}