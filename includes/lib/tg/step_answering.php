<?php
namespace lib\tg;
// use telegram class as bot
use \dash\social\telegram\tg as bot;
use \dash\social\telegram\hook;
use \dash\social\telegram\step;

class step_answering
{
	public static function start($_id)
	{
		// dont run on public chats!
		if(!bot::isPrivate())
		{
			survey::goToPrivate($_id);
			return false;
		}

		// its okay on start
		bot::ok();

		step::set('surveyNo', $_id);
		step::start('answering');

		// if start with callback answer callback
		if(bot::isCallback())
		{
			$callbackResult =
			[
				'text' => T_("Answer to survey"). ' '. $_id,
			];
			bot::answerCallbackQuery($callbackResult);
		}

		return self::step1();
	}


	// show question
	public static function step1($_txt = null, $_step = null)
	{
		// init first step
		if($_step === null)
		{
			$_step = 1;

			$msg = '☢️ '. T_('You can use below command when try to answering survey questions.'). "\n\n";
			$msg .= '/cancel'. "\n".  T_('Cancel answer to this survey and exit from it'). "\n";
			$msg .= '/skip'. "\n".  T_('Skip current question and get next one');
			$initMsg =
			[
				'text' => $msg,
				'reply_markup' => ['remove_keyboard' => true]
			];
			bot::sendMessage($initMsg);
			sleep(2);
		}
		// on save reset try
		step::checkFalseTry('reset');

		$surveyNo = step::get('surveyNo');
		step::set('surveyStep', $_step);
		// get question of this step
		$myQuestion    = \lib\app\tg\survey::get($surveyNo, $_step);
		$userAnswerArr = \dash\data::myAnswerTitle();
		if(isset($myQuestion['id']))
		{
			step::set('questionId', $myQuestion['id']);
		}
		else
		{
			// show thankyou msg
			survey::thankyou($surveyNo);
			step::stop();
			return true;
		}
		// send question
		questionSender::analyse($myQuestion, $userAnswerArr);
		// set type of question
		if(isset($myQuestion['type']))
		{
			step::set('questionType', $myQuestion['type']);
		}

		// go to next step to get answer
		step::plus();
	}


	// get answer
	public static function step2($_answer)
	{
		if(step::checkFalseTry())
		{
			return false;
		}
		// define variables
		$surveyNo     = step::get('surveyNo');
		$surveyStep   = step::get('surveyStep');
		$questionId   = step::get('questionId');
		$questionType = step::get('questionType');

		if(bot::isCallback())
		{
			if(substr($_answer, 0, 3) === 'cb_')
			$_answer = substr($_answer, 3);

			$fakeAnswer = true;
			$cmd = hook::cmd();

			if($cmd['commandRaw'] === 'cb_survey_'. $surveyNo)
			{
				if($cmd['optionalRaw'] === $questionId)
				{
					$fakeAnswer = false;
					$_answer    = $cmd['argumentRaw'];
				}
			}

			if($fakeAnswer)
			{
				// remove keyboard of old messages
				$newMsg =
				[
					'reply_markup' =>
					[
						'inline_keyboard' =>
						[
							[
								[
									'text' => T_("Sarshomar website"),
									'url'  => bot::website(),
								],
							]
						]
					]
				];
				bot::editMessageReplyMarkup($newMsg);
				// show funny message
				bot::answerCallbackQuery('❌ '. T_('How are you!'));
				// show false try message
				step::checkFalseTry(true);
				return false;
			}
			else
			{
				// reset false try if user send correct answer
				step::set('falseTry', false);
			}


			if($questionType === 'multiple_choice')
			{
				if($_answer === '/save')
				{
					// answer callback result
					bot::answerCallbackQuery('#'. $surveyStep. ' '. T_("Answer received"));
				}
				else
				{
					// answer callback result
					bot::answerCallbackQuery('#'. $surveyStep. ' '. T_("Item :val selected", ['val' => $_answer]));
				}
			}
			else
			{
				// answer callback result
				bot::answerCallbackQuery('#'. $surveyStep. ' '. T_("Answer received"));

				// // send message of recieve on callback
				// $receiveMsg =
				// [
				// 	'text' => T_("Your answer"). "\n<b>". $_answer. '</b>',
				// 	'reply_markup' => ['remove_keyboard' => true],
				// 	'disable_notification' => true,
				// ];
				// bot::sendMessage($receiveMsg);
			}
		}
		if($_answer === '/skip')
		{
			$saveResult = \lib\app\tg\survey::skip($surveyNo, $questionId);
		}
		else
		{
			if($questionType === 'multiple_choice')
			{
				$multipleAnswers = step::get('multipleAnswers');

				if($_answer === '/save')
				{
					// save answer
					$userAnswerKeys = array_keys($multipleAnswers);
					$saveResult = \lib\app\tg\survey::answer($surveyNo, $questionId, $userAnswerKeys);

					// clean all detail
					step::set('qMessageId', null);
					step::set('qChatId', null);
					step::set('multipleAnswers', null);
					step::set('multipleLastAnswer', null);
				}
				else
				{
					// set in variable
					step::set('multipleLastAnswer', $_answer);
					step::goingto(1);
					return self::step1(null, $surveyStep);
				}

			}
			else
			{
				// save answer
				$saveResult = \lib\app\tg\survey::answer($surveyNo, $questionId, $_answer);
			}
		}


		if($saveResult)
		{
			// increase step of survey
			$surveyStep++;
			if(isset($saveResult['step']))
			{
				$surveyStep = $saveResult['step'];
			}
			// go to next message
			step::goingto(1);

			return self::step1(null, $surveyStep);
		}
		else
		{
			// notif created on app based on question type
		}
	}
}
?>