import * as $ from "jquery";
import 'bootstrap';
import "@jalno/translator";
import { AjaxRequest, Router } from "webuilder";

enum MessageFormat {
	HTML = 'html',
	MARKDOWN = 'markdown',
}

interface ITemplate {
	message_format: MessageFormat,
	subject: {
		value: string | null,
		variables: string[],
	},
	content: {
		value: string,
		variables: string[],
	},
}

interface IResponse {
	status: true,
	data: ITemplate,
}

type SubmitCallback = (subject: string, content: string, message_format: MessageFormat) => void;

export default class TemplateSelector {
	private static $modal: JQuery | undefined = undefined;

	private $modal: JQuery;
	private $form: JQuery;
	private values: {
		subject: { [name: string]: string },
		content: { [name: string]: string }
	} = {
			subject: {},
			content: {},
		}
	private onSubmitCallback: SubmitCallback;
	private template: ITemplate = undefined;
	private _isReply: boolean = false;

	public constructor(private $select: JQuery) {
		this.$modal = this.getModal();
	}

	public run() {
		this.setEvents();
	}

	public isReply(isReply: boolean) {
		this._isReply = isReply;
	}

	public onSubmit(cb: SubmitCallback) {
		this.onSubmitCallback = cb;
	}

	private setEvents() {
		const that = this;
		const $body = $('.modal-body', this.$modal);

		this.$select.on('change', function () {
			const id = $(this).val() as string;

			if (!id) {
				return;
			}
			that.show();
			$(this).prop('disabled', true);

			const getTemplate = () => {
				$body.html(`<div class="alert alert-block alert-info">
				<p>
					<i class="fa fa-spin fa-spinner"></i>
				${t('titles.ticketing.loading')}
				</p>
			</div>`);
				AjaxRequest({
					url: Router.url('userpanel/ticketing/templates/' + id, { ajax: 1 } as any),
					success: (response: IResponse) => {
						$(this).prop('disabled', false);

						$body.html('');
						that.$form = $(`<form id="template-selector-form"></form>`).appendTo($body);
						const $list = $(`<div class="list-group"></div>`).appendTo(that.$form);
						that.template = response.data;

						if (!that._isReply && response.data.subject.value) {
							that.getElement('subject', t('titles.ticketing.templates.subject'), response.data.subject.value, response.data.subject.variables).appendTo($list);
						}

						const $item = that.getElement('content', t('titles.ticketing.templates.content'), response.data.content.value, response.data.content.variables).appendTo($list);
						const $textarea = $('textarea', $item);
						$textarea.css('height', ($textarea.get(0).scrollHeight + 2) + 'px');
						$(window).trigger('resize');
						that.runFormSubmitListener();
						$(this).val('');
					},
					error: () => {
						$(this).prop('disabled', false);
						$body.html(`<div class="alert alert-block alert-danger">
						<p>
							<i class="fa fa-info-circle"></i>
						${t('userpanel.formajax.error')}
						</p>
					</div>`);
						$(this).val('');
					},
				});
			}
			getTemplate();
		});
	}

	private runFormSubmitListener() {
		this.$form.on('submit', (e) => {
			e.preventDefault();

			const content = this.replace(Object.keys(this.values.content), Object.values(this.values.content), this.template.content.value);
			const subject = this.template.subject.value ? this.replace(Object.keys(this.values.subject), Object.values(this.values.subject), this.template.subject.value) : '';

			if (this.onSubmitCallback) {
				this.onSubmitCallback(subject, content, this.template.message_format);
			}

			this.hide()
		});
	}

	private show() {
		this.$modal.modal('show');
	}

	private hide() {
		this.$modal.modal('hide');
	}

	private getModal() {
		if (!TemplateSelector.$modal) {
			TemplateSelector.$modal = $(`<div class="modal modal-lg fade" id="template-selector-modal" tabindex="-1" data-show="true" role="dialog" data-backdrop="static" data-keyboard="false">
			<div class="modal-header">
				<h4 class="modal-title">${t('titiles.ticketing.template')}</h4>
			</div>
			<div class="modal-body">
				
			</div>
			<div class="modal-footer">
				<button type="submit" form="template-selector-form" class="btn btn-success">${t('submit')}</button>
				<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">${t('cancel')}</button>
			</div>
		</div>`);

			TemplateSelector.$modal.on('hide', () => {
				$('.modal-body', TemplateSelector.$modal).html('');
			});
		}

		return TemplateSelector.$modal;
	}

	private getElement(name: 'subject' | 'content', label: string, value: string, variables: string[]): JQuery {
		const that = this;
		let rows = 0;

		const $item = $(`<div class="list-group-item">
		<h4 class="list-group-item-heading">${label}</h4>
		<div class="list-group-item-text">
			<div class="form-group">
				
			</div>
		</div>
	</div>`);
		const $formGroup = $('.form-group', $item);
		const $input = $(
			'subject' === name ?
				`<input type="text" value="${value}" readonly name="${name}" class="form-control">` :
				`<textarea name="${name}" class="form-control" dir="auto" readonly>${value}</textarea>`
		).appendTo($formGroup);

		let $row: JQuery | undefined = undefined;

		for (const variable of variables) {
			if (rows == 0) {
				$row = $(`<div class="row"></div>`).insertBefore($formGroup);
			}

			this.values[name][variable] = variable;

			const $variable = $(`<div class="col-sm-6 col-xs-12">
			<div class="form-group">
				<label class="control-label required">${that.replace(['{{', '}}'], '', variable)}</label>
				<input type="text" value="" required="" name="${variable}" class="form-control">
			</div>
		</div>`).appendTo($row as JQuery);

			$('input', $variable).on('keyup change', function () {
				const originVal = $(this).val() as string;
				let val = that.replace(['{', '}'], '', originVal);
				if (val) {
					that.values[name][variable] = val.trim();
				} else {
					that.values[name][variable] = variable;
				}

				if (originVal != val) {
					$(this).val(val);
				}

				val = that.replace(Object.keys(that.values[name]), Object.values(that.values[name]), value);
				$input.val(val);

				if ('content' === name) {
					$input.css('height', ($input.get(0).scrollHeight + 2) + 'px');
				}
			});

			rows++;

			if (rows == 2) {
				rows = 0;
			}
		}

		return $item;
	}

	private replace(searches: string | Array<string | number>, replaces: string | Array<string | number>, subject: string): string {
		if (typeof searches === 'string') {
			searches = [searches];
		}
		if (typeof replaces === 'string') {
			replaces = [replaces];
		}

		const searchesLength = searches.length;
		const replaceLength = replaces.length - 1;

		for (let i = 0; i < searchesLength; i++) {
			subject = subject.replace(new RegExp(searches[i].toString(), "g"), replaces[Math.min(i, replaceLength)].toString());
		}

		return subject;
	}
}
