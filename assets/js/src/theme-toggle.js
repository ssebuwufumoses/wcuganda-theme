/**
 * Theme toggle — flips between dark (default) and light by setting
 * data-theme="light" on the <html> element. Persisted in localStorage,
 * respects prefers-color-scheme on first visit.
 *
 * Loaded inline (no defer) so the theme is applied before paint, avoiding
 * a flash of wrong theme.
 */
( function () {
	'use strict';

	const STORAGE_KEY = 'wcu-theme';
	const root = document.documentElement;

	const apply = ( theme ) => {
		if ( 'light' === theme ) {
			root.setAttribute( 'data-theme', 'light' );
		} else {
			root.removeAttribute( 'data-theme' );
		}
		document.querySelectorAll( '[data-wcu-theme-toggle]' ).forEach( ( btn ) => {
			btn.setAttribute( 'aria-pressed', String( 'light' === theme ) );
			btn.setAttribute(
				'aria-label',
				'light' === theme ? 'Switch to dark theme' : 'Switch to light theme'
			);
		} );
	};

	const stored = ( () => {
		try {
			return localStorage.getItem( STORAGE_KEY );
		} catch ( e ) {
			return null;
		}
	} )();

	const initial =
		stored ||
		( window.matchMedia && window.matchMedia( '(prefers-color-scheme: light)' ).matches
			? 'light'
			: 'dark' );

	apply( initial );

	document.addEventListener( 'click', ( event ) => {
		const target = event.target.closest( '[data-wcu-theme-toggle]' );
		if ( ! target ) {
			return;
		}
		event.preventDefault();
		const next =
			'light' === root.getAttribute( 'data-theme' ) ? 'dark' : 'light';
		try {
			localStorage.setItem( STORAGE_KEY, next );
		} catch ( e ) {
			// no-op when storage is unavailable (private mode, etc.)
		}
		apply( next );
	} );
}() );
