<?php
/* @var $view Foomo\MVC\View */
/* @var $model Foomo\Cache\Frontend\Model */
?>

<?= $view->partial('menu') ?>


<div class="rightBox">
	<?= $view->link('Back', '#', array(), array('class' => 'linkButtonYellow backButton')) ?>
</div>

<h2>Advanced invalidation for <?= $model->currentResourceName ?></h2>


<div class="whiteBox">
	<div class="innerBox">
		<?= $view->partial('resourceAnnotation') ?>
		<div class="greyBox">
			<div class="innerBox">
				<?= $view->partial('resourcePropertiesDefinitions',array('resourceName' =>$model->currentResourceName)); ?>
				<hr>
				<?= $view->partial('storageStatus',array('resourceName' => $model->currentResourceName)); ?>
			</div>
		</div>
		
		<br>
		
		<form method="post" action="<?= $view->url('advancedInvalidation', array($model->currentResourceName)) ?>">
			
			<div class="greyBox">

				<div class="formBox">	
					<div class="formTitle">Invalidation policy</div>
					<? foreach (array('POLICY_INSTANT_REBUILD' => 'rebuild', 'POLICY_INVALIDATE' => 'invalidate', 'POLICY_DELETE' => 'delete') as $const => $label): ?>
						<div class="floatLeftSpaceBox">
						<? $constValue = constant('Foomo\Cache\Invalidator::' . $const) ?>
						<input <?= ($model->currentInvalidationPolicy == $constValue) ? 'checked' : '' ?> type="radio" name="invalidationPolicy" value="<?= $constValue ?>"><span title="<?= 'Foomo\Cache\Invalidator::' . $const ?>"><?= $label ?></span> 
						</div>
					<? endforeach; ?>
						<br>
					
				</div>

				<div class="formBox">
					<div class="formTitle">Expression</div>
					<textarea name="expressionString" cols="80" rows="10"><?= $view->escape($model->advancedInvalidationUserExpressionString) ?></textarea>
				</div>
				
				
				
				<? if (!empty($model->advancedInvalidationUserExpressionString) && !is_null($model->advancedInvalidationUserExpression)): ?>
				<div class="formBox">
					<div class="greyBox">
						<div class="innerBox">
							Interpreted expression:
							<? highlight_string('<? ' . $model->advancedInvalidationUserExpressionString . '; ?>') ?>
						</div>
					</div>
				</div>
				<? endif; ?>
				
				<? if (!empty($model->advancedInvalidationUserExpressionInterpretationString)): ?>
				<div class="formBox">
					<div class="errorMessage">
						Compiled expression:<br>	
						<b><?= $view->escape($model->advancedInvalidationUserExpressionInterpretationString) ?></b>
					</div>
				</div>	
				<? endif; ?>
				
				<div class="formBox">
					<div class="greyBox">
						<div class="innerBox">
							Expression example:
							<? highlight_string("<?
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
				</div>
				
				<div class="formBox">
					<div class="formTitle">Action</div>
					<div class="floatLeftSpaceBox"><input checked type="radio" name="expressionVerified" value="false"><br>Evaluate expression</div>
					<div class="floatLeftSpaceBox"><input  type="radio" name="expressionVerified" value="true" <?= is_null($model->advancedInvalidationUserExpression) ? 'disabled' : '' ?>><br>Execute invalidation</div>
				</div>
				<br>
				<div class="formBox">
					<input class="submitButton" type="submit" value="Send invalidation"/>
				</div>
			</div>
		</form>
				
	</div>
</div>

<h3>Number of resources to invalidate with policy <?= $model->currentInvalidationPolicy ?> : <?= $model->currentExpressionResultsNumber ?> </h3>
<?= $view->partial('resourcesList', array('resources' => $model->currentInvalidationList)) ?>
