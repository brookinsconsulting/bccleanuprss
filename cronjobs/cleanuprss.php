<?php
/**
 * File containing the BCCleanupRSS cleanuprss eZ Publish cronjob part.
 *
 * @name BCCleanupRSS
 * @author Brookins Consulting <info a~t brookinsconsulting d~o~t com>
 * @copyright Copyright (C) 1999 - 2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @package bccleanuprss
 * @link http://projects.ez.no/bccleanuprss
 */

if ( !isset( $script ) )
{
    $script = eZScript::instance( array(
                                  'debug-message' => true,
                                  'use-session' => true,
                                  'use-modules' => true,
                                  'use-extensions' => true ) );
    $script->startup();
    $script->initialize();
    $standalone = true;
}
else
{
    $standalone = false;
}

if ( !isset( $cli ) )
{
    $cli = eZCLI::instance();
    // enable colors
    $cli->setUseStyles( true );
}

$rssCache = new BCCleanupRSS( );

$rssCache->cleanup( );

$cli->output( 'Done', true );

if ( $standalone )
{
    $script->shutdown();
}

?>
