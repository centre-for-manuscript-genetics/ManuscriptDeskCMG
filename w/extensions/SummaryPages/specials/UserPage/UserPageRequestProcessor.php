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
class UserPageRequestProcessor extends ManuscriptDeskBaseRequestProcessor {

    public function getDefaultPageData() {
        $request = $this->request;
        $validator = $this->validator;
        $posted_names = $request->getValueNames();
        $offset = 0;

        foreach ($posted_names as $name) {

            if ($name === 'view_manuscripts_posted' || $name === 'view_collections_posted' || $name === 'view_collations_posted' || $name === 'view_stylometricanalysis_posted') {
                $button_name = $name;
            }
            elseif ($name === 'offset') {
                $offset = (int) $validator->validateStringNumber($request->getText($name));

                if ($offset < 0) {
                    throw new \Exception('error-request');
                }
            }
        }

        if (!isset($button_name)) {
            throw new \Exception('error-request');
        }

        return array($button_name, $offset);
    }

    public function singleCollectionPosted() {
        if ($this->request->getText('single_collection_posted') !== '') {
            return true;
        }

        return false;
    }

    public function getLinkBackToManuscriptPage() {
        $request = $this->request;
        $validator = $this->validator;
        $value_name = 'link_back_to_manuscript_page';

        if ($request->getText($value_name) === '') {
            return '';
        }

        return $validator->validateStringUrl($request->getText($value_name));
    }

    public function saveCollectionMetadataPosted() {
        if ($this->request->getText('save_metadata_posted') !== '') {
            return true;
        }

        return false;
    }

    public function getAndValidateSavedCollectionMetadata() {
        $request = $this->request;
        $validator = $this->validator;
        $posted_names = $request->getValueNames();
        $saved_metadata = array();

        foreach ($posted_names as $formfield) {

            if (strpos($formfield, 'wpmetadata') !== false) {
                $saved_metadata [$formfield] = $validator->validateSavedCollectionMetadataField($request->getText($formfield), $formfield);
            }
        }

        return $saved_metadata;
    }

    public function editMetadataPosted() {
        if ($this->request->getText('edit_metadata_posted') !== '') {
            return true;
        }

        return false;
    }

    public function editSinglePageCollectionPosted() {
        if ($this->request->getText('edit_single_page_collection_posted') !== '') {
            return true;
        }

        return false;
    }

    public function changeSignatureManuscriptPosted() {
        $request = $this->request;
        if ($request->getText('change_signature_manuscript_posted') !== '') {
            return true;
        }

        return false;
    }

    public function getManuscriptSignatureChangeData() {
        $request = $this->request;
        $validator = $this->validator;
        $signature = $validator->validateString($request->getText('change_signature_manuscript_posted'));

        if ($signature !== 'public' && $signature !== 'private') {
            throw new \Exception('error-request');
        }

        $partial_url = $validator->validateStringUrl($request->getText('partial_url'));

        $button_name = $request->getText('button_name');

        if ($button_name !== 'view_manuscripts_posted') {
            throw new \Exception('error-request');
        }

        $offset = (int) $validator->validateStringNumber($request->getText('offset'));

        return array($partial_url, $signature, $button_name, $offset);
    }

    public function changeSignatureCollectionPagePosted() {
        $request = $this->request;
        if ($request->getText('change_signature_collection_page_posted') !== '') {
            return true;
        }

        return false;
    }

    public function changeSignatureCollationPosted() {
        $request = $this->request;
        if ($request->getText('change_signature_collation_posted') !== '') {
            return true;
        }

        return false;
    }

    public function changeSignatureStylometricAnalysisPosted() {
        $request = $this->request;
        if ($request->getText('change_signature_stylometricanalysis_posted') !== '') {
            return true;
        }

        return false;
    }

    public function getCollectionPageSignatureChangeData() {
        $request = $this->request;
        $validator = $this->validator;
        $signature = $validator->validateString($request->getText('change_signature_collection_page_posted'));

        if ($signature !== 'public' && $signature !== 'private') {
            throw new \Exception('error-request');
        }

        $partial_url = $validator->validateStringUrl($request->getText('partial_url'));
        $collection_title = $this->getCollectionTitle();
        return array($partial_url, $signature, $collection_title);
    }

    public function getCollationSignatureChangeData() {
        $request = $this->request;
        $validator = $this->validator;
        $signature = $validator->validateString($request->getText('change_signature_collation_posted'));

        if ($signature !== 'public' && $signature !== 'private') {
            throw new \Exception('error-request');
        }

        $partial_url = $validator->validateStringUrl($request->getText('partial_url'));

        $button_name = $request->getText('button_name');

        if ($button_name !== 'view_collations_posted') {
            throw new \Exception('error-request');
        }

        $offset = (int) $validator->validateStringNumber($request->getText('offset'));
        return array($partial_url, $signature, $button_name, $offset);
    }

    public function getStylometricAnalysisSignatureChangeData() {
        $request = $this->request;
        $validator = $this->validator;
        $signature = $validator->validateString($request->getText('change_signature_stylometricanalysis_posted'));

        if ($signature !== 'public' && $signature !== 'private') {
            throw new \Exception('error-request');
        }

        $partial_url = $validator->validateStringUrl($request->getText('partial_url'));

        $button_name = $request->getText('button_name');

        if ($button_name !== 'view_stylometricanalysis_posted') {
            throw new \Exception('error-request');
        }

        $offset = (int) $validator->validateStringNumber($request->getText('offset'));
        return array($partial_url, $signature, $button_name, $offset);
    }

    public function getEditSinglePageCounter() {

        $request = $this->request;
        $pattern = 'changetitle_button';
        $counter = '';

        foreach ($request->getValueNames() as $value) {
            if (strpos($value, $pattern) !== false) {
                (int) $counter = str_replace($pattern, '', $value);
            }
        }

        return $counter;
    }

    public function getEditSinglePageCollectionData($counter = '') {
        $request = $this->request;
        $validator = $this->validator;

        $old_title = $validator->validateString($request->getText('old_title_posted' . $counter));
        $url_old_title = $validator->validateStringUrl($request->getText('url_old_title_posted' . $counter));

        if (empty($old_title) || empty($url_old_title)) {
            throw new \Exception('error-request');
        }

        return array($old_title, $url_old_title);
    }

    public function saveNewPageTitleCollectionPosted() {
        if ($this->request->getText('save_new_page_title_collection_posted') !== '') {
            return true;
        }

        return false;
    }

    public function getManuscriptNewTitleData() {
        $validator = $this->validator;
        $request = $this->request;
        return $validator->validateString($request->getText('wpmanuscript_new_title'));
    }

}
