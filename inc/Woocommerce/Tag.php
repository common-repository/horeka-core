<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use HorekaCore\Base\BaseController;

/**
* 
*/
class Tag extends BaseController
{

    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }
        
        if( !$this->activated( 'tag_discount' ) ) {
            return;
        }

        add_action( 'product_tag_add_form_fields', array( $this, 'displayTagDiscountOptions' ) );
        add_action( 'product_tag_edit_form_fields', array( $this, 'displayTagDiscountOptionsWithMeta' ) );
        add_action( 'edited_product_tag', array( $this, 'saveTagMeta' ) );
        add_action( 'create_product_tag', array( $this, 'saveTagMeta' ) );
    }

    public function displayTagDiscountOptions()
    {
        echo '<div class="form-field">
                <label for="tag_discount">' . esc_html__('Tag discount', 'rpd-restaurant-solution') . '</label>
                <select name="tag_discount" id="tag_discount" value="">
                    <option value="1">' . esc_html__('No discount', 'rpd-restaurant-solution') . '</option>
                    <option value="2">' . esc_html__('1 + 1', 'rpd-restaurant-solution') . '</option>
                    <option value="3">' . esc_html__('2 + 1', 'rpd-restaurant-solution') . '</option>
                    <option value="4">' . esc_html__('3 + 1', 'rpd-restaurant-solution') . '</option>
                </select>
            </div>';
    }

    public function displayTagDiscountOptionsWithMeta( $term )
    {
        $term_meta = get_term_meta($term->term_id);

        echo '<tr class="form-field">
                <th scope="row" valign="top">
                    <label for="tag_discount">' . esc_html__('Tag discount', 'rpd-restaurant-solution') . '</label>
                </th>
                <td>
                    <select name="tag_discount" id="tag_discount" value="">
                        <option value="1" ' . sanitize_text_field( $term_meta['tag_discount'][0] == '1' ? 'selected' : '' ) . ' >' . esc_html__('No discount', 'rpd-restaurant-solution') . '</option>
                        <option value="2" ' . sanitize_text_field( $term_meta['tag_discount'][0] == '2' ? 'selected' : '' ) . ' >' . esc_html__('1 + 1', 'rpd-restaurant-solution') . '</option>
                        <option value="3" ' . sanitize_text_field( $term_meta['tag_discount'][0] == '3' ? 'selected' : '' ) . ' >' . esc_html__('2 + 1', 'rpd-restaurant-solution') . '</option>
                        <option value="4" ' . sanitize_text_field( $term_meta['tag_discount'][0] == '4' ? 'selected' : '' ) . ' >' . esc_html__('3 + 1', 'rpd-restaurant-solution') . '</option>
                    </select>
                </td>
            </tr>';
    }

    public function saveTagMeta( $term_id )
    {   
        if ( isset( $_POST['tag_discount'] ) ) {
            update_term_meta($term_id, 'tag_discount', sanitize_text_field($_POST['tag_discount']));
        } else {
            update_term_meta($term_id, 'tag_discount', '1');
        }
    }

}