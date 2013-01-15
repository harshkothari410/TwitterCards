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
	'descriptionmsg' => 'TwitterCards-desc',
	'url' => 'http://www.mediawiki.org/wiki/Extension:TwitterCards',
);
$dir = dirname( __FILE__ );
$wgExtensionMessagesFiles['TwitterCardsMagic'] = $dir . '/TwitterCards.magic.php';
$wgExtensionMessagesFiles['TwitterCards'] = $dir . '/TwitterCards.i18n.php';
$wgHooks['BeforePageDisplay'][] = 'efTwitterCardsHook';
function efTwitterCardsHook( &$out, &$sk ) {
	global $wgLogo, $wgSitename, $wgArticle, $wgUploadPath, $wgServer, $wgArticleId; ;
	$title = $out->getTitle();
	$isMainpage = $title->isMainPage();
	$meta = array();
	$meta["twitter:card"] = "photo";
	$meta["twitter:site"] = $wgSitename;

	if ( isset( $wgArticle ) ) {
		$myArticle = $wgArticle;
	}
	else {
		return true;
	}

	$dbr = wfGetDB( DB_SLAVE );
	$pageId = $myArticle->getId();
	$res = $dbr->select(
		'revision',
		'rev_user_text',
		'rev_page = "' . $pageId . '"',
		__METHOD__,
		array( 'ORDER BY' => 'rev_timestamp ASC limit 1' )
	);

	foreach ( $res as $row ) {
    	$meta["twitter:creator"] = $row->rev_user_text;
	}

	$meta["twitter:title"] = $title->getText();
	$img_name = $title->getText();
	# description
	$dbr = wfGetDB( DB_SLAVE );
	$res = $dbr->select(
		'image',
		'img_description',
		'img_name = "' . $img_name . '"',
		__METHOD__,
		array( 'ORDER BY' => 'img_description ASC limit 1' )
	);

	foreach ( $res as $row ) {
    	$meta["twitter:description"] = $row->img_description;
	}

	if ( isset( $out->mDescription ) ) { // set by Description2 extension, install it if you want proper TwitterCards:description support
		$meta["twitter:description"] = $out->mDescription;
	}

	if ( $isMainpage ) {
		$meta["twitter:url"] = wfExpandUrl( $wgLogo );
	}
	else {
		$meta["twitter:url"] = $title->getFullURL();
	}
	# Finding Full Path
	$img = wfFindFile( $title );
	if ( $img ) {
		$thumb = $img->transform( array( 'width' => 400 ), 0 );
		$meta["twitter:image"] = $wgServer . $thumb->getUrl();
	}
	else {
		return true;
	}

	$meta["twitter:image:width"] = 600;
	$meta["twitter:image:height"] = 600;

	foreach ( $meta as $name => $value ) {
		if ( $value ) {
			if ( isset( OutputPage::$metaAttrPrefixes ) && isset( OutputPage::$metaAttrPrefixes['name'] ) ) {
				$out->addMeta( "name:$name", $value );
			} else {
				$out->addHeadItem( "meta:name:$name", "	" . Html::element( 'meta', array( 'name' => $name, 'content' => $value ) ) . "\n" );
			}
		}
	}
	return true;
}