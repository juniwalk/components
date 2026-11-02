
/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

class SearchExtension
{
	#form = undefined;
	#visible = undefined;
	#animation = undefined;

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
		let form = document.querySelector('[data-lte-search]');

		if (!form || !this.#fadeToggle(form, 300)) {
			return;
		}

		let input = form.querySelector('.form-control');
		let value = input?.value;

		input?.focus();

		if (input) {
			input.value = '';
			input.value = value;
		}
	}


	#fadeToggle(element, duration) {
		if (this.#form !== element) {
			this.#animation?.cancel();
			this.#animation = undefined;
			this.#form = element;
		}

		let currentOpacity = Number.parseFloat(getComputedStyle(element).opacity);
		let isVisible = this.#animation
			? this.#visible
			: element.getClientRects().length > 0 && getComputedStyle(element).display !== 'none';

		this.#visible = !isVisible;

		this.#animation?.cancel();
		this.#animation = undefined;

		if (this.#visible) {
			element.style.removeProperty('display');

			if (getComputedStyle(element).display === 'none') {
				element.style.display = 'block';
			}
		}

		let opacity = Number.parseFloat(getComputedStyle(element).opacity);
		let animation = element.animate(
			this.#visible ? [{ opacity: 0 }, { opacity }] : [{ opacity: currentOpacity }, { opacity: 0 }],
			duration
		);

		this.#animation = animation;

		animation.addEventListener('finish', () => {
			if (this.#animation !== animation) {
				return;
			}

			if (!this.#visible) {
				element.style.display = 'none';
			}

			this.#animation = undefined;
		}, { once: true });

		return this.#visible;
	}
}

// ? Auto register the extension in Naja.js
if (typeof naja !== 'undefined') {
	naja?.registerExtension(new SearchExtension);
}
