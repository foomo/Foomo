<?php
namespace Foomo\Jobs\Mock;
 
/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class DieWhileWorkingJob extends \Foomo\Jobs\AbstractJob
{
        protected $executionRule = '*   *       *       *       *';
        
		public function getMaxExecutionTime() {
			return 1;
		}
		public function getId()
        {
                return sha1(__CLASS__);
        }
        public function getDescription()
        {
                return 'sleep for some time and die before waking up';
        }
        public function run()
        {	
			//i should live forever
			while (true) {
				$i = 1 + 1;
				
			}
        }
}
