<?php
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

class Deactivate
{
	public static function deactivate() 
	{
		flush_rewrite_rules();
	}
}