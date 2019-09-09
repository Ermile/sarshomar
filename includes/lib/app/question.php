<?php
namespace lib\app;

/**
 * Class for question.
 */
class question
{

	use question\add;
	use question\get;
	use question\edit;
	use question\datalist;
	use question\dashboard;
	use question\type;
	use question\next;
	use question\delete;


	public static $raw_field =
	[
		'media',
		'setting',
		'choice',
		'password',
	];


	/**
	 * check args
	 *
	 * @return     array|boolean  ( description_of_the_return_value )
	 */
	private static function check($_id = null)
	{

		$args            = [];

		$survey_id = \dash\app::request('survey_id');
		$survey_id = \dash\coding::decode($survey_id);
		if(!$survey_id)
		{
			\dash\notif::error(T_("Survey id not set"), 'survey_id');
			return false;
		}

		$load_survey = \lib\db\surveys::get(['id' => $survey_id, 'limit' => 1]);
		if(!$load_survey || !isset($load_survey['user_id']))
		{
			\dash\notif::error(T_("Invalid survey id"), 'survey_id');
			return false;
		}

		if(intval(\dash\user::id()) !== intval($load_survey['user_id']))
		{
			if(!\dash\permission::supervisor())
			{
				\dash\log::set('isNotYourSurvey', ['code' => $survey_id]);
				\dash\notif::error(T_("This is not your survey!"), 'survey_id');
				return false;
			}
		}

		if($_id)
		{
			$load_question = \lib\db\questions::get(['id' => $_id, 'survey_id' => $survey_id, 'limit' => 1]);
			if(!$load_question)
			{
				\dash\notif::error(T_("Invalid questions id"), 'survey_id');
				return false;
			}
		}


		$title   = \dash\app::request('title');
		$desc    = \dash\app::request('desc');
		$media   = \dash\app::request('media');
		$require = \dash\app::request('require') ? 1 : null;


		$type = \dash\app::request('type');
		if($type && !self::get_type($type))
		{
			\dash\notif::error(T_("Invalid question type"), 'type');
			return false;
		}

		if(\dash\app::isset_request('type') && !$type)
		{
			\dash\notif::error(T_("Type of question can not be null"), 'type');
			return false;
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

		$remove_choice = \dash\app::request('remove_choice');
		$add_choice    = \dash\app::request('add_choice');

		$choicetitle  = \dash\app::request('choicetitle');

		if($choicetitle && mb_strlen($choicetitle) > 10000)
		{
			$choicetitle = substr($choicetitle, 0, 10000);
		}


		$choicescore  = \dash\app::request('choicescore');

		if($choicescore && !is_numeric($choicescore))
		{
			\dash\notif::error(T_("Please set choice score as a number"), 'choicescore');
			return false;
		}

		if($choicescore)
		{
			$choicescore = intval($choicescore);
			if(abs($choicescore) > 1000)
			{
				\dash\notif::error(T_("Please set choice score between -1000 and 1000"), 'choicescore');
				return false;
			}
		}



		$choicefile          = \dash\app::request('choicefile');

		if(\dash\app::isset_request('choicetitle') && $choicetitle !== '0' && !$choicetitle && !$choicefile)
		{
			\dash\notif::error(T_("Please fill the choice title"), 'choicetitle');
			return false;
		}

		$old_choice = [];


		if($add_choice || $remove_choice)
		{
			if(isset($load_question['choice']))
			{
				$old_choice = json_decode($load_question['choice'], true);
			}

			if(!is_array($old_choice))
			{
				$old_choice = [];
			}

			if($remove_choice)
			{
				$choice_key = \dash\app::request('choice_key');
				if(array_key_exists($choice_key, $old_choice))
				{
					unset($old_choice[$choice_key]);
				}
				else
				{
					\dash\notif::error(T_("Invalid choice key for remove"));
					return false;
				}
			}
			else
			{
				// check not duplicate choice title
				$old_choice_title = array_column($old_choice, 'title');
				if(in_array($choicetitle, $old_choice_title))
				{
					\dash\notif::error(T_("This title is exist in your options list"), 'choicetitle');
					return false;
				}

				$new_choice          = [];
				$new_choice['title'] = $choicetitle;
				$new_choice['score'] = $choicescore;

				if($choicefile)
				{
					$new_choice['file'] = $choicefile;
				}

				$old_choice[] = $new_choice;
			}

			// refresh key
			$newKey = 1;
			$temp_choice = [];
			foreach ($old_choice as $key => $value)
			{
				$temp_choice[$newKey] = array_merge($value, ['id' => $newKey]);
				$newKey++;
			}

			$choice         = json_encode($temp_choice, JSON_UNESCAPED_UNICODE);

			$args['choice'] = $choice;
		}

		$setting = [];
		$myType  = 'other';
		if($type)
		{
			$myType = $type;
		}
		elseif(isset($load_question['type']))
		{
			$myType = $load_question['type'];
		}

		if(\dash\app::isset_request('change_setting'))
		{

			if(isset($load_question['setting']))
			{
				if(!is_array($load_question['setting']))
				{
					$setting = json_decode($load_question['setting'], true);
				}

				if(!is_array($setting))
				{
					$setting = [];
				}
			}

		}

		if(\dash\app::isset_request('random'))
		{
			$setting[$myType]['random'] = \dash\app::request('random') ? true : false;
		}

		$min = \dash\app::request('min');
		if($min && !is_numeric($min))
		{
			\dash\notif::error(T_("Please fill min as a number"), 'min');
			return false;
		}

		if($min)
		{
			$min = abs(intval($min));
			if($min > 999999999999)
			{
				\dash\notif::error(T_("Min is out of range"), 'min');
				return false;
			}
		}

		$max = \dash\app::request('max');
		if($max && !is_numeric($max))
		{
			\dash\notif::error(T_("Please fill max as a number"), 'max');
			return false;
		}

		if($max)
		{
			$max = abs(intval($max));
			if($max > 999999999999)
			{
				\dash\notif::error(T_("Max is out of range"), 'max');
				return false;
			}
		}

		if(isset($min) && isset($max))
		{
			if($min === 0 || $max === 0 || $min === '' || $max === '')
			{
				// no problem
			}
			else
			{

				if(intval($min) > intval($max))
				{
					\dash\notif::error(T_("Please set the max larger than min"), ['element' => ['min', 'max']]);
					return false;
				}
			}
		}

		if(\dash\app::isset_request('min') && self::get_type($myType, 'min'))
		{
			if($myType === 'multiple_choice')
			{
				$choice_count = [];
				if(isset($load_question['choice']))
				{
					$choice_count = $load_question['choice'];
					$choice_count = json_decode($choice_count, true);
					if(!is_array($choice_count))
					{
						$choice_count = [];
					}
				}

				if($min < 0 )
				{
					\dash\notif::error(T_("Please set min choic larger than 0"), 'min');
					return false;
				}

				if($min > count($choice_count))
				{
					\dash\notif::error(T_("Minimum choice must be less than choice count"), 'min');
					return false;
				}
			}


			$setting[$myType]['min'] = \dash\app::request('min');
		}


		if(\dash\app::isset_request('max') && self::get_type($myType, 'max'))
		{
			if($myType === 'rating')
			{
				if(isset($max) && (intval($max) > 10 || intval($max) <= 0))
				{
					\dash\notif::error(T_("Please set maximum rate between 1 and 10"), 'max');
					return false;
				}
			}
			elseif($myType === 'multiple_choice')
			{
				$choice_count = [];
				if(isset($load_question['choice']))
				{
					$choice_count = $load_question['choice'];
					$choice_count = json_decode($choice_count, true);
					if(!is_array($choice_count))
					{
						$choice_count = [];
					}
				}

				if($max > count($choice_count))
				{
					\dash\notif::error(T_("Maximum choice must be less than choice count"), 'max');
					return false;
				}

			}



			$setting[$myType]['max'] = $max;
		}

		if(\dash\app::isset_request('placeholder') && self::get_type($myType, 'placeholder'))
		{
			$placeholder = \dash\app::request('placeholder');
			if($placeholder && mb_strlen($placeholder) > 10000)
			{
				$placeholder = substr($placeholder, 0, 10000);
			}

			if($placeholder)
			{
				$placeholder = \dash\safe::remove_nl($placeholder);
			}

			$setting[$myType]['placeholder'] = $placeholder;

		}

		if(\dash\app::isset_request('choiceinline') && self::get_type($myType, 'choiceinline'))
		{
			$setting[$myType]['choiceinline'] = \dash\app::request('choiceinline') ? true : false;
		}


		if(\dash\app::isset_request('choice_sort') && self::get_type($myType, 'random'))
		{
			$choice_sort = \dash\app::request('choice_sort');
			if($choice_sort && !in_array($choice_sort, ['save','random','asc','desc']))
			{
				\dash\notif::error(T_("Invalid choice sort of question"), 'choice_sort');
				return false;
			}
			$setting[$myType]['choice_sort']  = $choice_sort;
		}


		if(\dash\app::isset_request('choicehelp') && self::get_type($myType, 'choicehelp'))
		{
			$choicehelp = \dash\app::request('choicehelp');
			if($choicehelp && mb_strlen($choicehelp) > 10000)
			{
				$choicehelp = substr($choicehelp, 0, 10000);
			}

			if($choicehelp)
			{
				$choicehelp = \dash\safe::remove_nl($choicehelp);
			}

			$setting[$myType]['choicehelp'] = $choicehelp;
		}

		if(\dash\app::isset_request('ratetype') && self::get_type($myType, 'ratetype'))
		{
			$ratetype = \dash\app::request('ratetype');
			if($ratetype && !in_array($ratetype, ['star','heart','bell','flag','bookmark','like','dislike','user1']))
			{
				\dash\notif::error(T_("Invalid rate emoji"), 'ratetype');
				return false;
			}
			$setting[$myType]['ratetype'] = $ratetype;
		}


		if(\dash\app::isset_request('default') && self::get_type($myType, 'default'))
		{
			$default = \dash\app::request('default');
			if($default && !is_numeric($default))
			{
				\dash\notif::error(T_("Please set default as a number"), 'default');
				return false;
			}

			if($default)
			{
				$default = abs(intval($default));
			}

			$setting[$myType]['default'] = $default;
		}


		if(\dash\app::isset_request('step') && self::get_type($myType, 'step'))
		{
			$step = \dash\app::request('step');
			if($step && !is_numeric($step))
			{
				\dash\notif::error(T_("Please set step as a number"), 'step');
				return false;
			}

			if($step)
			{
				$step = abs(intval($step));
			}

			$setting[$myType]['step'] = $step;

		}

		if($myType === 'rangeslider')
		{
			$check_default = 0;
			$check_min     = 0;
			$check_max     = 0;
			$check_step    = 0;
			$range = intval(self::get_type($myType, 'maxrange'));

			if(isset($default)) $check_default = intval($default);
			if(isset($min)) $check_min         = intval($min);
			if(isset($max)) $check_max         = intval($max);
			if(isset($step)) $check_step       = intval($step);

			// var_dump($check_default, $check_min, $check_max, $check_step);exit();
			if($check_min > $check_max)
			{
				\dash\notif::error(T_("Please set min less than max"), ['element' => ['min', 'max']]);
				return false;
			}

			if($check_max - $check_min > $range)
			{
				\dash\notif::error(T_("Please set min and max in range :range", ['range' => \dash\utility\human::fitNumber($range)]), ['element' => ['min', 'max']]);
				return false;
			}

			if($check_step > $check_max)
			{
				\dash\notif::error(T_("Please set step less than max"), ['element' => ['step', 'max']]);
				return false;
			}

			if($check_default > $check_max)
			{
				\dash\notif::error(T_("Please set default less than max"), ['element' => ['default', 'max']]);
				return false;
			}

			if($check_default < $check_min)
			{
				\dash\notif::error(T_("Please set default larger than min"), ['element' => ['default', 'min']]);
				return false;
			}
		}

		if(\dash\app::isset_request('label1') && self::get_type($myType, 'label3'))
		{
			$label1 = \dash\app::request('label1');
			if($label1 && mb_strlen($label1) > 100)
			{
				$label1 = substr($label1, 0, 100);
			}

			if($label1)
			{
				$label1 = \dash\safe::remove_nl($label1);
			}

			$setting[$myType]['label1'] = $label1;
		}

		if(\dash\app::isset_request('label2') && self::get_type($myType, 'label3'))
		{
			$label2 = \dash\app::request('label2');
			if($label2 && mb_strlen($label2) > 100)
			{
				$label2 = substr($label2, 0, 100);
			}

			if($label2)
			{
				$label2 = \dash\safe::remove_nl($label2);
			}

			$setting[$myType]['label2'] = $label2;
		}

		if(\dash\app::isset_request('label3') && self::get_type($myType, 'label3'))
		{
			$label3 = \dash\app::request('label3');
			if($label3 && mb_strlen($label3) > 300)
			{
				$label3 = substr($label3, 0, 300);
			}

			if($label3)
			{
				$label3 = \dash\safe::remove_nl($label3);
			}

			$setting[$myType]['label3'] = $label3;
		}

		if($myType === 'password')
		{
			$password = \dash\app::request('password');

			if(\dash\app::request('setpassword') && (!isset($password) || is_null($password) || $password === ''))
			{
				\dash\notif::error(T_("Please fill the password text"), 'password');
				return false;
			}

			if(mb_strlen($password) > 200)
			{
				\dash\notif::error(T_("Please set password less than 200 character"), 'password');
				return false;
			}

			$setting[$myType]['password'] = $password;
		}

		$address = \dash\app::request('address');
		if($address && mb_strlen($address) > 200)
		{
			\dash\notif::error(T_("Please set address less than 200 character"), 'address');
			return false;
		}

		if($address)
		{
			$address = self::check_question_address($address, $_id, $survey_id);
			if($address === false)
			{
				return false;
			}
		}


		if(!empty($setting))
		{
			$args['setting'] = json_encode($setting, JSON_UNESCAPED_UNICODE);
		}

		$args['survey_id'] = $survey_id;
		$args['title']     = $title;
		$args['desc']      = $desc;
		$args['media']     = $media;
		$args['require']   = $require;
		$args['type']      = $type;
		$args['sort']      = $sort;
		$args['status']    = $status;
		$args['address']    = $address;

		return $args;
	}


	private static function check_question_address($_address, $_id, $_survey_id)
	{
		$address = \dash\utility\convert::to_en_number($_address);

		$address = preg_replace("/\_{2,}/", "_", $address);
		$address = preg_replace("/\-{2,}/", "-", $address);


		if(mb_strlen($address) > 200)
		{
			\dash\notif::error(T_("Please set the address less than 200 character"), 'address');
			return false;
		}

		if(!preg_match("/^[A-Za-z0-9_\-]+$/", $address))
		{
			\dash\notif::error(T_("Only [A-Za-z0-9_-] can use in address"), 'address');
			return false;
		}

		if(!preg_match("/[A-Za-z]+/", $address))
		{
			\dash\notif::error(T_("You must use a one character from [A-Za-z] in the address"), 'address');
			return false;
		}

		if(is_numeric($address))
		{
			\dash\notif::error(T_("Address should contain a Latin letter"),'address');
			return false;
		}

		if(is_numeric(substr($address, 0, 1)))
		{
			\dash\notif::error(T_("The address must begin with latin letters"),'address');
			return false;
		}

		if(!preg_match("/^[A-Za-z0-9]+$/", $address))
		{
			\dash\notif::error(T_("Only [A-Za-z0-9] can use in address"), 'address', 'arguments');
			return false;
		}


		$address = mb_strtolower($address);

		if(in_array($address, ['score', 'name', 'survey', 'question','user_id', 'user', 'mobile']))
		{
			\dash\notif::error(T_("You can not choose this address"), 'address', 'arguments');
			return false;
		}

		$check_duplicate_address = \lib\db\questions::get(['survey_id' => $_survey_id, 'address' => $address, 'limit' => 1]);
		if(isset($check_duplicate_address['id']))
		{
			if(intval($check_duplicate_address['id']) === intval($_id))
			{
				// noproblem
			}
			else
			{
				\dash\notif::error(T_("Duplicate address"), 'address');
				return false;
			}

		}
		return $address;
	}


	/**
	 * ready data of question to load in api
	 *
	 * @param      <type>  $_data  The data
	 */
	public static function ready($_data)
	{
		$setting = [];
		$myType = null;
		if(isset($_data['type']))
		{
			$myType = $_data['type'];
		}
		if(isset($_data['setting']))
		{
			$setting = $_data['setting'];
			$setting = json_decode($setting, true);
			if(isset($setting[$myType]))
			{
				$setting = $setting[$myType];
			}
		}

		$survey_id = null;
		if(isset($_data['survey_id']))
		{
			$survey_id = $_data['survey_id'];
		}

		$result = [];
		foreach ($_data as $key => $value)
		{

			switch ($key)
			{
				case 'status':

					break;

				case 'id':
				case 'user_id':
				case 'survey_id':
				case 'question_id':
					$result[$key] = \dash\coding::encode($value);
					break;

				case 'type':
					$result[$key] = $value;
					$result['type_detail'] = self::get_type($value);
					break;

				case 'media':
					$result[$key] = json_decode($value, true);
					break;

				case 'setting':
					$result[$key] = json_decode($value, true);
					if($myType)
					{
						$setting = array_merge(self::get_type($myType, 'default_load'), $setting);
					}
					$result[$key][$myType] = $setting;
					break;

				case 'choice':
					$choice       = json_decode($value, true);
					$result[$key] = $choice;

					if(!is_array($choice) || \dash\url::content() === 'a')
					{
						break;
					}

					if(is_array($choice))
					{
						foreach ($choice as $xkey => $xvalue)
						{
							$title           = isset($xvalue['title']) ? $xvalue['title'] : null;
							$title           = \lib\app\answer::replace_question_answer($title, $survey_id, \dash\user::id());
							$xvalue['title'] = $title;
							$choice[$xkey]   = $xvalue;
						}
					}

					$choice_sort  = 'save';
					if(isset($setting['choice_sort']))
					{
						$choice_sort = $setting['choice_sort'];
					}

					switch ($choice_sort)
					{
						case 'random':
							shuffle($choice);
							break;

						case 'asc':
							sort($choice);
							break;

						case 'desc':
							rsort($choice);
							break;

						case 'save':
						default:
							// no thing
							break;
					}

					$result[$key] = $choice;

					break;

				case 'title':
				case 'desc':
					$result[$key] = \lib\app\answer::replace_user_score($value, $survey_id, \dash\user::id()) ;
					$result[$key] = \lib\app\answer::replace_question_answer($result[$key], $survey_id, \dash\user::id());

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