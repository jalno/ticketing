import "bootstrap-inputmsg";
import "jquery.growl";
import "webuilder/formAjax";

export default class AddEdit
{
	public static initIfNeeded()
	{
		AddEdit.$form = $("#labels-add-edit-form");

		if (AddEdit.$form.length) {
			AddEdit.init();
		}
	}

	private static $form: JQuery;

	private static init()
	{
		AddEdit.runSubmitFormListener();
	}

	private static runSubmitFormListener()
	{
		const $btn = $('.panel-footer .btn[type=submit]', AddEdit.$form.parents('.panel'));
		const $icon = $('i', $btn);
		const originIconClass = $icon.attr('class') as string;

		AddEdit.$form.on('submit', function(e) {
			e.preventDefault();
			$('input,select,textarea', this).inputMsg('reset');
			$btn.prop('disabled', true);
			$icon.attr('class', 'fa fa-spin fa-spinner');

			$(this).formAjax({
				success: () => {
					$.growl.notice({
						title: t('userpanel.success'),
						message: t('userpanel.formajax.success'),
					});
					$btn.prop('disabled', false);
					$icon.attr('class', originIconClass);
				},
				error: (response: any) => {
					$btn.prop('disabled', false);
					$icon.attr('class', originIconClass);
					if (response.error === "data_duplicate" || response.error === "data_validation") {
						const $input = $(`[name="${response.input}"]`, this);
						const params = {
							title: t('error.fatal.title'),
							message: t(response.error),
						};

						if ($input.length) {
							$input.inputMsg(params);
						} else {
							$.growl.error(params);
						}
					} else {
						$.growl.error({
							title: t('error.fatal.title'),
							message: t('userpanel.formajax.error'),
						});
					}
				},
			});
		});
	}
}
