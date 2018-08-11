<?php
namespace lib\db;


class surveys
{

	public static function plus_field()
	{
		return \dash\db\config::public_plus_field('surveys', ...func_get_args());
	}


	public static function insert()
	{
		\dash\db\config::public_insert('surveys', ...func_get_args());
		return \dash\db::insert_id();
	}


	public static function update()
	{
		return \dash\db\config::public_update('surveys', ...func_get_args());
	}


	public static function get()
	{
		return \dash\db\config::public_get('surveys', ...func_get_args());
	}


	public static function search($_string = null, $_option = [])
	{
		$default_option =
		[
			'search_field' => " (title LIKE '%__string__%' ) ",
		];

		$_option = array_merge($default_option, $_option);
		return \dash\db\config::public_search('surveys', $_string, $_option);
	}


	public static function get_count()
	{
		return \dash\db\config::public_get_count('surveys', ...func_get_args());
	}

}
?>