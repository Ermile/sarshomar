<?php
namespace content\catalog;


class controller
{
	public static function routing()
	{
		$lastVersion = '10';
		$dlLink      = \dash\url::base().'/static/catalog/Sarshomar-Catalog-v'. $lastVersion. '-preview.pdf';

		\dash\redirect::to($dlLink);
	}
}
?>