import 'bootstrap';
import 'webuilder';
import { AjaxRequest, Router } from "webuilder";
import { formAjax } from "webuilder/formAjax";

interface ILabel {
	id?: number;
	title: string;
	color: string;
	description?: string;
}

declare const packages_ticketing_labels: ILabel[] | undefined;

export default class Labels {
	public static initIfNeeded() {
		Labels.$container = $('.ticket-labels');

		if (Labels.$container.length) {
			Labels.init();
		}
	}

	private static $container: JQuery;
	private static ticketId: number;
	private static preventClosePopover = false;
	private static selectedLabels: number[] = [];
	private static permissions: {can_search: boolean, can_add: boolean, can_delete: boolean};

	private static init() {
		Labels.ticketId = Labels.$container.data('ticket');

		Labels.runEditLabelsListener();
		Labels.runDeleteLabelsListener();
	}

	private static runEditLabelsListener() {
		const $btn = $('.ticket-label-group .btn-edit-labels');

		if (!$btn.length || typeof packages_ticketing_labels === 'undefined') {
			return;
		}

		Labels.permissions = $btn.data('permissions');

		Labels.selectedLabels = $btn.data('labels');

		let content = `<div class="select-container">
		<div class="label-popover-header">
			<div class="label-popover-title">
				<button class="btn btn-link btn-xs btn-dismiss-popover"><i class="fa fa-times"></i></button>
				${t('titles.ticketing.labels.assign')}
			</div>
			<div class="input-group">
				<span class="input-group-addon"><i class="fa fa-search"></i></span>
				<input name="search" type="text" />
			</div>
		</div>
		<div class="label-popover-body"><div class="list-group"></div></div>
		<div class="label-popover-footer">`;

		if (Labels.permissions.can_add) {
			content += `<button class="btn btn-block btn-link btn-add-label" href="#">
				<div class="btn-icons"><i class="fa fa-plus"></i></div>
			${t('titles.ticketing.labels.add')}
			</button>`
		}
		if (Labels.permissions.can_search) {
			content += `<a class="btn btn-block btn-link" target="_blank" href="${Router.url('userpanel/settings/ticketing/labels')}">
				<div class="btn-icons"><i class="fa fa-tags"></i></div>
			${t('titles.ticketing.labels.manage')}
			</a>`
		}

		content += `<button class="btn btn-block btn-success btn-update">
				<div class="btn-icons"><i class="fa fa-check-square-o"></i></div>
			${t('ticket.update')}
			</button>
		</div>
	</div>`;

		if (Labels.permissions.can_search) {
			content += `<div class="add-container hide">
			<div class="label-popover-header">
				<div class="label-popover-title">
					<button class="btn btn-link btn-xs btn-dismiss-popover"><i class="fa fa-times"></i></button>
					${t('titles.ticketing.labels.add')}
					<button class="btn btn-link btn-xs btn-back"><i class="fa fa-angle-${Translator.isRTL() ? 'left' : 'right'}"></i></button>
				</div>
			</div>
			<div class="label-popover-body">
				<form id="popover-add-labels-form">
					<div class="form-group">
						<label class="control-label required">${t('titles.ticketing.labels.title')}</label>
						<input type="text" value="" required name="title" class="form-control">
					</div>
					<div class="form-group">
						<label class="control-label required">${t('titles.ticketing.labels.color')}</label>
						<input type="color" value="" required name="color" class="form-control">
					</div>
				</form>
			</div>
			<div class="label-popover-footer">
				<button class="btn btn-block btn-success btn-submit" type="submit" form="popover-add-labels-form">
					<div class="btn-icons"><i class="fa fa-check-square-o"></i></div>
				${t('submit')}
				</button>
			</div>
		</div>`;
		}

		$btn.popover({
			html: true,
			container: 'body',
			content: content,
			placement: 'bottom',
			trigger: 'manual',
		}).on('inserted.bs.popover', function() {
			const $container = $(this).data("bs.popover").tip();
			$container.addClass('label-popover');
			$('.select-container .label-popover-body .list-group', $container).append(Labels.generateLabelList(packages_ticketing_labels));
			Labels.setEventsForPopover($container);
		});

		$(document).on('click', (e) => {
			if (Labels.preventClosePopover) {
				return;
			}

			const $el = $(e.target);

			if ($el.is($btn)) {
				$btn.popover('show');
			} else if (
				!$el.parents('.popover').length ||
				$el.parents('.btn-dismiss-popover').length ||
				$el.hasClass('btn-dismiss-popover')
			) {
				$btn.popover('hide');
			}
		});
	}

	private static generateLabelList (labels: ILabel[]): JQuery[] {
		const elements: JQuery[] = [];

		for (const label of labels) {
			const selected = Labels.selectedLabels.indexOf(label.id) > -1 ? ' selected' : '';

			const $el = $(`<a href="#" class="list-group-item${selected}">
			<h5 class="list-group-item-heading">
				<span class="badge" style="background-color: ${label.color}"></span>
				${label.title}
			</h5>
		${label.description ? `<p class="list-group-item-text">${label.description}</p>` : ''}
		</a>`);

			$el.data('label', label);

			$el.on('click', (e) => {
				e.preventDefault();
				const index = Labels.selectedLabels.indexOf(label.id);
				if (index > -1) {
					$el.removeClass('selected');
					Labels.selectedLabels.splice(index, 1);
				} else {
					$el.addClass('selected');
					Labels.selectedLabels.push(label.id);
				}
			});

			elements.push($el);
		}

		return elements;
	};

	private static setEventsForPopover($container: JQuery) {
		Labels.setSelectPopoverEvents($container);
		Labels.setAddPopoverEvents($container);
		
	}

	private static setSelectPopoverEvents($container: JQuery) {
		const $selectContainer = $('.select-container', $container);
		const $updateBtn = $('.btn-update', $selectContainer);
		const $input = $('.label-popover-header input[name=search]', $selectContainer);
		const $labels = $('.label-popover-body .list-group .list-group-item');
		const $goToAddContainerBtn = $('.btn-add-label', $selectContainer);
		const $addContainer = $('.add-container', $container);

		const resetUpdateButton = () => {
			Labels.preventClosePopover = false;
			$updateBtn.prop('disabled', false);
			$('i', $updateBtn).attr('class', 'fa fa-check-square');
			$goToAddContainerBtn.prop('disabled', false);
		}

		$updateBtn.on('click', function(e) {
			Labels.preventClosePopover = true;
			$(this).prop('disabled', true);
			$('i', this).attr('class', 'fa fa-spinner fa-spin');
			$goToAddContainerBtn.prop('disabled', true);

			AjaxRequest({
				url: Router.url(`userpanel/ticketing/edit/${Labels.ticketId}`, {ajax: 1} as any),
				method: 'POST',
				data: {
					labels: Labels.selectedLabels.join(','),
				},
				success: (response) => {
					resetUpdateButton();

					$.growl.notice({
						title: t('userpanel.success'),
						message: t('userpanel.formajax.success'),
					});
					Labels.setLabels(response?.ticket?.labels || []);
				},

				error: () => {
					resetUpdateButton();

					$.growl.error({
						title: t('error.fatal.title'),
						message: t('userpanel.formajax.error'),
					});
				}
			})
		});

		$input.on('keyup', () => {
			const keyword = $input.val() as string;
			if (keyword.length) {
				$labels.each(function() {
					const label = $(this).data('label') as ILabel;
					if (label.title.search(keyword) > -1) {
						$(this).show();
					} else {
						$(this).hide();
					}
				})
			} else {
				$labels.show();
			}
		});


		if ($goToAddContainerBtn.length) {
			$goToAddContainerBtn.on('click', (e) => {
				e.preventDefault();

				$selectContainer.addClass('hide');
				$addContainer.removeClass('hide');
			});
		}
	}

	private static setAddPopoverEvents($container: JQuery) {
		const $addContainer = $('.add-container', $container);
		if (!$addContainer.length) {
			return;
		}

		const $selectContainer = $('.select-container', $container);
		const $submitBtn = $('.label-popover-footer .btn-submit', $addContainer);
		const $labelsListGroup = $('.label-popover-body .list-group', $selectContainer);
		const $backToSelectContainerBtn = $('.label-popover-header .label-popover-title .btn-back', $addContainer);

		$backToSelectContainerBtn.on('click', (e) => {
			e.preventDefault();

			$addContainer.addClass('hide');
			$selectContainer.removeClass('hide');
		});

		const resetBtn = () => {
			$submitBtn.prop('disabled', false);
			$('i', $submitBtn).attr('class', 'fa fa-check-sqaure-o');
			$backToSelectContainerBtn.prop('disabled', false);
			Labels.preventClosePopover = false;
		};

		$('#popover-add-labels-form', $addContainer).on('submit', function(e) {
			e.preventDefault();
			$submitBtn.prop('disabled', true);
			$('i', $submitBtn).attr('class', 'fa fa-spinner fa-spin');
			$backToSelectContainerBtn.prop('disabled', true);
			Labels.preventClosePopover = true;
			$('.has-error input').inputMsg('reset');

			$(this).formAjax({
				url: Router.url('userpanel/settings/ticketing/labels/add', {ajax: 1} as any),
				method: 'POST',
				success: (response) => {
					resetBtn();
					$.growl.notice({
						title: t('userpanel.success'),
						message: t('userpanel.formajax.success'),
					});
					$labelsListGroup.html('');
					$labelsListGroup.append(Labels.generateLabelList([...[response.label], ...packages_ticketing_labels]));
					$addContainer.addClass('hide');
					$selectContainer.removeClass('hide');
					(this as HTMLFormElement).reset();
				},
				error: (response) => {
					resetBtn();
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
				}
			});
		});
	}

	private static runDeleteLabelsListener() {
		const $btns = $('.label .btn-delete', Labels.$container);

		if (!$btns.length) {
			return;
		}

		$btns.each(function() {
			const label = new Labels(Labels.ticketId, Labels.permissions, undefined, $(this));
		});
	}

	private static setLabels(labels: ILabel[]) {
		if (labels.length) {
			$('.ticket-labels').html('');
			for (const label of labels) {
				const obj = new Labels(Labels.ticketId, Labels.permissions, label);
			}
		} else {
			$('.ticket-labels').html(`<p class="text-muted">${t('titles.ticketing.labels.none')}</p>`);
		}
	}

	private static removeSelected(id: number) {
		const index = Labels.selectedLabels.indexOf(id);

		if (index > -1) {
			Labels.selectedLabels.splice(index, 1);

			$('.popover-content .select-container .label-popover-body .list-group .list-group-item.selected').each(function() {
				const label = $(this).data('label') as ILabel;

				if (id == label.id) {
					$(this).removeClass('selected');
					return false;
				}
			});
		}
	}

	private id: number;
	public constructor(private ticketId: number, private permissions: {can_delete: boolean}, private label?: ILabel, private $btn?: JQuery) {
		if (label) {
			this.create();
		} else if ($btn) {
			this.id = this.$btn.data('id');
	
			this.runDeleteListener();
		}
	}

	private runDeleteListener() {
		const activeBtns = ($loadingBtn?: JQuery) => {
			const $btns = $('.label .btn-delete', Labels.$container);
			$btns.prop('disabled', false);
			$btns.removeClass('disabled');

			if ($loadingBtn) {
				$('i', $loadingBtn).attr('class', 'fa fa-times');
			}
		}

		let confirmed = false;
		let confirmTimeout = undefined;

		const $icon = $('i', this.$btn);
		this.$btn.on('click', (e) => {
			e.preventDefault();

			if (!confirmed) {
				confirmed = true;
				$icon.attr('class', 'fa fa-info-circle').tooltip({
					title: 'برای تایید حذف مجددا کلیک کنید',
					trigger: 'manual',
					container: 'body',
				}).tooltip('show');
				confirmTimeout = setTimeout(() => {
					confirmed = false;
					$icon.attr('class', 'fa fa-times').tooltip('destroy');
				}, 3000);

				return false;
			}

			if (confirmTimeout) {
				clearTimeout(confirmTimeout);	
			}

			const $btns = $('.label .btn-delete', Labels.$container);
			$btns.prop('disabled', true);
			$btns.addClass('disabled');

			$icon.attr('class', 'fa fa-spinner fa-spin');

			AjaxRequest({
				url: Router.url(`userpanel/ticketing/edit/${this.ticketId}`, {ajax: 1} as any),
				method: 'POST',
				data: {
					'delete-labels': this.id,
				},
				success: () => {
					$icon.tooltip('destroy');
					this.$btn.parents('.ticket-label').remove();
					Labels.removeSelected(this.id);

					$.growl.notice({
						title: t('userpanel.success'),
						message: t('userpanel.formajax.success'),
					});

					if (!$('.ticket-labels .ticket-label').length) {
						$('.ticket-labels').html(`<p class="text-muted">${t('titles.ticketing.labels.none')}</p>`);
					}
					activeBtns();
				},

				error: () => {
					activeBtns();

					$.growl.error({
						title: t('error.fatal.title'),
						message: t('userpanel.formajax.error'),
					});
				}
			});
		});
	}

	private create() {
		let classNames = 'label ticket-label';
        const textColor = this.getLabelTextClass(this.label.color);
        let title = '';

        if (this.label.description) {
            classNames += ' tooltips';
            title = ` title="${this.label.description}"`;
        }

        const $el = $(`<span class="${classNames}"${title} style="background-color: ${this.label.color};">
		${this.permissions.can_delete ? `<a href="#" class="btn btn-link btn-xs btn-delete ${textColor}"><i class="fa fa-times"></i></a>` : ''}
		<a href="${Router.url('userpanel/ticketing', {labels: this.label.id} as any)}" class="btn btn-link btn-xs ${textColor}">${this.label.title}</a>
	</span>`);

		this.id = this.label.id;
		
		if (this.permissions.can_delete) {
			this.$btn = $('.btn-delete', $el);
			this.runDeleteListener();
		}

		$el.appendTo($('.ticket-labels'));
	}

	private rgbFromHex(hex: string) {
		const cleanHex = hex.replace('#', '');
		const rgb =
			cleanHex.length === 3
			? cleanHex.split('').map((val) => val + val)
			: cleanHex.match(/[\da-f]{2}/gi);
		const [r, g, b] = rgb.map((val) => parseInt(val, 16));
		return [r, g, b];
	}

	private toSrgb(value: number) {
		const normalized = value / 255;
		return normalized <= 0.03928 ? normalized / 12.92 : ((normalized + 0.055) / 1.055) ** 2.4;
	}

	private relativeLuminance(rgb: number[]) {
		// WCAG 2.1 formula: https://www.w3.org/TR/WCAG21/#dfn-relative-luminance
		// -
		// WCAG 3.0 will use APAC
		// Using APAC would be the ultimate goal, but was dismissed by engineering as of now
		// See https://gitlab.com/gitlab-org/gitlab-ui/-/merge_requests/3418#note_1370107090
		return 0.2126 * this.toSrgb(rgb[0]) + 0.7152 * this.toSrgb(rgb[1]) + 0.0722 * this.toSrgb(rgb[2]);
	}

	private getLabelTextClass(backgroundColor: string) {
		let color = [];
		const lightColor = this.rgbFromHex('#FFFFFF');
		const darkColor = this.rgbFromHex('#1f1e24');

		if (!backgroundColor.startsWith('#')) {
			throw new Error('Bad color format');
		}

		color = this.rgbFromHex(backgroundColor);

		const luminance = this.relativeLuminance(color);
		const lightLuminance = this.relativeLuminance(lightColor);
		const darkLuminance = this.relativeLuminance(darkColor);

		const contrastLight = (lightLuminance + 0.05) / (luminance + 0.05);
		const contrastDark = (luminance + 0.05) / (darkLuminance + 0.05);

		// Using a threshold contrast of 2.4 instead of 3
		// as this will solve weird color combinations in the mid tones
		return contrastLight >= 2.4 || contrastLight > contrastDark
			? 'label-text-light'
			: 'label-text-dark';
	}
}
