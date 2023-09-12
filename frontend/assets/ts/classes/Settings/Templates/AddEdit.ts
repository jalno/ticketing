import * as $ from "jquery";
import Templates from "../Templates";
import 'lightgallery.js';
import * as ClipboardJS from 'clipboard';

declare function lightGallery (el: HTMLElement);

enum MessageType {
	ADD = 1,
	REPLY = 2,
};

export default class AddEdit
{
	public static initIfNeeded()
	{
		AddEdit.$form = $("#templates-add-edit-form");

		if (AddEdit.$form.length) {
			AddEdit.init();
		}
	}

	private static $form: JQuery;

	private static init()
	{
		AddEdit.chnageMessageFormatListener();
		AddEdit.setEventsForSubjectField();
		AddEdit.runGalleryListener();
		AddEdit.runCopyToClipboardListener();
		AddEdit.runSubmitFormListener();
		AddEdit.runChangeMessageFormatListener();
	}

	private static chnageMessageFormatListener()
	{
		const $editTextarea = $('#ticketing-editor #editor-tab textarea') as JQuery<HTMLFormElement>;
		$('select[name="message_format"]', AddEdit.$form).on('change', function() {
			$editTextarea.data('message_format', $(this).val() as string).trigger('change');
		});

		Templates.runAutoInsertVariableFor($editTextarea);
	}

	private static setEventsForSubjectField()
	{
		const $input = $('input[name="subject"]', AddEdit.$form) as JQuery<HTMLFormElement>;
		$input.attr('dir', 'auto');
		Templates.runAutoInsertVariableFor($input);
	}

	private static runGalleryListener()
	{
		$('#tutorial-templates-modal .modal-body .gallery').each(function() {
			lightGallery(this);
		})
	}

	private static runCopyToClipboardListener()
	{
		const clipboard = new ClipboardJS('#tutorial-templates-modal .modal-body table tbody tr td code');
		clipboard.on('success', function (e) {
			$.growl.notice({
				title: '',
				message: t('titles.ticketing.copied'),
			});

			e.clearSelection();
		});
	}

	private static runSubmitFormListener()
	{
		const $btn = $('.panel-footer .btn[type=submit]', AddEdit.$form.parents('.panel'));
		const $icon = $('i', $btn);
		$icon.data('origin-icon-class', $icon.attr('class'));

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
					$icon.attr('class', $icon.data('origin-icon-class'));
				},
				error: (response) => {
					$btn.prop('disabled', false);
					$icon.attr('class', $icon.data('origin-icon-class'));
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

	private static runChangeMessageFormatListener()
	{
		const $subject = $('input[name=subject]', AddEdit.$form);
		$('select[name=message_type]', AddEdit.$form).on('change', function() {
			$subject.prop('disabled', MessageType.REPLY === parseInt($(this).val() as string));
		});
	}
}
