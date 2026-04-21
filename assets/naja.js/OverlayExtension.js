
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

		snippet.querySelectorAll('[data-download-fetch]')
			.forEach((element) => {
				element.addEventListener('click', (event) => this.#handleDownloadFetch(event, element));
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


	async #handleDownloadFetch(event, element) {
		event.preventDefault();

		if (element.hasAttribute('data-overlay')) {
			element.querySelector('[data-spin] > i')?.classList.add('fa-spin');
			this.#fadeIn(this.#overlay);
		}

		try {
			const response = await fetch(element.href);

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const blob = await response.blob();
			const url = window.URL.createObjectURL(blob);
			const link = document.createElement('a');
			link.href = url;
			link.download = this.#getFilenameFromResponse(response);

			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
			window.URL.revokeObjectURL(url);

		} catch (error) {
			console.error('Download failed:', error);

		} finally {
			this.#hide();
		}
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


	#getFilenameFromResponse(response) {
		const disposition = response.headers.get('Content-Disposition');
		if (!disposition) {
			return 'download';
		}

		const utf8Match = disposition.match(/filename\*\s*=\s*UTF-8''([^;\r\n]+)/i);
		if (utf8Match && utf8Match[1]) {
			try {
				return decodeURIComponent(utf8Match[1].trim());
			} catch {
				return utf8Match[1].trim();
			}
		}

		const quotedMatch = disposition.match(/filename\s*=\s*"([^"]+)"/i);
		if (quotedMatch && quotedMatch[1]) {
			return quotedMatch[1].trim();
		}

		const plainMatch = disposition.match(/filename\s*=\s*([^;\r\n]+)/i);
		if (plainMatch && plainMatch[1]) {
			return plainMatch[1].trim();
		}

		return 'download';
	}
}

// ? Auto register the extension in Naja.js
if (typeof naja !== 'undefined') {
	naja?.registerExtension(new OverlayExtension);
}
