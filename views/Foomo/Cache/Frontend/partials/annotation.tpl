<?php

use Foomo\Cache\Invalidator;

/* @var $model Foomo\Cache\Frontend\Model */

$props = array(
	'lifeTime', 
	'lifeTimeFast',
	'dependencies',
	'invalidationPolicy'
);

?>

	@Foomo\Cache\CacheResourceDescription(
		<?
			$i = 0;
			foreach($props as $prop): 
				$flag = 'Good';
				$comment = '';
				if(!isset($$prop)) {
					continue;
				}
				switch($prop) {
					case 'lifeTime':
					case 'lifeTimeFast':
						if(is_null($$prop)) {
							continue 2;
						}
						if(!is_integer($$prop)) {
							$flag = 'Bad';
							$comment = 'has to be an integer';
						}
						break;
					case 'dependencies':
						if(empty($$prop)) {
							$dependencies = '';
							continue 2;
						}
						$errors = array();
						foreach($$prop as $resourceName) {
							$resourceName = trim($resourceName);
							if(!empty($resourceName)) {
								if(!$model->getResourceRefl($resourceName)) {
									$errors[] = $resourceName;
								}
							}
						}
						if(!empty($errors)) {
							$flag = 'Bad';
							$comment = 'can not handle dependencies ' . implode(', ', $errors);
						}
						$$prop = '"' . implode(', ', $$prop) . '"';
						break;
					case 'invalidationPolicy':
						if(empty($$prop) || is_null($$prop)) {
							continue 2;
						}
						if(!in_array($$prop, array(Invalidator::POLICY_DELETE, Invalidator::POLICY_DO_NOTHING, Invalidator::POLICY_INSTANT_REBUILD, Invalidator::POLICY_INVALIDATE))) {
							$flag = 'Bad';
							$comment = 'invalid invalidation policy "' . $$prop . '" - must be one of Invalidator::POLICY_...';
						} else {
							$comment = var_export($$prop, true);
						}
						break;
					default:
				}
				$comment .= var_export($$prop, true) . ' ' . (is_null($$prop)?'is_null':'not_is_null');
		?>
			<? if($i > 0): ?>
				,
			<? endif; ?>
			<span class="annotationProperty<?= $flag ?>" title="<?= $view->escape($comment) ?>">
				<?= $prop ?>=<?= $view->escape($$prop) ?>
			</span>
		<? 
			$i ++;
			endforeach; 
		?>
)


