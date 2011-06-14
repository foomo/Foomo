<?

// My.Module/views/My/Module/Frontend/<name>.tpl

// tell your ide and your team mates what is going on here

/* @var $view Foomo\MVC\View */
/* @var $model My\Module\Frontend\Model */

?>
<h1>Hello!</h1>
<p><?= $view->escape($model->hello) ?></p>
<p>
	
	
	<!-- building a link yourself -->
	
	<? $href = $view->url('hello', array('world')) ?>
	<a href="<?= $view->escape($href) ?>">Say hello world</a><br>
	
	
	<!-- same thing from the view -->
	
	<?= $view->link('Say hello world', 'hello', array('world')) ?>
	
	
	<!-- same again, but translated -->
	
	<?= $view->link($view->_('SAY_HELLO_WORLD'), 'hello', array('world')) ?>
	
</p>
<!-- some more translation -->
<h2><?= $view->escape($view->_('HELLO')) ?></h2>


<!-- partials -->
<? 

foreach($model->foos as $foo): 
	/* @var $foo My\Module\Foo */
?>
	<?= 
		$view->partial(
			'example',
			// pass named variables into the partial
			array('foo' => $foo)
		) 
	?>
<? endforeach; ?>


<!-- a form example -->

<form 
	method="post" 
	action="<?= $view->escape($view->url('formExample')) ?>"
	>
	<!-- 
		when posting input names have to match 
		controller action parameter names
	-->
	<label>
		<?= $view->escape($view->_('LABEL_FOO')) ?>
	</label>
	<input type="text" name="foo"></input>
	
	<label>
		<?= $view->escape($view->_('LABEL_BAR')) ?>
	</label>	
	<input type="text" name="bar"></input>
	
	<!-- localized submit button --> 
	<input 
		type="submit"
		value="<?= $view->escape($view->_('SUBMIT_FORM_EXAMPLE')) ?>"
	></input>
</form>