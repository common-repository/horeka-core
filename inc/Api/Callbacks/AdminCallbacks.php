<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Api\Callbacks;

use HorekaCore\Base\BaseController;

class AdminCallbacks extends BaseController
{

	public function adminDashboard()
	{
		return require_once( "$this->plugin_path/templates/admin.php" );
	}

	public function adminDeliveryMethods()
	{
		return require_once( "$this->plugin_path/templates/delivery-methods.php" );
	}

	public function adminDistancesManager()
	{
		return require_once( "$this->plugin_path/templates/distance-manager.php" );
	}

	public function adminAreasImporter()
	{
		return require_once( "$this->plugin_path/templates/areas-importer.php" );
	}

	public function adminDeliveryPoints()
	{
		return require_once( "$this->plugin_path/templates/delivery-points.php" );
	}
	
}