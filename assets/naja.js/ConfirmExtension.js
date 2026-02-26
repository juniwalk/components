
/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

class ConfirmExtension
{
	initialize(naja) {
		naja.uiHandler.addEventListener('interaction', (event) => this.#onClick(event, event.detail.element));
		naja.snippetHandler.addEventListener('afterUpdate', (event) => this.#attach(event.detail.snippet));
		this.#attach(document);
	}


	#attach(snippet) {
		// ? Fallback attachment to non-AJAX links which are not handled through Naja
		snippet.querySelectorAll('[data-confirm]:not(.ajax)')
			.forEach((element) => {
				element.addEventListener('click', (event) => this.#onClick(event, element));
			});
	}


	#onClick(event, element) {
		let question = element.dataset.confirm ?? null;

		if (question && !window.confirm(question)) {
			event.preventDefault();
		}
	}
}

// ? Auto register the extension in Naja.js
if (typeof naja !== 'undefined') {
	naja?.registerExtension(new ConfirmExtension);
}
