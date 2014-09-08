<?php
//run a quick repair
require_once('modules/Administration/QuickRepairAndRebuild.php');
$RAC = new RepairAndClear();
$actions = array('clearAll');
$RAC->repairAndClearAll($actions, array(translate('LBL_ALL_MODULES')), false, true);