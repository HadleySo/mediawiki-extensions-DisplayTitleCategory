<?php

namespace MediaWiki\Extension\DisplayTitleCategory;

use MediaWiki\Hook\MultiContentSaveHook;
use MediaWiki\Revision\RenderedRevision;
use MediaWiki\User\UserIdentity;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
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

        // Get categories
        $parserOutput = $renderedRevision->getRevisionParserOutput();
        $pageCategories = $parserOutput->getCategoryNames();

        // Get intersection
        $configuredCategories = array_keys($wgDisplayTitleCategoryLabels);
        $result = array_intersect($configuredCategories, $pageCategories);

        // Create text to prepend or append
        $modString = "";
        foreach ($result as $category) {
            // Do something with the category
            $modString = $modString . " " . $wgDisplayTitleCategoryLabels[$category];
        }

        // Get current title
        $titleDB = $renderedRevision->getRevision()->getPage()->getDBkey();
        $titleObj = Title::newFromDBkey($titleDB);
        $regularTitle = $titleObj->getText();

        // Set display title
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

        return true;
    }
}
