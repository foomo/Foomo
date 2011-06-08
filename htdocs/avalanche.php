<?php

if(isset($_GET['exprId'])) {
	 Foomo\Cache\Frontend\Model::evaluateExpr($_GET['exprId']);
}