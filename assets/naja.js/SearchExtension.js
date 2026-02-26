
/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

class SearchExtension
{
	initialize(naja) {
		naja.snippetHandler.addEventListener('afterUpdate', (event) => this.#attach(event.detail.snippet));
		this.#attach(document);
	}


	#attach(snippet) {
		snippet.querySelectorAll('[data-lte-toggle=search]')
			.forEach((element) => {
				element.addEventListener('click', () => this.#toggleSearch());
			});
	}


	#toggleSearch() {
		let form = $('[data-lte-search]').fadeToggle(300);
		let input = $('.form-control', form);

		if (input.is(':visible')) {
			let value = input.val();
			input.focus().val('').val(value);
		}
	}
}
