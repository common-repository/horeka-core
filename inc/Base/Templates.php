<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

use HorekaCore\Base\BaseController;

/**
* 
*/
class Templates extends BaseController
{
    
    public $templates = array();

    public function register() 
	{   
        $this->templates = array(
            'template-add-to-cart.php' => 'RPD - Add to cart',
            'template-fake-checkout-wrapper.php' => 'RPD - Fake Checkout Wrapper',
            'template-fake-checkout.php' => 'RPD - Fake Checkout',
            'template-terms.php' => 'RPD - Terms',
            'template-update-amount.php' => 'RPD - Update Amount',
            'template-update-cart.php' => 'RPD - Update Cart',
            'template-tracking-link.php' => 'RPD - Tracking Page',
            'template-blank-page.php' => 'RPD - White Page',
            'template-activate-account.php' => 'RPD - Activate account',
        );

        add_filter( 'theme_page_templates', array( $this, 'insertPluginTemplates' ) );
        add_filter( 'page_template', array( $this, 'checkTemplateRedirect' ) );        
    }

    public function insertPluginTemplates( $templates )
    {
        if( !empty( $this->templates ) ) {
            $templates = array_merge( $templates, $this->templates );
        }

        return $templates;
    }

    public function checkTemplateRedirect( $template ) 
    {
        global $post;

        if( !isset($this->templates[get_post_meta( $post->ID, '_wp_page_template', true )] ) ) {
            return $template;
        }

        $file = $this->plugin_front_templates_path . get_post_meta( $post->ID, '_wp_page_template', true );

        if( file_exists( $file ) ) {
            return $file;
        } else { 
            echo $file; 
        }

        return $template;
    }

}