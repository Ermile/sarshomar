<?php
namespace content_a\question\settings;


class model
{
	public static function post()
	{
		$post            = [];
		$post['require'] = \dash\request::post('require');
		$post['maxchar'] = \dash\request::post('maxchar');
		$post['poll_id'] = \dash\request::get('id');

		$result = \lib\app\question::edit($post, \dash\request::get('questionid'));

		if(\dash\engine\process::status())
		{
			\dash\redirect::pwd();
		}
	}
}
?>