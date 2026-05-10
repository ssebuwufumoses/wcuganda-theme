/**
 * Primary navigation behavior.
 *
 * Mobile: a slide-in drawer from the right with a close button, backdrop,
 * focus trap, body-scroll lock, ESC dismissal, and accordion sub-menus.
 * Desktop: inline nav with hover/focus sub-menu disclosure (handled in CSS).
 */
( function () {
	'use strict';

	const NAV_ID = 'site-navigation';
	const BREAKPOINT_PX = 992; // matches $bp-lg
	const BODY_OPEN_CLASS = 'wcu-nav-open';
	const FOCUSABLE_SELECTOR = [
		'a[href]',
		'button:not([disabled])',
		'input:not([disabled])',
		'select:not([disabled])',
		'textarea:not([disabled])',
		'[tabindex]:not([tabindex="-1"])',
	].join( ',' );

	const nav = document.getElementById( NAV_ID );
	if ( ! nav ) {
		return;
	}

	const toggle = nav.querySelector( '.wcu-nav__toggle' );
	const panel = nav.querySelector( '.wcu-nav__panel' );
	const menu = nav.querySelector( '.wcu-nav__menu' );
	const closeBtn = nav.querySelector( '.wcu-nav__close' );
	const backdrop = nav.querySelector( '.wcu-nav__backdrop' );

	if ( ! toggle || ! panel || ! menu ) {
		if ( toggle ) {
			toggle.style.display = 'none';
		}
		return;
	}

	const isDesktop = () => window.matchMedia( `(min-width: ${ BREAKPOINT_PX }px)` ).matches;

	let lastFocused = null;

	const setOpen = ( open ) => {
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		panel.setAttribute( 'data-open', open ? 'true' : 'false' );
		if ( backdrop ) {
			backdrop.setAttribute( 'data-open', open ? 'true' : 'false' );
		}
		document.body.classList.toggle( BODY_OPEN_CLASS, open );

		if ( open ) {
			lastFocused = document.activeElement;
			// Defer so the panel transition can begin before focus shift.
			requestAnimationFrame( () => {
				const target = closeBtn || menu.querySelector( 'a' );
				if ( target ) {
					target.focus();
				}
			} );
		} else if ( lastFocused && typeof lastFocused.focus === 'function' ) {
			lastFocused.focus();
			lastFocused = null;
		}
	};

	setOpen( false );

	toggle.addEventListener( 'click', ( event ) => {
		event.preventDefault();
		const open = toggle.getAttribute( 'aria-expanded' ) !== 'true';
		setOpen( open );
	} );

	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			setOpen( false );
		} );
	}

	if ( backdrop ) {
		backdrop.addEventListener( 'click', () => setOpen( false ) );
	}

	// ESC closes the drawer.
	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
			setOpen( false );
		}
	} );

	// Focus trap: when the drawer is open, keep TAB cycling inside the panel.
	panel.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'Tab' || isDesktop() ) {
			return;
		}
		if ( toggle.getAttribute( 'aria-expanded' ) !== 'true' ) {
			return;
		}
		const focusables = Array.from( panel.querySelectorAll( FOCUSABLE_SELECTOR ) ).filter(
			( el ) => el.offsetParent !== null
		);
		if ( focusables.length === 0 ) {
			return;
		}
		const first = focusables[ 0 ];
		const last = focusables[ focusables.length - 1 ];
		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	// Tapping a real navigation link closes the drawer (not the chevron toggle).
	menu.addEventListener( 'click', ( event ) => {
		if ( isDesktop() ) {
			return;
		}
		const link = event.target.closest( 'a' );
		if ( link && panel.contains( link ) ) {
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
		mq.addListener( handleBreakpointChange );
	}

	// Sub-menu accordions on mobile (chevron toggles the children list).
	const subMenuParents = menu.querySelectorAll( '.menu-item-has-children, .page_item_has_children' );
	subMenuParents.forEach( ( item ) => {
		const link = item.querySelector( ':scope > a' );
		const submenu = item.querySelector( ':scope > .sub-menu, :scope > .children' );
		if ( ! link || ! submenu ) {
			return;
		}

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
			event.stopPropagation();
			const open = subToggle.getAttribute( 'aria-expanded' ) !== 'true';
			subToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			item.classList.toggle( 'is-open', open );
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
