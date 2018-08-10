<?php
namespace lib\app;

/**
 * Class for question.
 */
class question
{

	use question\add;
	use question\edit;
	use question\datalist;
	use question\dashboard;
	use question\type;


	public static $raw_field =
	[
		'media',
		'setting',
		'choice',
	];


	public static function get($_id)
	{
		$id = \dash\coding::decode($_id);
		if(!$id)
		{
			\dash\notif::error(T_("Poll id not set"));
			return false;
		}


		$get = \lib\db\questions::get(['id' => $id, 'limit' => 1]);

		if(!$get)
		{
			\dash\notif::error(T_("Invalid question id"));
			return false;
		}

		$result = self::ready($get);

		return $result;
	}


	public static function block_poll($_poll_id)
	{
		$poll_id = \dash\coding::decode($_poll_id);
		if(!$poll_id)
		{
			\dash\notif::error(T_("Poll id not set"));
			return false;
		}

		$result = \lib\db\questions::get(['poll_id' => $poll_id]);

		if(is_array($result))
		{
			$result = array_map(['self', 'ready'], $result);
		}

		return $result;
	}


	/**
	 * check args
	 *
	 * @return     array|boolean  ( description_of_the_return_value )
	 */
	private static function check($_id = null)
	{
		$args            = [];

		$poll_id = \dash\app::request('poll_id');
		$poll_id = \dash\coding::decode($poll_id);
		if(!$poll_id)
		{
			\dash\notif::error(T_("Poll id not set"), 'poll_id');
			return false;
		}

		$load_poll = \lib\db\polls::get(['id' => $poll_id, 'limit' => 1]);
		if(!$load_poll || !isset($load_poll['user_id']))
		{
			\dash\notif::error(T_("Invalid poll id"), 'poll_id');
			return false;
		}

		if(intval(\dash\user::id()) !== intval($load_poll['user_id']))
		{
			if(!\dash\permission::supervisor())
			{
				\dash\log::db('isNotYourPoll', ['data' => $poll_id]);
				\dash\notif::error(T_("This is not your poll!"), 'poll_id');
				return false;
			}
		}

		if($_id)
		{
			$load_question = \lib\db\questions::get(['id' => $_id, 'poll_id' => $poll_id, 'limit' => 1]);
			if(!$load_question)
			{
				\dash\notif::error(T_("Invalid questions id"), 'poll_id');
				return false;
			}
		}

		$title   = \dash\app::request('title');
		$desc    = \dash\app::request('desc');
		$media   = \dash\app::request('media');
		$require = \dash\app::request('require') ? 1 : null;
		$setting = \dash\app::request('setting');

		$type = \dash\app::request('type');
		if($type && mb_strlen($type) >= 200)
		{
			\dash\notif::error(T_("Please fill the question type less than 200 character"), 'type');
			return false;
		}

		$maxchar = \dash\app::request('maxchar');
		if($maxchar && !is_numeric($maxchar))
		{
			\dash\notif::error(T_("Please fill maxchar as a number"), 'maxchar');
			return false;
		}

		if($maxchar)
		{
			$maxchar = abs(intval($maxchar));
			if($maxchar > 1E+9)
			{
				\dash\notif::error(T_("Maxchart is out of range"), 'maxchar');
				return false;
			}
		}

		$sort = \dash\app::request('sort');
		if($sort && !is_numeric($sort))
		{
			\dash\notif::error(T_("Please fill the sort as a number"), 'sort');
			return false;
		}

		if($sort)
		{
			$sort = abs(intval($sort));
			if($sort > 1E+9)
			{
				\dash\notif::error(T_("Maxchart is out of range"), 'sort');
				return false;
			}
		}

		$status = \dash\app::request('status');
		if($status && !in_array($status, ['draft','publish','expire','deleted','lock','awaiting','question','filter','close', 'full']))
		{
			\dash\notif::error(T_("Invalid status of question"), 'status');
			return false;
		}

		if(is_array($media))
		{
			$media = json_encode($media, JSON_UNESCAPED_UNICODE);
		}


		$choisetitle  = \dash\app::request('choisetitle');

		if($choisetitle && mb_strlen($choisetitle) > 10000)
		{
			$choisetitle = substr($choisetitle, 0, 10000);
		}

		$old_choise = [];

		if($choisetitle)
		{
			if(isset($load_question['choice']))
			{
				$old_choise = json_decode($load_question['choice'], true);
			}

			if(!is_array($old_choise))
			{
				$old_choise = [];
			}

			$old_choise[] = ['title' => $choisetitle];
		}

		if($old_choise)
		{
			$choice         = json_encode($old_choise, JSON_UNESCAPED_UNICODE);
			$args['choice'] = $choice;
		}


		$args['poll_id'] = $poll_id;
		$args['title']   = $title;
		$args['desc']    = $desc;
		$args['media']   = $media;
		$args['require'] = $require;
		$args['setting'] = $setting;
		$args['type']    = $type;
		$args['maxchar'] = $maxchar;
		$args['sort']    = $sort;
		$args['status']  = $status;
		return $args;
	}


	/**
	 * ready data of question to load in api
	 *
	 * @param      <type>  $_data  The data
	 */
	public static function ready($_data)
	{
		$result = [];
		foreach ($_data as $key => $value)
		{

			switch ($key)
			{
				case 'id':
				case 'user_id':
					$result[$key] = \dash\coding::encode($value);
					break;

				case 'media':
				case 'choice':
				case 'setting':
					$result[$key] = json_decode($value, true);
					break;

				default:
					$result[$key] = $value;
					break;
			}
		}

		return $result;
	}

}
?>