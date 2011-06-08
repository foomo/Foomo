<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>
<?= $view->partial('header') ?>

<?= $view->partial('menu') ?>

<h2>Advanced invalidation for <?= $model->currentResourceName ?></h2>
<?= $view->partial('resourceAnnotation') ?>
<?= $view->partial('resourcePropertiesDefinitions', array('resourceName' => $model->currentResourceName)) ?>


<form method="post" action="<?= $view->url('advancedInvalidation', array($model->currentResourceName)) ?>">

	<label>Invalidation policy</label>
	<br>
	<p>
		<? foreach (array('POLICY_INSTANT_REBUILD' => 'rebuild', 'POLICY_INVALIDATE' => 'invalidate', 'POLICY_DELETE' => 'delete') as $const => $label): ?>
		<? $constValue = constant('Foomo\Cache\Invalidator::' . $const) ?>
			<span title="<?= 'Foomo\Cache\Invalidator::' . $const ?>"><?= $label ?></span> <input <?= ($model->currentInvalidationPolicy == $constValue) ? 'checked' : '' ?> type="radio" name="invalidationPolicy" value="<?= $constValue ?>">
		<? endforeach; ?>
		</p>

		
		<div id="expression">
			<label>Expression</label>
			<div id="expressionAreaContainer">
				<div>
					<textarea name="expressionString" cols="80" rows="10"><?= $view->escape($model->advancedInvalidationUserExpressionString) ?></textarea>
				</div>
			<? if (!empty($model->advancedInvalidationUserExpressionString) && !is_null($model->advancedInvalidationUserExpression)): ?>
				<div id="interpretedExpressionContainer">
					<p>Interpreted expression</p>
					<p>
					<? highlight_string('<? ' . $model->advancedInvalidationUserExpressionString . '; ?>') ?>
					</p>
				</div>
			<? endif; ?>
			<? if (!empty($model->advancedInvalidationUserExpressionError)): ?>

						<p><i>Your expression could not be parsed ...</i></p>
						<div class="userExpressionError"><?= $view->escape(var_export($model->advancedInvalidationUserExpressionError, true)) ?></div>
			<? endif; ?>

			<? if (!empty($model->advancedInvalidationUserExpressionInterpretationString)): ?>
							<div class="userExpressionError">
								<p>Compiled expression</p>
								<p>
					<?= $view->escape($model->advancedInvalidationUserExpressionInterpretationString) ?>

						</p>
					</div>
			<? endif; ?>
						</div>
						<div id="expressionExampleContainer">
							<p>Expression example:</p>
			<?
							highlight_string("<?
Expr::groupAnd(
	 Expr::idEq('12345'),
	 Expr::idNe('54321'),
	 Expr::statusValid(),
	 Expr::groupOr(
				Expr::propEq('contentId','abcd1234'),
				Expr::isNotExpired()
	 )
);")
			?>
						</div>
					</div>








	<? if (count($model->currentInvalidationList) > 0): ?>

	<? endif; ?>

	<div id ="buttonsContainer">
		<p><label>Action</label></p>
		Evaluate expression <input checked type="radio" name="expressionVerified" value="false">
		Execute invalidation <input  type="radio" name="expressionVerified" value="true" <?= is_null($model->advancedInvalidationUserExpression) ? 'disabled' : '' ?>>
		<input type="submit" value="go" name="submitButton"/>
	</div>

</form>

<h3>Number of resources to invalidate with policy <?= $model->currentInvalidationPolicy ?> : <?= $model->currentExpressionResultsNumber ?> </h3>
<?= $view->partial('resourcesList', array('resources' => $model->currentInvalidationList)) ?>


<style type="text/css">
	body {
	}
	#expression {float: left; width:100%; padding: 20px;}

	
	
	#interpretedExpressionContainer { }

	#userExpressionError {}
	#buttonsContainer {}
	#expressionExampleContainer { }
</style>