<?php
namespace content_a\report\question;


class view
{
	public static function config()
	{
		\dash\data::page_pictogram('list');
		\dash\data::page_title(T_("Question list"));
		\dash\data::page_desc(T_("List of your survey answers by select one question"));
		$dataTable = '[]';

		if(\dash\request::get('id'))
		{

			\dash\data::badge_link(\dash\url::this(). '?id='. \dash\request::get('id'));
			\dash\data::badge_text(T_('Back to report dashboard'));

			// \content_a\survey\view::load_survey();

			if(!\dash\request::get('questionid'))
			{
				\dash\redirect::to(\dash\url::this(). '/questionlist?id='. \dash\request::get('id'));
			}
			else
			{
				\dash\data::badge_link(\dash\url::this(). '/questionlist?id='. \dash\request::get('id'));
				\dash\data::badge_text(T_('Back to question list'));

				$question_detail = \content_a\question\view::load_question();

				\dash\data::page_title(\dash\data::page_title(). ' | '. \dash\data::surveyRow_title());

				\dash\data::questionDetail($question_detail);
				// if(isset($question_detail['title']))
				// {
				// 	// show step of this question in tile
				// 	// \dash\data::page_title(\dash\data::surveyRow_title(). ' | '. $question_detail['title']);
				// 	\dash\data::page_title(T_(). ' | '. \dash\data::surveyRow_title());

				// }

				$id = \dash\request::get('id');

				$questionList = \lib\app\question::block_survey($id);

				\dash\data::questionList($questionList);

				if(\dash\request::get('question2') || \dash\request::get('question3'))
				{
					$load_advance_chart = self::load_advance_chart(\dash\request::get('id'),\dash\request::get('questionid'), \dash\request::get('question2'), \dash\request::get('question3'));
					if($load_advance_chart)
					{
						\dash\data::showAdvanceChart(true);
						return;
					}
				}

				$questionid = \dash\request::get('questionid');

				if(isset($question_detail['type_detail']['chart']))
				{
					$sort  = null;
					$order = null;

					if(\dash\request::get('sort'))
					{
						if(\dash\request::get('sort') === 'answer')
						{
							$sort = 'answerdetails.answerterm_id';
						}
						elseif(in_array(\dash\request::get('sort'), ['value', 'value_all', 'percent']))
						{
							$sort = 'count';
						}
					}

					if(\dash\request::get('order') && in_array(\dash\request::get('order'), ['asc', 'desc']))
					{
						$order = \dash\request::get('order');
					}


					$dataTable = \lib\app\answer::get_result(\dash\request::get('id'), $questionid, $sort, $order);

					$table = $dataTable;
					if(is_array($table))
					{
						$table = array_map('json_decode', $table);
						\dash\data::tableRow($table);
						\dash\data::sortLink(\dash\app\sort::make_sortLink(['answer', 'value', 'value_all', 'percent'], \dash\url::this(). '/question'));
					}
					\dash\data::showChart(true);



				}
				else
				{
					$args                              = [];
					$args['answerdetails.question_id'] = \dash\coding::decode($questionid);

					$dataTable = \lib\app\answerdetail::list(null, $args);
					if($question_detail['type'] === 'date' && is_array($dataTable) && \dash\language::current() === 'fa')
					{

						foreach ($dataTable as $key => $value)
						{

							if(isset($value['text']))
							{
								$dataTable[$key]['jdate'] = \dash\datetime::fit($value['text'], null, 'date');
							}
						}
					}

					\dash\data::showChart(false);
				}
			}

		}
		else
		{
			\dash\redirect::to(\dash\url::here());
		}

		\dash\data::dataTable($dataTable);
	}


	public static function load_advance_chart($_id, $_question1, $_question2, $_question3 = null)
	{
		$id        = \dash\coding::decode($_id);
		$question1 = \dash\coding::decode($_question1);
		$question2 = \dash\coding::decode($_question2);
		$question3 = \dash\coding::decode($_question3);

		if(!$_question2 && !$question3)
		{
			return false;
		}

		if(!$question2 && $question3)
		{
			$question2 = $question3;
			$question3 = null;
		}

		if($question2 == $question3)
		{
			$question3 = null;
		}


		$result = \lib\app\answer\chart::advance_chart($id, $question1, $question2, $question3, ['sort' => \dash\request::get('sort'), 'order' => \dash\request::get('order')]);
		if(!$result)
		{
			\dash\redirect::to(\dash\url::this(). '/question?id='. \dash\request::get('id'). '&questionid='. \dash\request::get('questionid'));
		}

		\dash\data::advanceChart($result);
		// \dash\data::sortLink(\dash\app\sort::make_sortLink(['count', 'q1', 'q2', 'q3'], \dash\url::this(). '/question'));
		return true;

	}
}
?>
