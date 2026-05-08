/**
 * Stats counter — count up from 0 to the target value when a stat scrolls into
 * view. Respects prefers-reduced-motion and only handles purely numeric values.
 */
( function () {
	'use strict';

	const stats = document.querySelectorAll( '[data-wcu-stat]' );
	if ( ! stats.length ) {
		return;
	}

	const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	if ( reduce || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	const DURATION_MS = 1200;

	const animate = ( el ) => {
		const raw = el.getAttribute( 'data-wcu-stat' );
		const target = parseInt( raw, 10 );

		if ( Number.isNaN( target ) ) {
			return;
		}

		// Strip the original number so we can rebuild it with any suffix
		// (e.g. "60+" stays as "60+" once finished). Suffix = anything trailing
		// the leading run of digits in the original string.
		const suffixMatch = raw.match( /^\d+(.*)$/ );
		const suffix = suffixMatch ? suffixMatch[ 1 ] : '';

		const start = performance.now();

		const tick = ( now ) => {
			const elapsed = now - start;
			const progress = Math.min( elapsed / DURATION_MS, 1 );
			// Ease-out cubic for a more natural finish.
			const eased = 1 - Math.pow( 1 - progress, 3 );
			const current = Math.round( target * eased );
			el.textContent = current.toLocaleString() + suffix;

			if ( progress < 1 ) {
				requestAnimationFrame( tick );
			}
		};

		requestAnimationFrame( tick );
	};

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					animate( entry.target );
					observer.unobserve( entry.target );
				}
			} );
		},
		{ threshold: 0.4 }
	);

	stats.forEach( ( el ) => observer.observe( el ) );
}() );
