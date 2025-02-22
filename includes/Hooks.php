<?php

namespace MediaWiki\Extension\DisplayTitleCategory;

use MediaWiki\Revision\RenderedRevision;
use MediaWiki\User\UserIdentity;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\Content\ContentHandler; 
use CommentStoreComment;
use Status;

class Hooks {
    /**
     * Implements MultiContentSave hook.
     * @param renderedRevision MediaWiki\Revision\RenderedRevision (object) representing the planned revision. 
     * @param user the MediaWiki\User\UserIdentity saving the article.
     * @param summary CommentStoreComment object containing the edit comment.
     * @param flags All EDIT_… flags (including EDIT_MINOR) as an integer number. See WikiPage::doEditContent documentation.
     * @param hookStatus if the hook is aborted, error code can be placed into this Status.
     * 
     */
    public static function onMultiContentSave(
        RenderedRevision $renderedRevision,
        UserIdentity $user,
        CommentStoreComment $summary,
        $flags,
        Status $hookStatus
    ) {
        $config = MediaWikiServices::getInstance()->getMainConfig();
        $wgDisplayTitleCategoryPrepend = $config->get('DisplayTitleCategoryPrepend');
        $wgDisplayTitleCategoryAppend = $config->get('DisplayTitleCategoryAppend');
        $wgDisplayTitleCategoryLabels = $config->get('DisplayTitleCategoryLabels');
        $wgDisplayTitleCategoryDisplayTitle = $config->get('DisplayTitleCategoryDisplayTitle');

        // Get categories
        $parserOutput = $renderedRevision->getRevisionParserOutput();
        $pageCategories = $parserOutput->getCategoryNames();

        // Get intersection
        $configuredCategories = array_keys($wgDisplayTitleCategoryLabels);
        $result = array_intersect($configuredCategories, $pageCategories);

        if (count($result) < 1) {
            return true;
        }
        
        // If using DisplayTitle
        $revision = $renderedRevision->getRevision();
        $content = $revision->getContent( 'main' );
        $wikitext = ContentHandler::getContentText( $content );
        

        // Create text to prepend or append
        $modString = "";
        foreach ($result as $category) {
            // Do something with the category
            $modString = $modString . " " . $wgDisplayTitleCategoryLabels[$category];
        }

        // Get current title
        $titleDB = $revision->getPage()->getDBkey();
        $titleObj = Title::newFromDBkey($titleDB);
        $regularTitle = $titleObj->getText();

        // Set display title
        $setDisplay = "";
        if ($wgDisplayTitleCategoryPrepend && $wgDisplayTitleCategoryAppend) {
            $setDisplay = $modString . " " . $regularTitle . " " . $modString;
            $parserOutput->setDisplayTitle($setDisplay);
        } elseif ($wgDisplayTitleCategoryPrepend) {
            $setDisplay = $modString . " " . $regularTitle;
            $parserOutput->setDisplayTitle($setDisplay);
        } elseif ($wgDisplayTitleCategoryAppend) {
            $setDisplay = $regularTitle . " " . $modString;
            $parserOutput->setDisplayTitle($setDisplay);
        }

        // If using DisplayTitle
        if ($wgDisplayTitleCategoryDisplayTitle) {
            $magicInsert = "{{DISPLAYTITLE:" . $setDisplay . "}}";
            // $wikitext = str_replace($magicInsert, '', $wikitext);
            $wikitext = preg_replace('/{{DISPLAYTITLE:.*}}/', '', $wikitext);
            $wikitext = $wikitext . $magicInsert;
            $newContent = ContentHandler::makeContent( $wikitext, $titleObj );
            $revision->setContent( 'main', $newContent );
        }

        return true;
    }
}
