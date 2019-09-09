<?php
namespace lib\app\question;


trait type
{

	public static function get_type($_key, $_index = null)
	{
		$list = self::all_type();

		if(array_key_exists($_key, $list))
		{
			if($_index)
			{
				if(array_key_exists($_index, $list[$_key]))
				{
					return $list[$_key][$_index];
				}
				else
				{
					return null;
				}
			}
			else
			{
				return $list[$_key];
			}
		}

		return null;
	}


	public static function all_type()
	{

		// sample array
		// $sample =
		// [
		// 	'key'           => 'picture_choice',
		//  'placeholder'   => true,
		// 	'title'         => T_('Picture choice'),
		// 	'choice'        => true,
		// 	'random'        => true,
		// 	'min'           => false,
		// 	'max'           => false,
		// 	'otherchoice'   => false,
		// 	'rangenumber'   => false,
		// 	'ratetype'      => null,
		// 	'chart'         => false,
		// 	'prifile_field' => null,
		// 	'minchoice'     => false,
		// 	'maxchoice'     => false,
		// 	'choiceinline'  => false,
		// 	'step'          => 10,
		// 	'default'       => 5,
		// 	'label3'        => false,
		// 	'choicehelp'    => false,
		//  'default_load'  => [],
		// 	'desc'          => T_('Picture choice'),
		// 	'logo'          => \dash\url::static(). '/images/question-type/sarshomar.png',
		// 	'upload_choice' => true,
		// ];

		$type = [];


		$type['short_answer'] =
		[
			'key'          => 'short_answer',
			'title'        => T_("Short answer"),
			'placeholder'  => true,
			'max'          => true,
			'logo'         => \dash\url::static(). '/images/question-type/short_answer.png',
			'default_load' =>
			[
				'placeholder' => T_("Type here ..."),
				'max'         => 500,
				'default'     => 100,
			],
		];


		$type['descriptive_answer'] =
		[
			'key'          => 'descriptive_answer',
			'title'        => T_('Descriptive answer'),
			'placeholder'  => true,
			'max'          => true,
			'logo'         => \dash\url::static(). '/images/question-type/descriptive_answer.png',
			'default_load' =>
			[
				'max'     => 10000,
				'default' => 1000,
			],
		];


		$type['numeric'] =
		[
			'key'          => 'numeric',
			'placeholder'  => true,
			'title'        => T_('Numberic'),
			'min'          => true,
			'max'          => true,
			'chart'        => true,
			'logo'         => \dash\url::static(). '/images/question-type/numeric.png',
			'default_load' =>
			[
				'min'     => 0,
				'max'     => 999999999999,
			],
		];


		$type['single_choice'] =
		[
			'key'          => 'single_choice',
			'title'        => T_('Single choice'),
			'choice'       => true,
			'choiceinline' => true,
			'random'       => true,
			'chart'        => true,
			'logo'         => \dash\url::static(). '/images/question-type/single_choice.png',
			'default_load' =>
			[
				'choiceinline' => true,
			],
		];


		$type['multiple_choice'] =
		[
			'key'          => 'multiple_choice',
			'title'        => T_('Multiple choice'),
			'choice'       => true,
			'random'       => true,
			'chart'        => true,
			'min'          => true,
			'max'          => true,
			'choicehelp'   => true,
			'logo'         => \dash\url::static(). '/images/question-type/multiple_choice.png',
			'default_load' =>
			[
				'min'        => 1,
				'choicehelp' => T_("You can choose as many as you want"),
			],
		];


		$type['dropdown'] =
		[
			'key'          => 'dropdown',
			'title'        => T_('Dropdown'),
			'placeholder'  => true,
			'choice'       => true,
			'chart'        => true,
			'random'       => true,
			'logo'         => \dash\url::static(). '/images/question-type/dropdown.png',
			'default_load' =>
			[
				'placeholder' => T_("Please choose one item"),
			],
		];


		$type['date'] =
		[
			'key'          => 'date',
			'placeholder'  => true,
			'title'        => T_('Date'),
			'logo'         => \dash\url::static(). '/images/question-type/date.png',
			'default_load' =>
			[
				'placeholder' => \dash\utility\human::fitNumber("1369/10/09"),
			],
		];

		$type['time'] =
		[
			'key'          => 'time',
			'placeholder'  => true,
			'title'        => T_('Time'),
			'logo'         => \dash\url::static(). '/images/question-type/time.png',
			'default_load' =>
			[
				'placeholder' => T_("Choose time"),
			],
		];


		$type['mobile'] =
		[
			'key'          => 'mobile',
			'title'        => T_('Mobile'),
			'logo'         => \dash\url::static(). '/images/question-type/mobile.png',
			'placeholder'  => true,
			'default_load' =>
			[
				'placeholder' => T_("Enter mobile"),
			],
		];

		$type['email'] =
		[
			'key'          => 'email',
			'placeholder'  => true,
			'title'        => T_('Email'),
			'logo'         => \dash\url::static(). '/images/question-type/email.png',
			'default_load' =>
			[
				'placeholder' => T_("abc@youdomain.com"),
			],
		];

		$type['website'] =
		[
			'key'          => 'website',
			'placeholder'  => true,
			'title'        => T_('Website'),
			'logo'         => \dash\url::static(). '/images/question-type/website.png',
			'default_load' =>
			[
				'placeholder' => T_("http://"),
			],
		];


		$type['rating'] =
		[
			'key'          => 'rating',
			'title'        => T_('Rating'),
			'max'          => true,
			'ratetype'     => true,
			'chart'        => true,
			'logo'         => \dash\url::static(). '/images/question-type/rating.png',
			'default_load' =>
			[
				'max'      => 10,
				'ratetype' => 'star',
			],
		];

		$type['rangeslider'] =
		[
			'key'          => 'rangeslider',
			'title'        => T_('Range slider'),
			'max'          => true,
			'min'          => true,
			'label3'       => true,
			'default'      => true,
			'step'         => true,
			'chart'        => true,
			'maxrange'     => 1000,
			'logo'         => \dash\url::static(). '/images/question-type/rangeslider.png',
			'default_load' =>
			[
				'min'     => 0,
				'max'     => 1000,
				'step'    => 1,
				'default' => 0,

			],
		];

		$type['password'] =
		[
			'key'          => 'password',
			'title'        => T_('Password'),
			// 'max'          => true,
			// 'min'          => true,
			// 'label3'       => true,
			// 'default'      => true,
			// 'step'         => true,
			// 'chart'        => true,
			// 'maxrange'     => 1000,
			'logo'         => \dash\url::static(). '/images/question-type/short_answer.png',
			'default_load' =>
			[
				// 'min'     => 0,
				// 'max'     => 1E+10,
				// 'step'    => 1,
				// 'default' => 0,

			],
		];

		// card_descign
		// file_upload
		return $type;
	}
}
?>