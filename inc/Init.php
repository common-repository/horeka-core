<?php
/**
 * @package  HorekaCore
 */
namespace HorekaCore;

final class Init
{
	/**
	 * Store all the classes inside an array
	 * @return array Full list of classes
	 */
	public static function get_services() 
	{
		return array (
			Pages\Dashboard::class,
			Base\Enqueue::class,
			Base\Dequeue::class,
			Base\Functions::class,
			Base\Templates::class,
			Base\DeliveryMethodsController::class,
			Base\DistanceManagerController::class,
			Base\DeliveryPointsController::class,
			Base\AreasImporterController::class,
			Base\CronController::class,
			Base\RegisterController::class,
			Woocommerce\Category::class,
			Woocommerce\Tag::class,
			Woocommerce\CheckoutFields::class,
			Woocommerce\Functions::class,
			Woocommerce\LightFunctions::class,
			Woocommerce\Mobilpay::class,
			Woocommerce\CheckoutV2::class,
			Woocommerce\ParentCategoryRestriction::class,
			Woocommerce\CategoryDeliveryTime::class,
			Api\Actions::class,
			Woocommerce\ProductActions::class,
			Woocommerce\CompanyDiscount::class,
			Woocommerce\CustomPickupPoints::class
		);
	}

	/**
	 * Loop through the classes, initialize them, 
	 * and call the register() method if it exists
	 * @return
	 */
	public static function register_services() 
	{
		foreach ( self::get_services() as $class ) {
			$service = self::instantiate( $class );
			if ( method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}
	}

	/**
	 * Initialize the class
	 * @param  class $class    class from the services array
	 * @return class instance  new instance of the class
	 */
	private static function instantiate( $class )
	{
		$service = new $class();

		return $service;
	}
}