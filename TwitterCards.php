<?php
/**
 * TwitterCards
 * Extensions
 * @author Harsh Kothari (http://mediawiki.org/wiki/User:Harsh4101991) <harshkothari410@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
if ( !defined( 'MEDIAWIKI' ) ) die( "This is an extension to the MediaWiki package and cannot be run standalone." );

$wgExtensionCredits['parserhook'][] = array (
	"path" => __FILE__,
	"name" => "TwitterCards",
	"author" => "Harsh Kothari", 
	'descriptionmsg' => 'TwitterCards make it possible for you to attach media experiences to Tweets that link to your content.',
	'url' => 'http://www.mediawiki.org/wiki/Extension:TwitterCards',
);

$dir = dirname( __FILE__ );
$wgExtensionMessagesFiles['TwitterCardsMagic'] = $dir . '/TwitterCards.magic.php';
$wgExtensionMessagesFiles['TwitterCards'] = $dir . '/TwitterCards.i18n.php';

#$wgHooks['ParserFirstCallInit'][] = 'efTwitterCardsInit';
#function efTwitterCardsInit( $parser ) {
#	$parser->setFunctionHook( 'setmainimage', 'efSetMainImagePF' );
#	return true;
#}

$wgHooks['BeforePageDisplay'][] = 'efTwitterCardsHook';
function efTwitterCardsHook( &$out, &$sk ) {
	global $wgLogo, $wgSitename;
	$title = $out->getTitle();
	$isMainpage = $title->isMainPage();
	$meta = array();
	$meta["twitter:card"] = "image";
	if ( $isMainpage ) {
		$meta["twitter:site"] = $wgSitename;
	} else {
		$meta["twitter:site"] = $wgSitename;
	}



#For Finding Creator
	global $wgArticle;
 
    if (isset($wgArticle))
    {
    	$myArticle=$wgArticle;        
    }
    else
    { 
        $myTitle=$parser->getTitle();
        $myArticle=new Article($myTitle);
    }
 
    $dbr = wfGetDB( DB_SLAVE );
    $revTable = $dbr->tableName( 'revision' );
 
    $pageId = $myArticle->getId();
    
    $query = "select rev_user_text from ".$revTable." where rev_page=".$pageId." order by rev_timestamp asc limit 1";
	
	$res = mysql_query($query);
	

	$row = mysql_fetch_object($res);

	$meta["twitter:creator"] = $row->rev_user_text;
	$meta["twitter:title"] = $title->getText();
	$img_name = $title->getText();
		// Try to chose the most appropriate title for showing in news feeds.
#	if ( ( defined('NS_BLOG_ARTICLE') && $title->getNamespace() == NS_BLOG_ARTICLE ) ||
#		( defined('NS_BLOG_ARTICLE_TALK') && $title->getNamespace() == NS_BLOG_ARTICLE_TALK ) ){
#			$meta["twitter:title"] = $title->getSubpageText();
#		} else {
#			$meta["twitter:title"] = $title->getText();
#		}
	
#description testing

	
	$dbr = wfGetDB( DB_SLAVE );
    $revTable = $dbr->tableName( 'image' );
	$query = "SELECT img_description FROM ".$revTable." WHERE img_name='".$img_name."' ORDER BY `img_description` ASC limit 1";
	$res = mysql_query($query);
	$row = mysql_fetch_object($res);
	$meta["twitter:description"] = $row->img_description;

############3


	if ( isset($out->mDescription) ) { // set by Description2 extension, install it if you want proper TwitterCards:description support
		#$meta["twitter:description"] = $out->mDescription;
	}

	if( $isMainpage ) {
		$meta["twitter:image"] = wfExpandUrl($wgLogo);
	}
	else {
		$meta["twitter:image"] = $title->getFullURL();
	}
	$meta["twitter:image:width"] = 600;
	$meta["twitter:image:height"] = 600;

	foreach( $meta as $name => $value ) {
		if ( $value ) {
			if ( isset( OutputPage::$metaAttrPrefixes ) && isset( OutputPage::$metaAttrPrefixes['name'] ) ) {
				$out->addMeta( "name:$name", $value );
			} else {
				$out->addHeadItem("meta:name:$name", "	".Html::element( 'meta', array( 'name' => $name, 'content' => $value ) )."\n");
			}
		}
	}
	return true;
}



