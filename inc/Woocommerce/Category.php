<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Woocommerce;

use HorekaCore\Base\BaseController;

/**
* 
*/
class Category extends BaseController
{

    public function register() 
    {
        // If this option is active we use Horeka Core plugin only for importing orders to our API
        if( $this->activated( 'only_import_orders' ) ) {
            return;
        }
        
        if( !$this->activated( 'category_discount' ) ) {
            return;
        }
        
        add_action( 'product_cat_add_form_fields', array( $this, 'displayProductDescription' ) );
        add_action( 'product_cat_edit_form_fields', array( $this, 'displayProductDescriptionWithMeta' ) );
        add_action( 'product_cat_add_form_fields', array( $this, 'displayCategoryDiscountOptions' ) );
        add_action( 'product_cat_edit_form_fields', array( $this, 'displayCategoryDiscountOptionsWithMeta' ) );
        add_action( 'edited_product_cat', array( $this, 'saveCategoryMeta' ) );
        add_action( 'create_product_cat', array( $this, 'saveCategoryMeta' ) );
    }

    public function displayProductDescription()
    {
        echo '<div class="form-field">
                <label for="display_product_description">' . esc_html__('Display products description', 'rpd-restaurant-solution') . '</label>
                <input type="checkbox" name="display_product_description" id="pos_cat_id" value="" >
              </div>';
    }

    public function displayProductDescriptionWithMeta( $term )
    {
        $term_meta = get_term_meta($term->term_id);

        echo '<tr class="form-field">
                <th scope="row" valign="top">
                    <label for="display_product_description">' . esc_html__('Display products description', 'rpd-restaurant-solution') . '</label>
                </th>
                <td>
                    <input type="checkbox" name="display_product_description" id="display_product_description"' . sanitize_text_field( $term_meta['display_product_description'][0] ? 'checked' : '' ) . '>
                </td>
              </tr>';
    }

    public function displayCategoryDiscountOptions()
    {
        echo '<div class="form-field">
                <label for="category_discount">' . esc_html__('Category discount', 'rpd-restaurant-solution') . '</label>
                <select name="category_discount" id="category_discount" value="">
                    <option value="1">' . esc_html__('No discount', 'rpd-restaurant-solution') . '</option>
                    <option value="2">' . esc_html__('1 + 1', 'rpd-restaurant-solution') . '</option>
                    <option value="3">' . esc_html__('2 + 1', 'rpd-restaurant-solution') . '</option>
                    <option value="4">' . esc_html__('3 + 1', 'rpd-restaurant-solution') . '</option>
                </select>
            </div>';
    }

    public function displayCategoryDiscountOptionsWithMeta( $term )
    {
        $term_meta = get_term_meta($term->term_id);

        echo '<tr class="form-field">
                <th scope="row" valign="top">
                    <label for="category_discount">' . esc_html__('Category discount', 'rpd-restaurant-solution') . '</label>
                </th>
                <td>
                    <select name="category_discount" id="category_discount" value="">
                        <option value="1" ' . sanitize_text_field( $term_meta['category_discount'][0] == '1' ? 'selected' : '' ) . ' >' . esc_html__('No discount', 'rpd-restaurant-solution') . '</option>
                        <option value="2" ' . sanitize_text_field( $term_meta['category_discount'][0] == '2' ? 'selected' : '' ) . ' >' . esc_html__('1 + 1', 'rpd-restaurant-solution') . '</option>
                        <option value="3" ' . sanitize_text_field( $term_meta['category_discount'][0] == '3' ? 'selected' : '' ) . ' >' . esc_html__('2 + 1', 'rpd-restaurant-solution') . '</option>
                        <option value="4" ' . sanitize_text_field( $term_meta['category_discount'][0] == '4' ? 'selected' : '' ) . ' >' . esc_html__('3 + 1', 'rpd-restaurant-solution') . '</option>
                    </select>
                </td>
            </tr>';
    }

    public function saveCategoryMeta( $term_id )
    {   
        if ( isset( $_POST['display_product_description'] ) ) {
            update_term_meta($term_id, 'display_product_description', sanitize_text_field($_POST['display_product_description']));
        } else {
            update_term_meta($term_id, 'display_product_description', '');
        }
        
        if ( isset( $_POST['category_discount'] ) ) {
            update_term_meta($term_id, 'category_discount', sanitize_text_field($_POST['category_discount']));
        } else {
            update_term_meta($term_id, 'category_discount', '1');
        }
    }

}