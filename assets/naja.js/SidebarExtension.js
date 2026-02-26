
/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

class SidebarExtension
{
	#classClose = 'sidebar-collapse';
	#classOpen = 'sidebar-open';
	#breakPoint = 992;

	initialize(naja) {
		if (!document.body.classList.contains('sidebar-persist')) {
			return;
		}

		naja.snippetHandler.addEventListener('afterUpdate', (event) => this.#attach(event.detail.snippet));
		this.#attach(document);

		window.addEventListener('resize', () => this.#loadState());
		this.#loadState();
	}


	#attach(snippet) {
		snippet.querySelectorAll('[data-lte-toggle=sidebar]')
			.forEach((element) => {
				element.addEventListener('click', () => this.#saveState());
				element.style.display = 'block';
			});
	}


	#saveState() {
		let bodyClass = document.body.classList;
		let status = bodyClass.contains(this.#classClose)
			? this.#classClose
			: this.#classOpen;

		localStorage.setItem('lte-sidebar', status);
	}


	#loadState() {
		if (window.innerWidth <= this.#breakPoint) {
			return;
		}

		let bodyClass = document.body.classList;
		let status = localStorage.getItem('lte-sidebar');

		if (status) {
			bodyClass.remove(this.#classOpen, this.#classClose);
			bodyClass.add(status);
		}
	}
}

// ? Auto register the extension in Naja.js
if (typeof naja !== 'undefined') {
	naja?.registerExtension(new SidebarExtension);
}
