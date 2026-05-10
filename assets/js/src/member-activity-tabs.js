/**
 * Member profile WordPress.org tabs.
 *
 * Each tab in the sidebar (Activity / Courses / Photos / Favorites /
 * Translations) reveals a dedicated panel with that section's full
 * data — same layout pattern as wp.org's profile tabs. Tabs and
 * panels are rendered server-side; this script only handles the
 * show/hide + active-state toggling.
 */
( function () {
	'use strict';

	const wraps = document.querySelectorAll( '[data-wcu-folks-tabs]' );
	if ( ! wraps.length ) {
		return;
	}

	wraps.forEach( ( wrap ) => {
		const tabs = wrap.querySelectorAll( '.wcu-folks-tabs__tab' );
		const panels = wrap.querySelectorAll( '[data-wcu-tab-panel]' );

		if ( ! tabs.length || ! panels.length ) {
			return;
		}

		const showPanel = ( target ) => {
			tabs.forEach( ( tab ) => {
				const isActive = tab.getAttribute( 'data-wcu-tab' ) === target;
				tab.classList.toggle( 'is-active', isActive );
				tab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
			} );
			panels.forEach( ( panel ) => {
				const isActive = panel.getAttribute( 'data-wcu-tab-panel' ) === target;
				panel.hidden = ! isActive;
				panel.classList.toggle( 'is-active', isActive );
			} );
		};

		tabs.forEach( ( tab ) => {
			tab.addEventListener( 'click', ( event ) => {
				event.preventDefault();
				const target = tab.getAttribute( 'data-wcu-tab' );
				if ( target ) {
					showPanel( target );
				}
			} );
		} );
	} );
}() );
