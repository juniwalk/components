
/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

class OverlayExtension
{
	#overlay = undefined;
	#trigger = undefined;

	initialize(naja) {
		this.#overlay = document.querySelector(':is(.overlay-wrapper, .app-wrapper, .wrapper) > .overlay');

		if (this.#overlay === undefined) {
			return;
		}

		naja.snippetHandler.addEventListener('afterUpdate', (event) => this.#attach(event.detail.snippet));
		naja.uiHandler.addEventListener('interaction', (event) => {
			this.#trigger = event.detail.element;
		});

		naja.addEventListener('start', (event) => this.#show(event, this.#trigger));
		naja.addEventListener('complete', () => this.#hide());

		this.#attach(document);
	}


	#attach(snippet) {
		snippet.querySelectorAll('[data-overlay]:not(.ajax)')
			.forEach((element) => {
				if (element.matches('button[type=submit]')) {
					element.form.addEventListener('submit', (event) => this.#show(event, element));

				} else {
					element.addEventListener('click', (event) => this.#show(event, element));
				}
			});
	}


	#show(event, element) {
		if (element === undefined) {
			return;
		}

		// ? Remove all shown tooltips from the document so they do not linger stuck in the DOM
		document.querySelectorAll('.tooltip.show').forEach((tooltip) => tooltip.remove());
		element.querySelector('[data-spin] > i')?.classList.add('fa-spin');

		if (!element.hasAttribute('data-overlay')) {
			return;
		}

		setTimeout(() => {
			if (event.defaultPrevented) {
				return;
			}

			this.#fadeIn(this.#overlay);
		}, 0);
	}


	#hide() {
		document.querySelectorAll('[data-spin] > i.fa-spin').forEach(
			(element) => element.classList.remove('fa-spin')
		);

		this.#fadeOut(this.#overlay);
		this.#trigger = undefined;
	}


	#fadeIn(element, speed = 250) {
		element.style.removeProperty('display');
		element.style.opacity = 0;

		this.#animate(speed, (delta) => {
			element.style.opacity = 0 + (delta * 1);
		});
	}


	#fadeOut(element, speed = 250) {
		element.style.opacity = 1;

		this.#animate(speed, (delta) => {
			element.style.opacity = 1 - (delta * 1);

			if (delta >= 1) {
				element.style.display = 'none';
			}
		});
	}


	#animate(duration, callback) {
		let startTime = null;

		function step(timestamp) {
			if (!startTime) startTime = timestamp;

			const elapsed = timestamp - startTime;
			const delta = Math.min(elapsed / duration, 1);

			callback(delta);

			if (delta < 1) {
				requestAnimationFrame(step);
			}
		}

		requestAnimationFrame(step);
	}
}

// ? Auto register the extension in Naja.js
if (typeof naja !== 'undefined') {
	naja?.registerExtension(new OverlayExtension);
}
