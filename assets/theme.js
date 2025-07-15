
/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2025 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 */

(() => {
	'use strict'

	const setStoredTheme = (theme) => localStorage.setItem('theme', theme);
	const getStoredTheme = () => localStorage.getItem('theme') ?? 'auto';
	const getSystemTheme = () => {
		return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
	};

	const setTheme = (theme) => {
		if (theme === 'auto') {
			theme = getSystemTheme();
		}

		document.documentElement.setAttribute('data-bs-theme', theme);
	}

	const showActiveTheme = (theme, focus = false) => {
		const themeSwitcher = document.querySelector('#bs-theme');

		if (!themeSwitcher) {
			return;
		}

		const activeThemeIcon = document.querySelector('#bs-theme .theme-icon-active');
		const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`);
		const btnToCheck = document.querySelector(`[data-bs-theme-value="${theme}"] i.fa-check`);
		const svgOfActiveBtn = btnToActive.getAttribute('data-bs-theme-icon');

		document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
			const themeIcon = element.getAttribute('data-bs-theme-icon');
			const checkIcon = element.querySelector('i.fa-check');

			activeThemeIcon.classList.remove(themeIcon);
			checkIcon.classList.add('d-none');
			element.classList.remove('active');
		});

		btnToActive.classList.add('active');
		btnToCheck.classList.remove('d-none');
		activeThemeIcon.classList.add(svgOfActiveBtn);

		if (focus) {
			themeSwitcher.focus();
		}
	}

	setTheme(getStoredTheme());

	window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => setTheme(getStoredTheme()));
	window.addEventListener('DOMContentLoaded', () => {
		let theme = getStoredTheme();
		showActiveTheme(theme);

		document.querySelectorAll('[name=recaptcha]').forEach(element => {
			if (theme === 'auto') {
				theme = getSystemTheme();
			}

			element.setAttribute('data-theme', theme);
		});

		document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
			toggle.addEventListener('click', () => {
				const theme = toggle.getAttribute('data-bs-theme-value');
				setStoredTheme(theme);
				setTheme(theme);
				showActiveTheme(theme, true);
			});
		});
	});

})();
