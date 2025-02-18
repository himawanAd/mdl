<?php
require_once(__DIR__ . '/../../config.php');
$id = required_param('id', PARAM_INT);
redirect(new moodle_url('/mod/monitoring/view.php', array('id' => $id)));
