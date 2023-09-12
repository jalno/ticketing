import AddEdit from "./Templates/AddEdit";

export default class Templates
{
	public static initIfNeeded()
	{
		AddEdit.initIfNeeded();
	}

	public static runAutoInsertVariableFor($el: JQuery<HTMLFormElement>)
	{
		const insertVariable = () => {
			const position = Templates.getCursorPosition($el);
			const content = $el.val() as string;
			const variable = `{{${t('titles.ticketing.templates.variable')}}}`;

			$el.val(content.substring(0, position)+variable+content.substring(position)).trigger('change');
			Templates.setCursorPosistion($el, position+variable.length);
		}

		$el.on('click', (e) => {
			if (!e.ctrlKey) {
				return;
			}

			insertVariable();
		});

		$el.on('keydown', (e) => {
			if (e.ctrlKey && e.key == ' ') {
				insertVariable();
			}
		});
	}

	public static getCursorPosition($el: JQuery) {
        const el = $el.get(0) as HTMLFormElement;
        let pos = 0;
        if ('selectionStart' in el) {
            pos = el.selectionStart as number;
        } else if ('selection' in document) {
            el.focus();
            var Sel = (document.selection as any).createRange();
            var SelLength = (document.selection as any).createRange().text.length;
            Sel.moveStart('character', -el.value.length);
            pos = Sel.text.length - SelLength;
        }

        return pos;
    }

	public static setCursorPosistion($el: JQuery<HTMLFormElement>, position: number)
	{
		const el = $el.get(0);
		if('selectionStart' in el) {
            el.selectionStart = position;
            el.selectionEnd = position;
        } else if(el.setSelectionRange) {
            el.setSelectionRange(position, position);
        } else if(el.createTextRange) {
            var range = el.createTextRange();
            range.collapse(true);
            range.moveEnd('character', position);
            range.moveStart('character', position);
            range.select();
        }
	}
}
