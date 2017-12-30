<?php
/**
 * @package install-git-hooks
 * @author Michael Zapf <m.zapf@mztx.de>
 * @version 0.1
 * @copyright (C) 2017 Michael Zapf <m.zapf@mztx.de>
 * @license MIT
 */


$base = dirname(__FILE__);


echo "Running SHINAGE Post-Install-Script.\n";
echo "Installing git-hooks to\n";
echo "    " . realpath($base) . "/.git/hooks\n";

chdir($base . '/.git/hooks/');

@symlink('../../git-hooks/pre-commit', './pre-commit');

chdir($base);

echo "Done!\n";
