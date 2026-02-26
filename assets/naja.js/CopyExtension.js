
/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

class CopyExtension
{
	initialize(naja) {
		naja.snippetHandler.addEventListener('afterUpdate', (event) => this.#attach(event.detail.snippet));
		this.#attach(document);
	}


	#attach(snippet) {
		snippet.querySelectorAll('[data-copy]')
			.forEach((element) => {
				element.addEventListener('click', (event) => this.#onClick(event, element));
			});
	}


	#onClick(event, element) {
		let clone = document.createElement('textarea');
		let value = element.dataset.copy.trim();

		if (!value) {
			value = element.innerText.trim();
		}

		document.body.appendChild(clone);
		clone.value = value;
		clone.select();
		document.execCommand('copy');
		document.body.removeChild(clone);
	}
}
