<?php
/* @var $view \Foomo\MVC\View */
/* @var $model Foomo\BasicAuth\Frontend\Model */
use Foomo\BasicAuth\Utils;
?>
<div id="main">
	<?= $view->partial('menu', array('allowedDomains' => $model->allowedDomains)) ?>
	<div id="appContent">
		
		<h2>Auth domains</h2>
		
		<? 
			foreach(Utils::getDomains() as $domain):
				if(!empty($model->allowedDomains) && !in_array($domain, $model->allowedDomains)) {
					continue;
				}
		?>

		<div class="toggleBox">
			<div class="toogleButton">
				<div class="toggleOpenIcon">+</div>
				<div class="toggleOpenContent"><?= $domain ?></div>
			</div>
			<div class="toggleContent">
				
				<? if(empty($model->allowedDomains)): ?>
					<div class="deleteBox">
						<?= $view->link('Delete domain', 'deleteDomain', array($domain), array('class' => 'linkButtonRed')) ?>
					</div>
				<? endif; ?>
				<div class="tabBox">
				
					<div class="tabNavi">
						<ul>
							<li class="selected">Existing <?= $domain ?> users</li>
							<li>Create a new <?= $domain ?> user</li>
						</ul>
						<hr class="greyLine">
					</div>
					
					<div class="tabContentBox">

						<div class="tabContent tabContent-1 selected">

								<? foreach(Utils::getUsers($domain) as $user => $hash): ?>
								
								<div class="toggleBox">
									<div class="toogleButton">
										<div class="toggleOpenIcon">+</div>
										<div class="toggleOpenContent"><?= $view->escape($user) ?></div>
										<div class="toggleOpenInfo"><?= $view->link('Delete user', 'deleteUser', array($domain, $user), array('class' => 'linkButtonSmallRed')) ?></div>
									</div>
									<div class="toggleContent">
										
										<form action="<?= $view->escape($view->url('updateUser')) ?>" method="post">
											
										<div class="greyBox">

											<div class="formBox">
												<div class="formTitle">New password</div>
												<input type="password" name="password">
											</div>
											
											<div class="formBox">

												<input type="hidden" name="domain" value="<?= $view->escape($domain) ?>">
												<input type="hidden" name="user" value="<?= $view->escape($user) ?>">
												<input class="submitButton" type="submit" value="Set password">
												
											</div>
										</form>
										
										</div>

									</div>
								</div>
								
								<? endforeach; ?>
									

							
						</div>

						<div class="tabContent tabContent-2">
							
							<div class="greyBox">

							<form action="<?= $view->escape($view->url('updateUser')) ?>" method="post">
								<div class="formBox">	
									<div class="formTitle">Name of the new user</div>
									<input type="text" name="user">
								</div>
								
								<div class="formBox">	
									<div class="formTitle">Password</div>
									<input type="password" name="password" value="">
								</div>
								
								<div class="formBox">
									<input type="hidden" name="domain" value="<?= $view->escape($domain) ?>">
									<input class="submitButton" type="submit" value="Create new user">
								</div>
							</form>

							</div>
							
						</div>
					</div>
					
				</div>
				
			</div>
		</div>
		
		<? endforeach; ?>
		
			
	</div>
</div>
