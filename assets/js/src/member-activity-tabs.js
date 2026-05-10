/**
 * Member profile activity tabs.
 *
 * Filters the wp.org-scraped activity feed by category. Tabs are rendered
 * server-side (only categories with items are emitted), so this script
 * only handles selection state + show/hide of the rows.
 */
( function () {
	'use strict';

	const wraps = document.querySelectorAll( '[data-wcu-activity-tabs]' );
	if ( ! wraps.length ) {
		return;
	}

	wraps.forEach( ( wrap ) => {
		const tabs = wrap.querySelectorAll( '.wcu-folks-activity__tab' );
		const items = wrap.querySelectorAll( '.wcu-folks-activity__item' );
		const empty = wrap.querySelector( '.wcu-folks-activity__empty' );

		if ( ! tabs.length || ! items.length ) {
			return;
		}

		const apply = ( category ) => {
			let shown = 0;
			items.forEach( ( item ) => {
				const matches = category === 'all' || item.getAttribute( 'data-wcu-activity-cat' ) === category;
				item.hidden = ! matches;
				if ( matches ) {
					shown++;
				}
			} );
			if ( empty ) {
				empty.hidden = shown !== 0;
			}
		};

		tabs.forEach( ( tab ) => {
			tab.addEventListener( 'click', ( event ) => {
				event.preventDefault();
				const target = tab.getAttribute( 'data-wcu-tab' );
				if ( ! target ) {
					return;
				}
				tabs.forEach( ( other ) => {
					const isActive = other === tab;
					other.classList.toggle( 'is-active', isActive );
					other.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
				} );
				apply( target );
			} );
		} );
	} );
}() );
