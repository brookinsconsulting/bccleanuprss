#!/usr/bin/env php
<?php
//
// Created on: <17-Jul-2009 04:02:00 gb>
//
// Copyright (C) 2001-2009 Brookins Consulting. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 or greater as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@brookinsconsulting.com if any conditions of
// this licencing isn't clear to you.
//

require 'autoload.php';

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
        $standalone = false;
 
if ( !isset( $cli ) )
{
        // Enable colors
        $cli = eZCLI::instance( );
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