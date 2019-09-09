<?php
namespace content_a\question\general;


class model
{
	public static $questionid = null;

	public static function post()
	{
		if(!\dash\request::get('questionid'))
		{
			if(!\dash\request::get('type'))
			{
				\dash\notif::error(T_("Please select one type"));
				return false;
			}

			$post              = [];
			$post['type']      = \dash\request::get('type');
			$post['survey_id'] = \dash\request::get('id');

			$result = \lib\app\question::add($post);

			if(\dash\engine\process::status())
			{
				if(isset($result['id']))
				{
					self::$questionid = $result['id'];
				}
				else
				{
					\dash\notif::error(T_("We can not add your question"));
					return false;
				}
			}
		}
		else
		{
			self::$questionid = \dash\request::get('questionid');
		}


		if(\dash\request::post('formType') === 'title')
		{
			return self::title();
		}
		elseif(\dash\request::post('formType') === 'question')
		{
			return self::choice();
		}
		else
		{
			return self::setting();
		}

	}


	public static function redirect_to()
	{
		if(\dash\engine\process::status())
		{
			\dash\redirect::to(\dash\url::this(). '/general?id='. \dash\request::get('id'). '&questionid='. self::$questionid);
		}
	}


	public static function title()
	{
		$post              = [];
		$post['title']     = \dash\request::post('title');
		$post['desc']      = \dash\request::post('desc');
		$post['password']  = \dash\request::post('password');
		$post['setpassword']    = \dash\url::child() === 'general' ? 1 : null;
		$post['address']   = \dash\request::post('address');
		$post['survey_id'] = \dash\request::get('id');

		$file = \dash\app\file::upload_quick('media');

		if($file === false)
		{
			return false;
		}

		if($file)
		{
			$post['media']['file'] = $file;
		}

		$result = \lib\app\question::edit($post, self::$questionid);

		self::redirect_to();
	}

	public static function setting()
	{
		$post                   = [];
		$post['step']           = \dash\request::post('step');
		$post['require']        = \dash\request::post('require');
		$post['default']        = \dash\request::post('default');
		$post['label1']         = \dash\request::post('label1');
		$post['label2']         = \dash\request::post('label2');
		$post['label3']         = \dash\request::post('label3');
		$post['survey_id']      = \dash\request::get('id');
		$post['ratetype']       = \dash\request::post('ratetype');
		$post['choicehelp']     = \dash\request::post('choicehelp');
		$post['choice_sort']    = \dash\request::post('choice_sort');
		$post['choiceinline']   = \dash\request::post('choiceinline');
		$post['min']            = \dash\request::post('min');
		$post['max']            = \dash\request::post('max');
		$post['placeholder']    = \dash\request::post('placeholder');
		$post['change_setting'] = true;

		$result = \lib\app\question::edit($post, self::$questionid);

		self::redirect_to();
	}

	public static function choice()
	{
		if(\dash\request::post('action') === 'remove')
		{
			$post['survey_id']       = \dash\request::get('id');
			$post['choice_key']    = \dash\request::post('key');
			$post['remove_choice'] = true;
			$result = \lib\app\question::edit($post, self::$questionid);
		}
		else
		{
			$post                = [];
			$post['survey_id']     = \dash\request::get('id');
			$post['choicetitle'] = \dash\request::post('choicetitle');
			$post['add_choice']  = true;

			$file = \dash\app\file::upload_quick('media');

			if($file === false)
			{
				return false;
			}

			if($file)
			{
				$post['choicefile'] = $file;
			}


			$result = \lib\app\question::edit($post, self::$questionid);
		}

		self::redirect_to();
	}
}
?>
