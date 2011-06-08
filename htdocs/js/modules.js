function showAllResources()
{
	document.getElementById('hideModulesButton').style['display'] = 'block'; 
	document.getElementById('showModulesButton').style['display'] = 'none'; 
	allResourcesDisplay('table-cell');
}

function hideAllResources()
{
	document.getElementById('hideModulesButton').style['display'] = 'none'; 
	document.getElementById('showModulesButton').style['display'] = 'block'; 
	allResourcesDisplay('none');
}

function allResourcesDisplay(display)
{
	i=0;
	while(resEl = document.getElementById('resourceDisplay_' + i)) {
		resEl.style['display'] = display;
		i++;
	}
}