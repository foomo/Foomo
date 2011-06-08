<?php
/* @var $model Foomo\CliCall */
?>Foomo\CliCall Report :
  
  called:
  
    <?php echo $model->cmd . ' ' .implode(' ', $model->arguments)?>
  	
  
  rendered command :
  
    <?php echo implode(PHP_EOL . '    ', explode(';', $model->lastCommandExecuted)) ?>
    
  	
  environement variables exported:
 
<?php foreach($model->envVars as $key => $value): ?>
    <?php echo $key . ' => ' . $value ?>
<?php endforeach; ?>


  execution date : 
    
    <?php echo date('Y-m-d H:i:s') ?>
    
  
  execution time :
    
    real : <?php echo $model->timeReal ?>
    
    sys  : <?php echo $model->timeSys ?>
    
    user : <?php echo $model->timeUser ?>
    

  exit status:
  
    <?php echo $model->exitStatus ?>
    
<?php foreach(array('stdOut', 'stdErr') as $stdType): ?>    
  <?php echo $stdType ?> :
  
    ---------------------------------------------------------------------------
    | <?php echo implode(PHP_EOL . '    | ', explode(PHP_EOL, $model->$stdType)) ?>

    ---------------------------------------------------------------------------
<?php endforeach; ?>   
