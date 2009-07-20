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

class BCCleanupRSS
{

    /*!
      Constructor, does nothing by default.
    function __construct()
    {
    }
    */

    public function bccleanuprss( )
    {
        $this->total_removed = 0;

        $ini =& eZINI::instance( 'bccleanuprss.ini' );

        // How many RSS import items to leave on the system
        $this->rss_limit = $ini->variable( 'CleanupRSSSettings', 'RSSLimit' );

        // RSS parent node IDs (note: this assumes that there is nothing but imported objects below these IDs)
        $this->rss_classes = $ini->variable( 'CleanupRSSSettings', 'RSSClasses' );

        // RSS Cleanup User, Default 'admin' ( //*** ez administror )
        $this->rss_cleanup_user = $ini->variable( 'CleanupRSSSettings', 'RSSCleanupUser' );

        // RSS Cleanup Log File
        $this->logFile = $ini->variable( 'CleanupRSSSettings', 'RSSCleanupLogFile' );
        $this->logger = new eZLog( );

        $this->log( 'Notice: Limit, '.$this->rss_limit.' RSS objects per feed' );
        $this->log( 'Starting Cleanup RSS ...' );
    } //bccleanuprss

    public function cleanup( )
    {
        // Set Current User
        $this->setCurrentlyLoggedInUser( );

        // Fetch this class
        $this->log( 'Checking for RSS Objects in eZ Publish' );

        $rssImportArray = eZRSSImport::fetchActiveList( );

        $this->log( 'Total Active Feeds: '.count( $rssImportArray ) );

	$i = 0;

	// Iterate
        foreach ( array_keys( $rssImportArray ) as $rssImportKey )
        {
            // Fetch RSSImport Content Object
            $rssImport = $rssImportArray[$rssImportKey];

	    // Fetch Parent ContentObjectTreeNode Object
            $parentNodeID = $rssImport->attribute( 'destination_node_id' );

            $this->log( 'Feed Count: '.$i);

            $this->log( 'Feed: '. $rssImport->attribute( 'name' ) .' [ID: '.$rssImport->ID.']');

            $this->num_removed = $this->clearRSSCacheNodeSubTree( $rss_classes, $rss_limit, $parentNodeID );

            $this->total_removed = $this->num_removed + $this->total_removed;
            $i++;
        }

        $this->log( 'RSS cleanup done: ' . $this->total_removed . ' items removed.' );
    } // cleanup

    public function clearRSSCacheNodeSubTree ( $rss_classes, $rss_limit, $parentNodeID )
    {
        if ( $rss_limit === 0 ) {
	    return false;
        }
 
        $params=array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => $rss_classes,
                'Depth' => 0,
                'Offset' => $rss_limit,
                'SortBy' => array( 'published',false ),
                'status' => eZContentObject::STATUS_PUBLISHED );

        $childNodes = eZContentObjectTreeNode::subTreeByNodeId( $params, $parentNodeID );

        $num_childNodes = count( $childNodes );

        if ( $num_childNodes != 0 )
        {
            foreach( $childNodes as $child )
            {
                $deleteIDArray[] = $id = $child->attribute( 'main_node_id' );

                $log = 'Deleting cache for: '.$child->attribute( 'name' ).' [MainNodeID: '.$id.']';
		$this->log( $log );

                eZContentObjectTreeNode::removeSubtrees( $deleteIDArray, false );

                unset( $deleteIDArray );
                unset( $id );
            }
            unset( $childNodes );
        }
        return $num_childNodes;
    } // clearRSSCacheNodeSubTree

    public function log( $s )
    {
        global $cli;
        // print_r( $s ."\n" );
        $this->logWrite( $s, $this->logFile );
        if ( isset( $cli ) )
        $cli->output( $s, true );
    } // log

    public function logWrite( $s )
    {
        $this->logger->write( $s, $this->logFile );
    } // logWrite

    public function setCurrentlyLoggedInUser( )
    {
        $user = eZUser::fetchByName( $this->rss_cleanup_user );
        $userID = $user->attribute( 'contentobject_id' );
        eZUser::setCurrentlyLoggedInUser( $user, $userID );
        return $user;
    } // setCurrentlyLoggedInUser

}

?>