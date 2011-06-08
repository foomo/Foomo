
/**
 * popup js
 */
function foomoPop(opn) 
{
	pop=window.open("/foomo/output.php?radPopupContentId="+opn,"RadWindow","width=100,height=100,toolbar=no,status=no,scrollbars=no,menubar=no,location=no,titlebar=no,resizable=yes,directories=no");
	pop.focus();
}

/**
 * toggle an element 
 */
function foomoToggleElement(elId, visibleDisplay)
{
	if (!visibleDisplay) {
		visibleDisplay = 'block';
	}
	el = document.getElementById(elId);
	if (el.style['display'] != 'none' && el.style['display'] != '') {
		el.style['display'] = 'none';
	} else {
		el.style['display'] = visibleDisplay;
	}
}

