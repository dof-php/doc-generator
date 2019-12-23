<?php

$gwt->true('Test \filemtime() enabled', \function_exists('filemtime'));

$gwt->true('Test if `gitbook` command was installed', function () {
	if (\function_exists('shell_exec')) {
		return !\is_null(\shell_exec('gitbook'));
	}

	return false;
});
