/**
 * Member profile Share button.
 *
 * Uses the Web Share API on supported devices (mobile + recent desktop
 * browsers); falls back to copying the URL to the clipboard with a
 * brief on-button confirmation.
 */
( function () {
	'use strict';

	const buttons = document.querySelectorAll( '.wcu-folks-share' );
	if ( ! buttons.length ) {
		return;
	}

	const RESET_MS = 2000;

	buttons.forEach( ( button ) => {
		const title = button.getAttribute( 'data-share-title' ) || document.title;
		const url   = button.getAttribute( 'data-share-url' ) || window.location.href;
		const original = button.textContent;

		button.addEventListener( 'click', async () => {
			if ( navigator.share ) {
				try {
					await navigator.share( { title, url } );
					return;
				} catch ( err ) {
					// AbortError when the user dismisses — silently ignore.
					if ( err && err.name !== 'AbortError' ) {
						copyToClipboard( url, button, original );
					}
					return;
				}
			}
			copyToClipboard( url, button, original );
		} );
	} );

	/**
	 * Copy a string to the clipboard and confirm on the button.
	 *
	 * @param {string} text
	 * @param {HTMLButtonElement} button
	 * @param {string} originalText
	 */
	function copyToClipboard( text, button, originalText ) {
		const finish = () => {
			button.textContent = 'Copied!';
			button.disabled = true;
			setTimeout( () => {
				button.textContent = originalText;
				button.disabled = false;
			}, RESET_MS );
		};

		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text ).then( finish ).catch( () => {
				legacyCopy( text );
				finish();
			} );
			return;
		}
		legacyCopy( text );
		finish();
	}

	/**
	 * Legacy copy for browsers without the Clipboard API.
	 *
	 * @param {string} text
	 */
	function legacyCopy( text ) {
		const ta = document.createElement( 'textarea' );
		ta.value = text;
		ta.setAttribute( 'readonly', '' );
		ta.style.position = 'absolute';
		ta.style.left = '-9999px';
		document.body.appendChild( ta );
		ta.select();
		try {
			document.execCommand( 'copy' );
		} catch ( e ) {
			// no-op
		}
		document.body.removeChild( ta );
	}
}() );
