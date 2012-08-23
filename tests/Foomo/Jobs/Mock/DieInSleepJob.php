<?php
namespace Foomo\Jobs\Mock;
 
/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author Jan Halfar jan@bestbytes.com
 */
class DieInSlepJob extends \Foomo\Jobs\AbstractJob
{
        protected $executionRule = '*   *       *       *       *';
        
		public function getMaxExecutionTime() {
			return 2;
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
			for ($i=0; $i<25; $i++) {
				sleep(1);
			}
        }
}
