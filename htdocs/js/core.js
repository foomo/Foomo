function hideMenu()
{
	hideLink = document.getElementById('hideMenuLink');
	hideLink.style['display'] = 'none';
	showLink = document.getElementById('showMenuLink');
	showLink.style['display'] = 'block';
	setMenu(0, 'none');
}

function setMenu(width, display)
{
	headLine = document.getElementById('headLine');
	menuContainer = document.getElementById('menuContainer');
	contentContainer = document.getElementById('contentContainer');
	menuContainer.style['width'] = width + 'px';
	headLine.style['display'] = menuContainer.style['display'] = display;
	contentContainer.style['left'] = (width + 60) + 'px';
}

function showMenu()
{
	hideLink = document.getElementById('hideMenuLink');
	hideLink.style['display'] = 'block';
	showLink = document.getElementById('showMenuLink');
	showLink.style['display'] = 'none';
	setMenu(200, 'block');

}