<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\contact;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class Map extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
      $key = tep_db_fetch_array(tep_db_query("select info as setting_code from " . TABLE_GOOGLE_SETTINGS . " where module='mapskey'"));

      $data = Info::platformData();
      $settings_street = Info::widgetSettings('contact\StreetView');
      if ($settings_street[0]['street_address']) {
          $address = $settings_street[0]['street_address'];
		  $addressnosuburb = $addressnocode = $address;
      } else {
          $address =
            $data['street_address'] . ' ' .
            $data['suburb'] .($data['suburb'] ? ' ' : '') .
            $data['city'] . ' ' .
            $data['state'] . ' ' .
            $data['postcode'] . ' ' .
            $data['country'];
          $addressnosuburb =
            $data['street_address'] . ' ' .
            $data['city'] . ' ' .
            $data['state'] . ' ' .
            $data['postcode'] . ' ' .
            $data['country'];	
          $addressnocode =
            $data['street_address'] . ' ' .
			$data['suburb'] .($data['suburb'] ? ' ' : '') .
            $data['city'] . ' ' .
            $data['state'] . ' ' .
            $data['country'];				
      }
	if (isset($data['country_info']['zoom'])){
		$data['country_info']['zoom'] = max($data['country_info']['zoom'], 16); 
	} else {
		$data['country_info']['zoom'] = 16; 
	}
	

    return IncludeTpl::widget(['file' => 'boxes/contact/map.tpl', 'params' => [
      'address' => $address,
	  'addressnosuburb' => $addressnosuburb,
	  'addressnocode' => $addressnocode,
      'country_info' => $data['country_info'],
      'key' => $key['setting_code'],
      'settings_street' => $settings_street
    ]]);

  }
}