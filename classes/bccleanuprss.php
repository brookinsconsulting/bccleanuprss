<?php
/**
 * File containing the BCCleanupRSS class.
 *
 * @name BCCleanupRSS
 * @author Brookins Consulting <info a~t brookinsconsulting d~o~t com>
 * @copyright Copyright (C) 1999 - 2011 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @package bccleanuprss
 * @link http://projects.ez.no/bccleanuprss
 */

class BCCleanupRSS
{
    /*!
      PHP5 Default constructor, does nothing extra by default.
    function __construct()
    {
    }
    */

    /*!
     * Default constructor. Creates object variable defaults
     *
     * @return void
     */
    public function BCCleanupRSS( )
    {
        $this->totalRemoved = 0;

        $ini = eZINI::instance( 'bccleanuprss.ini' );

        // How many RSS import items to leave on the system
        $this->rssLimit = $ini->variable( 'CleanupRSSSettings', 'RSSLimit' );

        // RSS parent node IDs (note: this assumes that there is nothing but imported objects below these IDs)
        $this->rssClasses = $ini->variable( 'CleanupRSSSettings', 'RSSClasses' );

        // RSS Cleanup User, Default 'admin' ( Which is the default eZ Publish site administrator )
        $this->rssCleanupUser = $ini->variable( 'CleanupRSSSettings', 'RSSCleanupUser' );

        // RSS Cleanup Log File
        $this->logFile = $ini->variable( 'CleanupRSSSettings', 'RSSCleanupLogFile' );
        $this->logger = new eZLog( );

        $this->log( 'Notice: Limit, '.$this->rssLimit.' RSS objects per feed' );
        $this->log( 'Starting Cleanup RSS ...' );
    }

    /*!
     * Performs related rss cleanup actions
     *
     * @return void
     */
    public function cleanup( )
    {
        // Set Current User
        $this->setCurrentlyLoggedInUser();

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

            $this->numberRemoved = $this->clearRSSCacheNodeSubTree( $this->rssClasses, $this->rssLimit, $parentNodeID );

            $this->totalRemoved = $this->numberRemoved + $this->totalRemoved;
            $i++;
        }

        $this->log( 'RSS cleanup done: ' . $this->totalRemoved . ' items removed.' );
    }

    /*!
     * Clears rss cache by parentNodeID parameter
     *
     * @param array $rssClassIdentifiers is an array of rss class identifiers used to limit the rss cache clearing operation
     * @param integer $rssOffsetLimit is an integer representing the number to use to offset the fetched results by durring the rss cache clearning operation
     * @param integer $roleID is an integer of the role_id otherwise known as the ID number of the role
     * @return integer returns number of child nodes of parentNodeID which have had their rss cache cleared
     */
    public function clearRSSCacheNodeSubTree ( $rssClassIdentifiers, $rssOffsetLimit, $parentNodeID )
    {
        if ( $rssOffsetLimit === 0 ) {
	        return false;
        }
 
        $fetchParams = array( 'ClassFilterType' => 'include',
                              'ClassFilterArray' => $rssClassIdentifiers,
                              'Depth' => 0,
                              'Offset' => $rssOffsetLimit,
                              'SortBy' => array( 'published',false ),
                              'status' => eZContentObject::STATUS_PUBLISHED );

        $childNodes = eZContentObjectTreeNode::subTreeByNodeId( $fetchParams, $parentNodeID );

        $childNodesCount = count( $childNodes );

        if ( $childNodesCount != 0 )
        {
            foreach( $childNodes as $child )
            {
                $deleteIDArray[] = $id = $child->attribute( 'main_node_id' );

                $log = 'Deleting cache for: ' . $child->attribute( 'name' ) . ' [MainNodeID: ' . $id .']';
        		$this->log( $log );

                // eZContentObjectTreeNode::removeSubtrees( $deleteIDArray, false );
                eZContentObjectTreeNode::removeSubtrees( $deleteIDArray, false );

                unset( $deleteIDArray );
                unset( $id );
            }
            unset( $childNodes );
        }

        return $childNodesCount;
    } // clearRSSCacheNodeSubTree

    /*!
     * Writes log entry. Optionally displays message to user via cli
     *
     * @return void
     */
    public function log( $s )
    {
        global $cli;

        // print_r( $s ."\n" );
        $this->logWrite( $s, $this->logFile );

        if ( isset( $cli ) )
        $cli->output( $s, true );
    } // log

    /*!
     * Wires log entry directly
     *
     * @return void
     */
    public function logWrite( $s )
    {
        $this->logger->write( $s, $this->logFile );
    } // logWrite

    /*!
     * Sets currently logged in user within eZ Publish context
     *
     * @return object returns eZUser class object
     */
    public function setCurrentlyLoggedInUser( )
    {
        $user = eZUser::fetchByName( $this->rssCleanupUser );
        $userID = $user->attribute( 'contentobject_id' );
        eZUser::setCurrentlyLoggedInUser( $user, $userID );

        return $user;
    } // setCurrentlyLoggedInUser

}

?>
