<?php
namespace content_a\survey\review;


class view
{
	public static function config()
	{
		\dash\data::page_title(T_("Review Survey"));
		\dash\data::page_desc(T_("Check your survey detail and track everything about this survey."));
		\dash\data::page_pictogram('plus-zoom');

		if(\dash\request::get('id'))
		{
			$id          = \dash\request::get('id');
			$load_survey = \lib\app\survey::get($id);
			if(!$load_survey)
			{
				\dash\header::status(404, T_("Invalid survey id"));
			}
			\dash\data::surveyRow($load_survey);

			\dash\data::page_title(\dash\data::page_title(). ' | '. \dash\data::surveyRow_title());

			\dash\data::badge_link(\dash\url::kingdom(). '/s/'. $id);
			\dash\data::badge_text(T_('Preview'));

			\dash\data::badge2_link(\dash\url::this().'?id='. $id);
			\dash\data::badge2_text(T_('Back to survey dashboard'));

			$dashboard_detail['month_detail'] = \dash\date::month_precent();

			\dash\data::dashboardDetail($dashboard_detail);


			$questionsData = \lib\app\question::block_survey($id);
			\dash\data::questionList($questionsData);
		}
		else
		{
			\dash\redirect::to(\dash\url::here());
		}

	}
}
?>
