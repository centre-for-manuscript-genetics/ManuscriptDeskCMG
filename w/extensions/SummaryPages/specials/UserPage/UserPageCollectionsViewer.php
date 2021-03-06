<?php

/**
 * This file is part of the Manuscript Desk (github.com/akvankorlaar/manuscriptdesk)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar 'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 */
class UserPageCollectionsViewer extends UserPageBaseViewer implements UserPageViewerInterface {

    use HTMLUserPageMenuBar,
        HTMLJavascriptLoaderDots,
        HTMLPreviousNextPageLinks,
        HTMLCollectionMetaTable;

    public function setUserName($user_name) {

        if (isset($this->user_name)) {
            return;
        }

        return $this->user_name = $user_name;
    }

    public function showPage($button_name, $page_titles, $offset, $next_offset) {

        $out = $this->out;
        global $wgArticleUrl;
        $user_name = $this->user_name;

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);
        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderDots();

        $html .= "<div class='javascripthide'>";
        $html .= $this->getHTMLPreviousNextPageLinks($out, $edit_token, $offset, $next_offset, $button_name, 'UserPage');

        $created_message = $out->msg('userpage-created');
        $html .= "<br>";

        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-three'>" . "<b>" . $out->msg('userpage-tabletitle') . "</b>" . "</td>";
        $html .= "<td class='td-three'><b>" . $out->msg('userpage-creationdate') . "</b></td>";
        $html .= "<td class='td-three'></td>";
        $html .= "</tr>";

        foreach ($page_titles as $key => $array) {

            $collection_title = isset($array['collections_title']) ? $array['collections_title'] : '';
            $collection_date = isset($array['collections_date']) ? $array['collections_date'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-three'>" . $this->getSingleCollectionForm($collection_title) . "</td>";
            $html .= "<td class='td-three'>" . htmlspecialchars($collection_date) . "</td>";
            $html .= "<td class='td-three'>" . $this->getExportCollectionTEIForm($collection_title) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "</div>";

        return $out->addHTML($html);
    }

    private function getSingleCollectionForm($collection_title) {
        global $wgArticleUrl;
        $edit_token = $this->out->getUser()->getEditToken();
        $html = '';
        $html .= "<form class='summarypage-form' id='userpage-collection' action='" . $wgArticleUrl . "Special:UserPage' method='post'>";
        $html .= "<input type='submit' class='userpage-collectionlist' name='collection_title' value='" . htmlspecialchars($collection_title) . "'>";
        $html .= "<input type='hidden' name='single_collection_posted' value='single_collection_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";
        return $html;
    }

    /**
     * Get HTML form to download the collection in TEI format
     * 
     * @global type $wgArticleUrl
     * @param type $collection_title
     * @return string HTML
     */
    private function getExportCollectionTEIForm($collection_title) {
        global $wgArticleUrl;
        $user_name = $this->user_name;
        $out = $this->out;
        $collection_tei_export = $wgArticleUrl . "Special:CollectionTEIExport?username=" . $user_name . "&collection=" . $collection_title;
        $html = '';
        $html .= "<form class='manuscriptpage-form' action='" . $collection_tei_export . "' method='post'>";
        $html .= "<input type='submit' class='button-transparent' value='" . $out->msg('teiexport') . "'>";
        $html .= "</form>";
        return $html;
    }

    public function showEmptyPageTitlesError($button_name) {

        global $wgArticleUrl;
        $article_url = $wgArticleUrl;
        $out = $this->out;
        $user_name = $this->user_name;

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);

        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderDots();

        $html .= "<div class='javascripthide'>";
        $html .= "<p>" . $out->msg('userpage-nocollections') . "</p>";
        $html .= "<p><a class='userpage-transparent' href='" . $article_url . "Special:NewManuscript'>" . $out->msg('userpage-newcollection') . "</a></p>";
        $html .= "</div>";

        return $out->addHTML($html);
    }

    /**
     * This function displays a single collection (metadata and information on the pages) to the user
     */
    public function showSingleCollectionData($collection_title, $single_collection_data) {

        global $wgArticleUrl;
        $out = $this->out;
        $user_name = $this->user_name;
        list($meta_data, $pages_within_collection) = $single_collection_data;

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);

        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderDots();
        $html .= "<div class='javascripthide'>";

        $html .= "<form class='summarypage-form' id='userpage-editmetadata' action='" . $wgArticleUrl . "Special:UserPage' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='edit_metadata_posted' value='" . $out->msg('userpage-editmetadatabutton') . "'>";
        $html .= "<input type='hidden' name='collection_title' value='" . $collection_title . "'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        //redirect to Special:NewManuscript, and automatically have the current collection selected
        $html .= "<form class='summarypage-form' id='userpage-addnewpage' action='" . $wgArticleUrl . "Special:NewManuscript' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='add_new_page_posted' title='" . $out->msg('userpage-newcollection') . "' value='Add New Page'>";
        $html .= "<input type='hidden' name='collection_title' value='" . $collection_title . "'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        $html .= "<h2 style='text-align: center;'>" . $out->msg('userpage-collection') . ": " . $collection_title . "</h2>";
        $html .= "<br>";
        $html .= "<h3>" . $out->msg('userpage-metadata') . "</h3>";

        $html .= $this->getHTMLCollectionMetaTable($out, $meta_data);

        $html .= "<h3>Pages</h3>";
        $html .= $out->msg('userpage-contains') . " " . count($pages_within_collection) . " " . $out->msg('userpage-contains2');
        $html .= "<br>";

        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-five'>" . "<b>" . $out->msg('userpage-tabletitle') . "</b>" . "</td>";
        $html .= "<td class='td-five'><b>" . $out->msg('userpage-creationdate') . "</b></td>";
        $html .= "<td class='td-five'><b>" . $out->msg('userpage-signature') . "</b></td>";
        $html .= "<td class='td-five'></td>";
        $html .= "<td class='td-five'></td>";
        $html .= "</tr>";

        $counter = 0;

        foreach ($pages_within_collection as $single_page_data) {

            $partial_url = isset($single_page_data['manuscripts_url']) ? $single_page_data['manuscripts_url'] : '';
            $manuscripts_title = isset($single_page_data['manuscripts_title']) ? $single_page_data['manuscripts_title'] : '';
            $manuscripts_date = isset($single_page_data['manuscripts_date']) ? $single_page_data['manuscripts_date'] : '';
            $signature = isset($single_page_data['manuscripts_signature']) ? $single_page_data['manuscripts_signature'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-four'><a href='" . $wgArticleUrl . htmlspecialchars($partial_url) . "' title='" . htmlspecialchars($partial_url) . "'>"
                . htmlspecialchars($manuscripts_title) . "</a></td>";
            $html .= "<td class='td-five'>" . htmlspecialchars($manuscripts_date) . "</td>";
            $html .= "<td class='td-five'>" . $this->getChangeSignatureCollectionPageForm($partial_url, $signature, $collection_title) . "</td>";
            $html .= "<td class='td-five'>" . $this->getEditSinglePageCollectionForm($counter, $collection_title, $manuscripts_title, $partial_url) . "</td>";
            $html .= "<td class='td-five'>" . $this->getExportManuscriptTEIForm($manuscripts_title) . "</td>";
            $html .= "</tr>";

            $counter+=1;
        }

        $html .= "</table>";
        $html .= "</div>";

        return $out->addHTML($html);
    }

    private function getChangeSignatureCollectionPageForm($partial_url, $signature, $collection_title) {

        global $wgArticleUrl;
        $edit_token = $this->out->getUser()->getEditToken();

        if ($signature === 'private') {
            $new_signature = 'public';
        }
        else {
            $new_signature = 'private';
        }

        $html = "";
        $html .= '<form class="manuscriptpage-form" action="' . $wgArticleUrl . 'Special:UserPage" method="post">';
        $html .= "<input class='button-transparent' type='submit' name='editlink' value='$signature'>";
        $html .= "<input type='hidden' name='partial_url' value='$partial_url'>";
        $html .= "<input type='hidden' name='change_signature_collection_page_posted' value = '$new_signature'>";
        $html .= "<input type='hidden' name='collection_title' value = '$collection_title'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        return $html;
    }

    private function getEditSinglePageCollectionForm($counter, $collection_title, $manuscripts_title, $manuscripts_url) {
        global $wgArticleUrl;
        $out = $this->out;
        $edit_token = $out->getUser()->getEditToken();

        $html = '';
        $html .= "<form summarypage-form' id='userpage-edittitle' class='summarypage-form' action='" . $wgArticleUrl . "Special:UserPage' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='changetitle_button" . $counter . "' "
            . "value='" . $out->msg('userpage-changetitle') . "'>";
        $html .= "<input type='hidden' name='old_title_posted" . $counter . "' value = '" . htmlspecialchars($manuscripts_title) . "'>";
        $html .= "<input type='hidden' name='url_old_title_posted" . $counter . "' value = '" . htmlspecialchars($manuscripts_url) . "'>";
        $html .= "<input type='hidden' name='edit_single_page_collection_posted' value = 'edit_single_page_collection_posted'>";
        $html .= "<input type='hidden' name='collection_title' value = '" . $collection_title . "'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        return $html;
    }

    /**
     * This function constructs the edit form for editing metadata.
     * 
     * See https://www.mediawiki.org/wiki/HTMLForm/tutorial for information on the MediaWiki form builder
     */
    public function showEditCollectionMetadata($collection_title, $collection_metadata, $link_back_to_manuscript_page, $error_message = '') {

        global $wgArticleUrl;

        $collection_metadata = $this->HTMLSpecialCharachtersArray($collection_metadata);

        $metatitle = isset($collection_metadata['collections_metatitle']) ? $collection_metadata['collections_metatitle'] : '';
        $metaauthor = isset($collection_metadata['collections_metaauthor']) ? $collection_metadata['collections_metaauthor'] : '';
        $metayear = isset($collection_metadata['collections_metayear']) ? $collection_metadata['collections_metayear'] : '';
        $metapages = isset($collection_metadata['collections_metapages']) ? $collection_metadata['collections_metapages'] : '';
        $metacategory = isset($collection_metadata['collections_metacategory']) ? $collection_metadata['collections_metacategory'] : '';
        $metaproduced = isset($collection_metadata['collections_metaproduced']) ? $collection_metadata['collections_metaproduced'] : '';
        $metaproducer = isset($collection_metadata['collections_metaproducer']) ? $collection_metadata['collections_metaproducer'] : '';
        $metaeditors = isset($collection_metadata['collections_metaeditors']) ? $collection_metadata['collections_metaeditors'] : '';
        $metajournal = isset($collection_metadata['collections_metajournal']) ? $collection_metadata['collections_metajournal'] : '';
        $metajournalnumber = isset($collection_metadata['collections_metajournalnumber']) ? $collection_metadata['collections_metajournalnumber'] : '';
        $metatranslators = isset($collection_metadata['collections_metatranslators']) ? $collection_metadata['collections_metatranslators'] : '';
        $metawebsource = isset($collection_metadata['collections_metawebsource']) ? $collection_metadata['collections_metawebsource'] : '';
        $metaid = isset($collection_metadata['collections_metaid']) ? $collection_metadata['collections_metaid'] : '';
        $metanotes = isset($collection_metadata['collections_metanotes']) ? $collection_metadata['collections_metanotes'] : '';

        $out = $this->out;
        $user_name = $this->user_name;

        $max_length = $this->max_string_formfield_length;

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);

        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderDots();
        $html .= "<div class='javascripthide'>";

        $html .= "<form class='summarypage-form' id='userpage-collection' action='" . $wgArticleUrl . "Special:UserPage' method='post'>";
        $html .= "<input type='submit' class='button-transparent' value='" . $out->msg('userpage-goback') . "'>";
        $html .= "<input type='hidden' name='collection_title' value='" . htmlspecialchars($collection_title) . "'>";
        $html .= "<input type='hidden' name='single_collection_posted' value='single_collection_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        $html .= "<h2>" . $out->msg('userpage-editmetadata') . " " . $collection_title . "</h2>";
        $html .= $out->msg('userpage-optional');
        $html .= "<br><br>";

        if (!empty($error_message)) {
            $html .= "<div class='error'>" . $error_message . "</div>";
        }

        $html .= "</div>";

        $out->addHTML($html);

        $descriptor = array();

        //important! These are posted as 'metadata_', but will appear as 'wpmetadata_' in the request object ! 
        $descriptor['metadata_title'] = array(
          'label-message' => 'metadata-title',
          'class' => 'HTMLTextField',
          'default' => $metatitle,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_author'] = array(
          'label-message' => 'metadata-author',
          'class' => 'HTMLTextField',
          'default' => $metaauthor,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_year'] = array(
          'label-message' => 'metadata-year',
          'class' => 'HTMLTextField',
          'default' => $metayear,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_pages'] = array(
          'label-message' => 'metadata-pages',
          'class' => 'HTMLTextField',
          'default' => $metapages,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_category'] = array(
          'label-message' => 'metadata-category',
          'class' => 'HTMLTextField',
          'default' => $metacategory,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_produced'] = array(
          'label-message' => 'metadata-produced',
          'class' => 'HTMLTextField',
          'default' => $metaproduced,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_producer'] = array(
          'label-message' => 'metadata-producer',
          'class' => 'HTMLTextField',
          'default' => $metaproducer,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_editors'] = array(
          'label-message' => 'metadata-editors',
          'class' => 'HTMLTextField',
          'default' => $metaeditors,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_journal'] = array(
          'label-message' => 'metadata-journal',
          'class' => 'HTMLTextField',
          'default' => $metajournal,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_journalnumber'] = array(
          'label-message' => 'metadata-journalnumber',
          'class' => 'HTMLTextField',
          'default' => $metajournalnumber,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_translators'] = array(
          'label-message' => 'metadata-translators',
          'class' => 'HTMLTextField',
          'default' => $metatranslators,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_websource'] = array(
          'label-message' => 'metadata-websource',
          'class' => 'HTMLTextField',
          'default' => $metawebsource,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_id'] = array(
          'label-message' => 'metadata-id',
          'class' => 'HTMLTextField',
          'default' => $metaid,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_notes'] = array(
          'type' => 'textarea',
          'labelmessage' => 'metadata-notes',
          'default' => $metanotes,
          'rows' => 20,
          'cols' => 20,
          'maxlength' => ($max_length * 10),
        );

        //in case the user was directed here from a manuscript page, send the link back to that manuscript page with the form
        if (!empty($link_back_to_manuscript_page)) {
            $descriptor['hidden'] = array(
              'type' => 'hidden',
              'name' => 'link_back_to_manuscript_page',
              'default' => $link_back_to_manuscript_page,
            );
        }

        $html_form = new HTMLForm($descriptor, $out->getContext());
        $html_form->setSubmitText($out->msg('metadata-submit'));
        $html_form->addHiddenField('collection_title', $collection_title);
        $html_form->addHiddenField('save_metadata_posted', 'save_metadata_posted');
        $html_form->setSubmitCallback(array('SpecialUserPage', 'processInput'));
        $html_form->show();
    }

    /**
     * This function shows a confirmation of the edit after submission of the form, in case the user has reached the page via the link on a manuscript page
     * 
     */
    public function showRedirectBackToManuscriptPageAfterEditMetadata($link_back_to_manuscript_page) {

        global $wgArticleUrl;
        $article_url = $wgArticleUrl;
        $user_name = $this->user_name;
        $out = $this->out;
        $html = "";

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);
        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderDots();
        $html .= "<div class='javascripthide'>";

        $html .= "<p>" . $out->msg('userpage-editcomplete') . "</p>";

        $html .= "<form id='userpage-linkback' action='" . $article_url . $link_back_to_manuscript_page . "' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='linkback' title='" . $out->msg('userpage-linkback1') . "' value='" . $out->msg('userpage-linkback2') . $link_back_to_manuscript_page . "'>";
        $html .= "</form>";

        $html .= "</div>";

        return $out->addHTML($html);
    }

    /**
     * This function shows the form when editing a manuscript title
     * 
     * See https://www.mediawiki.org/wiki/HTMLForm/tutorial for information on the MediaWiki form builder
     */
    public function showEditPageSingleCollectionForm($error_message = '', $collection_title, $manuscript_old_title, $manuscript_url_old_title) {

        global $wgArticleUrl;
        $out = $this->out;
        $user_name = $this->user_name;
        $max_length = $this->max_string_formfield_length;

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);

        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderDots();

        $html .= "<div class='javascripthide'>";

        $html .= "<form class='summarypage-form' id='userpage-collection' action='" . $wgArticleUrl . "Special:UserPage' method='post'>";
        $html .= "<input type='submit' class='button-transparent' value='" . $out->msg('userpage-goback') . "'>";
        $html .= "<input type='hidden' name='collection_title' value='" . htmlspecialchars($collection_title) . "'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "<input type='hidden' name='single_collection_posted' value='single_collection_posted'>";
        $html .= "</form>";

        $html .= "<h2>" . $out->msg('userpage-edittitle') . " " . $manuscript_old_title . "</h2>";
        $html .= $out->msg('userpage-edittitleinstruction');
        $html .= "<br><br>";

        if (!empty($error_message)) {
            $html .= "<div class='error'>" . $error_message . "</div>";
        }

        $html .= "</div>";

        $out->addHTML($html);

        $descriptor = array();

        $descriptor['manuscript_new_title'] = array(
          'label-message' => 'userpage-newmanuscripttitle',
          'class' => 'HTMLTextField',
          'default' => $manuscript_old_title,
          'maxlength' => $max_length,
        );

        $html_form = new HTMLForm($descriptor, $out->getContext());
        $html_form->setSubmitText($out->msg('metadata-submit'));
        $html_form->addHiddenField('collection_title', $collection_title);
        $html_form->addHiddenField('old_title_posted', $manuscript_old_title);
        $html_form->addHiddenField('url_old_title_posted', $manuscript_url_old_title);
        $html_form->addHiddenField('save_new_page_title_collection_posted', 'save_new_collection_title_posted');
        $html_form->addHiddenField('save_page_posted', 'save_page_posted');
        $html_form->setSubmitCallback(array('SpecialUserPage', 'processInput'));
        return $html_form->show();
    }

}
