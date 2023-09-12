import * as $ from "jquery";
import Ticket from "./Ticket";
import { AjaxRequest, Router } from "webuilder";
import Templates from "./Settings/Templates";

export default class Editor
{
	public static initIfNeeded()
	{
		Editor.$container = $("#ticketing-editor");

		if (Editor.$container.length) {
			Editor.init();
		}
	}

	private static $container: JQuery;

	private static init()
	{
		Ticket.runTextareaAutosize(Editor.$container);
		Editor.showPreviewListener();
		Editor.setEvents();
	}

	private static showPreviewListener()
	{
		const $btn = $("#preview-ticket-btn", Editor.$container);
		const $parent = $btn.parent();
		const $tab = $('#preview-tab', Editor.$container);
		const $alert = $('.alert', $tab);
		const $textarea = $('#editor-tab textarea', Editor.$container) as JQuery<HTMLFormElement>;
		const $previewContainer = $('.ticket-preview-container', $tab);

		let lastContent: string = '';
		let lastFormat = $textarea.data('message_format');

		$btn.data('disabled', !$textarea.val());
		$textarea.attr('dir', 'auto');

		const previewContent = () => {
			const content = $textarea.val() as string;
			const format = $textarea.data('message_format');

			if (lastContent == content && lastFormat == format) {
				return;
			}

			lastContent = content;
			lastFormat = format;

			$alert.show();
			$previewContainer.hide();
			$previewContainer.html('');

			$btn.data('disabled', true);
			AjaxRequest({
				url: Router.url('userpanel/ticketing/editor/preview', {ajax: 1} as any),
				type: 'POST',
				data: {
					format: format,
					content: content,
				},
				success: (response) => {
					$btn.data('disabled', false);
					$previewContainer.html(response.content);
					$alert.hide();
					$previewContainer.show();
				},
				error: (response) => {
					$btn.data('disabled', false);
				}
			});
		}

		$btn.on('click', (e) => {
			if ($parent.hasClass('active')) {
				return;
			}

			if ($btn.data('disabled')) {
				e.preventDefault();
				e.stopPropagation();
			} else {
				previewContent();
			}
		});

		$textarea.on('change', () => {
			const content = $textarea.val();
			lastContent = '';
			if (content) {
				$btn.data('disabled', false);
				if ($parent.hasClass('active')) {
					previewContent();
				}
			} else {
				$btn.data('disabled', true);
			}
		});
	}

	private static setEvents()
	{
		$("#editor-tab textarea", Editor.$container).on('resize', function() {
			console.log('resize', ($(this).get(0).scrollHeight + 2) + 'px');
			$(this).css('height', ($(this).get(0).scrollHeight + 2) + 'px');
			$(this).trigger('change');
		});
	}
}
