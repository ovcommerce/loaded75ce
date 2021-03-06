<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  class language {
    var $languages, $catalog_languages, $browser_languages, $language;

    function __construct($lng = '') {
      $this->languages = array('ar' => 'ar([-_][a-zA-Z]{2})?|arabic',
                               'bg' => 'bg|bulgarian',
                               'br' => 'pt[-_]br|brazilian portuguese',
                               'ca' => 'ca|catalan',
                               'cs' => 'cs|czech',
                               'da' => 'da|danish',
                               'de' => 'de([-_][a-zA-Z]{2})?|german',
                               'el' => 'el|greek',
                               'en' => 'en([-_][a-zA-Z]{2})?|english',
                               'es' => 'es([-_][a-zA-Z]{2})?|spanish',
                               'et' => 'et|estonian',
                               'fi' => 'fi|finnish',
                               'fr' => 'fr([-_][a-zA-Z]{2})?|french',
                               'gl' => 'gl|galician',
                               'he' => 'he|hebrew',
                               'hu' => 'hu|hungarian',
                               'id' => 'id|indonesian',
                               'it' => 'it|italian',
                               'ja' => 'ja|japanese',
                               'ko' => 'ko|korean',
                               'ka' => 'ka|georgian',
                               'lt' => 'lt|lithuanian',
                               'lv' => 'lv|latvian',
                               'nl' => 'nl([-_][a-zA-Z]{2})?|dutch',
                               'no' => 'no|norwegian',
                               'pl' => 'pl|polish',
                               'pt' => 'pt([-_][a-zA-Z]{2})?|portuguese',
                               'ro' => 'ro|romanian',
                               'ru' => 'ru|russian',
                               'sk' => 'sk|slovak',
                               'sr' => 'sr|serbian',
                               'sv' => 'sv|swedish',
                               'th' => 'th|thai',
                               'tr' => 'tr|turkish',
                               'uk' => 'uk|ukrainian',
                               'tw' => 'zh[-_]tw|chinese traditional',
                               'zh' => 'zh|chinese simplified');

      $this->catalog_languages = array();
      $languages_query = tep_db_query("select languages_id, name, code, image, directory, locale from " . TABLE_LANGUAGES . " where languages_status = 1  order by IF(code='".tep_db_input(strtolower(DEFAULT_LANGUAGE))."',0,1), sort_order");
      while ($languages = tep_db_fetch_array($languages_query)) {
        $this->catalog_languages[strtolower($languages['code'])] = array('id' => $languages['languages_id'],
                                                             'name' => $languages['name'],
                                                             'image' => $languages['image'],
                                                             'directory' => $languages['directory'],
                                                             'locale' => $languages['locale']);
      }

      $this->browser_languages = '';
      $this->language = '';

      $this->set_language($lng);
    }

    function set_language($language) {
      if ( (tep_not_null($language)) && (isset($this->catalog_languages[strtolower($language)])) ) {
        $this->language = $this->catalog_languages[strtolower($language)];
      } else {
        $this->language = $this->catalog_languages[strtolower(DEFAULT_LANGUAGE)];
      }
    }    

    function get_browser_language() {
      $this->browser_languages = explode(',', getenv('HTTP_ACCEPT_LANGUAGE'));

      for ($i=0, $n=sizeof($this->browser_languages); $i<$n; $i++) {
        reset($this->languages);
        while (list($key, $value) = each($this->languages)) {
          if (preg_match('/^(' . $value . ')(;q=[0-9]\\.[0-9])?$/i', $this->browser_languages[$i]) && isset($this->catalog_languages[$key])) {
            $this->language = $this->catalog_languages[$key];
            break 2;
          }
        }
      }
    }
    
    function set_locale(){
      @setlocale(LC_TIME, $this->language['locale'] . '.UTF-8');
    }
    
    function load_vars(){
      global $languages_id;
      $query = tep_db_query("select configuration_key, configuration_value from " . TABLE_LANGUAGES_FORMATS . " where language_id = '" . (int)$languages_id . "'");
      if (tep_db_num_rows($query)){
        while ($row = tep_db_fetch_array($query)){
          defined($row['configuration_key']) or define($row['configuration_key'], $row['configuration_value']);
        }
      }
    }
  }
