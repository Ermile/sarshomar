<?php
namespace lib\tg;
// use telegram class as bot
use \dash\social\telegram\tg as bot;
use \dash\social\telegram\step;

class step_survey
{
	public static function start($_id)
	{
		// its okay on start
		bot::ok();

		step::set('surveyNo', $_id);
		step::start('surveyAnswer');

		// if start with callback answer callback
		if(bot::isCallback())
		{
			$callbackResult =
			[
				'text' => T_("Answer to survey "). $_id,
			];
			bot::answerCallbackQuery($callbackResult);
		}
		var_dump(22);

		return self::step1();
	}


	public static function step1()
	{
		step::plus();
		$txt_text = T_("Hello survey");

		// empty keyboard
		$result =
		[
			'text'         => $txt_text,
			'reply_markup' =>
			[
				'keyboard' => [[T_('Cancel')]],
				'resize_keyboard' => true,
				'one_time_keyboard' => true
			],
		];
		bot::sendMessage($result);
	}


	public static function step2($_answer)
	{
		if(bot::isCallback())
		{
			$callbackResult =
			[
				'text' => T_("Please choose answer")." 📝",
				'show_alert' => true,
			];
			bot::answerCallbackQuery($callbackResult);
			return false;
		}
		elseif(step::checkFalseTry())
		{
			return false;
		}

		$surveyNo = step::get('surveyNo');

		step::stop();
	}
}
?>