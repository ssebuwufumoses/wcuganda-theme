/**
 * Primary navigation behavior.
 *
 * Handles toggling the mobile menu, ARIA state, focus management, ESC-to-close,
 * outside-click dismissal, sub-menu accordions on mobile, and a header
 * shadow when the page is scrolled.
 */
( function () {
	'use strict';

	const NAV_ID = 'site-navigation';
	const BREAKPOINT_PX = 992; // matches $bp-lg
	const BODY_OPEN_CLASS = 'wcu-nav-open';

	const nav = document.getElementById( NAV_ID );
	if ( ! nav ) {
		return;
	}

	const toggle = nav.querySelector( '.wcu-nav__toggle' );
	const menu = nav.querySelector( '.wcu-nav__menu' );

	if ( ! toggle || ! menu ) {
		if ( toggle ) {
			toggle.style.display = 'none';
		}
		return;
	}

	/**
	 * Whether the viewport is currently desktop-width.
	 */
	const isDesktop = () => window.matchMedia( `(min-width: ${ BREAKPOINT_PX }px)` ).matches;

	/**
	 * Open or close the mobile menu.
	 *
	 * @param {boolean} open Whether the menu should be open.
	 */
	const setOpen = ( open ) => {
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		menu.setAttribute( 'data-open', open ? 'true' : 'false' );
		document.body.classList.toggle( BODY_OPEN_CLASS, open );
	};

	// Initialize closed.
	setOpen( false );

	// Toggle on button click.
	toggle.addEventListener( 'click', ( event ) => {
		event.preventDefault();
		const open = toggle.getAttribute( 'aria-expanded' ) !== 'true';
		setOpen( open );
		if ( open ) {
			const firstLink = menu.querySelector( 'a' );
			if ( firstLink ) {
				firstLink.focus();
			}
		}
	} );

	// Close on ESC.
	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
			setOpen( false );
			toggle.focus();
		}
	} );

	// Close on outside click (mobile only).
	document.addEventListener( 'click', ( event ) => {
		if ( isDesktop() ) {
			return;
		}
		if ( toggle.getAttribute( 'aria-expanded' ) !== 'true' ) {
			return;
		}
		if ( ! nav.contains( event.target ) ) {
			setOpen( false );
		}
	} );

	// Reset state when crossing the desktop breakpoint.
	const mq = window.matchMedia( `(min-width: ${ BREAKPOINT_PX }px)` );
	const handleBreakpointChange = ( e ) => {
		if ( e.matches ) {
			setOpen( false );
		}
	};
	if ( typeof mq.addEventListener === 'function' ) {
		mq.addEventListener( 'change', handleBreakpointChange );
	} else if ( typeof mq.addListener === 'function' ) {
		// Safari < 14 fallback.
		mq.addListener( handleBreakpointChange );
	}

	// Sub-menu accordions on mobile, hover/focus on desktop (handled in CSS).
	const subMenuParents = menu.querySelectorAll( '.menu-item-has-children, .page_item_has_children' );
	subMenuParents.forEach( ( item ) => {
		const link = item.querySelector( ':scope > a' );
		const submenu = item.querySelector( ':scope > .sub-menu, :scope > .children' );
		if ( ! link || ! submenu ) {
			return;
		}

		// Inject a dedicated toggle button so the parent link still navigates
		// while the chevron triggers the sub-menu on mobile.
		const subToggle = document.createElement( 'button' );
		subToggle.type = 'button';
		subToggle.className = 'wcu-nav__sub-toggle';
		subToggle.setAttribute( 'aria-expanded', 'false' );
		subToggle.setAttribute( 'aria-label', link.textContent.trim() );
		subToggle.innerHTML = '<span class="screen-reader-text">Toggle</span>';
		link.insertAdjacentElement( 'afterend', subToggle );

		subToggle.addEventListener( 'click', ( event ) => {
			if ( isDesktop() ) {
				return;
			}
			event.preventDefault();
			const open = subToggle.getAttribute( 'aria-expanded' ) !== 'true';
			subToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			item.classList.toggle( 'is-open', open );
			submenu.style.display = open ? 'block' : 'none';
		} );
	} );

	// Header shadow on scroll.
	const header = document.getElementById( 'masthead' );
	if ( header ) {
		const onScroll = () => {
			header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
		};
		onScroll();
		window.addEventListener( 'scroll', onScroll, { passive: true } );
	}
}() );
