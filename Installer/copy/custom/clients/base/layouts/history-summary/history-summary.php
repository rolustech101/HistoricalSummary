<?php
/*
 * layout override for the stock history-summary
 */
$viewdefs['base']['layout']['history-summary'] = array(
    'components' => array(
        array(
            'view' => 'custom-history-summary',
        ),
    ),
    'type' => 'simple',
    'name' => 'base',
    'span' => 12,
);