<?php
/*
 * bestbytes-copyright-placeholder
 */
Foomo\Log\Logger::transactionBegin('run log utils');
Foomo\Services\Cli::serveClass('Foomo\\Log\\CliUtils');
Foomo\Log\Logger::transactionComplete('run log utils');